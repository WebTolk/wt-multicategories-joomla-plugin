<?php
/**
 * WT Multicategories
 *
 * @package    System - WT Multicategories
 * @version     1.2.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Traits;

use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Plugin\System\Wtmulticategories\Service\MappingService;
use Joomla\Utilities\ArrayHelper;

use function array_filter;
use function array_unique;
use function defined;
use function count;
use function implode;
use function is_array;
use function is_numeric;
use function sort;

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
     * @since 1.2.0
     */
    public function applyMappedCategoryFilter(
        QueryInterface $query,
        int $field_id,
        $cat_id,
        string $context,
        string $itemTable,
        string $itemIdColumn,
        string $itemCategoryColumn,
        string $categoryExtension,
        bool $includeSubcategories = false,
        ?int $levels = null
    ): QueryInterface
    {
        $categoryIds = $this->normalizeCategoryIds($cat_id);

        if (empty($categoryIds))
        {
            return $query;
        }

        $categoryIds = $this->expandCategoryIds($categoryIds, $categoryExtension, $includeSubcategories, $levels);

        if (empty($categoryIds))
        {
            $query->where('1 = 0');

            return $query;
        }

        $db = $this->getDatabase();
        $baseQuery = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName($itemIdColumn, 'id'))
            ->from($db->quoteName($itemTable))
            ->where($db->quoteName($itemCategoryColumn) . ' IN (' . implode(',', $categoryIds) . ')');
        $mappedQuery = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('m.item_id', 'id'))
            ->from($db->quoteName(MappingService::TABLE, 'm'))
            ->where($db->quoteName('m.item_context') . ' = ' . $db->quote($context))
            ->where($db->quoteName('m.field_id') . ' = ' . (int) $field_id)
            ->where($db->quoteName('m.category_id') . ' IN (' . implode(',', $categoryIds) . ')');

        $query->innerJoin('(' . $baseQuery . ' UNION ' . $mappedQuery . ') AS ' . $db->quoteName('wtmc') . ' ON ' . $db->quoteName('wtmc.id') . ' = ' . $db->quoteName('a.id'));

        return $query;
    }

    /**
     * @param   int[]    $categoryIds            Category ids.
     * @param   string   $categoryExtension      Category extension.
     * @param   bool     $includeSubcategories   Include descendants.
     * @param   int|null $levels                 Max category depth relative to base category.
     *
     * @return  int[]
     *
     * @since 1.2.0
     */
    private function expandCategoryIds(array $categoryIds, string $categoryExtension, bool $includeSubcategories, ?int $levels): array
    {
        if (empty($categoryIds))
        {
            return [];
        }

        if (!$includeSubcategories)
        {
            return $categoryIds;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('base.id'),
                $db->quoteName('base.lft'),
                $db->quoteName('base.rgt'),
                $db->quoteName('base.level'),
            ])
            ->from($db->quoteName('#__categories', 'base'))
            ->where($db->quoteName('base.extension') . ' = ' . $db->quote($categoryExtension))
            ->where($db->quoteName('base.id') . ' IN (' . implode(',', $categoryIds) . ')');

        $baseCategories = $db->setQuery($query)->loadObjectList();

        if (empty($baseCategories))
        {
            return [];
        }

        $where = [];

        foreach ($baseCategories as $baseCategory)
        {
            $condition = '('
                . $db->quoteName('c.lft') . ' >= ' . (int) $baseCategory->lft
                . ' AND ' . $db->quoteName('c.rgt') . ' <= ' . (int) $baseCategory->rgt;

            if ($levels !== null && $levels >= 0)
            {
                $condition .= ' AND ' . $db->quoteName('c.level') . ' <= ' . ((int) $baseCategory->level + $levels);
            }

            $condition .= ')';
            $where[] = $condition;
        }

        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('c.id'))
            ->from($db->quoteName('#__categories', 'c'))
            ->where($db->quoteName('c.extension') . ' = ' . $db->quote($categoryExtension))
            ->where('(' . implode(' OR ', $where) . ')');

        $expandedIds = ArrayHelper::toInteger((array) $db->setQuery($query)->loadColumn());
        $expandedIds = array_values(array_unique(array_filter($expandedIds)));
        sort($expandedIds);

        return $expandedIds;
    }

    /**
     * @param   int|array  $catId  Category ids.
     *
     * @return  int[]
     *
     * @since 1.2.0
     */
    private function normalizeCategoryIds($catId): array
    {
        if (is_numeric($catId))
        {
            return [(int) $catId];
        }

        if (!is_array($catId) || count($catId) === 0)
        {
            return [];
        }

        $categoryIds = ArrayHelper::toInteger($catId);
        $categoryIds = array_filter($categoryIds, static fn($value) => $value > 0);

        return array_values(array_unique($categoryIds));
    }
}
