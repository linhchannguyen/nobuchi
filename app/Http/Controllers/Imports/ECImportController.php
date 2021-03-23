<?php

namespace App\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Services\ECImport\ECImportServiceContract;
use App\Repositories\Services\Order\OrderServiceContract;
use Config;

class ECImportController extends Controller
{
    private $ECimport_service;
    private $Order_service;

    public function __construct(ECImportServiceContract $ECimport_service, 
    OrderServiceContract $a)
    {
        $this->ECimport_service = $ECimport_service;    
        $this->Order_service =  $a;
    }
    //
    public function import(Request $request) 
    {
        $data = $this->ECimport_service->import();
        $order = $this->Order_service->getTotalStatus($request);
        return;
    }
}
