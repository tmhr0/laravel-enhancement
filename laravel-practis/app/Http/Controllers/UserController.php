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
            ->when($search && $searchOption === 'user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($search && $searchOption === 'company', function ($query) use ($search) {
                $query->whereHas('company', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            })
            ->when($search && $searchOption === 'section', function ($query) use ($search) {
                $query->whereHas('sections', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            })
            ->paginate(10);

        return view('users.index', compact('users'));
    }
}
