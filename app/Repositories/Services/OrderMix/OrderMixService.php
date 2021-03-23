<?php
// set namespace
namespace App\Repositories\Services\OrderMix;

use App\Model\Orders\OrderMix;
use App\Repositories\Services\OrderMix\OrderMixServiceContract;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderMixService implements OrderMixServiceContract
{
    private $OrderMixodel;
    /**
     * function construct
     */
    public function __construct()
    {        
        $this->OrderMixodel = new OrderMix();
    }

    /**
     * function get total table in order
     * @author Dat
     */
    public function getTotalStatus($request = null)
    {
        // status trạng thái order (1: chờ nhận tiền, 2: đang order, 3: đang xử lý đặt hàng, 4: đang xử lý đóng gói, 5: tạo xong bill gửi hàng, 6: thông báo xuất hàng xong, 7 : theo dõi xong, 8: hủy)
        // flag_confirm 0: không có( chưa đánh dấu), 1: cần xác nhận, 2: đang bảo lưu
        $query = $this->OrderMixodel;
        try{
            $query=$query->select(DB::raw("
                count(*) as total,
                sum(case when order_mix.status = '1' then 1 else 0 end ) as o_watting_money,
                sum(case when order_mix.status= '2' then 1 else 0 end ) as o_proccess,
                sum(case when order_mix.status='3' then 1 else 0 end ) as o_proccess_purchase,
                sum(case when order_mix.status='4' then 1 else 0 end ) as o_proccess_wrap,
                sum(case when order_mix.status='5' then 1 else 0 end ) as o_proccess_ship,
                sum(case when order_mix.status='6' then 1 else 0 end ) as o_ship_notified,
                sum(case when order_mix.status='8' then 1 else 0 end ) as o_del,
                sum(case when order_mix.flag_confirm='1' then 1 else 0 end ) as o_confirm,
                sum(case when order_mix.flag_confirm='2' then 1 else 0 end ) as o_save"
            ));
            $param_request = $request->input();
            if(count($param_request) == 0)
            {
                $date = Carbon::now();
                $today = $date->today();
                $query = $query->join('imports', function ($join) use ($today) {
                    $join->on('order_mix.import_id', 'imports.id')
                    ->whereBetween('imports.date_import', ['2018-02-03 23:18:20',$today]);
                 });
                $query = $query->groupBy('order_mix.order_code');
                 return $query->get()->toArray();
            }
        }catch(Exception $exception)
        {
            Log::debug($exception->getMessage());
            return [
                "status" =>false,
                "message" => "Not connect to Databases"
            ];
        }
    }
}