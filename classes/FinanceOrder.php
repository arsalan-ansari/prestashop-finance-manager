<?php

class FinanceOrder
{
    public static function search($keyword = '', $limit = 20)
    {
        $keyword = trim($keyword);

        $sql = new DbQuery();

        $sql->select('
            o.id_order,
            o.reference,
            o.total_paid_tax_incl,
            o.date_add,
            CONCAT(a.firstname, " ", a.lastname) AS customer,
            a.phone,
            a.phone_mobile
        ');

        $sql->from('orders', 'o');

        $sql->leftJoin(
            'address',
            'a',
            'a.id_address = o.id_address_delivery'
        );

        $sql->where(
            'o.id_order NOT IN (
                SELECT id_order
                FROM `' . _DB_PREFIX_ . 'finance_cashbook`
                WHERE id_order IS NOT NULL
            )'
        );

        if ($keyword != '') {

            $keyword = pSQL($keyword);

            $sql->where('(
                o.id_order LIKE "%'.$keyword.'%"
                OR o.reference LIKE "%'.$keyword.'%"
                OR CONCAT(a.firstname," ",a.lastname) LIKE "%'.$keyword.'%"
                OR a.firstname LIKE "%'.$keyword.'%"
                OR a.lastname LIKE "%'.$keyword.'%"
                OR a.phone LIKE "%'.$keyword.'%"
                OR a.phone_mobile LIKE "%'.$keyword.'%"
            )');
        }

        $sql->orderBy('o.id_order DESC');
        $sql->limit((int)$limit);

        return Db::getInstance()->executeS($sql);
    }

    public static function getById($idOrder)
    {
        $sql = new DbQuery();

        $sql->select('
            o.*,
            CONCAT(a.firstname," ",a.lastname) AS customer,
            a.phone,
            a.phone_mobile
        ');

        $sql->from('orders', 'o');

        $sql->leftJoin(
            'address',
            'a',
            'a.id_address = o.id_address_delivery'
        );

        $sql->where('o.id_order='.(int)$idOrder);

        return Db::getInstance()->getRow($sql);
    }

    public static function exists($idOrder)
    {
        return (bool)Db::getInstance()->getValue(
            'SELECT id_order
            FROM `'._DB_PREFIX_.'orders`
            WHERE id_order='.(int)$idOrder
        );
    }

    public static function alreadyCaptured($idOrder)
    {
        return (bool)Db::getInstance()->getValue(
            'SELECT id_cashbook
            FROM `'._DB_PREFIX_.'finance_cashbook`
            WHERE id_order='.(int)$idOrder.'
            LIMIT 1'
        );
    }

    public static function getAmount($idOrder)
    {
        return (float)Db::getInstance()->getValue(
            'SELECT total_paid_tax_incl
            FROM `'._DB_PREFIX_.'orders`
            WHERE id_order='.(int)$idOrder
        );
    }

    public static function getCustomerName($idOrder)
    {
        $sql = new DbQuery();

        $sql->select('CONCAT(a.firstname," ",a.lastname)');

        $sql->from('orders', 'o');

        $sql->leftJoin(
            'address',
            'a',
            'a.id_address = o.id_address_delivery'
        );

        $sql->where('o.id_order='.(int)$idOrder);

        return Db::getInstance()->getValue($sql);
    }

    public static function getLatest($limit = 20)
    {
        $sql = new DbQuery();

        $sql->select('
            o.id_order,
            o.reference,
            o.total_paid_tax_incl,
            CONCAT(a.firstname," ",a.lastname) AS customer
        ');

        $sql->from('orders', 'o');

        $sql->leftJoin(
            'address',
            'a',
            'a.id_address = o.id_address_delivery'
        );

        $sql->where(
            'o.id_order NOT IN (
                SELECT id_order
                FROM `'._DB_PREFIX_.'finance_cashbook`
                WHERE id_order IS NOT NULL
            )'
        );

        $sql->orderBy('o.id_order DESC');

        $sql->limit((int)$limit);

        return Db::getInstance()->executeS($sql);
    }
}