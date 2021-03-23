<?php
namespace App\Http\ViewComposers;
use Exception;
use Carbon\Carbon;
use App\Model\Orders\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class ShareViewCompoer {
    public function compose(View $view) {
        $totalOrderSidebar = [];
        $now = Carbon::now()->format('Y/m/d');
        $effectiveDate = date('Y/m/d', strtotime("-3 months", strtotime($now)));
        $query = new Order();
        // query bảng order detail: nếu cùng 1 order có nhiều nhà cung cấp thì sum order đó lại
        $sql = "
            sum(case when orders.status = 1 then 1 else 0 end) as status_1,
            sum(case when orders.status = 2 then 1 else 0 end) as status_2,
            sum(case when orders.status = 3 then 1 else 0 end) as status_3,
            sum(case when orders.status = 4 then 1 else 0 end) as status_4,
            sum(case when orders.status = 5 then 1 else 0 end) as status_5
        ";
        // query thống kê: nếu sum > 0 (1 order có 2 dòng order detail) thì lấy 1 else lấy 0
        $sub_sql = "
            sum(case when status_1 > 0 then 1 else 0 end ) as status_1,
            sum(case when status_2 > 0 then 1 else 0 end ) as status_2,
            sum(case when status_3 > 0 then 1 else 0 end ) as status_3,
            sum(case when status_4 > 0 then 1 else 0 end ) as status_4,
            sum(case when status_5 > 0 then 1 else 0 end ) as status_5
        ";
        $query = $query->select(
            DB::raw($sub_sql)
        );
        $query = $query->from(function($subquery) use ($sql, $effectiveDate, $now){
            return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                ->whereBetween(DB::raw('CAST(orders.order_date AS DATE)'), [$effectiveDate, $now])
                                ->whereNotIn('orders.status', [6,7])
                                ->groupBy('orders.id')
                                ->orderBy('orders.id');
                },'foo');
        $totalOrderSidebar = $query->get()->toArray()[0];
        $view->with('totalOrderSidebar', $totalOrderSidebar);
    }
}