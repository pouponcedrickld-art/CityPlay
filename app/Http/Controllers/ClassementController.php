<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassementController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)
            ->orderByDesc('total_score')
            ->limit(50)
            ->get(['id', 'name', 'total_score']);

        return Inertia::render('Player/Classement', [
            'users' => $users,
            'currentUser' => auth()->user()->only(['id', 'name', 'total_score']),
        ]);
    }
}
