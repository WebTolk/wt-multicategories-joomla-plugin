<?php
/**
 * @package    System - WT Multicategories
 * @version     1.2.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Extension;

use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Content\Administrator\Model\ArticlesModel as AdminArticlesModel;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Component\Contact\Site\Model\CategoryModel as ContactCategoryModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wtmulticategories\Console\RebuildMappingsCommand;
use Joomla\Plugin\System\Wtmulticategories\Service\MappingService;
use function in_array;
use function strtolower;
use function ucfirst;
use function defined;

defined('_JEXEC') or die;

/**
 * Main Wtmulticategories class
 *
 * @since  1.0.0
 */
class Wtmulticategories extends CMSPlugin implements SubscriberInterface
{
    /**
     * Allowed extensions for to override a List Model getListQuery method for category view
     *
     * @var string[]
     * @since 1.0.0
     */
    private static $allowedExtensions = ['content', 'contact'];

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
            'onAfterExtensionBoot' => 'onAfterExtensionBoot',
            'onContentAfterSave'   => 'onContentAfterSave',
            'onContentAfterDelete' => 'onContentAfterDelete',
        ];
    }

    /**
     * Register CLI commands.
     *
     * @return  void
     *
     * @since 1.3.0
     */
    public function registerCommands(): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('cli'))
        {
            return;
        }

        $app->addCommand(new RebuildMappingsCommand());
    }


    /**
     * Trigger from bootExtension() method of ExtensionManagerTrait
     *
     * @param   EventInterface  $event
     *
     * @see   \Joomla\CMS\Extension\ExtensionManagerTrait
     * @since 1.0.0
     */
    public function onAfterExtensionBoot(EventInterface $event)
    {
        // Work only on frontend
	    if (!$this->getApplication()->isClient('site') && !($this->getApplication()->isClient('administrator') && $this->params->get('work_in_admin', false)))
	    {
		    return;
	    }

        // Test that a component is being booted.
        if ($event->getArgument('type') !== ComponentInterface::class)
        {
            return;
        }

        // Test that this is com_content or com_contact component.
        if (!in_array(strtolower($event->getArgument('extensionName')), self::$allowedExtensions))
        {
            return;
        }

        // Get the service container.
        $container = $event->getArgument('container');

        if (!($container instanceof Container))
        {
            return;
        }


        // Check that MVC factory is used and can be overridden.
        if (!$container->has(MVCFactoryInterface::class) || $container->isProtected(MVCFactoryInterface::class))
        {
            return;
        }

        $extensionName = ucfirst($event->getArgument('extensionName'));

        // Register the custom MVC factory. Here an anonymous class is used,
        // but you can use a concrete class.

        $container->set(
            MVCFactoryInterface::class,
            static function () use ($container, $extensionName) {
                $factory = new class ('Joomla\\Component\\' . $extensionName) extends MVCFactory {
                    protected function getClassName(string $suffix, string $prefix)
                    {
                        $class = parent::getClassName($suffix, $prefix);

                        switch ($class)
                        {
                            case ArticlesModel::class :
                                return 'Joomla\Plugin\System\Wtmulticategories\Model\ArticlesModel';
                                break;
                            case AdminArticlesModel::class :
                                return 'Joomla\Plugin\System\Wtmulticategories\Model\AdminArticlesModel';
                                break;
                            case ContactCategoryModel::class :
                                return 'Joomla\Plugin\System\Wtmulticategories\Model\CategoryModel';
                                break;
                            default:
                                return $class;
                                break;
                        }
                    }
                };

                $factory->setFormFactory($container->get(FormFactoryInterface::class));
                $factory->setDispatcher($container->get(DispatcherInterface::class));
                $factory->setDatabase($container->get(DatabaseInterface::class));
                $factory->setSiteRouter($container->get(SiteRouter::class));
                $factory->setCacheControllerFactory($container->get(CacheControllerFactoryInterface::class));
                $factory->setUserFactory($container->get(UserFactoryInterface::class));
                $factory->setMailerFactory($container->get(MailerFactoryInterface::class));

                return $factory;
            }
        );
    }

    /**
     * Sync mappings after content save.
     *
     * @param   Model\AfterSaveEvent  $event
     *
     * @return  void
     *
     * @since 1.2.0
     */
    public function onContentAfterSave(Model\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();
        $data    = $event->getData();

        if (empty($item->id))
        {
            return;
        }

        $service = $this->getMappingService();

        if ($context === 'com_content.article')
        {
            $fieldId     = (int) $this->params->get('multicategories_com_content_field_id', 0);
            $fieldValues = $service->extractFieldValuesFromData($data, $fieldId, $context);

            $service->rebuildItemMappings((int) $item->id, $context, $fieldId, $fieldValues);
        }
        elseif ($context === 'com_contact.contact')
        {
            $fieldId     = (int) $this->params->get('multicategories_com_contact_field_id', 0);
            $fieldValues = $service->extractFieldValuesFromData($data, $fieldId, $context);

            $service->rebuildItemMappings((int) $item->id, $context, $fieldId, $fieldValues);
        }
    }

    /**
     * Cleanup mappings after content delete.
     *
     * @param   Model\AfterDeleteEvent  $event
     *
     * @return  void
     *
     * @since 1.2.0
     */
    public function onContentAfterDelete(Model\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        if (empty($item->id) || !in_array($context, ['com_content.article', 'com_contact.contact'], true))
        {
            return;
        }

        $this->getMappingService()->deleteItemMappings((int) $item->id, $context);
    }

    /**
     * Build mapping service instance.
     *
     * @return  MappingService
     *
     * @since 1.2.0
     */
    private function getMappingService(): MappingService
    {
        $service = new MappingService();
        $service->setDatabase(Factory::getContainer()->get(DatabaseDriver::class));

        return $service;
    }
}
