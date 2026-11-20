<?php

Configuration::deleteByName('FINANCE_HISTORY_ENABLED');

$tables = [
    'finance_cashbook_history',
    'finance_cashbook',
    'finance_category',
];

foreach ($tables as $table) {
    $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }
}

return true;