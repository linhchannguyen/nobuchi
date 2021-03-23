<?php
 
 namespace App\Repositories\Services\Order;

 interface OrderDetailServiceContract
 {
     public function editOrderDetails($data_detail, $order_id, $list_order_del, $ship_exist);
     public function addOrderDetails($data_add_details, $order_id, $ship_exist);
     public function checkUpdate($updated_at);
 }