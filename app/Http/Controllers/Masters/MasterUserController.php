<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Repositories\Services\Masters\MasterUserServiceContract;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MasterUserController extends Controller
{
    private $usermaster_service;
    //
    public function __construct(MasterUserServiceContract $usermaster_service)
    {
        $this->usermaster_service = $usermaster_service;
    }
    /**
     * function index master users
     * @author Dat
     * 2019/11/15
     */
    public function masterUsersIndex(Request $request)
    {
        $this->data['user_list'] =  $this->usermaster_service->getUsers($request);
        $this->data['title'] =  'ユーザー管理';
        $this->data['active'] =  7;
        $this->data['user_login'] = auth()->user()->type;
        $this->data['user_name'] = auth()->user()->login_id;
        return view('masters/users/index', $this->data);
    }

    /**
     * function add user
     * @author Dat
     * 2019/11/28
     */
    public function ajax_add_users (Request $request)
    {
        if(empty($request['user_name']))
        {
            Log::debug('Not data user request to server');
            return [
                'status' => false,
                'message' => "Can't add users"
            ];
        }else {            
            if(auth()->user()->type != 0){
                Log::debug('Add user: Access Denied');
                return [
                    'status' => false,
                    'message' => "現在のログインアカウントはこの権限を持っていません。"
                ];
            }else {
                if($request['user_type'] == 0){
                    Log::debug('Add user: Access Denied');
                    return [
                        'status' => false,
                        'message' => "管理者権限持つアカウントが作成出来ません。"
                    ];
                }
            }
        }
        $data = $this->usermaster_service->addUsers($request->all());
        return $data;
    }

    /**
     * function ajax_update_users
     * Description: update user info
     * @author chan_nl
     * Created: 2020/05/21
     * Updated: 2020/05/21
     */
    public function ajax_update_users(Request $request) 
    {
        $user = auth()->user();
        if(empty($request['user_id']))
        {
            Log::debug('Not data user request to server');
            return [
                'status' => false,
                'message' => "Can't update users"
            ];
        }else {
            if(($user->type == 1 && $user->login_id != $request['user_name']) || $user->type == 2){
                Log::debug('Update user: Access Denied');
                return [
                    'status' => false,
                    'message' => "現在のログインアカウントはこの権限を持っていません。"
                ];
            }
        }
        $data = $this->usermaster_service->updateUsers($request->all());
        return $data;
    }

    /**
     * function ajax_delete_users
     * Description: update user info
     * @author chan_nl
     * Created: 2020/05/21
     * Updated: 2020/05/21
     */
    public function ajax_delete_users(Request $request)
    {
        if(empty($request['user_id']))
        {
            Log::debug('Not data user request to server');
            return [
                'status' => false,
                'message' => "Can't delete users"
            ];
        }else {
            if(auth()->user()->type != 0 || $request['user_type'] == 0){
                Log::debug('Delete user: Access Denied');
                return [
                    'status' => false,
                    'message' => "現在のログインアカウントはこの権限を持っていません。"
                ];
            }
        }
        $data = $this->usermaster_service->deleteUsers($request->all());
        return $data;
    }

    /**
     * function ajax_get_list_process_history
     * Description: get list process history in user screen
     * @author chan_nl
     * Created: 2020/05/14
     * Updated: 2020/05/14
     */
    public function ajax_get_list_process_history(Request $request){
        return $data = $this->usermaster_service->get_list_process_history($request->all());
    }
}
