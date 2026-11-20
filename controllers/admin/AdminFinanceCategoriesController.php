<?php

require_once dirname(__DIR__, 2) . '/classes/FinanceCategory.php';

class AdminFinanceCategoriesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'finance_category';
        $this->className = 'FinanceCategory';
        $this->identifier = 'id_category';
        $this->lang = false;

        parent::__construct();

        $this->fields_list = [
            'id_category' => [
                'title' => 'ID',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => 'Category',
            ],
            'type' => [
                'title' => 'Type',
                'callback' => 'renderType',
            ],
            'position' => [
                'title' => 'Position',
                'class' => 'fixed-width-xs',
            ],
            'active' => [
                'title' => 'Status',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => 'Delete Selected',
                'confirm' => 'Delete selected categories?',
                'icon' => 'icon-trash',
            ],
        ];
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex .
                '&add' . $this->table .
                '&token=' . $this->token,
            'desc' => 'Add Category',
            'icon' => 'process-icon-new',
        ];
    }

    public function renderType($value)
    {
        if ($value == 'income') {
            return '<span class="badge badge-success">Cash In</span>';
        }

        return '<span class="badge badge-danger">Cash Out</span>';
    }

    public function renderForm()
    {
        $this->fields_form = [

            'legend' => [
                'title' => 'Category',
                'icon'  => 'icon-tags',
            ],

            'input' => [

                [
                    'type' => 'text',
                    'label' => 'Category Name',
                    'name' => 'name',
                    'required' => true,
                ],

                [
                    'type' => 'select',
                    'label' => 'Type',
                    'name' => 'type',
                    'required' => true,
                    'options' => [
                        'query' => [
                            [
                                'id' => 'income',
                                'name' => 'Cash In',
                            ],
                            [
                                'id' => 'expense',
                                'name' => 'Cash Out',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => 'Position',
                    'name' => 'position',
                ],

                [
                    'type' => 'switch',
                    'label' => 'Status',
                    'name' => 'active',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => 'Enabled',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => 'Disabled',
                        ],
                    ],
                ],

            ],

            'submit' => [
                'title' => 'Save',
            ],

        ];

        return parent::renderForm();
    }

    public function processSave()
    {
        if (Tools::isSubmit('submitAddfinance_category')) {

            $name = trim(Tools::getValue('name'));

            if (empty($name)) {
                $this->errors[] = 'Category name is required.';
            }

            $exists = Db::getInstance()->getValue(
                'SELECT id_category
                FROM `' . _DB_PREFIX_ . 'finance_category`
                WHERE name="' . pSQL($name) . '"
                AND type="' . pSQL(Tools::getValue('type')) . '"'
            );

            if ($exists && !$this->object->id) {
                $this->errors[] = 'Category already exists.';
            }

            if (!count($this->errors)) {
                return parent::processSave();
            }
        }

        return false;
    }

    public function processDelete()
    {
        $id = (int) Tools::getValue('id_category');

        $used = Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'finance_cashbook`
            WHERE id_category=' . $id
        );

        if ($used) {
            $this->errors[] =
                'This category is already used in transactions and cannot be deleted.';

            return false;
        }

        return parent::processDelete();
    }
}