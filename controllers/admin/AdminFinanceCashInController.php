<?php

class AdminFinanceCashInController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'finance_cashbook';
        $this->className = 'FinanceCashbook';
        $this->identifier = 'id_cashbook';

        parent::__construct();
    }

    public function initContent()
    {
        $this->context->smarty->assign([
            'categories' => FinanceCategory::getIncomeCategories(),
            'orders' => $this->getManualOrders(),
        ]);

        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/cashin.tpl'
        );

        parent::initContent();
    }

    protected function getManualOrders()
    {
        $sql = '
            SELECT
                o.id_order,
                o.reference,
                CONCAT(a.firstname, " ", a.lastname) AS customer,
                o.total_paid_tax_incl
            FROM `'._DB_PREFIX_.'orders` o
            INNER JOIN `'._DB_PREFIX_.'address` a
                ON (a.id_address = o.id_address_delivery)
            WHERE o.id_order NOT IN (
                SELECT id_order
                FROM `'._DB_PREFIX_.'finance_cashbook`
                WHERE id_order IS NOT NULL
            )
            ORDER BY o.id_order DESC
            LIMIT 100
        ';

        return Db::getInstance()->executeS($sql);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitCashIn')) {

            $cashbook = new FinanceCashbook();

            $cashbook->type = 'income';
            $cashbook->id_category = (int)Tools::getValue('id_category');
            $cashbook->id_order = (int)Tools::getValue('id_order');
            $cashbook->transaction_date = Tools::getValue('transaction_date');
            $cashbook->amount = (float)Tools::getValue('amount');
            $cashbook->description = trim(Tools::getValue('description'));

            if ($cashbook->add()) {

                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminFinanceCashbook') .
                    '&conf=4'
                );
            }

            $this->errors[] = $this->trans(
                'Unable to save transaction.',
                [],
                'Modules.Financemanager.Admin'
            );
        }

        parent::postProcess();
    }
}