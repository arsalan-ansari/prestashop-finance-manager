<?php

class AdminFinanceExpenseController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'finance_cashbook';
        $this->className = 'FinanceCashbook';
        $this->identifier = 'id_cashbook';
        $this->lang = false;

        parent::__construct();
    }

    public function renderForm()
    {
        $expenseCategories = FinanceCategory::getExpenseCategories();

        $categoryOptions = [];

        foreach ($expenseCategories as $category) {
            $categoryOptions[] = [
                'id' => $category['id_category'],
                'name' => $category['name'],
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => 'Cash Out',
                'icon'  => 'icon-minus-circle',
            ],

            'input' => [

                [
                    'type' => 'date',
                    'label' => 'Transaction Date',
                    'name' => 'transaction_date',
                    'required' => true,
                ],

                [
                    'type' => 'select',
                    'label' => 'Category',
                    'name' => 'id_category',
                    'required' => true,
                    'options' => [
                        'query' => $categoryOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => 'Amount',
                    'name' => 'amount',
                    'required' => true,
                ],

                [
                    'type' => 'textarea',
                    'label' => 'Description',
                    'name' => 'description',
                    'rows' => 5,
                    'cols' => 60,
                    'autoload_rte' => false,
                ],

            ],

            'submit' => [
                'title' => 'Save Cash Out',
            ],

        ];

        $this->fields_value = [
            'transaction_date' => date('Y-m-d'),
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddfinance_cashbook')) {

            if ((float)Tools::getValue('amount') <= 0) {
                $this->errors[] = 'Amount must be greater than zero.';
            }

            if (!(int)Tools::getValue('id_category')) {
                $this->errors[] = 'Please select a category.';
            }

            if (!count($this->errors)) {

                $cashbook = new FinanceCashbook();

                $cashbook->type = 'expense';
                $cashbook->id_category = (int)Tools::getValue('id_category');
                $cashbook->id_order = null;
                $cashbook->transaction_date = Tools::getValue('transaction_date');
                $cashbook->amount = (float)Tools::getValue('amount');
                $cashbook->description = trim(Tools::getValue('description'));

                if ($cashbook->add()) {

                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminFinanceCashbook')
                        . '&conf=4'
                    );
                }

                $this->errors[] = 'Unable to save transaction.';
            }
        }

        parent::postProcess();
    }
}