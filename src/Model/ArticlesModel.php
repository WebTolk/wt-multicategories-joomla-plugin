<?php
/**
 * Override getListQuery method for Joomla\Component\Content\Site\Model\ArticlesModel
 *
 * @package    System - WT Multicategories
 * @version     1.0.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Model;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Site\Model\ArticlesModel as BaseArticlesModel;
use Joomla\Database\QueryInterface;
use Joomla\Plugin\System\Wtmulticategories\Traits\ItemsFinderTrait;
use Joomla\Registry\Registry;

use function defined;
use function explode;
use function is_string;
use function str_contains;
use function trim;

defined('_JEXEC') or die;

/**
 * This models supports retrieving lists of articles.
 *
 * @since  1.6
 */
class ArticlesModel extends BaseArticlesModel
{
    use ItemsFinderTrait;

    protected $context = 'com_content.articles';
    protected $filterFormName = 'filter_articles';

    /**
     * Get the master query for retrieving a list of articles subject to the model state.
     *
     * @return  QueryInterface
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $query = parent::getListQuery();

        $plugin = PluginHelper::getPlugin('system','wtmulticategories');
        $plugin_params = new Registry($plugin->params);
        $multicategories_com_content_field_id = trim($plugin_params->get('multicategories_com_content_field_id',0));

        if($multicategories_com_content_field_id > 0 )
        {
            // Filter by a single or group of categories
            $categoryId = $this->getState('filter.category_id');
            $query = $this->findItemsByFieldValue($query, $multicategories_com_content_field_id, $categoryId);
        }

        return $query;

    }

}
