<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route login


// IMPORT ROUTE

use Illuminate\Support\Facades\Route;

Route::prefix('import')->group(function () {
    Route::get('eccube', 'Imports\ECImportController@import');
    // Route::get('rakuten', 'Imports\RakutenImportController@import');
});
Route::get('login', 'User\UserController@login')->name('login');
Route::post('login', 'User\UserController@doLogin');
// dat add
Route::get('logout', 'User\UserController@logout');
Route::get('access-denied', 'AccessDenied\AccessDeniedController@index');
// end
// middleware group 
Route::middleware('auth')->group(function () {
    // admin permission
    Route::middleware('admin')->group(function () {
        // order group route dat add
        Route::prefix('order')->group(function () {
            Route::get('search-order', 'Order\OrderController@searchIndex');
            Route::get('ajax-search-conditions', 'Order\OrderController@ajax_search_conditions');
            Route::post('ajax-update-order', 'Order\OrderController@ajax_update_order');
            Route::get('edit-order/{id}', 'Order\OrderController@editOrder');
            Route::post('ajax-edit-order/{id}', 'Order\OrderController@ajax_edit_order');
            Route::get('create', 'Order\OrderController@createOrder');
            Route::post('ajax-create-order', 'Order\OrderController@ajax_create_order');
            Route::post('ajax-copy-order', 'Order\OrderController@ajax_copy_order');
            Route::post('ajax-delete-order', 'Order\OrderController@ajax_delete_order');
            Route::get('export-purchase', 'Order\OrderController@ExportPurchase');
            Route::get('export-bill-yamoto', 'Order\OrderController@ExportBillYamoto');
            Route::get('export-notification-amazon', 'Order\OrderController@ExportNotificationAmazon');
            Route::post('ajax-check-shipcode', 'Order\OrderController@ajax_check_shipcode')->name('ajax_check_shipcode');
        });
        Route::prefix('product')->group(function () {
            Route::get('search-sku', 'Products\ProductController@search_sku');
            Route::get('categories', 'Products\ProductController@get_categories_id');
        });
        Route::prefix('supplier')->group(function () {
            Route::get('search-modal-supplier', 'Suppliers\SupplierController@search_modal_supplier');
        });
        Route::prefix('import')->group(function () {
            Route::get('', 'Imports\ImportController@IndexImport');
            Route::post('ajax-import-ec-cube', 'Imports\ImportController@ImportEcCube');
            Route::post('ajax-re-import', 'Imports\ImportController@ReImport');
            Route::post('ajax-import-master', 'Imports\ImportController@importMaster')->name('imoport-master');
        });
        // end dat
        Route::get('/', 'Home\HomeController@index'); 

        // [5] purchase screen
        Route::group(['prefix' => '/purchase'], function () {
            Route::get('/', 'Purchases\PurchaseController@index');
            Route::post('/ajax-search-purchase', 'Purchases\PurchaseController@ajax_search_purchase');
            // Route::get('/ajax-export-purchase', 'Purchases\PurchaseController@ajax_export_purchase');
        });
        
        // [6] shipment screen
        Route::group(['prefix' => '/shipment'], function () {
            Route::get('/', 'Shipments\ShipmentController@index');
            Route::post('/ajax-search-shipment', 'Shipments\ShipmentController@ajax_search_shipment');
            Route::get('/ajax-get-shipment-code', 'Shipments\ShipmentController@ajax_get_shipment_code');
            Route::get('/ajax-export-shipment', 'Shipments\ShipmentController@ajax_export_shipment');
            Route::get('/ajax-export-shipment-II', 'Shipments\ShipmentController@ajax_export_shipment_II');
            Route::get('/ajax-export-shipment-bill', 'Shipments\ShipmentController@ajax_export_shipment_bill');
            Route::post('/ajax-get-list-supplier-by-delivery-method', 'Shipments\ShipmentController@ajax_get_list_supplier');
            Route::get('/ajax-export-sagawa-shipment', 'Shipments\ShipmentController@ajax_export_sagawa_shipment');
        });
        
        // [7] shipment notification screen
        Route::group(['prefix' => '/shipment-notification'], function () {
            Route::get('/', 'Shipments\ShipmentNotificationController@index');
            Route::post('/ajax-search-shipment', 'Shipments\ShipmentNotificationController@ajax_search_shipment_notifi');
            Route::post('/ajax-get-list-supplier-by-site-type', 'Shipments\ShipmentNotificationController@ajax_get_list_supplier_by_site_type');
            Route::get('/ajax-export-shipment-notification', 'Shipments\ShipmentNotificationController@ajax_export_shipment_notification');
            Route::get('export-notification-amazon', 'Order\OrderController@ExportNotificationAmazon');
            Route::post('/ajax-import-shipbill', 'Shipments\ShipmentNotificationController@ImportShipmentBill')->name('import-shipbill');
        });

        // [8] payable screen
        Route::group(['prefix' => '/payable'], function () {
            Route::get('/', 'Payables\PayableController@index');
            Route::post('/ajax-money-owed-to-suppliers', 'Payables\PayableController@ajax_money_owed_to_suppliers');
        });
 
        // [9] payable detail screen
        Route::group(['prefix' => '/payable-detail'], function () {
            Route::any('/', 'Payables\PayableDetailController@index')->name('search-payable-detail');
            Route::post('/ajax-update-order-detail', 'Payables\PayableDetailController@ajax_update_order_detail');
            Route::post('/ajax-search-order-detail', 'Payables\PayableDetailController@ajax_search_order_detail');
            Route::get('/ajax-order-payable', 'Payables\PayableDetailController@ajax_order_payable');
        });
        Route::prefix('master')->group(function () {
            Route::get('users', 'Masters\MasterUserController@masterUsersIndex')->name('user-list');
            Route::post('users/ajax-add-users', 'Masters\MasterUserController@ajax_add_users')->name('ajax-add-users');
            Route::post('users/ajax-delete-users', 'Masters\MasterUserController@ajax_delete_users')->name('ajax-delete-users');
            Route::post('users/ajax-update-users', 'Masters\MasterUserController@ajax_update_users')->name('ajax-update-users');
            Route::post('users/ajax-get-list-process-history', 'Masters\MasterUserController@ajax_get_list_process_history')->name('get-list-process-history');
        });
        Route::get('/', 'Home\HomeController@index'); 
    }); // check quyen
    // end dat
    // end admin permission

    // supplier permission
    Route::middleware('supplier')->group(function () {
        Route::group(['prefix' => '/supplier'], function(){
            // [21] home supplier screen
            Route::get('/', 'SupplierHome\SupplierHomeController@index')->name('supplier-index');
            Route::post('/ajax-search-purchase', 'SupplierHome\SupplierHomeController@ajax_search_purchase');
            // [22] purchase supllier screen
            Route::get('/purchase-confirm', 'SupplierHome\PurchaseConfirmController@index')->name('search-purchase'); 
            Route::post('/ajax-update-order-detail', 'SupplierHome\PurchaseConfirmController@ajax_update_order_detail');
            Route::post('/ajax-update-status-purchase', 'SupplierHome\PurchaseConfirmController@ajaxUpdateStatusPurchase');

        });
    });
    // end supplier permission       
    Route::get('/purchase/ajax-export-purchase', 'Purchases\PurchaseController@ajax_export_purchase');
    Route::get('/', 'Home\HomeController@index');
});