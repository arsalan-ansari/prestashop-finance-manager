<?php

class AdminFinanceIncomeController extends ModuleAdminController
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
        $incomeCategories = FinanceCategory::getIncomeCategories();

        $categoryOptions = [];

        foreach ($incomeCategories as $category) {
            $categoryOptions[] = [
                'id' => $category['id_category'],
                'name' => $category['name'],
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => 'Cash In',
                'icon'  => 'icon-plus-circle',
            ],

            'input' => [

                [
                    'type' => 'date',
                    'label' => 'Transaction Date',
                    'name' => 'transaction_date',
                    'required' => true,
                ],

                [
                    'type' => 'radio',
                    'label' => 'Source',
                    'name' => 'income_source',
                    'required' => true,
                    'values' => [
                        [
                            'id' => 'manual_order',
                            'value' => 'order',
                            'label' => 'Manual Order',
                        ],
                        [
                            'id' => 'other_income',
                            'value' => 'other',
                            'label' => 'Other Income',
                        ],
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => 'Manual Order ID',
                    'name' => 'id_order',
                    'hint' => 'Enter PrestaShop manual order ID.',
                ],

                [
                    'type' => 'select',
                    'label' => 'Category',
                    'name' => 'id_category',
                    'required' => true,
                    'options' => [
                        'query' => $categoryOptions,
                        'id' => 'id',
                        'name' => 'name',
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
                    'autoload_rte' => false,
                    'rows' => 4,
                    'cols' => 60,
                ],

            ],

            'submit' => [
                'title' => 'Save Cash In',
            ],
        ];

        $this->fields_value = [
            'transaction_date' => date('Y-m-d'),
            'income_source'    => 'order',
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddfinance_cashbook')) {

            $idOrder = (int) Tools::getValue('id_order');

            if ($idOrder > 0 && FinanceCashbook::orderAlreadyExists($idOrder)) {
                $this->errors[] = 'This order already exists in the cashbook.';
            }

            if ((float) Tools::getValue('amount') <= 0) {
                $this->errors[] = 'Amount must be greater than zero.';
            }

            if (!count($this->errors)) {

                $cashbook = new FinanceCashbook();

                $cashbook->type = 'income';
                $cashbook->id_category = (int) Tools::getValue('id_category');
                $cashbook->id_order = $idOrder ?: null;
                $cashbook->transaction_date = Tools::getValue('transaction_date');
                $cashbook->amount = (float) Tools::getValue('amount');
                $cashbook->description = Tools::getValue('description');

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