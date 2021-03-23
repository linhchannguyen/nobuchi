<?php
// set namespace user service
namespace App\Repositories\Services\User;
// use Model
use App\Model\Users\Users;
class UserService implements UserServiceContract
{
    /**
     * class UserService implements UserServiceContract
     * @author Dat
     * date 2019/10/03
     */
    public function __construct()
    {
        
    }
    /**
     * login function 
     * 
     */
    public function login ()
    {
        
    }
    /**
     *  function do login 
     * @author Dat
     * date 2019/104
     */
    public function doLoginService ()
    {
        $user = Users::all();
        return $user;
    }
}