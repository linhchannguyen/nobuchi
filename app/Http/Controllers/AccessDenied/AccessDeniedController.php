<?php

namespace App\Http\Controllers\AccessDenied;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessDeniedController extends Controller
{
    public function index()
    {
        return view('access_denied.index');
    }
}
