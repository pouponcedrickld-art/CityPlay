<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'gps_validation' => fn () => $request->session()->get('gps_validation'),
                'points_gagnes' => fn () => $request->session()->get('points_gagnes'),
                'score_total' => fn () => $request->session()->get('score_total'),
                'solution_revelee' => fn () => $request->session()->get('solution_revelee'),
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'pseudo' => $request->user()->pseudo,
                    'total_score' => (int) $request->user()->total_score,
                    'balance_coins' => (int) $request->user()->balance_coins,
                    'avatar_path' => $request->user()->avatar_path ? asset('storage/' . $request->user()->avatar_path) : null,
                    'created_teams_count' => $request->user()->createdTeams()->count(),
                    'is_admin' => (bool) $request->user()->is_admin,
                    'roles' => $request->user()->getRoleNames(), // Spatie roles
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'), // Spatie permissions
                ] : null,
            ],
        ];
    }
}
