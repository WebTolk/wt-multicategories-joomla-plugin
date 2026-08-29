# Анализ: `UserFactory not set` при включенном WT Multicategories без выбранного поля контактов

## Контекст

- ProcessForge snapshot: `ctx-20260829-060006-9fc02e`.
- Платформа: `platform.joomla`.
- Процесс: `software-feature-development`.
- Специализация: `specialization.fullstack`.
- Режим задачи: read-only, код не изменялся.

## Использованные знания и источники

- Локальные Joomla-знания: `joomla-toolkit/README.md`, `joomla-extension-structures.md`.
- Локальный Joomla core source: `Joomla-core/6.x/6.1.2`.
- Проектный код: `src/Extension/Wtmulticategories.php`, `src/Model/CategoryModel.php`, `src/Model/ArticlesModel.php`, `src/Model/AdminArticlesModel.php`, `services/provider.php`, `wtmulticategories.xml`, `README.md`.
- GitHub issue: <https://github.com/WebTolk/wt-multicategories-joomla-plugin/issues/2>.
- Public plugin description: <https://web-tolk.ru/en/dev/joomla-plugins/wt-multicategories>.

## Что подтверждено по issue #2

Issue #2 описывает падение одиночной страницы контакта, если у контакта есть связанный Joomla user. Ошибка:

```text
UserFactory not set in Joomla\Component\Contact\Site\View\Contact\HtmlView
```

В комментариях подтверждено, что страницы контактов без связанного пользователя открываются, а страницы со связанным пользователем падают. Issue закрыт 2026-03-13 с комментарием `Fixed in 1.2.0`.

## Как Joomla приходит к этой ошибке

В Joomla core `components/com_contact/src/View/Contact/HtmlView.php`:

- `HtmlView` реализует `UserFactoryAwareInterface`;
- использует `UserFactoryAwareTrait`;
- на строке `344` вызывает `getUserFactory()->loadUserById(...)`, когда включен параметр `show_user_custom_fields` и у контакта есть `user_id`.

В Joomla core `libraries/src/User/UserFactoryAwareTrait.php`:

- `getUserFactory()` выбрасывает `UnexpectedValueException` с текстом `UserFactory not set in ...`, если фабрика пользователя не была установлена.

В Joomla core `libraries/src/MVC/Factory/MVCFactory.php`:

- `createView()` создает view;
- затем вызывает `setUserFactoryOnObject($view)`;
- если view реализует `UserFactoryAwareInterface`, в него должна быть передана фабрика пользователей.

Следовательно, одиночная страница контакта со связанным пользователем является естественным smoke-test для корректной MVCFactory компонента `com_contact`: если фабрика была заменена и зависимости переданы неполно, ошибка проявляется именно там.

## Что делает WT Multicategories сейчас

В `src/Extension/Wtmulticategories.php`:

- `self::$allowedExtensions = ['content', 'contact']`;
- `onAfterExtensionBoot()` срабатывает для `ComponentInterface`;
- если расширение `content` или `contact`, плагин получает контейнер компонента;
- затем на `MVCFactoryInterface` выполняется `container->set(...)`;
- создается анонимный класс-наследник `Joomla\CMS\MVC\Factory\MVCFactory`;
- внутри `getClassName()` подменяются:
  - `Joomla\Component\Content\Site\Model\ArticlesModel`;
  - `Joomla\Component\Content\Administrator\Model\ArticlesModel`;
  - `Joomla\Component\Contact\Site\Model\CategoryModel`.

В текущем исходнике уже есть важный фрагмент:

```php
$factory->setUserFactory($container->get(UserFactoryInterface::class));
```

Это похоже на исправление из версии `1.2.0`: новая фабрика получает `UserFactory`, и обычный механизм Joomla сможет передать ее в `Contact\HtmlView`.

Но подмена `MVCFactoryInterface` выполняется без проверки, выбран ли параметр `multicategories_com_contact_field_id`.

## Почему проблема возникает при пустом поле контактов

Параметр `multicategories_com_contact_field_id` проверяется поздно, только внутри `src/Model/CategoryModel.php`:

- `getListQuery()` читает параметр на строке `116`;
- дополнительная multicategory-логика запускается только при `$fieldId > 0 && $categoryId > 0`.

Это защищает только SQL-логику `Contact\CategoryModel`. Но к этому моменту плагин уже заменил MVCFactory всего компонента `com_contact`.

Иными словами:

1. Пользователь не выбрал поле дополнительных категорий контактов.
2. С точки зрения бизнес-логики WT Multicategories для `com_contact` делать нечего.
3. Но `onAfterExtensionBoot()` все равно заменяет MVCFactory компонента `com_contact`.
4. Эта фабрика участвует не только в списках категорий контактов, но и в создании одиночного `Contact\HtmlView`.
5. Если установленная версия/собранный пакет/ветка на стенде не содержит полного набора dependency setters или Joomla получает неполностью настроенную фабрику, связанный пользователь в контакте приводит к `UserFactory not set`.

Даже если текущий исходник с `setUserFactory()` не воспроизводит старое падение напрямую, архитектурная причина остается: плагин вмешивается в `com_contact`, когда его функциональность для контактов отключена параметрами.

## Причина

Корневая причина: слишком широкая и безусловная подмена `MVCFactoryInterface` для `com_contact` в `onAfterExtensionBoot()`.

Проверка `multicategories_com_contact_field_id` находится не на границе вмешательства в компонент, а внутри переопределенной модели. Поэтому настройка "поле не выбрано" не отключает самую рискованную часть плагина: замену фабрики MVC компонента Contact.

## Рекомендуемое решение

Перед `container->set(MVCFactoryInterface::class, ...)` нужно определить, нужна ли подмена фабрики для конкретного компонента.

Предлагаемая логика:

- для `com_contact` подменять MVCFactory только если `(int) $this->params->get('multicategories_com_contact_field_id', 0) > 0`;
- для frontend `com_content` подменять MVCFactory только если `(int) $this->params->get('multicategories_com_content_field_id', 0) > 0`;
- для administrator `com_content` учитывать оба условия: `work_in_admin = true` и content field id `> 0`;
- если для текущего компонента нет включенной функции multicategory, возвращаться из `onAfterExtensionBoot()` до доступа к `MVCFactoryInterface`.

Дополнительно стоит сузить mapping классов по компоненту:

- при `extensionName = content` маппить только content models;
- при `extensionName = contact` маппить только `Contact\Site\Model\CategoryModel`;
- не держать contact mapping в фабрике content и наоборот.

Минимальный фикс: ранний guard по параметрам. Более чистый фикс: отдельный расчет `shouldOverrideMvcFactory($extensionName, $client)` и компонентно-специфичный список model replacements.

## Что не стоит делать

- Не решать это только повторным добавлением `setUserFactory()`: в текущем исходнике он уже есть.
- Не оставлять подмену `com_contact` активной при пустом `multicategories_com_contact_field_id`: это сохраняет ненужный риск для одиночных contact views.
- Не переносить проверку поля глубже в SQL-запрос: она уже поздняя и не защищает MVCFactory.

## Проверка на Joomla.local после реализации

Использовать тестовый стенд `Joomla.local`.

Минимальная матрица:

1. Плагин включен, `multicategories_com_contact_field_id` пустой, у контакта есть связанный Joomla user, `show_user_custom_fields` включен: одиночная страница контакта открывается без `UserFactory not set`.
2. Плагин включен, `multicategories_com_contact_field_id` пустой: категория контактов работает как штатный `com_contact`, без multicategory join.
3. Плагин включен, `multicategories_com_contact_field_id` задан: категория контактов учитывает дополнительные категории, одиночная страница контакта со связанным user продолжает открываться.
4. Плагин включен, `multicategories_com_content_field_id` задан: категории материалов продолжают учитывать дополнительные категории.
5. `work_in_admin = 0`: админский список материалов не должен получать ненужную подмену модели.
6. `work_in_admin = 1` и content field id задан: админский список материалов сохраняет расширенную фильтрацию.

## Вывод

Проблема не в том, что модель контактов неправильно обрабатывает пустой field id. Проблема раньше: плагин заменяет MVCFactory компонента `com_contact` уже при одном факте включенного плагина. При пустом `multicategories_com_contact_field_id` эта замена должна вообще не выполняться.
