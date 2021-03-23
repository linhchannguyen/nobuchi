<?php  
namespace App\Repositories\Services\Masters;

use App\Model\HistoryProcess\HistoryProcess;
use App\Repositories\Services\Masters\MasterUserServiceContract;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterUserService implements MasterUserServiceContract
{
    private $user_model;
    private $process_history_model;
    public function __construct()
    {
        $this->user_model = new User();
        $this->process_history_model = new HistoryProcess();
    }

    /**
     * get all user
     * @author Dat
     * 2019/11/15
     */
    public function getUsers($request)
    {
        $query = $this->user_model;
        try {
            $query = $query->selectRaw('users.login_id as name, users.type, users.id, users.supplier_id as supplier_id ,  suppliers.name as supplier_name,
                                        (select process_description from history_processes where process_user = login_id order by created_at desc limit 1) as process_user')
            ->leftJoin('suppliers', 'users.supplier_id', 'suppliers.id')
            ->where('users.login_id', '!=', 'adminvv');
            if(isset($request['search-user-name'])){
                if(!empty($request['search-user-name'])){
                    $query = $query->where('users.login_id', 'like', "%".$request['search-user-name']."%");
                }
            }
            if(isset($request['search-supplier-name'])){
                if(!empty($request['search-supplier-name'])){
                    $query = $query->where('suppliers.name', 'like', "%".$request['search-supplier-name']."%");                
                }
            }
            $arr_permission = [];
            if(isset($request['user-permission'])){
                if(in_array(0, $_GET['user-permission'])){
                    array_push($arr_permission, 0);
                }
                if(in_array(1, $_GET['user-permission'])){
                    array_push($arr_permission, 1);
                }
                if(in_array(2, $_GET['user-permission'])){
                    array_push($arr_permission, 2);
                }
            }
            if(count($arr_permission) > 0){
                $query = $query->whereIn('users.type', $arr_permission);
            }
            $query = $query->orderBy("users.type")
            ->orderBy("id", 'desc')
            ->paginate(10);
            return $query;
        }catch (Exception $exception)
        {
            Log::debug($exception->getMessage());
        }
    }

    /**
     * function addUsers
     * Description: update user info
     * @author chan_nl
     * Created: 2019/11/28
     * Updated: 2020/05/21
     */
    public function addUsers ($data = null) 
    {     
        $permission = config('constants.PERMISSION');
        $query_add = $this->user_model;
        $user_login  = Auth::user();
        try{
            $check_user = DB::table('users')->where('login_id', $data['user_name'])->get()->toArray();
            if(empty($check_user)){
                DB::beginTransaction();// start transaction
                $insert_HP = array();
                $insert_HP['process_user'] = $user_login->login_id;
                $insert_HP['process_permission'] = $user_login->type;
                $insert_HP['process_screen'] = 'ユーザー管理';
                $str_description = '<b>追加</b>: ユーザー名: '.$data['user_name'];
                Log::info("User: $user_login->name - add user ".$data['user_name']);
                $user = [];
                if ($data['user_type'] == 1){
                    $user = [
                        'login_id' => $data['user_name'],
                        'name' => $data['user_name'],
                        'password' => bcrypt($data['password']),
                        'type' => $data['user_type']
                    ];                    
                    //start log
                    $str_description .= '<br>権限: '.$permission[$data['user_type']];
                    //end log
                }else {
                    $user = [
                        'login_id' => $data['user_name'],
                        'name' => $data['user_name'],
                        'password' => bcrypt($data['password']),
                        'type' => $data['user_type'],
                        'supplier_id' => $data['supplier_id']
                    ];
                    //start log
                    $str_description .= '<br>権限: '.$permission[$data['user_type']];
                    $str_description .= '<br>仕入先名: '.$data['supplier_name'];
                    //end log
                }
                $insert_HP['process_description'] = $str_description;
                self::addHistoryProcess($insert_HP);
                $query_add = $query_add->insert($user);
                DB::commit(); // commit database
                return [
                    'status' => true,
                    'message' => 'ユーザーを追加しました。'
                ];
            }else {
                return [
                    'status' => false,
                    'message' => 'このユーザー名は既に存在しますため他のユーザー名を入力して再追加ください。'
                ];
            }
        }catch(Exception $exception){
            DB::rollBack(); // reset data
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message' => 'ERROR QUERY DATABASE!'
            ];
        }
    }

    /**
     * function updateUsers
     * Description: update user info
     * @author chan_nl
     * Created: 2019/11/19
     * Updated: 2020/05/21
     */
    public function updateUsers($data) {
        $permission = config('constants.PERMISSION');
        $user_login  = Auth::user();
        try{
            $check_user = DB::table('users')->where('login_id', $data['user_name'])->where('id', '!=', $data['user_id'])->get()->toArray();
            if(empty($check_user)){
                DB::beginTransaction();
                $insert_HP = array();
                $insert_HP['process_user'] = $user_login->login_id;
                $insert_HP['process_permission'] = $user_login->type;
                $insert_HP['process_screen'] = 'ユーザー管理';
                $str_description = '<b>変更:</b> ユーザー名: '.$data['user_name'];
                Log::info("User: $user_login->name - update user ".$data['user_name']);
                $user = [];
                if($data['user_type'] == 0){
                    if($user_login->type == 0){
                        $user = [
                            'password' => bcrypt($data['password'])
                        ];                        
                        $str_description .= '<br>パスワード変更';
                    }else {
                        return [
                            'status' => false,
                            'message' => "現在のログインアカウントはこの権限を持っていません。"
                        ];
                    }
                }else if ($data['user_type'] == 1){
                    if($data['user_old']['user_type'] != $data['user_type']){
                        $user = [
                            'password' => bcrypt($data['password']),
                            'type' => $data['user_type']
                        ];
                        $str_description .= '<br>パスワード変更';
                        $str_description .= '<br>権限: '.$permission[$data['user_old']['user_type']].' -> '.$permission[$data['user_type']];
                    }else {                        
                        $user = [
                            'password' => bcrypt($data['password'])
                        ];
                        $str_description .= '<br>パスワード変更';
                    }
                }else {
                    if(empty($data['password'])){
                        $user = [
                            'type' => $data['user_type'],
                            'supplier_id' => $data['supplier_id']
                        ];
                    }else {
                        $user = [
                            'password' => bcrypt($data['password']),
                            'type' => $data['user_type'],
                            'supplier_id' => $data['supplier_id']
                        ];    
                        $str_description .= '<br>パスワード変更';                
                    }
                    //start log
                    if($data['user_old']['user_type'] != $data['user_type']){
                        $str_description .= '<br>権限: '.$permission[$data['user_old']['user_type']].' -> '.$permission[$data['user_type']];
                    }
                    if($data['user_old']['supplier_id'] != $data['supplier_id']){
                        $str_description .= '<br>仕入先名: '.$data['user_old']['supplier_name'].' -> '.$data['supplier_name'];
                    }
                    //end log
                }
                $insert_HP['process_description']  = $str_description;
                self::addHistoryProcess($insert_HP);
                $query_update = $this->user_model;
                $query_update = $query_update->where('id', $data['user_id'])->update($user);
                DB::commit();
                return [
                    'status' => true,
                    'message' => 'ユーザーを編集しました。'
                ];
            }else {
                return [
                    'status' => false,
                    'message' => 'このユーザー名は既に存在しますため他のユーザー名を入力して再追加ください。'
                ];
            }
        }catch(Exception $exception){
            DB::rollBack();
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message' => 'ERROR QUERY DATABASE!'
            ];
        }
    }
    /**
     * function delete users
     * @author Dat
     * 2019/11/28
     */
    public function deleteUsers($data)
    {
        $user  = Auth::user();
        try{
            DB::beginTransaction();// start transaction
            $insert_HP = array();
            $insert_HP['process_user'] = $user->login_id;
            $insert_HP['process_permission'] = $user->type;
            $insert_HP['process_screen'] = 'ユーザー管理';
            $insert_HP['process_description'] = '<b>削除</b>: ユーザー名: '.$data['user_name'];
            Log::info("User: $user->name - delete ".$data['user_name']);
            self::addHistoryProcess($insert_HP);
            $delete_query = $this->user_model;
            $delete_query = $delete_query->where('id', $data['user_id'])->delete();
            DB::commit(); // commit database
            return [
                'status' => true,
                'message' => 'ユーザーを削除しました。'
            ];
        }catch(Exception $exception)
        {
            DB::rollBack(); // reset data
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message' => 'ERROR QUERY DATABASE!'
            ];
        }
    }

    /**
     * function get_list_process_history
     * Description: get list process history in user screen
     * @author chan_nl
     * Created: 2020/05/14
     * Updated: 2020/05/14
     */
    public function get_list_process_history($request){
        try{
            $query = $this->process_history_model;
            $query = $query->selectRaw('process_user, process_permission, process_screen, process_description, created_at');
            $query = $query->whereBetween(DB::raw('CAST(created_at as DATE)'), [$request['date_from'], $request['date_to']]);
            if(isset($request['user_name'])){
                $query = $query->where('process_user', 'like', "%".$request['user_name']."%");
            }
            $arr_permission = [];
            if($request['permission0'] != -1){
                array_push($arr_permission, 0);
            }
            if($request['permission1'] != -1){
                array_push($arr_permission, 1);
            }
            if($request['permission2'] != -1){
                array_push($arr_permission, 2);
            }
            if(count($arr_permission) > 0){
                $query = $query->whereIn('process_permission', $arr_permission);
            }
            $query = $query->orderBy('created_at', 'desc');
            $query = $query->get()->toArray();
            return [
                'status' => true,
                'data' => $query
            ];
        }catch(Exception $e){
            Log::info($e->getMessage());
            return [
                'status' => false,
                'message' =>  'Query error.'
            ];
        }
    }

    public function addHistoryProcess($insert_HP){
        DB::beginTransaction(); 
        try {
            $historyProcessModel = new HistoryProcess();     
            $hisProcess = $historyProcessModel;
            $hisProcess->create($insert_HP);
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            Log::info($e->getMessage());            
        }
    }
}