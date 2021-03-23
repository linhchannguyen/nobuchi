<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Services\Product\ProductServiceContract;

class ProductController extends Controller
{
    //
    private $product_service;
    public function __construct(ProductServiceContract $product_service)
    {
        $this->product_service = $product_service;
    }
    /**
     * function search sku modal
     */
    public function search_sku (Request $request)
    {
        $data = $this->product_service->searchSku($request);
        return $data;
    }
    /**
     * function get categories
     * @author Dat
     * 2019/10/14
     */
    public function get_categories_id(Request $request)
    {
        $products = $this->product_service->getGroupId($request);
        return $products;
    }
}
