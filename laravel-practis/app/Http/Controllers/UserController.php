<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $users = User::with(['company', 'sections'])
            ->when($search, function ($query) use ($search) {
                $query->search($search);
            })
            ->paginate(10);

        return view('users.index', compact('users'));
    }
}
