<?php
// set namspace of file contract shipment service
namespace App\Repositories\Services\ShipmentNotification;

interface ShipmentNotificationServiceContract {
    /**
     * class ShipmentNotificationServiceContract 
     * define function use in ShipmentNotificationService connect with Controller.
     * @author channl
     * date: 2019/10/15
     */
    public function getTotalOrder($range, $date_from, $date_to);
    public function getListShipmentBySiteType($range, $date_from, $date_to);
    public function getListSupplierBySiteType($request = null);
    public function updateStatusAtShipmentNotification($arr_order);
    public function getRecordImport($query_shipbill);
    public function updateRecordImport($data);
}