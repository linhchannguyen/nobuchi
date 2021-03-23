<?php
// set name space
namespace App\Repositories\Services\SupplierHome;

interface SupplierHomeServiceContract
{
    public function getListPurchaseBySupplier($supplier_id, $year, $month);
    public function getPurchaseDetailByDate($supplier_id, $date, $date_to = null, $flag_p_status_1, $flag_p_status_2, $flag_p_status_3);
    public function updatePurchaseDetail($request);
    public function updatePurchaseStatus($request);
}