<?php

require_once dirname(__DIR__, 2) . '/classes/FinanceCashbook.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceCategory.php';

class AdminFinanceReportsController extends ModuleAdminController
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
        $rows = FinanceCashbook::getTransactions(100000, 0);

        usort($rows, function ($a, $b) {

            $date = strcmp($a['transaction_date'], $b['transaction_date']);

            if ($date == 0) {
                return $a['id_cashbook'] - $b['id_cashbook'];
            }

            return $date;

        });

        $balance = 0;

        foreach ($rows as &$row) {

            $row['cash_in'] = '';
            $row['cash_out'] = '';

            if ($row['type'] == 'income') {

                $row['cash_in'] = (float)$row['amount'];
                $balance += (float)$row['amount'];

            } else {

                $row['cash_out'] = (float)$row['amount'];
                $balance -= (float)$row['amount'];

            }

            $row['balance'] = $balance;
        }

        unset($row);

        $this->context->smarty->assign([
            'cash_in_hand' => $balance,
            'ledger' => array_reverse($rows),
        ]);

        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() .
            'views/templates/admin/reports.tpl'
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

        $this->page_header_toolbar_title = 'Finance Ledger';

        $this->page_header_toolbar_btn['export_csv'] = [
            'href' => self::$currentIndex .
                '&exportFinanceCsv=1&token=' . $this->token,
            'desc' => 'Export CSV',
            'icon' => 'process-icon-export',
        ];
    }
    
        public function postProcess()
    {
        if (Tools::isSubmit('exportFinanceCsv')) {
            $this->exportCsv();
        }

        parent::postProcess();
    }

    protected function exportCsv()
    {
        $rows = FinanceCashbook::getTransactions(100000, 0);

        usort($rows, function ($a, $b) {

            $date = strcmp($a['transaction_date'], $b['transaction_date']);

            if ($date == 0) {
                return $a['id_cashbook'] - $b['id_cashbook'];
            }

            return $date;

        });

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=finance_ledger.csv');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Date',
            'Transaction ID',
            'Description',
            'Cash In',
            'Cash Out',
            'Balance',
        ]);

        $balance = 0;

        foreach ($rows as $row) {

            $cashIn = '';
            $cashOut = '';

            if ($row['type'] == 'income') {

                $cashIn = number_format((float)$row['amount'], 2, '.', '');
                $balance += (float)$row['amount'];

            } else {

                $cashOut = number_format((float)$row['amount'], 2, '.', '');
                $balance -= (float)$row['amount'];

            }

            fputcsv($output, [
                $row['transaction_date'],
                '#' . $row['id_cashbook'],
                $row['description'],
                $cashIn,
                $cashOut,
                number_format($balance, 2, '.', ''),
            ]);
        }

        fclose($output);
        exit;
    }
}