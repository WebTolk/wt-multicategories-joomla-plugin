<?php
/**
 * @package    System - WT Multicategories
 * @version     1.1.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wtmulticategories\Extension\Wtmulticategories;

defined('_JEXEC') or die;

return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		/**
		 * Set a resource to the container. If the value is null the resource is removed.
		 *
		 * @param   string   $key        Name of resources key to set.
		 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
		 * @param   boolean  $shared     True to create and store a shared instance.
		 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
		 */
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$subject = $container->get(DispatcherInterface::class);
				$config  = (array) PluginHelper::getPlugin('system', 'wtmulticategories');
				$plugin = new Wtmulticategories($subject, $config);
				$plugin->setApplication(\Joomla\CMS\Factory::getApplication());
				return $plugin;
			}
		);
	}
};
