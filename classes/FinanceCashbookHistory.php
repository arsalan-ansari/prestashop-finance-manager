<?php

class FinanceCashbookHistory
{
    public static function add($idCashbook, $action, $fieldName = null, $oldValue = null, $newValue = null)
    {
        $historyEnabled = Configuration::get('FINANCE_HISTORY_ENABLED');

        if ($historyEnabled === false) {
            Configuration::updateValue('FINANCE_HISTORY_ENABLED', 1);
            $historyEnabled = 1;
        }

        if (!(int)$historyEnabled) {
            return true;
        }

        $employee = Context::getContext()->employee;
        $employeeName = '';

        if (Validate::isLoadedObject($employee)) {
            $employeeName = trim($employee->firstname . ' ' . $employee->lastname);
        }

        return Db::getInstance()->insert('finance_cashbook_history', [
            'id_cashbook'   => (int)$idCashbook,
            'id_employee'   => Validate::isLoadedObject($employee) ? (int)$employee->id : 0,
            'employee_name' => pSQL($employeeName),
            'action'        => pSQL($action),
            'field_name'    => $fieldName !== null ? pSQL($fieldName) : null,
            'old_value'     => $oldValue !== null ? pSQL((string)$oldValue, true) : null,
            'new_value'     => $newValue !== null ? pSQL((string)$newValue, true) : null,
            'date_add'      => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getByCashbookId($idCashbook)
    {
        $sql = new DbQuery();

        $sql->select('*');
        $sql->from('finance_cashbook_history');
        $sql->where('id_cashbook = ' . (int)$idCashbook);
        $sql->orderBy('date_add DESC, id_history DESC');

        return Db::getInstance()->executeS($sql);
    }

    public static function formatFieldName($field)
    {
        $labels = [
            'type' => 'Type',
            'id_category' => 'Category',
            'id_order' => 'Order',
            'transaction_date' => 'Transaction Date',
            'amount' => 'Amount',
            'description' => 'Description',
        ];

        return isset($labels[$field]) ? $labels[$field] : (string)$field;
    }

    public static function formatValue($field, $value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        switch ($field) {
            case 'type':
                return $value === 'income' ? 'Cash In' : 'Cash Out';

            case 'id_category':
                $name = Db::getInstance()->getValue(
                    'SELECT name FROM `' . _DB_PREFIX_ . 'finance_category`' .
                    ' WHERE id_category = ' . (int)$value
                );
                return $name ?: (string)$value;

            case 'id_order':
                return '#' . (int)$value;

            case 'amount':
                return number_format((float)$value, 2, '.', '');

            default:
                return (string)$value;
        }
    }
}
