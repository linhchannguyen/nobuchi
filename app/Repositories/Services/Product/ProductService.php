<?php
// namespace
namespace App\Repositories\Services\Product;

use App\Model\Groups\Group;
use App\Repositories\Services\Product\ProductServiceContract;
use App\Model\Products\Product;
use App\Model\Products\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductService implements ProductServiceContract
{

    private $product_model;
    private $product_category_model;
    public function __construct()
    {
        $this->product_model = new Product();
        $this->product_category_model = new ProductCategory();
    }
    /**
     * function search SKU product
     */
    public function searchSku($request = null)
    {
        $now = Carbon::now();
        $date = date('Y-m-d', strtotime($now));
        $query  = $this->product_model;
        try
        {
            $query = $query->selectRaw('products.*, delivery_methods.*, foo.tax_rate, product_statuses.product_status_id, suppliers.name as sup_name')
            ->leftJoin('suppliers', 'products.supplied_id', '=', 'suppliers.id')
            ->leftJoin('delivery_methods', 'suppliers.shipping_method','=','delivery_methods.id')
            ->leftJoin('product_statuses','product_statuses.product_id', '=', 'products.product_id')
            ->leftJoin(DB::raw("(select tax_rate, tax_class from tax_details
                where CAST(apply_date as date) <= '$date' and tax_class = tax_details.tax_class
                order by apply_date DESC limit 1) as foo"), 
                function($join){
                    $join->on('products.tax_class', '=', 'foo.tax_class');
                }
            );
            if(isset($request['supplied_id'])&& $request['supplied_id'] != '')
            {
                $query = $query->where('suppliers.name','like', "%".$request['supplied_id']."%");
            }
            if(!empty($request['group']) && !empty($request['category_id']))
            {
                $group = $request['group'];
                $file_group = "group$group"."_id";
                $query = $query->where("$file_group" , $request['category_id']);
            }
            if(isset($request['delivery_method'])&& count($request['delivery_method'])> 0)
            {
                $query = $query->whereIn('shipping_method', $request['delivery_method']);
            }
            if(isset($request['orther'])&& count($request['orther'])> 0)
            {
                $query = $query->whereIn('product_statuses.product_status_id', $request['orther'])->where('product_statuses.del_flg', 0);
            }
            if(isset($request['sku'])&& $request['sku'] != '')
            {
                $query = $query->where('products.sku', 'like', "%".$request['sku']."%");
            }
            if(isset($request['product_name'])&& $request['product_name'] != '')
            {
                $query = $query->where('products.short_name', 'like', "%".$request['product_name']."%");
            }
            $query = $query->where('products.status', $request['status_flg']);
            $query = $query->where('products.handling_flg', $request['handling_flg']);
            $query = $query->where('products.product_del_flg', 0)->where('products.product_class_del_flg', 0);
            $query = $query->whereNotNull('code')->whereNotNull('products.supplied_id');
            $query = $query->get()->toArray();
            return $query;
        }catch (Exception $exception)
        {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    /**
     * functio getCategoriesProduct
     * get all categories
     * @author Dat
     * 2019/10/14
     */
    public function getCategoriesProduct ()
    {
        $query = $this->product_category_model;
        try
        {
            $query = $query->all();
            return $query->toArray();

        } catch (Exception $exception)
        {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    /**
     * function getCategoriesId
     * @author Dat
     * 2019/10/14
     */
    public function getGroupId($request = null)
    {
        $query = $this->product_model;
        $groups = new Group();
        try
        {
            if(!empty($request['group']))
            {
                $query = $query->selectRaw('groups.id, groups.name as name');
                $groupId = $request['group'];
                if($groupId == 1)
                {
                    $query = $query->join('groups', 'groups.id', 'products.group1_id')
                                    ->where('groups.level', $groupId);
                } else if($groupId == 2)
                {
                    $query = $query->join('groups', 'groups.id', 'products.group2_id')
                                    ->where('groups.level', $groupId);
                }else if($groupId == 3)
                {
                    $query = $query->join('groups', 'groups.id', 'products.group3_id')
                                    ->where('groups.level', $groupId);
                }else if($groupId == 4)
                {
                    $query = $query->join('groups', 'groups.id', 'products.group4_id')
                                    ->where('groups.level', $groupId);
                }else if($groupId == 5)
                {
                    $query = $query->join('groups', 'groups.id', 'products.group5_id')
                                    ->where('groups.level', $groupId);
                }
            }
            $query = $query->groupBy('groups.id')->get()->toArray();
            return $query;

        }  catch (Exception $exception)
        {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
}