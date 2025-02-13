<?php
/**
 * @package    System - WT Multicategories
 * @version     1.1.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Content\Administrator\Model\ArticlesModel as AdminArticlesModel;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Component\Contact\Site\Model\CategoryModel as ContactCategoryModel;
use Joomla\DI\Container;
use Joomla\Event\EventInterface;
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
            'onAfterExtensionBoot' => 'onAfterExtensionBoot',
        ];
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
            static fn() => new class ('Joomla\\Component\\' . $extensionName) extends MVCFactory {
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
            }
        );
    }
}
