<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function allAccounts(Request $request)
    {
        $users = User::all();

        return view('admin.accounts', compact('users')); // Sesuaikan nama view
    }
}