<?php

namespace App\Http\Controllers\Payables;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Repositories\Services\Payable\PayableServiceContract;

class PayableController extends Controller
{
    /**
     * class Payable controller
     * class controll the data and show data in view
     * @author channl
     * date 2019/10/03
     */
    private $PayableService;
    public function __construct(PayableServiceContract $PayableService)
    {
        $this->PayableService = $PayableService;
    }
    
    /**
     * function index
     * @author channl
     * date 2019/10/08
     */
    public function index()
    {
        $this->data['title'] = '仕入先別買掛一覧';
        $this->data['active'] = 6;
        return view('payables.index', $this->data);
    }

    /**
     * function ajax_money_owed_to_suppliers
     * description: thống kê tiền phải trả cho từng nhà cung cấp
     * @author channl
     * Created: 2019/10/16
     * Updated: 2019/10/16
     */
    public function ajax_money_owed_to_suppliers(Request $request){
        $year = $request->get('year');//thống kê trước năm $year
        $fee = (int)$request->get('fee');//0: chưa có thuế, 1: đã có thuế
        $this->data['data'] = $this->PayableService->listMoneyOwedToSuppliers($year, $fee);
        if(!empty($this->data['data'])){
            $result = array (
                'data' => $this->data['data']
            );
        }else {
            $result = array (
                'data' => ''
            );
        }
        return Response::json($result);
    }
}
