<?php

namespace App\Http\Controllers;

use App\Events\TeamMessageSent;
use App\Models\Team;
use App\Models\TeamMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getMessages(Team $team)
    {
        // Vérifier si l'utilisateur appartient à la team
        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $team->messages()->with('user')->orderBy('created_at', 'asc')->get();
    }

    public function sendMessage(Request $request, Team $team)
    {
        // Vérifier si l'utilisateur appartient à la team
        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = TeamMessage::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        broadcast(new TeamMessageSent($message))->toOthers();

        return $message->load('user');
    }
}
