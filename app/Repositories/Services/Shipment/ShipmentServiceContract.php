<?php
// set namspace of file contract shipment service
namespace App\Repositories\Services\Shipment;

interface ShipmentServiceContract {
    /**
     * class ShipmentservicerContract 
     * define function use in shipmentservice connect with Controller.
     * @author Dat
     * date: 2019/10/03
     */
    public function getBillNumber($times);
    public function getTotalOrder($range, $date_from, $date_to);
    public function getListShipmentByDeliveryMethod($range, $date_from, $date_to);
    public function exportShipment($list_order_details, $date_from = null, $date_to = null, $delivery_method = null, $range = null, $stage1 = null, $stage2);
    public function getListSupplierByDeliveryMethod($request = null);
    public function ExportNotificationAmazon($data_details =[]);
    public function updateStatusAtShipment($order_id);
    public function updateOrderDetail($detail_id, $shipment_id);
    public function getListProductStatus($arr_product_id);
}