<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['company', 'sections'])->paginate(10);

        return view('users.index', compact('users'));
    }
}
