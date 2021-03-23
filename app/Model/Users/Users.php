<?php

namespace App\Model\Users;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    //
    protected $fillable = [
        'id', 'name', 'email', 'login_id', 'password', 'department',
        'salt', 'authority', 'rank', 'type', 'supplier_id', 'work', 'del_flg',
        'created_by', 'updated_by', 'remember_token', 'created_at', 'updated_at'
    ];

    public function supplier()
    {
        return $this->belongsTo('App\Model\Suppliers\Supplier', 'supplier_id');
    }
}
