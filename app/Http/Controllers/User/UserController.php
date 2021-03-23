<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Repositories\Services\User\UserServiceContract;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    /**
     * user controller class
     * controll user
     * @author Dat
     * date 2019/10/03
     */
    private $user_service;
    public function __construct(UserServiceContract $user_service)
    {
        $this->user_service = $user_service;
    }
    /**
     * login function 
     * authencation user
     * return login view
     * @author Dat
     * date 2019/10/03
     */
    public function login()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('users.login');
    }
    /**
     * process login 
     * function login to system RimacEc
     * @author Dat
     * date 2019/10/04
     */
    public function doLogin(Request $request)
    {
        $user_data = array(
            'login_id'  => $request->get('loginId'),
            'password' => $request->get('loginPassword')
        );
        $url = '';
        if(Auth::attempt($user_data))
        {
            $user = auth()->user();
            Session::put('login', true);
            Session::put('type', $user->type);
            Session::put('rank', $user->rank);
            Session::put('authority', $user->authority);
            Session::put('salt', $user->salt);
            Session::put('department', $user->department);
            Session::put('name', $user->login_id);
            Session::put('email', $user->email);
            if($user->type == 2){
                $url = '/supplier';
            }
            return [
                'status' => 'true',
                'url' => $url
            ];
        }
        else
        {
            return [
                'status' => 'false', 
                'message' => 'invalid'
            ];
        }
    }
    /**
     * function logout
     * @author Dat
     * 2019/10/07
     */
    public function logout()
    {
        Auth::logout();
        return redirect('login');
    }
}
