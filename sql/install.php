<?php

$sql = [];

/*
|--------------------------------------------------------------------------
| Finance Categories
|--------------------------------------------------------------------------
*/

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'finance_category` (
    `id_category` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `type` ENUM("income","expense") NOT NULL,
    `position` INT NOT NULL DEFAULT 0,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_category`),
    KEY `idx_type` (`type`),
    KEY `idx_active` (`active`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

/*
|--------------------------------------------------------------------------
| Finance Cashbook
|--------------------------------------------------------------------------
*/

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'finance_cashbook` (
    `id_cashbook` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` ENUM("income","expense") NOT NULL,
    `id_category` INT UNSIGNED NOT NULL,
    `id_order` INT UNSIGNED DEFAULT NULL,
    `transaction_date` DATE NOT NULL,
    `amount` DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    `description` TEXT DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_cashbook`),
    KEY `idx_type` (`type`),
    KEY `idx_category` (`id_category`),
    KEY `idx_order` (`id_order`),
    KEY `idx_transaction_date` (`transaction_date`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

/*
|--------------------------------------------------------------------------
| Finance Cashbook History
|--------------------------------------------------------------------------
*/

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'finance_cashbook_history` (
    `id_history` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cashbook` INT UNSIGNED NOT NULL,
    `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
    `employee_name` VARCHAR(255) NOT NULL DEFAULT \'\',
    `action` VARCHAR(20) NOT NULL,
    `field_name` VARCHAR(100) DEFAULT NULL,
    `old_value` TEXT DEFAULT NULL,
    `new_value` TEXT DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`id_history`),
    KEY `idx_cashbook` (`id_cashbook`),
    KEY `idx_employee` (`id_employee`),
    KEY `idx_date_add` (`date_add`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

Configuration::updateValue('FINANCE_HISTORY_ENABLED', 1);

/*
|--------------------------------------------------------------------------
| Execute SQL
|--------------------------------------------------------------------------
*/

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| Default Income Categories
|--------------------------------------------------------------------------
*/

$incomeCategories = [
    'Offline Sale',
    'Other Income',
];

$position = 1;

foreach ($incomeCategories as $category) {
    Db::getInstance()->insert('finance_category', [
        'name'      => pSQL($category),
        'type'      => 'income',
        'position'  => $position++,
        'active'    => 1,
        'date_add'  => date('Y-m-d H:i:s'),
        'date_upd'  => date('Y-m-d H:i:s'),
    ]);
}

/*
|--------------------------------------------------------------------------
| Default Expense Categories
|--------------------------------------------------------------------------
*/

$expenseCategories = [
    'Fuel',
    'Wages',
    'Office Supplies',
    'Cleaning',
    'Repairs',
    'Advertising',
    'Utilities',
    'Rent',
    'Furniture Purchase',
    'Miscellaneous',
];

$position = 1;

foreach ($expenseCategories as $category) {
    Db::getInstance()->insert('finance_category', [
        'name'      => pSQL($category),
        'type'      => 'expense',
        'position'  => $position++,
        'active'    => 1,
        'date_add'  => date('Y-m-d H:i:s'),
        'date_upd'  => date('Y-m-d H:i:s'),
    ]);
}

return true;