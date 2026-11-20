<?php

function upgrade_module_1_0_1($module)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'finance_cashbook_history` (
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

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    $className = 'AdminFinanceHistory';

    if (Tab::getIdFromClassName($className)) {
        return true;
    }

    $parentId = (int)Tab::getIdFromClassName('AdminFinanceDashboard');

    if (!$parentId) {
        return false;
    }

    $tab = new Tab();
    $tab->active = 1;
    $tab->class_name = $className;
    $tab->module = $module->name;
    $tab->id_parent = $parentId;

    foreach (Language::getLanguages(false) as $language) {
        $tab->name[(int)$language['id_lang']] = 'History';
    }

    return (bool)$tab->add();
}
