<?php

namespace App\Http\Controllers\SupplierHome;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Repositories\Services\SupplierHome\SupplierHomeServiceContract;

class SupplierHomeController extends Controller
{
    private $SupplierHomeService;
    public function __construct(SupplierHomeServiceContract $SupplierHomeService){
        $this->SupplierHomeService = $SupplierHomeService;
    }
    /**
     * function index
     * Description: index of supplier when login with supplier account
     */
    public function index(){
        $date = Carbon::now()->format('Y/m/d');
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $user = auth()->user();
        $this->data['title'] = '仕入先様用トップ';
        return view('home.supplier.index', $this->data);
    }

    /**
     * function ajax_search_purchase
     * Description: thống kê đơn đặt hàng của nhà cung cấp login
     * @author channl
     * Created: 2019/11/07
     * Updated: 2019/11/07
     */
    public function ajax_search_purchase(Request $request){
        $result = array (
            'success' => false
        );
        $user_login = auth()->user();
        $supplier_id = $user_login->supplier_id;
        $year = (int)$request['year'];
        $month = (int)$request['month'];
        $data = $this->SupplierHomeService->getListPurchaseBySupplier($supplier_id, $year, $month);
        if(count($data) > 0){
            $result = array (
                'success' => true,
                'data' => $data,
            );
        }
        return Response::json($result);
    }
}
