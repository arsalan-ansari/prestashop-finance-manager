<?php

require_once dirname(__DIR__, 2) . '/classes/FinanceOrder.php';
require_once dirname(__DIR__, 2) . '/classes/FinanceCashbook.php';

class AdminFinanceAjaxController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->ajax = true;
    }

    public function init()
    {
        parent::init();

        $action = Tools::getValue('action');

        switch ($action) {

            case 'searchOrders':
                $this->searchOrders();
                break;

            case 'getOrder':
                $this->getOrder();
                break;

            case 'checkOrder':
                $this->checkOrder();
                break;

            case 'getOrderAmount':
                $this->getOrderAmount();
                break;

            default:
                $this->json([
                    'success' => false,
                    'message' => 'Invalid action.',
                ]);
        }

        exit;
    }

    protected function searchOrders()
    {
        $keyword = trim(Tools::getValue('keyword'));

        $orders = FinanceOrder::search($keyword);

        $this->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    protected function getOrder()
    {
        $idOrder = (int)Tools::getValue('id_order');

        if (!$idOrder) {

            $this->json([
                'success' => false,
                'message' => 'Invalid order.',
            ]);
        }

        $order = FinanceOrder::getById($idOrder);

        if (!$order) {

            $this->json([
                'success' => false,
                'message' => 'Order not found.',
            ]);
        }

        $this->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    protected function checkOrder()
    {
        $idOrder = (int)Tools::getValue('id_order');

        $this->json([
            'success' => true,
            'exists' => FinanceOrder::exists($idOrder),
            'captured' => FinanceOrder::alreadyCaptured($idOrder),
        ]);
    }

    protected function getOrderAmount()
    {
        $idOrder = (int)Tools::getValue('id_order');

        $this->json([
            'success' => true,
            'amount' => FinanceOrder::getAmount($idOrder),
        ]);
    }

    protected function json(array $data)
    {
        header('Content-Type: application/json; charset=utf-8');
    
        exit(json_encode($data));
    }
}