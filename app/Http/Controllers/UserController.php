<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }
}
