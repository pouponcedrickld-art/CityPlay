<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\TeamMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Événement déclenché lors de l'envoi d'un message dans un chat d'équipe.
 * Implemente ShouldBroadcast pour être diffusé via WebSockets.
 */
class TeamMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Le message à diffuser.
     * Public pour être automatiquement sérialisé dans le JSON envoyé au client.
     */
    public $message;

    /**
     * Initialise l'événement avec le message et charge l'utilisateur expéditeur.
     */
    public function __construct(TeamMessage $message)
    {
        $this->message = $message->load('user');
    }

    /**
     * Définit sur quel canal le message doit être diffusé.
     * Ici, un canal privé propre à l'équipe.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('team.' . $this->message->team_id),
        ];
    }
}
