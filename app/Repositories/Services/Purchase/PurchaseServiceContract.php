<?php
// set namspace of file contract Purchase service
namespace App\Repositories\Services\Purchase;

interface PurchaseServiceContract {
    /**
     * class PurchaseservicerContract 
     * define function use in Purchaseservice connect with Controller.
     * @author channl
     * date: 2019/10/25
     */

    public function getTotalOrder($range, $date_from, $date_to);
    public function getListPurchaseBySupplier($range, $date_from, $date_to);
    public function exportPurchase($date_from = null, $date_to = null, $supplier_id = null, $range = null, $order_detail_id = [], $stage1 = null, $stage2);
    public function updateStatusAtPurchase($arr_order = null, $screen = null);
}