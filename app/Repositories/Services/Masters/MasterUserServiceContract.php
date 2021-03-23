<?php
namespace App\Repositories\Services\Masters;

interface MasterUserServiceContract
{
    public function getUsers($request);
    public function addUsers ($data);
    public function updateUsers($data);
    public function deleteUsers($data);
    public function get_list_process_history($request);
    public function addHistoryProcess($insert_HP);
}