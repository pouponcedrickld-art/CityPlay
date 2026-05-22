<?php

namespace App\Events;

use App\Models\Partie;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement déclenché lorsqu'un joueur résout une énigme.
 * Notifie tous les membres de l'équipe pour synchroniser la progression.
 */
class EnigmeResolue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $partie;
    public $resoluPar;
    public $enigmeId;
    public $prochaineEnigmeId;
    public $message;

    /**
     * Crée une nouvelle instance d'événement.
     *
     * @param Partie $partie La partie concernée
     * @param User $resoluPar L'utilisateur qui a résolu l'énigme
     * @param int $enigmeId ID de l'énigme résolue
     * @param int|null $prochaineEnigmeId ID de la prochaine énigme (null si fini)
     * @param string $message Message à afficher
     */
    public function __construct(
        Partie $partie,
        User $resoluPar,
        int $enigmeId,
        ?int $prochaineEnigmeId = null,
        string $message = ''
    ) {
        $this->partie = $partie;
        $this->resoluPar = $resoluPar;
        $this->enigmeId = $enigmeId;
        $this->prochaineEnigmeId = $prochaineEnigmeId;
        $this->message = $message ?: $resoluPar->name . ' a résolu l\'énigme !';
    }

    /**
     * Détermine sur quels canaux diffuser l'événement.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Canal privé de l'équipe si mode team
        if ($this->partie->team_id) {
            $channels[] = new PrivateChannel('team.' . $this->partie->team_id);
        }
        
        // Canal privé de la partie
        $channels[] = new PrivateChannel('partie.' . $this->partie->id);
        
        return $channels;
    }

    /**
     * Nom de l'événement côté client.
     */
    public function broadcastAs(): string
    {
        return 'EnigmeResolue';
    }

    /**
     * Données à diffuser (sérialisées en JSON).
     */
    public function broadcastWith(): array
    {
        return [
            'partie_id' => $this->partie->id,
            'resolu_par' => [
                'id' => $this->resoluPar->id,
                'name' => $this->resoluPar->name,
            ],
            'enigme_id' => $this->enigmeId,
            'prochaine_enigme_id' => $this->prochaineEnigmeId,
            'message' => $this->message,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
