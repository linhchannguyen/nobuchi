<?php
//  set namespace
namespace App\Repositories\Services\Order;
interface OrderServiceContract
{
    /**
     * interface contract order service class
     * define function order service
     * @author Dat
     * date 2019/10/07
     */
    public function searchConditions();
    public function getTotalStatus();
    public function updateOrder($request);
    public function getByOrderId($orderId);
    public function getDetailOrder($id);
    public function editOrder($data_order, $orderId);
    public function createOrder($order, $add_detail, $ship_exist);
    public function copyOrders ($request);
    public function deleteOrders ($request);
    public function getDataExport();
    public function getLastId();
}