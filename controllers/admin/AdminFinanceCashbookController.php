<?php

require_once dirname(__DIR__, 2) . '/classes/FinanceCashbook.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceCategory.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceOrder.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceCashbookHistory.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceCashbookWebhook.php';

class AdminFinanceCashbookController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'finance_cashbook';
        $this->className = 'FinanceCashbook';
        $this->identifier = 'id_cashbook';
        $this->lang = false;
        $this->explicitSelect = true;

        parent::__construct();

        $this->_select = '
            a.type AS transaction_type,
            c.name AS category_name,
            0 AS history
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'finance_category` c
                ON (c.id_category = a.id_category)
        ';

        $this->_orderBy = 'transaction_date';
        $this->_orderWay = 'DESC';

        $this->fields_list = [

            'id_cashbook' => [
                'title' => 'ID',
                'class' => 'fixed-width-xs',
                'align' => 'center',
            ],

            'transaction_date' => [
                'title' => 'Date',
                'type' => 'date',
            ],

            'transaction_type' => [
                'title' => 'Type',
                'callback' => 'renderType',
                'search' => false,
                'orderby' => false,
            ],

            'category_name' => [
                'title' => 'Category',
            ],

            'id_order' => [
                'title' => 'Order',
                'callback' => 'renderOrder',
                'search' => false,
            ],

            'description' => [
                'title' => 'Description',
            ],

            'amount' => [
                'title' => 'Amount',
                'type' => 'price',
                'align' => 'text-right',
                'callback' => 'renderAmount',
            ],

        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => 'Delete Selected',
                'confirm' => 'Delete selected transactions?',
                'icon' => 'icon-trash',
            ],
        ];

        $this->fields_list['history'] = [
            'title' => 'History',
            'align' => 'center',
            'search' => false,
            'orderby' => false,
            'callback' => 'renderHistory',
        ];
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_title = 'Cashbook';

        $this->page_header_toolbar_btn['cash_in'] = [
            'href' => self::$currentIndex .
                '&addfinance_cashbook&type=income&token=' . $this->token,
            'desc' => 'Cash In',
            'icon' => 'process-icon-new',
        ];

        $this->page_header_toolbar_btn['cash_out'] = [
            'href' => self::$currentIndex .
                '&addfinance_cashbook&type=expense&token=' . $this->token,
            'desc' => 'Cash Out',
            'icon' => 'process-icon-new',
        ];

        $this->page_header_toolbar_btn['print_today'] = [
            'href' => self::$currentIndex .
                '&printFinanceTransactions=1&print_from=' . date('Y-m-d') .
                '&print_to=' . date('Y-m-d') .
                '&token=' . $this->token,
            'desc' => 'Print Today',
            'icon' => 'process-icon-print',
        ];
    }

    protected function loadObject($opt = false)
    {
        parent::loadObject($opt);
    
        if (Validate::isLoadedObject($this->object)) {
    
            $this->fields_value = [
                'id_cashbook'      => (int)$this->object->id_cashbook,
                'type'             => $this->object->type,
                'transaction_date' => $this->object->transaction_date,
                'id_category'      => (int)$this->object->id_category,
                'id_order'         => (int)$this->object->id_order,
                'amount'           => Tools::ps_round((float)$this->object->amount, 2),
                'description'      => $this->object->description,
            ];
    
            if ((int)$this->object->id_order) {
                $this->fields_value['income_source'] = 'order';
            } else {
                $this->fields_value['income_source'] = 'other';
            }
        }
    
        return $this->object;
    }
    
    public function processUpdate()
    {
        return $this->processSave();
    }
    
    public function processAdd()
    {
        return $this->processSave();
    }
    
    public function processDuplicate()
    {
        $this->errors[] = 'Duplicating cashbook transactions is not allowed.';
        return false;
    }
    
    public function processStatus()
    {
        return false;
    }
    
    public function renderType($value)
    {
        if ($value === 'income') {
            return '<span class="badge badge-success">Cash In</span>';
        }

        return '<span class="badge badge-danger">Cash Out</span>';
    }

    public function renderOrder($value)
    {
        if ((int)$value > 0) {
            return '#' . (int)$value;
        }

        return '-';
    }

    public function renderHistory($value, $row)
    {
        $idCashbook = isset($row['id_cashbook']) ? (int)$row['id_cashbook'] : 0;

        if (!$idCashbook || !$this->context->employee->isSuperAdmin()) {
            return '-';
        }

        $url = $this->context->link->getAdminLink(
            'AdminFinanceHistory',
            true,
            [],
            ['id_cashbook' => $idCashbook]
        );

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="btn btn-default btn-xs">' .
            '<i class="icon-history"></i> History</a>';
    }

    public function renderAmount($value, $row)
    {
        $amount = $this->context->getCurrentLocale()->formatPrice(
            (float)$value,
            $this->context->currency->iso_code
        );
    
        $type = '';
    
        if (is_array($row)) {
    
            if (isset($row['type'])) {
                $type = $row['type'];
            } elseif (isset($row['transaction_type'])) {
                $type = $row['transaction_type'];
            }
    
        }
    
        if ($type === 'income') {
            return '<span class="text-success"><strong>+' . $amount . '</strong></span>';
        }
    
        if ($type === 'expense') {
            return '<span class="text-danger"><strong>-' . $amount . '</strong></span>';
        }
    
        return $amount;
    }
    
    public function renderForm()
    {
        if (Validate::isLoadedObject($this->object)) {

            $type = $this->object->type;
        
        } else {
        
            $type = Tools::getValue('type', 'income');
        
        }
    
        $this->fields_form = [
    
            'legend' => [
                'title' => ($type == 'income') ? 'Cash In' : 'Cash Out',
                'icon'  => ($type == 'income') ? 'icon-plus-circle' : 'icon-minus-circle',
            ],
    
            'input' => [
    
                [
                    'type' => 'hidden',
                    'name' => 'type',
                ],
                
                [
                    'type' => 'hidden',
                    'name' => 'id_cashbook',
                ],
    
                [
                    'type' => 'date',
                    'label' => 'Transaction Date',
                    'name' => 'transaction_date',
                    'required' => true,
                ],
    
                [
                    'type' => 'text',
                    'label' => 'Amount',
                    'name' => 'amount',
                    'required' => true,
                    'class' => 'fixed-width-lg',
                ],
    
                [
                    'type' => 'textarea',
                    'label' => 'Description',
                    'name' => 'description',
                    'required' => true, 
                    'rows' => 4,
                    'cols' => 60,
                    'autoload_rte' => false,
                ],
    
            ],
    
            'submit' => [
                'title' => 'Save',
                'class' => 'btn btn-primary pull-right',
            ],
    
        ];
    
        if ($type == 'income') {
    
            array_splice(
                $this->fields_form['input'],
                2,
                0,
                [
                    [
                        'type' => 'radio',
                        'label' => 'Source',
                        'name' => 'income_source',
                        'required' => true,
                        'values' => [
                            [
                                'id' => 'source_order',
                                'value' => 'order',
                                'label' => 'Cash Sale',
                            ],
                            [
                                'id' => 'source_other',
                                'value' => 'other',
                                'label' => 'Other Income',
                            ],
                        ],
                    ],
                    [
                        'type'  => 'hidden',
                        'name'  => 'id_order',
                    ],
                    
                    [
                        'type' => 'html',
                        'label' => 'Select Cash Order',
                        'name' => 'finance_order_selector',
                        'html_content' => '
                            <div id="finance-selected-order" style="display:none;">
                    
                                <div class="alert alert-success">
                    
                                    <strong>Selected Order</strong>
                    
                                    <table class="table" style="margin-top:10px;">
                    
                                        <tr>
                                            <th width="180">Order</th>
                                            <td id="finance_order_number">-</td>
                                        </tr>
                    
                                        <tr>
                                            <th>Customer</th>
                                            <td id="finance_order_customer">-</td>
                                        </tr>
                    
                                        <tr>
                                            <th>Amount</th>
                                            <td id="finance_order_amount">-</td>
                                        </tr>
                    
                                    </table>
                    
                                    <button
                                        type="button"
                                        class="btn btn-default"
                                        id="finance_change_order">
                    
                                        Change Order
                    
                                    </button>
                    
                                </div>
                    
                            </div>
                    
                            <div id="finance-order-placeholder">
                    
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    id="btnSearchOrder">
                    
                                    <i class="icon-search"></i>
                    
                                    Search Cash Sale
                    
                                </button>
                    
                            </div>
                        ',
                    ],
                ]
            );
        }
        
        if ($type == 'expense') {
            array_splice(
                $this->fields_form['input'],
                1,
                0,
                [
                    [
                        'type' => 'select',
                        'label' => 'Category',
                        'name' => 'id_category',
                        'required' => true,
                        'options' => [
                            'query' => FinanceCategory::getExpenseCategories(),
                            'id'    => 'id_category',
                            'name'  => 'name',
                        ],
                    ],
                ]
            );
        
        }
    
        if (!Validate::isLoadedObject($this->object)) {

            $this->fields_value = [
                'type'             => $type,
                'id_cashbook'      => 0,
                'transaction_date' => date('Y-m-d'),
                'income_source'    => 'order',
            ];
        
        }
        
        $this->fields_form['input'][] = [
            'type' => 'html',
            'name' => 'finance_order_modal',
            'html_content' => $this->context->smarty->fetch(
                $this->module->getLocalPath() .
                'views/templates/admin/order_search.tpl'
            ),
        ];
    
        return parent::renderForm();
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('printFinanceTransactions')) {
            $this->printTransactions();
        }

        if (Tools::isSubmit('submitAddfinance_cashbook')) {
    
            $type = Tools::getValue('type');
    
            if (!in_array($type, ['income', 'expense'])) {
                $this->errors[] = 'Invalid transaction type.';
            }
    
            if (
                $type == 'expense' &&
                !(int)Tools::getValue('id_category')
            ) {
                $this->errors[] = 'Please select a category.';
            }
    
            if (!Validate::isDate(Tools::getValue('transaction_date'))) {
                $this->errors[] = 'Invalid transaction date.';
            }
            
            $description = trim(Tools::getValue('description'));
            if ($description === '') {
                $this->errors[] = 'Please enter a description.';
            }
    
            $idOrder = (int)Tools::getValue('id_order');
    
            if ($type == 'income') {
    
                if (Tools::getValue('income_source') == 'order') {
    
                    if (!$idOrder) {
                        $this->errors[] = 'Please select a Cash Sale.';
                    } else {
    
                        $order = new Order($idOrder);
    
                        if (!Validate::isLoadedObject($order)) {
                            $this->errors[] = 'Selected order does not exist.';
                        }
    
                        $idCashbook = (int)Tools::getValue('id_cashbook');
                        
                        if (FinanceCashbook::orderAlreadyExists($idOrder, $idCashbook)) {
                            $this->errors[] = 'Cash has already been recorded for this order.';
                        }
    
                    }
    
                } else {
    
                    $idOrder = null;
                
                    if ((float)Tools::getValue('amount') <= 0) {
                        $this->errors[] = 'Please enter an amount.';
                    }
    
                }
    
            } else {
    
                $idOrder = null;
    
            }
    
            if (!count($this->errors)) {
    
                $idCashbook = (int)Tools::getValue('id_cashbook');

                if ($idCashbook > 0) {
                
                    $cashbook = new FinanceCashbook($idCashbook);
                
                    if (!Validate::isLoadedObject($cashbook)) {
                        $this->errors[] = 'Transaction not found.';
                        return;
                    }
                
                } else {
                
                    $cashbook = new FinanceCashbook();
                
                }
    
                $oldValues = null;

                if ($idCashbook > 0) {
                    $oldValues = [
                        'type' => $cashbook->type,
                        'id_category' => (int)$cashbook->id_category,
                        'id_order' => (int)$cashbook->id_order,
                        'transaction_date' => $cashbook->transaction_date,
                        'amount' => (float)$cashbook->amount,
                        'description' => $cashbook->description,
                    ];
                }

                $cashbook->type = $type;
                
                if ($type == 'expense') {
                
                    $cashbook->id_category = (int)Tools::getValue('id_category');
                
                } else {
                
                    $cashbook->id_category = 1;
                
                }
                $cashbook->transaction_date = Tools::getValue('transaction_date');
                $cashbook->description = trim(Tools::getValue('description'));
                
                if (
                    $type == 'income' &&
                    Tools::getValue('income_source') == 'order'
                ) {
                
                    $cashbook->id_order = $idOrder;
                
                    // Get the amount directly from the PrestaShop order
                    $cashbook->amount = (float)$order->total_paid_tax_incl;
                
                } else {
                
                    $cashbook->id_order = null;
                
                    // Use the manually entered amount
                    $cashbook->amount = (float)Tools::getValue('amount');
                
                }
    
                if ($idCashbook > 0) {

                    if ($cashbook->update()) {
                        $this->recordUpdateHistory($idCashbook, $oldValues, $cashbook);
                        FinanceCashbookWebhook::send('updated', $idCashbook);

                        Tools::redirectAdmin(
                            self::$currentIndex .
                            '&conf=4&token=' .
                            $this->token
                        );
                    }
                
                } else {
                
                    if ($cashbook->add()) {
                        $this->recordAddHistory((int)$cashbook->id);
                        FinanceCashbookWebhook::send('added', (int)$cashbook->id);
                
                        Tools::redirectAdmin(
                            self::$currentIndex .
                            '&conf=3&token=' .
                            $this->token
                        );
                    }
                
                }
    
                $this->errors[] = 'Unable to save transaction.';
            }
        }
    
        parent::postProcess();
    }
    
    public function renderList()
    {
        $today = date('Y-m-d');
        $currentIndex = self::$currentIndex;
        $token = $this->token;

        $printTodayUrl = $currentIndex .
            '&printFinanceTransactions=1&print_from=' . $today .
            '&print_to=' . $today .
            '&token=' . $token;

        $html = '<div class="panel" style="margin-bottom:15px;">';
        $html .= '<div class="panel-heading"><i class="icon-print"></i> Print Transactions</div>';
        $html .= '<div class="panel-body">';
        $html .= '<form method="get" action="" target="_blank" class="form-inline">';
        $html .= '<input type="hidden" name="controller" value="AdminFinanceCashbook">';
        $html .= '<input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<input type="hidden" name="printFinanceTransactions" value="1">';
        $html .= '<div class="form-group" style="margin-right:10px;">';
        $html .= '<label for="finance_print_from" style="margin-right:5px;">From</label>';
        $html .= '<input type="date" id="finance_print_from" name="print_from" class="form-control" required>';
        $html .= '</div>';
        $html .= '<div class="form-group" style="margin-right:10px;">';
        $html .= '<label for="finance_print_to" style="margin-right:5px;">To</label>';
        $html .= '<input type="date" id="finance_print_to" name="print_to" class="form-control" required>';
        $html .= '</div>';
        $html .= '<button type="submit" class="btn btn-primary">';
        $html .= '<i class="icon-print"></i> Print Selected Dates';
        $html .= '</button>';
        $html .= '</form>';
        $html .= '<div style="margin-top:10px;">';
        $html .= '<a href="' . htmlspecialchars($printTodayUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank" class="btn btn-default">';
        $html .= '<i class="icon-calendar"></i> Print Today';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div></div>';

        return $html . parent::renderList();
    }

    protected function printTransactions()
    {
        $from = Tools::getValue('print_from');
        $to = Tools::getValue('print_to');

        if (!Validate::isDate($from) || !Validate::isDate($to)) {
            die('Invalid print date selection.');
        }

        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $rows = FinanceCashbook::getTransactionsForPrint($from, $to);
        $cashIn = 0;
        $cashOut = 0;

        foreach ($rows as &$row) {
            if ($row['type'] === 'income') {
                $cashIn += (float)$row['amount'];
            } else {
                $cashOut += (float)$row['amount'];
            }
        }
        unset($row);

        $this->context->smarty->assign([
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net' => $cashIn - $cashOut,
            'employee_name' => trim($this->context->employee->firstname . ' ' . $this->context->employee->lastname),
        ]);

        $content = $this->context->smarty->fetch(
            $this->module->getLocalPath() .
            'views/templates/admin/print_transactions.tpl'
        );

        echo $content;
        exit;
    }
    
    public function processDelete()
    {
        $idCashbook = (int)Tools::getValue('id_cashbook');
        $oldValues = $idCashbook ? $this->getTransactionValues($idCashbook) : null;

        $result = parent::processDelete();

        if ($result && $idCashbook && $oldValues) {
            $this->recordDeleteHistory($idCashbook, $oldValues);
            FinanceCashbookWebhook::send('deleted', $idCashbook, $oldValues);
        }

        return $result;
    }

    public function processBulkDelete()
    {
        $ids = Tools::getValue($this->table . 'Box');
        $history = [];

        if (is_array($ids)) {
            foreach ($ids as $idCashbook) {
                $idCashbook = (int)$idCashbook;
                if ($idCashbook) {
                    $values = $this->getTransactionValues($idCashbook);
                    if ($values) {
                        $history[$idCashbook] = $values;
                    }
                }
            }
        }

        $result = parent::processBulkDelete();

        if ($result) {
            foreach ($history as $idCashbook => $oldValues) {
                $this->recordDeleteHistory($idCashbook, $oldValues);
                FinanceCashbookWebhook::send('deleted', $idCashbook, $oldValues);
            }
        }

        return $result;
    }

    protected function getTransactionValues($idCashbook)
    {
        $cashbook = new FinanceCashbook((int)$idCashbook);

        if (!Validate::isLoadedObject($cashbook)) {
            return null;
        }

        return [
            'type' => $cashbook->type,
            'id_category' => (int)$cashbook->id_category,
            'id_order' => (int)$cashbook->id_order,
            'transaction_date' => $cashbook->transaction_date,
            'amount' => (float)$cashbook->amount,
            'description' => $cashbook->description,
        ];
    }

    protected function recordAddHistory($idCashbook)
    {
        $values = $this->getTransactionValues($idCashbook);

        if (!$values) {
            return;
        }

        foreach ($values as $field => $value) {
            FinanceCashbookHistory::add(
                $idCashbook,
                'Added',
                $field,
                null,
                $value
            );
        }
    }

    protected function recordUpdateHistory($idCashbook, $oldValues, $cashbook)
    {
        $newValues = [
            'type' => $cashbook->type,
            'id_category' => (int)$cashbook->id_category,
            'id_order' => (int)$cashbook->id_order,
            'transaction_date' => $cashbook->transaction_date,
            'amount' => (float)$cashbook->amount,
            'description' => $cashbook->description,
        ];

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field];

            if ((string)$oldValue !== (string)$newValue) {
                FinanceCashbookHistory::add(
                    $idCashbook,
                    'Updated',
                    $field,
                    $oldValue,
                    $newValue
                );
            }
        }
    }

    protected function recordDeleteHistory($idCashbook, $oldValues)
    {
        foreach ($oldValues as $field => $oldValue) {
            FinanceCashbookHistory::add(
                $idCashbook,
                'Deleted',
                $field,
                $oldValue,
                null
            );
        }
    }
    
    public function initToolbar()
    {
        parent::initToolbar();
    
        if ($this->display == 'add' || $this->display == 'edit') {
    
            unset($this->toolbar_btn['new']);
    
        }
    }
    
    public function init()
    {
        parent::init();
    
        if (Tools::isSubmit('addfinance_cashbook')) {
            $this->display = 'add';
        }
    
        if (Tools::isSubmit('updatefinance_cashbook')) {
            $this->display = 'edit';
        }
    }
    
    protected function setOrderSearchAssets()
    {
        $this->addCSS(
            $this->module->getPathUri() . 'views/css/order-search.css'
        );
    
        $this->addJS(
            $this->module->getPathUri() . 'views/js/order-search.js'
        );
    
        Media::addJsDef([
            'financeAjaxUrl' => $this->context->link->getAdminLink('AdminFinanceAjax'),
        ]);
    }
    
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
    
        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin.css'
        );
    
        $this->addJS(
            $this->module->getPathUri() . 'views/js/admin.js'
        );
    
        if ($this->display == 'add' || $this->display == 'edit') {
            $this->setOrderSearchAssets();
        }
        
        $this->loadSelectedOrder();
    }
    
    protected function loadSelectedOrder()
    {
        if (!Validate::isLoadedObject($this->object)) {
            return;
        }
    
        if ($this->object->type !== 'income') {
            return;
        }
    
        if (!(int)$this->object->id_order) {
            return;
        }
    
        $order = FinanceOrder::getById((int)$this->object->id_order);
    
        if (!$order) {
            return;
        }
    
        Media::addJsDef([
            'financeSelectedOrder' => [
                'id_order'  => (int)$order['id_order'],
                'customer'  => $order['customer'],
                'amount'    => (float)$order['total_paid_tax_incl'],
                'reference' => $order['reference'],
            ],
        ]);
    }
    
    
}