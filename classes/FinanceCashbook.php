<?php

class FinanceCashbook extends ObjectModel
{
    public $id_cashbook;
    public $type;
    public $id_category;
    public $id_order;
    public $transaction_date;
    public $amount;
    public $description;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'finance_cashbook',
        'primary' => 'id_cashbook',
        'fields' => [
            'type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'size' => 20,
            ],
            'id_category' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'transaction_date' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'amount' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => true,
            ],
            'description' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    public static function getTransactions($limit = 50, $offset = 0)
    {
        $query = new DbQuery();

        $query->select('
            a.*,
            c.name AS category
        ');

        $query->from('finance_cashbook', 'a');

        $query->leftJoin(
            'finance_category',
            'c',
            'c.id_category = a.id_category'
        );

        $query->orderBy('a.transaction_date DESC, a.id_cashbook DESC');
        $query->limit((int)$limit, (int)$offset);

        return Db::getInstance()->executeS($query);
    }

    public static function getTransactionsForPrint($from, $to)
    {
        $sql = new DbQuery();

        $sql->select('a.*, c.name AS category');
        $sql->from('finance_cashbook', 'a');
        $sql->leftJoin(
            'finance_category',
            'c',
            'c.id_category = a.id_category'
        );
        $sql->where(
            "a.transaction_date >= '" . pSQL($from) . "'"
        );
        $sql->where(
            "a.transaction_date <= '" . pSQL($to) . "'"
        );
        $sql->orderBy('a.transaction_date ASC, a.id_cashbook ASC');

        return Db::getInstance()->executeS($sql);
    }

    public static function getTotalIncome($from = null, $to = null)
    {
        $sql = 'SELECT SUM(amount)
                FROM `'._DB_PREFIX_.'finance_cashbook`
                WHERE type="income"';

        if ($from) {
            $sql .= ' AND transaction_date >= "'.pSQL($from).'"';
        }

        if ($to) {
            $sql .= ' AND transaction_date <= "'.pSQL($to).'"';
        }

        return (float) Db::getInstance()->getValue($sql);
    }

    public static function getTotalExpense($from = null, $to = null)
    {
        $sql = 'SELECT SUM(amount)
                FROM `'._DB_PREFIX_.'finance_cashbook`
                WHERE type="expense"';

        if ($from) {
            $sql .= ' AND transaction_date >= "'.pSQL($from).'"';
        }

        if ($to) {
            $sql .= ' AND transaction_date <= "'.pSQL($to).'"';
        }

        return (float) Db::getInstance()->getValue($sql);
    }

    public static function orderAlreadyExists($idOrder, $excludeId = 0)
    {
        $sql = '
            SELECT id_cashbook
            FROM `' . _DB_PREFIX_ . 'finance_cashbook`
            WHERE id_order = ' . (int)$idOrder;
    
        if ($excludeId > 0) {
            $sql .= ' AND id_cashbook != ' . (int)$excludeId;
        }
    
        $sql .= ' LIMIT 1';
    
        $result = Db::getInstance()->executeS($sql);
    
        return !empty($result);
    }

    public static function getByOrderId($idOrder)
    {
        return Db::getInstance()->getRow(
            'SELECT *
             FROM `'._DB_PREFIX_.'finance_cashbook`
             WHERE id_order='.(int)$idOrder.'
             LIMIT 1'
        );
    }
}