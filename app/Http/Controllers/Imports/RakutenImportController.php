<?php

namespace App\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Services\RakutenImport\RakutenImportServiceContract;
use Config;

class RakutenImportController extends Controller
{
    private $Rakutenimport_service;

    public function __construct(RakutenImportServiceContract $Rakutenimport_service)
    {
        $this->Rakutenimport_service = $Rakutenimport_service;
    }
    //
    public function import(Request $request) 
    {
        $data = $this->Rakutenimport_service->import();
        print_r($data);
        return;
    }
}
