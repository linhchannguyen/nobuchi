<?php
// namspace use
namespace App\Repositories\Services\Product;

interface ProductServiceContract
{
    public function searchSku();
    public function getCategoriesProduct();
    public function getGroupId();
}