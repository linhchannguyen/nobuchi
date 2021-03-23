<?php

namespace App\Model\HistoryProcess;

use Illuminate\Database\Eloquent\Model;

class HistoryProcess extends Model
{
    protected $fillable =
    [
        'id', 'process_user', 'process_permission', 'process_screen', 'process_description', 'created_at', 'updated_at'
    ];
}
