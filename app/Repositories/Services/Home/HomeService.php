<?php
// set namspace homeservice
namespace App\Repositories\Services\Home;
use App\Model\Orders\Order;
use Illuminate\Pagination\Paginator as IlluminatePaginator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HomeService implements HomeServiceContract
{
    protected $Order;

    /**
     * function __construct
     * @author channl
     * Created: 2019/12/31
     * Updated: 2019/12/31
     */
    public function __construct(){
        $this->Order = new Order();
    }

    /**
     * function turnOver
     * Description: Hàm thống kê doanh thu
     * get data from datanbase
     * @author channl
     * Created: 2019/12/31
     * Updated: 2019/12/31
     */
    public function turnOver(){
        $date = Carbon::now();
        try{
            $query = $this->Order;
            $str_sub_sql = "
                site_type.id, site_type.name,
                sum(case when total_price > 0 then total_price else 0 end ) as total_price,
                sum(case when import_suc > 0 then import_suc else 0 end ) as import_suc,
                sum(case when import_err > 0 then import_err else 0 end ) as import_err,
                sum(case when turn_over > 0 then turn_over else 0 end ) as turn_over,
                sum(case when last_year_achievement > 0 then last_year_achievement else 0 end ) as last_year_achievement
            ";
            $str_sql = "
                sum(case when orders.status in (3,4,5,6) and CAST(imports.date_import AS DATE)  = '".date('Y/m/d', strtotime($date))."' then order_details.total_price_sale_tax else 0 end) as total_price,
                sum(case when orders.status in (3,4,5,6) and CAST(imports.date_import AS DATE)  = '".date('Y/m/d', strtotime($date))."' then imports.number_success else 0 end) as import_suc,
                sum(case when orders.status in (3,4,5,6) and CAST(imports.date_import AS DATE)  = '".date('Y/m/d', strtotime($date))."' then imports.number_error else 0 end) as import_err,
                sum(case when orders.status in (3,4,5,6) and CAST(imports.date_import AS DATE)  BETWEEN '".($date->year)."/".$date->month."/01' AND '".date('Y/m/d', strtotime($date))."' then order_details.total_price_sale_tax else 0 end) as turn_over";            

                // sum(case when CAST(imports.date_import AS DATE)  BETWEEN '".($date->year-1)."/".$date->month."/01' 
                // AND '".($date->year-1)."/".$date->month."/".$date->day."' then order_details.total_price_sale_tax else 0 end) as last_year_achievement
            $sql_last_year = "
            COALESCE((select
                SUM(case when od_in.total_price_sale_tax <> 0 then od_in.total_price_sale_tax else 0 end) as total_price_sale_tax
                from orders as o_in
                join imports on imports.id = o_in.import_id 
                join order_details od_in on od_in.order_id = o_in.id
                where o_in.status in (3,4,5,6) and cast(imports.date_import as date) between '".($date->year-1)."/".$date->month."/01' 
                AND '".($date->year-1)."/".$date->month."/".$date->day."' 
                and od_in.site_type = orders.site_type
                group by o_in.site_type
            ), 0) as last_year_achievement";
            $query = $query->select(DB::raw($str_sub_sql));
            $query = $query->from(function($subquery) use ($str_sql, $sql_last_year){
                $subquery = $subquery->from('orders')
                                     ->join('order_details', 'orders.id', 'order_details.order_id')
                                     ->join('imports', 'imports.id', 'orders.import_id')
                                     ->whereIn('orders.status', [3,4,5,6]);
                return  $subquery->select('orders.site_type as s_id', DB::raw($str_sql), DB::raw($sql_last_year))
                                 ->groupBy('s_id');
            },'foo');
            $query = $query->join('site_type', 'site_type.id', 'foo.s_id')
                           ->groupBy('site_type.id')->orderBy('site_type.id', 'desc');           
            return $query->get()->toArray();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function rankingOrder($request){        
        $date = Carbon::now();
        try{
            $query = $this->Order;
            $currentPage = 1;
            $per_page = 10;
            if(isset($request['page']))
            {
                $currentPage = $request['page'];
            }
            if(isset($request['per_page']))
            {
                $per_page = $request['per_page'];
            }
            // set page hiện tại để có thể lấy kết quả
            IlluminatePaginator::currentPageResolver(function() use ($currentPage) {
                return $currentPage;
            });
            $str_sql = "
                orders.site_type, order_details.product_code, order_details.product_name, sum(order_details.quantity) as quantity,
                SUM(order_details.total_price_sale_tax) as turn_over, SUM(order_details.total_price_tax) as total_price_tax,
                (select name from site_type where site_type.id = orders.site_type ) as site_name";
            $sql_last_year = "
                COALESCE((select
                    SUM(case when od_in.total_price_sale_tax <> 0 then od_in.total_price_sale_tax else 0 end) as total_price_sale_tax
                    from orders as o_in
                    join imports on imports.id = o_in.import_id 
                    join order_details od_in on od_in.order_id = o_in.id
                    where o_in.status = 6 and date(imports.date_import) between '".($date->year-1)."/".$date->month."/01' AND '".($date->year-1)."/".$date->month."/".$date->day."' 
                    and od_in.product_code = order_details.product_code and od_in.product_name = order_details.product_name
                    and od_in.site_type = orders.site_type
                    group by o_in.site_type, od_in.product_code, od_in.product_name
                ), 0) as last_year_achievement";
            $query = $query->select(DB::raw($str_sql), DB::raw($sql_last_year))
                           ->join('imports', 'imports.id', '=', 'orders.import_id')
                           ->join('order_details', 'order_details.order_id', '=', 'orders.id')
                           ->whereIn('orders.status', [3,4,5,6])
                           ->where(DB::raw('date(imports.date_import)'), date('Y/m/d', strtotime($date)))
                           ->groupBy(DB::raw('orders.site_type, order_details.product_code, order_details.product_name'))
                           ->orderBy('turn_over', 'desc')->paginate($per_page);
            return $query;
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }
}