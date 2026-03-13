<?php
/**
 * Override getListQuery method for Joomla\Component\Contact\Site\Model\CategoryModel
 *
 * @package    System - WT Multicategories
 * @version     1.2.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Model;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Contact\Site\Model\CategoryModel as ContactCategoryModel;
use Joomla\Database\ParameterType;
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
        $user   = $this->getCurrentUser();
        $groups = $user->getAuthorisedViewLevels();
        $db     = $this->getDatabase();

        /** @var \Joomla\Database\DatabaseQuery $query */
        $query = $db->getQuery(true);

        $query->select($this->getState('list.select', 'a.*'))
            ->select(
                'CASE WHEN CHAR_LENGTH(' . $db->quoteName('a.alias') . ')'
                . ' THEN CONCAT_WS(\':\', ' . $db->quoteName('a.id') . ', ' . $db->quoteName('a.alias') . ')'
                . ' ELSE ' . $db->quoteName('a.id') . ' END AS ' . $db->quoteName('slug')
            )
            ->select(
                'CASE WHEN CHAR_LENGTH(' . $db->quoteName('c.alias') . ')'
                . ' THEN CONCAT_WS(\':\', ' . $db->quoteName('c.id') . ', ' . $db->quoteName('c.alias') . ')'
                . ' ELSE ' . $db->quoteName('c.id') . ' END AS ' . $db->quoteName('catslug')
            )
            ->from($db->quoteName('#__contact_details', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
            ->whereIn($db->quoteName('a.access'), $groups);

        $plugin = PluginHelper::getPlugin('system', 'wtmulticategories');
        $pluginParams = new Registry($plugin->params);
        $fieldId = (int) trim($pluginParams->get('multicategories_com_contact_field_id', 0));
        $categoryId = (int) $this->getState('category.id');
        $includeSubcategories = (int) $this->getState('filter.max_category_levels', 1) !== 0;
        $levels = (int) $this->getState('filter.max_category_levels', 1);

        if ($fieldId > 0 && $categoryId > 0)
        {
            $query = $this->applyMappedCategoryFilter(
                $query,
                $fieldId,
                $categoryId,
                'com_contact.contact',
                '#__contact_details',
                'id',
                'catid',
                'com_contact',
                $includeSubcategories,
                $levels
            );
        }
        elseif ($includeSubcategories)
        {
            $subQuery = $db->getQuery(true)
                ->select($db->quoteName('sub.id'))
                ->from($db->quoteName('#__categories', 'sub'))
                ->join(
                    'INNER',
                    $db->quoteName('#__categories', 'this'),
                    $db->quoteName('sub.lft') . ' > ' . $db->quoteName('this.lft')
                    . ' AND ' . $db->quoteName('sub.rgt') . ' < ' . $db->quoteName('this.rgt')
                )
                ->where($db->quoteName('this.id') . ' = :subCategoryId');

            $query->bind(':subCategoryId', $categoryId, ParameterType::INTEGER);

            if ($levels >= 0)
            {
                $subQuery->where($db->quoteName('sub.level') . ' <= ' . $db->quoteName('this.level') . ' + :levels');
                $query->bind(':levels', $levels, ParameterType::INTEGER);
            }

            $query->where(
                '(' . $db->quoteName('a.catid') . ' = :categoryId OR ' . $db->quoteName('a.catid') . ' IN (' . $subQuery . '))'
            );
            $query->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        }
        else
        {
            $query->where($db->quoteName('a.catid') . ' = :acatid')
                ->whereIn($db->quoteName('c.access'), $groups);
            $query->bind(':acatid', $categoryId, ParameterType::INTEGER);
        }

        $query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
            ->select('ua.email AS author_email')
            ->leftJoin($db->quoteName('#__users', 'ua') . ' ON ua.id = a.created_by')
            ->leftJoin($db->quoteName('#__users', 'uam') . ' ON uam.id = a.modified_by');

        $state = $this->getState('filter.published');

        if (is_numeric($state))
        {
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $state, ParameterType::INTEGER);
        }
        else
        {
            $query->whereIn($db->quoteName('c.published'), [0, 1, 2]);
        }

        $nowDate = Factory::getDate()->toSql();

        if ($this->getState('filter.publish_date'))
        {
            $query->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :publish_up)')
                ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :publish_down)')
                ->bind(':publish_up', $nowDate)
                ->bind(':publish_down', $nowDate);
        }

        $search = $this->getState('list.filter');

        if (!empty($search))
        {
            $search = '%' . trim($search) . '%';
            $query->where($db->quoteName('a.name') . ' LIKE :name ');
            $query->bind(':name', $search);
        }

        if ($this->getState('filter.language'))
        {
            $query->whereIn($db->quoteName('a.language'), [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        if ($this->getState('list.ordering') === 'sortname')
        {
            $query->order($db->escape('a.sortname1') . ' ' . $db->escape($this->getState('list.direction', 'ASC')))
                ->order($db->escape('a.sortname2') . ' ' . $db->escape($this->getState('list.direction', 'ASC')))
                ->order($db->escape('a.sortname3') . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
        }
        elseif ($this->getState('list.ordering') === 'featuredordering')
        {
            $query->order($db->escape('a.featured') . ' DESC')
                ->order($db->escape('a.ordering') . ' ASC');
        }
        else
        {
            $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
        }

        return $query;
    }

}
