<?php

namespace App\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use App\Model\HistoryProcess\HistoryProcess;
use App\Repositories\Services\Import\ImportServiceContract;
use App\Repositories\Services\ECImport\ECImportServiceContract;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    private $ECimport_service;
    private $import_service;
    protected $historyProcessModel;
    public function __construct(ImportServiceContract $import_service, ECImportServiceContract $ECimport_service, HistoryProcess $historyProcessModel)
    {
        $this->import_service = $import_service;
        $this->ECimport_service = $ECimport_service;    
        $this->historyProcessModel = $historyProcessModel;
    }
    /**
     * function index
     * @author Dat
     */
    public function IndexImport() 
    {
        $data = $this->import_service->IndexImport();
        $this->data['data'] = $data;
        $this->data['title'] = '取込設定';
        $this->data['active'] = 2;
        return view('imports.index',  $this->data);
    }
    /**
     * function import thu cong
     * @author Dat
     * date 2020-01-22
     */
    public function ImportEcCube(Request $request)
    {
        if(empty($request->input('date_from')) && empty($request->input('date_to')))
        {
            return [
                'status' =>false,
                'message' => 'データがありません'
            ];
        }
        $date_from = '';
        $date_to = '';
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $result = $this->import_service->ImportEcCube($date_from, $date_to, 1);
        return $result;
    }
    /**
     * function re import order
     * @author Dat
     *  */ 
    public function ReImport(Request $request)
    {
        $error_id = $request->input('error_id');
        $import_id = $request->input('import_id');
        $list_import = [];
        if(!is_numeric($error_id))
        {
            return [
                'status' => false,
                'message' => 'データがありません'
            ];
        }
        $results_list = $this->import_service->getListImportId($error_id);
        if(empty($results_list))
        {
            return [
                'status' => false,
                'message' => '再取込条件に該当するデータがありません。'
            ];
        }
        $list_error = rtrim($results_list->list_id, ',');
        $list_import = explode(',', $list_error);
        $result  = $this->import_service->ReImport($list_import, $import_id);
        return $result;
    }

    /**
     * function importMaster
     */
    public function importMaster(){
        $hisProcess = $this->historyProcessModel;
        $import = $this->ECimport_service->importMaster();
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $insert_HP['process_screen'] = '取込設定';
        $str_process_description = '<b>マスタ取込:</b> 商品マスタ、仕入先マスタ';
        $insert_HP['process_description'] = $str_process_description;
        if($import){
            $hisProcess->create($insert_HP);
            return [
                'message' => 'マスタを正常に取込みました。'
            ];
        }
        return [
            'message' => 'マスタが取込めていません。再取込んでください。'
        ];
    }
}