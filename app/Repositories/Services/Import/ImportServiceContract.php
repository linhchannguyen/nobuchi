<?php
// set namespace
namespace App\Repositories\Services\Import;

interface ImportServiceContract
{
    public function IndexImport();
    public function ImportEcCube ($date_from, $date_to, $site_type);
    public function getListImportId($import_id);
    public function ReImport ($list_order = null, $import_id = null);
}