<?php
namespace App\Repositories\Services\ECImport;

interface ECImportServiceContract
{
    public function import();
    public function importOrder();
    public function groups();
    public function suppliers();
    public function products();
    public function importMaster();
}