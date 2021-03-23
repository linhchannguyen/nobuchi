<?php
// set namspace of file contract Payable service
namespace App\Repositories\Services\Payable;

interface PayableServiceContract {
    /**
     * class PayableServiceContract 
     * define function use in PayableService connect with Controller.
     * @author Chan
     * date: 2019/10/08
     */
    public function listMoneyOwedToSuppliers($year, $fee);
}