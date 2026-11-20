<?php

class AdminFinanceDashboardController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'finance_cashbook';
        $this->className = 'FinanceCashbook';

        parent::__construct();
    }

    public function initContent()
    {
        $todayIncome = FinanceCashbook::getTodayIncome();
        $todayExpense = FinanceCashbook::getTodayExpense();

        $monthIncome = FinanceCashbook::getMonthIncome();
        $monthExpense = FinanceCashbook::getMonthExpense();

        $this->context->smarty->assign([
            'today_income'  => $todayIncome,
            'today_expense' => $todayExpense,
            'today_net'     => $todayIncome - $todayExpense,

            'month_income'  => $monthIncome,
            'month_expense' => $monthExpense,
            'month_net'     => $monthIncome - $monthExpense,
        ]);

        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() .
            'views/templates/admin/dashboard.tpl'
        );

        parent::initContent();
    }

    public function renderView()
    {
        return $this->content;
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_title = 'Finance Dashboard';

        $this->page_header_toolbar_btn['cash_in'] = [
            'href' => $this->context->link->getAdminLink('AdminFinanceCashbook')
                . '&addfinance_cashbook&type=income',
            'desc' => 'Cash In',
            'icon' => 'process-icon-new',
        ];

        $this->page_header_toolbar_btn['cash_out'] = [
            'href' => $this->context->link->getAdminLink('AdminFinanceCashbook')
                . '&addfinance_cashbook&type=expense',
            'desc' => 'Cash Out',
            'icon' => 'process-icon-new',
        ];

        $this->page_header_toolbar_btn['categories'] = [
            'href' => $this->context->link->getAdminLink('AdminFinanceCategories'),
            'desc' => 'Categories',
            'icon' => 'process-icon-edit',
        ];
    }
}