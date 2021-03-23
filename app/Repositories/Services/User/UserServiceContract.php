<?php
// set namspace user service
namespace App\Repositories\Services\User;

interface UserServiceContract {
    /**
     * class contract user
     * @author Dat
     * date 2019/10/03
     */
    public function login();
    public function doLoginService();
}