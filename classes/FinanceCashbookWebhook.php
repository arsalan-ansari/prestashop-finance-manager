<?php

class FinanceCashbookWebhook
{
    public static function send($action, $idCashbook, $values = null)
    {
        $enabled = Configuration::get('FINANCE_WEBHOOK_ENABLED');
        if ($enabled === false) {
            $enabled = Configuration::get('FINANCE_PABBLY_WEBHOOK_ENABLED', null, null, null, 1);
        }

        if (!(int)$enabled) {
            return true;
        }

        $webhookUrl = trim((string)Configuration::get('FINANCE_WEBHOOK_URL'));
        if ($webhookUrl === '') {
            $webhookUrl = trim((string)Configuration::get('FINANCE_PABBLY_WEBHOOK_URL'));
        }

        if ($webhookUrl === '') {
            return true;
        }

        $idCashbook = (int)$idCashbook;

        if (!$values) {
            $cashbook = new FinanceCashbook($idCashbook);

            if (!Validate::isLoadedObject($cashbook)) {
                return false;
            }

            $values = [
                'type' => $cashbook->type,
                'id_category' => (int)$cashbook->id_category,
                'id_order' => (int)$cashbook->id_order,
                'transaction_date' => $cashbook->transaction_date,
                'amount' => (float)$cashbook->amount,
                'description' => $cashbook->description,
                'date_add' => $cashbook->date_add,
                'date_upd' => $cashbook->date_upd,
            ];
        }

        $categoryName = Db::getInstance()->getValue(
            'SELECT name FROM `' . _DB_PREFIX_ . 'finance_category`' .
            ' WHERE id_category = ' . (int)$values['id_category']
        );

        $employee = Context::getContext()->employee;
        $employeeName = '';

        if (Validate::isLoadedObject($employee)) {
            $employeeName = trim($employee->firstname . ' ' . $employee->lastname);
        }

        $amount = (float)$values['amount'];

        $payload = [
            'action' => $action,
            'transaction_id' => $idCashbook,
            'transaction_date' => $values['transaction_date'],
            'type' => $values['type'],
            'category_id' => (int)$values['id_category'],
            'category' => (string)$categoryName,
            'order_id' => (int)$values['id_order'],
            'description' => (string)$values['description'],
            'amount' => number_format($amount, 2, '.', ''),
            'cash_in' => $values['type'] === 'income' ? number_format($amount, 2, '.', '') : '0.00',
            'cash_out' => $values['type'] === 'expense' ? number_format($amount, 2, '.', '') : '0.00',
            'employee' => $employeeName,
            'date_add' => isset($values['date_add']) ? $values['date_add'] : '',
            'date_upd' => isset($values['date_upd']) ? $values['date_upd'] : '',
        ];

        $ch = curl_init($webhookUrl);

        if (!$ch) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Webhook failure must never prevent the Finance Manager transaction from saving.
        return $response !== false && $httpCode >= 200 && $httpCode < 300;
    }
}
