<?php
/**
 * WT Multicategories
 *
 * @package    System - WT Multicategories
 * @version     1.0.1
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Traits;

use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;

use function defined;
use function is_numeric;
use function count;
use function is_array;

defined('_JEXEC') or die;

trait ItemsFinderTrait
{
    use DatabaseAwareTrait;

    /**
     * @param   QueryInterface  $query
     * @param   int             $field_id
     * @param   int|array       $cat_id
     *
     *
     * @since 1.0.0
     */
    public function findItemsByFieldValue(QueryInterface $query, int $field_id, int|array $cat_id): QueryInterface
    {
        $db       = $this->getDatabase();
        $subQuery = $db->getQuery(true)
                       ->select($db->quoteName('fv.item_id'))
                       ->from($db->quoteName('#__fields_values', 'fv'))
                       ->where($db->quoteName('fv.field_id') . ' = ' . $db->quote($field_id));

        if (is_numeric($cat_id))
        {
            $subQuery->where($db->quoteName('fv.value') . ' = ' . $db->quote($cat_id));
        }
        elseif (is_array($cat_id) && (count($cat_id) > 0))
        {
            $cat_id = ArrayHelper::toInteger($cat_id);

            if (!empty($cat_id))
            {
                $subQuery->whereIn($db->quoteName('fv.value'), $cat_id);
            }
        }

        $query->extendWhere(
            'OR',
            $db->quoteName('a.id') . ' IN (' . $subQuery . ')'
        );

        return $query;
    }
}