<?php
// set namspace of file contract PayableDetail service
namespace App\Repositories\Services\PayableDetail;

interface PayableDetailServiceContract {
    /**
     * class PayableDetailServiceContract 
     * define function use in PayableDetailService connect with Controller.
     * @author Chan
     * date: 2019/10/08
     */
    public function PayableDetail($supplier_id = null, $year, $month, $order_id = null, $purchase_id = null);
    public function updateOrderDetail($request);
    public function exportPayableForSupplier($order_detail_id = []);
}