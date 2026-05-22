<?php

namespace App\Http\Controllers;

use App\Events\TeamMessageSent;
use App\Models\Team;
use App\Models\TeamMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Récupère l'historique des messages pour une équipe spécifique.
     *
     * @param Team $team L'équipe concernée
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function getMessages(Team $team)
    {
        // Sécurité : Vérifier si l'utilisateur appartient bien à l'équipe
        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Accès non autorisé à ce chat'], 403);
        }

        // Récupère les messages avec les infos de l'expéditeur, triés par date
        return $team->messages()->with('user')->orderBy('created_at', 'asc')->get();
    }

    /**
     * Enregistre et diffuse un nouveau message dans le chat d'équipe.
     *
     * @param Request $request La requête contenant le message
     * @param Team $team L'équipe destinataire
     * @return TeamMessage
     */
    public function sendMessage(Request $request, Team $team)
    {
        // Sécurité : Vérifier l'appartenance à l'équipe avant l'envoi
        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Vous ne pouvez pas envoyer de message à cette équipe'], 403);
        }

        // Validation du contenu du message
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // Création du message en base de données
        $message = TeamMessage::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Diffusion du message via WebSocket (Laravel Reverb)
        // .toOthers() évite que l'expéditeur reçoive son propre message via WebSocket
        broadcast(new TeamMessageSent($message))->toOthers();

        return $message->load('user');
    }
}
