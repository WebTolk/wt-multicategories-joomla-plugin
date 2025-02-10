<?php
/**
 * Override getListQuery method for Joomla\Component\Contact\Site\Model\CategoryModel
 *
 * @package    System - WT Multicategories
 * @version     1.0.1
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Model;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Contact\Site\Model\CategoryModel as ContactCategoryModel;
use Joomla\Database\QueryInterface;
use Joomla\Plugin\System\Wtmulticategories\Traits\ItemsFinderTrait;
use Joomla\Registry\Registry;

use function defined;
use function trim;

defined('_JEXEC') or die;

/**
 * Single category item model for a contact
 *
 * @package    System - WT Multicategories
 * @subpackage  com_contact
 * @since       1.5
 */
class CategoryModel extends ContactCategoryModel
{
    use ItemsFinderTrait;

    /**
     * Category item data
     *
     * @var    CategoryNode
     */
    protected $_item;

    /**
     * Category left and right of this one
     *
     * @var    CategoryNode[]|null
     */
    protected $_siblings;

    /**
     * Array of child-categories
     *
     * @var    CategoryNode[]|null
     */
    protected $_children;

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     */
    protected $_parent;

    /**
     * The category that applies.
     *
     * @var    object
     */
    protected $_category;

    /**
     * The list of other contact categories.
     *
     * @var    array
     */
    protected $_categories;


    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  QueryInterface    An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $query = parent::getListQuery();

        $plugin = PluginHelper::getPlugin('system','wtmulticategories');
        $plugin_params = new Registry($plugin->params);
        $multicategories_com_content_field_id = trim($plugin_params->get('multicategories_com_contact_field_id',0));

        if($multicategories_com_content_field_id > 0 )
        {
            // Filter by a single or group of categories
            $categoryId = $this->getState('category.id');
            $query = $this->findItemsByFieldValue($query, $multicategories_com_content_field_id, $categoryId);
        }
        return $query;
    }

}
