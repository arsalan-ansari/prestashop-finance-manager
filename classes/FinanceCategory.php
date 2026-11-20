<?php

class FinanceCategory extends ObjectModel
{
    public $id_category;
    public $name;
    public $type;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'finance_category',
        'primary' => 'id_category',
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isGenericName',
                'size' => 150,
            ],
            'type' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isString',
                'size' => 20,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
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

    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->position) {
            $this->position = (int)Db::getInstance()->getValue(
                'SELECT IFNULL(MAX(position),0)+1
                FROM `'._DB_PREFIX_.'finance_category`
                WHERE type="'.pSQL($this->type).'"'
            );
        }

        return parent::add($autoDate, $nullValues);
    }

    public static function getCategoriesByType($type)
    {
        $sql = new DbQuery();

        $sql->select('*');
        $sql->from('finance_category');
        $sql->where('type = "' . pSQL($type) . '"');
        $sql->where('active = 1');
        $sql->orderBy('position ASC');

        return Db::getInstance()->executeS($sql);
    }

    public static function getIncomeCategories()
    {
        return self::getCategoriesByType('income');
    }

    public static function getExpenseCategories()
    {
        return self::getCategoriesByType('expense');
    }

    public static function categoryExists($name, $type, $excludeId = 0)
    {
        $sql = 'SELECT id_category
                FROM `'._DB_PREFIX_.'finance_category`
                WHERE name = "'.pSQL($name).'"
                AND type = "'.pSQL($type).'"';

        if ($excludeId) {
            $sql .= ' AND id_category != '.(int)$excludeId;
        }

        return (bool)Db::getInstance()->getValue($sql);
    }

    public static function isUsed($idCategory)
    {
        return (bool)Db::getInstance()->getValue(
            'SELECT id_cashbook
             FROM `'._DB_PREFIX_.'finance_cashbook`
             WHERE id_category='.(int)$idCategory.'
             LIMIT 1'
        );
    }

    public static function getNextPosition($type)
    {
        return (int)Db::getInstance()->getValue(
            'SELECT IFNULL(MAX(position),0)+1
            FROM `'._DB_PREFIX_.'finance_category`
            WHERE type="'.pSQL($type).'"'
        );
    }
}