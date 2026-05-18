<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AppInvitationController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Invitations/Index', [
            'invitations' => AppInvitation::with(['creator', 'user'])
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $token = Str::random(32);

        AppInvitation::create([
            'token' => $token,
            'email' => $request->email,
            'created_by' => auth()->id(),
            'expires_at' => $request->expires_at ?? now()->addDays(7),
        ]);

        return redirect()->route('admin.invitations.index')
            ->with('success', 'Lien d\'invitation généré avec succès.');
    }

    public function destroy(AppInvitation $invitation)
    {
        $invitation->delete();

        return redirect()->route('admin.invitations.index')
            ->with('success', 'Lien d\'invitation supprimé.');
    }
}
