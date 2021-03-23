<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Services\Supplier\SupplierServiceContract;

class SupplierController extends Controller
{
    //
    private $supplier_service;
    public function __construct(SupplierServiceContract $supplier_service)
    {
        $this->supplier_service = $supplier_service;
    }
    /**
     * function search modal supplier
     * @author Dat
     * 2019/10/15
     */
    public function search_modal_supplier(Request $request)
    {
        $data = $this->supplier_service->searchSupplierModal($request);
        return $data;
    }
}
