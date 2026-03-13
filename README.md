# WT Multicategories Joomla plugin

## English

WT Multicategories lets Joomla articles and contacts appear in more than one category. Additional categories are read from a custom field selected in the plugin settings.

The plugin stores these relations in a dedicated `#__wtmulticategories_map` table and uses that mapping in category queries for `com_content` and `com_contact`. This keeps category listings predictable and avoids reading custom field values directly in every frontend query.

Use cases:
- show one article in several Joomla categories without duplicating content
- show one contact in several contact categories
- optionally extend category filtering in the administrator articles list

Canonical item URLs still use the primary category. The plugin changes category listings, not article or contact routing.

For easier category selection in custom fields, you can use the **WT Category custom field plugin**:
- [Website](https://web-tolk.ru/en/dev/joomla-plugins/wt-category-plagin-polzovatelskogo-polya-joomla)
- [Joomla Extensions Directory](https://extensions.joomla.org/extension/authoring-a-content/custom-fields/wt-category/)
- [GitHub](https://github.com/WebTolk/WT-Category-Joomla-custom-field-plugin-for-categories-selection)

### CLI rebuild command

If you update from `v.1.1.0`, existing materials and contacts are not rebuilt automatically. Use the CLI command to regenerate the mapping table for already saved items:

```bash
/path/to/php /path/to/joomla/cli/joomla.php wtmulticategories:rebuild-map
```

### For developers

The plugin overrides Joomla MVC models and factory resolution for:
- `Joomla\Component\Content\Site\Model\ArticlesModel`
- `Joomla\Component\Content\Administrator\Model\ArticlesModel`
- `Joomla\Component\Contact\Site\Model\CategoryModel`

The plugin injects additional joins into category list queries and syncs the mapping table on save and delete events.

## Русский

WT Multicategories позволяет показывать материалы и контакты Joomla сразу в нескольких категориях. Дополнительные категории читаются из пользовательского поля, выбранного в настройках плагина.

Плагин хранит эти связи в отдельной таблице `#__wtmulticategories_map` и использует ее в запросах категорий для `com_content` и `com_contact`. Это делает выборки категорий предсказуемыми и избавляет от чтения значений поля напрямую в каждом frontend-запросе.

Основные сценарии:
- показ одного материала сразу в нескольких категориях Joomla без дублирования контента
- показ одного контакта сразу в нескольких категориях контактов
- опциональная поддержка фильтрации по категориям в списке материалов панели администратора

Канонические URL материалов и контактов по-прежнему строятся по основной категории. Плагин меняет выборки в категориях, а не роутинг элементов.

Для более удобного выбора категорий в пользовательских полях можно использовать плагин поля **WT Category**:
- [Сайт](https://web-tolk.ru/dev/joomla-plugins/wt-category-plagin-polzovatelskogo-polya-joomla)
- [Joomla Extensions Directory](https://extensions.joomla.org/extension/authoring-a-content/custom-fields/wt-category/)
- [GitHub](https://github.com/WebTolk/WT-Category-Joomla-custom-field-plugin-for-categories-selection)

### CLI-команда пересборки

Если вы обновляетесь с `v.1.1.0`, уже существующие материалы и контакты автоматически не пересобираются. Для заполнения таблицы маппинга для ранее сохраненных элементов используйте CLI-команду:

```bash
/path/to/php /path/to/joomla/cli/joomla.php wtmulticategories:rebuild-map
```

### Для разработчиков

Плагин переопределяет MVC-модели Joomla и логику разрешения factory для:
- `Joomla\Component\Content\Site\Model\ArticlesModel`
- `Joomla\Component\Content\Administrator\Model\ArticlesModel`
- `Joomla\Component\Contact\Site\Model\CategoryModel`

Плагин добавляет дополнительные `JOIN` в запросы списков категорий и синхронизирует таблицу маппинга при сохранении и удалении элементов.