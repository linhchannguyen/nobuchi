<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// define use class in provider
// homeservice and homeserviceContract
use App\Repositories\Services\Home\HomeService;
use App\Repositories\Services\Home\HomeServiceContract;
// use shipmentservice and shipmentserviceContract
use App\Repositories\Services\Shipment\ShipmentService;
use App\Repositories\Services\Shipment\ShipmentServiceContract;
// use shipmentservice and ShipmentTypeServiceContract
use App\Repositories\Services\ShipmentType\ShipmentTypeService;
use App\Repositories\Services\ShipmentType\ShipmentTypeServiceContract;
// ShipmentNotificationService service and ShipmentNotificationServiceContract
use App\Repositories\Services\ShipmentNotification\ShipmentNotificationService;
use App\Repositories\Services\ShipmentNotification\ShipmentNotificationServiceContract;
// PayableService service and PayableServiceContract
use App\Repositories\Services\Payable\PayableService;
use App\Repositories\Services\Payable\PayableServiceContract;
// PayableService service and PayableServiceContract
use App\Repositories\Services\PayableDetail\PayableDetailService;
use App\Repositories\Services\PayableDetail\PayableDetailServiceContract;
// PurchaseService service and PurchaseServiceContract
use App\Repositories\Services\Purchase\PurchaseService;
use App\Repositories\Services\Purchase\PurchaseServiceContract;
// User service
use App\Repositories\Services\User\UserService;
use App\Repositories\Services\User\UserServiceContract;
// order service
use App\Repositories\Services\Order\OrderService;
use App\Repositories\Services\Order\OrderServiceContract;
// product service
use App\Repositories\Services\Product\ProductService;
use App\Repositories\Services\Product\ProductServiceContract;
// supplier service
// ec import
use App\Repositories\Services\ECImport\ECImportService;
use App\Repositories\Services\ECImport\ECImportServiceContract;
// rakuten import
use App\Repositories\Services\RakutenImport\RakutenImportService;
use App\Repositories\Services\RakutenImport\RakutenImportServiceContract;
// use import service
use App\Repositories\Services\Import\ImportService;
use App\Repositories\Services\Import\ImportServiceContract;
// supplier
use App\Repositories\Services\Supplier\SupplierService;
use App\Repositories\Services\Supplier\SupplierServiceContract;
// supplier home
use App\Repositories\Services\SupplierHome\SupplierHomeService;
use App\Repositories\Services\SupplierHome\SupplierHomeServiceContract;
// order Mix
//master user
use App\Repositories\Services\Masters\MasterUserServiceContract;
use App\Repositories\Services\Masters\MasterUserService;
// use order detail
use App\Repositories\Services\Order\OrderDetailService;
use App\Repositories\Services\Order\OrderDetailServiceContract;
use App\Repositories\Services\OrderMix\OrderMixService;
use App\Repositories\Services\OrderMix\OrderMixServiceContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ShipmentServiceContract::class, ShipmentService::class); // bind class homserviceContract and homeservice 
        $this->app->bind(ShipmentTypeServiceContract::class, ShipmentTypeService::class); // bind class homserviceContract and homeservice 
        $this->app->bind(HomeServiceContract::class, homeservice::class); // bind class homserviceContract and homeservice
        $this->app->bind(UserServiceContract::class, UserService::class);// bind class UserServiceContract and class UserService
        $this->app->bind(OrderServiceContract::class, OrderService::class);// bind class OrderServiceContract and OrderService
        $this->app->bind(ShipmentNotificationServiceContract::class, ShipmentNotificationService::class);// bind class ShipmentNotificationServiceContract and class ShipmentNotificationService
        $this->app->bind(PayableServiceContract::class, PayableService::class);// bind class PayableServiceContract and class PayableService
        $this->app->bind(PayableDetailServiceContract::class, PayableDetailService::class);// bind class PayableDetailServiceContract and class PayableDetailService
        $this->app->bind(PurchaseServiceContract::class, PurchaseService::class);// bind class PurchaseServiceContract and class PurchaseService
        $this->app->bind(ProductServiceContract::class, ProductService::class);// bind class OrderServiceContract and OrderService
        $this->app->bind(SupplierServiceContract::class, SupplierService::class);// bind class SupplierServiceContract and SupplierService
        $this->app->bind(RakutenImportServiceContract::class, RakutenImportService::class);
        $this->app->bind(ECImportServiceContract::class, ECImportService::class);// bind class ECImportServiceContract and ECImportService
        $this->app->bind(ImportServiceContract::class, ImportService::class);// bind class ImportServiceContract and ImportService
        $this->app->bind(OrderMixServiceContract::class, OrderMixService::class);// bind class OrderMixServiceContract and OrderMixService
        $this->app->bind(SupplierHomeServiceContract::class, SupplierHomeService::class);// bind class SupplierHomeServiceContract and SupplierHomeService
        $this->app->bind(MasterUserServiceContract::class, MasterUserService::class);// bind class SupplierHomeServiceContract and SupplierHomeService
        $this->app->bind(OrderDetailServiceContract::class, OrderDetailService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        //write all log query database
        $ip = $request->ip(); // lấy IP
        DB::listen(function($query) use ($ip) {
            $messlog ="Ip $ip query ($query->sql)";
            Log::info($messlog, $query->bindings, $query->time); 
        });
    }
}
