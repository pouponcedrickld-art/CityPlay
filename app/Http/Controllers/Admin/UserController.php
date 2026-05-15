<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::latest()->get(),
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut admin.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        // Optionnel: synchroniser le rôle Spatie si utilisé
        if ($user->is_admin) {
            $user->assignRole('admin');
        } else {
            $user->removeRole('admin');
        }

        return back()->with('success', 'Statut administrateur mis à jour.');
    }
}
