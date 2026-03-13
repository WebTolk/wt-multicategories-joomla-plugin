<?php
/**
 * WT Multicategories
 *
 * @package    System - WT Multicategories
 * @version     1.2.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.2.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Service;

use Joomla\Database\DatabaseAwareTrait;
use Joomla\Utilities\ArrayHelper;

use function array_filter;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function count;
use function in_array;
use function is_array;
use function is_scalar;
use function json_decode;
use function preg_split;
use function trim;

defined('_JEXEC') or die;

final class MappingService
{
    use DatabaseAwareTrait;

    public const TABLE = '#__wtmulticategories_map';

    /**
     * @var array<string, string|null>
     *
     * @since 1.2.0
     */
    private static array $fieldNameCache = [];

    /**
     * Rebuild all mapping rows for a field/context pair.
     *
     * @param   string  $context  Item context.
     * @param   int     $fieldId  Field id.
     *
     * @return  int
     *
     * @since 1.2.0
     */
    public function rebuildAllMappings(string $context, int $fieldId): int
    {
        if ($fieldId <= 0)
        {
            return 0;
        }

        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([$db->quoteName('item_id'), $db->quoteName('value')])
            ->from($db->quoteName('#__fields_values'))
            ->where($db->quoteName('field_id') . ' = ' . (int) $fieldId);

        $rows = $db->setQuery($query)->loadAssocList();

        $this->deleteMappingsByField($context, $fieldId);

        if (empty($rows))
        {
            return 0;
        }

        $mappingRows = [];

        foreach ($rows as $row)
        {
            $itemId = (int) ($row['item_id'] ?? 0);

            if ($itemId <= 0)
            {
                continue;
            }

            $categoryIds = $this->normalizeCategoryIds($row['value'] ?? null);

            foreach ($categoryIds as $categoryId)
            {
                $key = $context . ':' . $itemId . ':' . $categoryId . ':' . $fieldId;
                $mappingRows[$key] = [
                    'item_context' => $context,
                    'item_id' => $itemId,
                    'category_id' => $categoryId,
                    'field_id' => $fieldId,
                ];
            }
        }

        $this->insertMappings(array_values($mappingRows));

        return count($mappingRows);
    }

    /**
     * Rebuild one item's mapping rows.
     *
     * @param   int         $itemId       Item id.
     * @param   string      $context      Item context.
     * @param   int         $fieldId      Field id.
     * @param   array|null  $fieldValues  Raw values from request if available.
     *
     * @return  void
     *
     * @since 1.2.0
     */
    public function rebuildItemMappings(int $itemId, string $context, int $fieldId, ?array $fieldValues = null): void
    {
        if ($itemId <= 0 || $fieldId <= 0)
        {
            return;
        }

        $this->deleteItemMappings($itemId, $context, $fieldId);

        $categoryIds = $fieldValues ?? $this->loadFieldValuesFromDatabase($itemId, $fieldId);

        if (empty($categoryIds))
        {
            return;
        }

        $rows = [];

        foreach ($categoryIds as $categoryId)
        {
            $rows[] = [
                'item_context' => $context,
                'item_id' => $itemId,
                'category_id' => $categoryId,
                'field_id' => $fieldId,
            ];
        }

        $this->insertMappings($rows);
    }

    /**
     * Delete mappings for one item.
     *
     * @param   int        $itemId    Item id.
     * @param   string     $context   Item context.
     * @param   int|null   $fieldId   Field id filter.
     *
     * @return  void
     *
     * @since 1.2.0
     */
    public function deleteItemMappings(int $itemId, string $context, ?int $fieldId = null): void
    {
        if ($itemId <= 0)
        {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName(self::TABLE))
            ->where($db->quoteName('item_context') . ' = ' . $db->quote($context))
            ->where($db->quoteName('item_id') . ' = ' . (int) $itemId);

        if ($fieldId !== null && $fieldId > 0)
        {
            $query->where($db->quoteName('field_id') . ' = ' . (int) $fieldId);
        }

        $db->setQuery($query)->execute();
    }

    /**
     * Extract raw field values from submitted data.
     *
     * @param   mixed   $data     Event data.
     * @param   int     $fieldId  Field id.
     * @param   string  $context  Item context.
     *
     * @return  array|null
     *
     * @since 1.2.0
     */
    public function extractFieldValuesFromData($data, int $fieldId, string $context): ?array
    {
        if (!is_array($data) || empty($data['com_fields']) || !is_array($data['com_fields']))
        {
            return null;
        }

        $fieldName = $this->getFieldName($fieldId, $context);

        if (!$fieldName || !array_key_exists($fieldName, $data['com_fields']))
        {
            return null;
        }

        return $this->normalizeCategoryIds($data['com_fields'][$fieldName]);
    }

    /**
     * Normalize category ids from different storage formats.
     *
     * @param   mixed  $rawValue  Raw field value.
     *
     * @return  int[]
     *
     * @since 1.2.0
     */
    public function normalizeCategoryIds($rawValue): array
    {
        if ($rawValue === null || $rawValue === '' || $rawValue === false)
        {
            return [];
        }

        $values = [];

        if (is_array($rawValue))
        {
            foreach ($rawValue as $value)
            {
                $values = array_merge($values, $this->normalizeCategoryIds($value));
            }
        }
        elseif (is_scalar($rawValue))
        {
            $stringValue = trim((string) $rawValue);

            if ($stringValue === '')
            {
                return [];
            }

            $decoded = json_decode($stringValue, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
            {
                return $this->normalizeCategoryIds($decoded);
            }

            if (in_array($stringValue, ['[]', '{}', 'null'], true))
            {
                return [];
            }

            $values = preg_split('/[\s,;]+/', $stringValue) ?: [];
        }

        $values = ArrayHelper::toInteger($values);
        $values = array_filter($values, static fn($value) => $value > 0);

        return array_values(array_unique($values));
    }

    /**
     * Load stored field values for an item.
     *
     * @param   int  $itemId   Item id.
     * @param   int  $fieldId  Field id.
     *
     * @return  int[]
     *
     * @since 1.2.0
     */
    private function loadFieldValuesFromDatabase(int $itemId, int $fieldId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('value'))
            ->from($db->quoteName('#__fields_values'))
            ->where($db->quoteName('field_id') . ' = ' . (int) $fieldId)
            ->where($db->quoteName('item_id') . ' = ' . $db->quote($itemId));

        return $this->normalizeCategoryIds($db->setQuery($query)->loadColumn());
    }

    /**
     * Resolve field name by id.
     *
     * @param   int     $fieldId  Field id.
     * @param   string  $context  Context.
     *
     * @return  string|null
     *
     * @since 1.2.0
     */
    private function getFieldName(int $fieldId, string $context): ?string
    {
        $cacheKey = $context . ':' . $fieldId;

        if (array_key_exists($cacheKey, self::$fieldNameCache))
        {
            return self::$fieldNameCache[$cacheKey];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__fields'))
            ->where($db->quoteName('id') . ' = ' . (int) $fieldId)
            ->where($db->quoteName('context') . ' = ' . $db->quote($context));

        self::$fieldNameCache[$cacheKey] = $db->setQuery($query)->loadResult() ?: null;

        return self::$fieldNameCache[$cacheKey];
    }

    /**
     * Delete all mappings for one context/field pair.
     *
     * @param   string  $context  Item context.
     * @param   int     $fieldId  Field id.
     *
     * @return  void
     *
     * @since 1.2.0
     */
    private function deleteMappingsByField(string $context, int $fieldId): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName(self::TABLE))
            ->where($db->quoteName('item_context') . ' = ' . $db->quote($context))
            ->where($db->quoteName('field_id') . ' = ' . (int) $fieldId);

        $db->setQuery($query)->execute();
    }

    /**
     * Insert mapping rows in batches.
     *
     * @param   array  $rows  Mapping rows.
     *
     * @return  void
     *
     * @since 1.2.0
     */
    private function insertMappings(array $rows): void
    {
        if (empty($rows))
        {
            return;
        }

        $db = $this->getDatabase();
        $columns = [
            $db->quoteName('item_context'),
            $db->quoteName('item_id'),
            $db->quoteName('category_id'),
            $db->quoteName('field_id'),
        ];

        foreach (array_chunk($rows, 500) as $chunk)
        {
            $query = $db->getQuery(true)
                ->insert($db->quoteName(self::TABLE))
                ->columns($columns);

            foreach ($chunk as $row)
            {
                $query->values(
                    $db->quote($row['item_context']) . ', '
                    . (int) $row['item_id'] . ', '
                    . (int) $row['category_id'] . ', '
                    . (int) $row['field_id']
                );
            }

            $db->setQuery($query)->execute();
        }
    }
}
