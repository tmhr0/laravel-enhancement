<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:20',
            ],
            'search_option' => [
                'nullable',
                'in:user,company,section',
            ],
        ]);

        $search = $request->input('search');
        $searchOption = $request->input('search_option');

        $users = User::with(['company', 'sections'])
            ->search($search, $searchOption)
            ->paginate(10);

        return view('users.index', compact('users'));
    }
}
