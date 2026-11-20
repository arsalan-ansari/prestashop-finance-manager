<?php

require_once dirname(__DIR__, 2) . '/classes/FinanceCashbook.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceCashbookHistory.php';

class AdminFinanceHistoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'finance_cashbook_history';
        $this->identifier = 'id_history';
        $this->lang = false;

        parent::__construct();
    }

    public function initContent()
    {
        if (!$this->context->employee->isSuperAdmin()) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminFinanceCashbook')
            );
        }

        $idCashbook = (int)Tools::getValue('id_cashbook');
        $history = [];
        $transaction = null;

        if ($idCashbook > 0) {
            $transaction = new FinanceCashbook($idCashbook);

            if (Validate::isLoadedObject($transaction)) {
                $history = FinanceCashbookHistory::getByCashbookId($idCashbook);
            }
        }

        foreach ($history as &$row) {
            $row['field_display'] = FinanceCashbookHistory::formatFieldName($row['field_name']);

            $row['old_display'] = FinanceCashbookHistory::formatValue(
                $row['field_name'],
                $row['old_value']
            );

            $row['new_display'] = FinanceCashbookHistory::formatValue(
                $row['field_name'],
                $row['new_value']
            );
        }
        unset($row);

        $this->context->smarty->assign([
            'history' => $history,
            'transaction' => $transaction,
            'id_cashbook' => $idCashbook,
            'back_url' => self::$currentIndex . '&token=' . $this->token,
        ]);

        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() .
            'views/templates/admin/history.tpl'
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

        $this->page_header_toolbar_title = 'Finance History';
    }
}
