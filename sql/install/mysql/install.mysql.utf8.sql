CREATE TABLE IF NOT EXISTS `#__wtmulticategories_map` (
    `item_context` VARCHAR(100) NOT NULL DEFAULT 'com_content.article',
    `item_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `field_id` INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`item_context`, `item_id`, `category_id`, `field_id`),
    KEY `idx_lookup` (`item_context`, `field_id`, `category_id`, `item_id`),
    KEY `idx_category_item` (`item_context`, `category_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
