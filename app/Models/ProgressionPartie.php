<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ProgressionPartie
 * 
 * Représente l'état d'avancement d'une partie en cours.
 * Chaque partie a une et une seule progression.
 * Ce modèle est mis à jour en temps réel pendant le jeu.
 */
class ProgressionPartie extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée
     */
    protected $table = 'progression_parties';

    /**
     * Attributs pouvant être assignés en masse (mass assignment)
     * Sécurité : seuls ces champs peuvent être remplis via create() ou update()
     */
    protected $fillable = [
        'partie_id',           // Référence vers la partie
        'lieux_a_visiter',     // Ordre des lieux (JSON)
        'lieux_decouverts',    // Lieux résolus (JSON)
        'lieux_restants',      // Lieux non résolus (JSON)
        'enigme_courante_id',  // Énigme actuelle
        'temps_restant',       // Temps en secondes
        'score',               // Score actuel
        'statut',              // en_cours / pause / terminee
    ];

    /**
     * Cast des attributs JSON en tableaux PHP automatiquement
     * Évite de faire json_decode() manuellement
     */
    protected $casts = [
        'lieux_a_visiter' => 'array',
        'lieux_decouverts' => 'array',
        'lieux_restants' => 'array',
        'temps_restant' => 'integer',
        'score' => 'integer',
    ];

    /**
     * Relation : une progression appartient à une partie
     * Accès : $progression->partie
     */
    public function partie(): BelongsTo
    {
        return $this->belongsTo(Partie::class);
    }

    /**
     * Relation : l'énigme courante associée
     * Accès : $progression->enigmeCourante
     */
    public function enigmeCourante(): BelongsTo
    {
        return $this->belongsTo(Enigme::class, 'enigme_courante_id');
    }

    /**
     * Vérifie si la partie est terminée
     * Utilisé pour bloquer les actions sur une partie finie
     */
    public function estTerminee(): bool
    {
        return $this->statut === 'terminee';
    }

    /**
     * Vérifie si la partie est en pause
     * Utilisé pour figer le chronomètre
     */
    public function estEnPause(): bool
    {
        return $this->statut === 'pause';
    }

    /**
     * Passe à l'énigme suivante dans la liste
     * Met à jour enigme_courante_id et les tableaux de lieux
     * 
     * @return bool True si une énigme suivante existe, false si fin de partie
     */
    public function passerEnigmeSuivante(): bool
    {
        // Récupère le prochain lieu dans la liste des restants
        $lieuxRestants = $this->lieux_restants ?? [];

        if (empty($lieuxRestants)) {
            // Plus de lieux = partie terminée
            $this->statut = 'terminee';
            $this->enigme_courante_id = null;
            $this->save();

            // Mettre à jour la partie aussi
            $this->partie->update([
                'statut' => 'terminee',
                'ended_at' => now()
            ]);
            
            return false;
        }

        // Prend le premier lieu restant
        $prochainLieuId = array_shift($lieuxRestants);
        $lieu = Lieu::find($prochainLieuId);

        if (!$lieu) {
            return $this->passerEnigmeSuivante(); // Sécurité si lieu supprimé
        }

        // On récupère l'énigme de la difficulté choisie pour cette partie
        $difficulte = $this->partie->parametres['difficulte'] ?? 2;
        $typeEnigme = match ((int)$difficulte) {
            1 => 'force1',
            2 => 'force2',
            3 => 'force3',
            default => 'force2',
        };

        $enigme = $lieu->enigmes()->where('type', $typeEnigme)->first() 
                  ?? $lieu->enigmes()->first(); // Fallback sur n'importe quelle énigme si le type manque

        if (!$enigme) {
            $this->lieux_restants = $lieuxRestants; // On ignore ce lieu sans énigme
            $this->save();
            return $this->passerEnigmeSuivante();
        }

        $this->enigme_courante_id = $enigme->id;
        $this->lieux_restants = $lieuxRestants;
        $this->save();

        return true;
    }

    /**
     * Marque l'énigme courante comme résolue
     * Ajoute le lieu aux découverts et incrémente le score
     */
    public function resoudreEnigmeCourante(): void
    {
        $enigme = $this->enigmeCourante;
        if (!$enigme) return;

        $lieuId = $enigme->lieu_id;

        if ($lieuId) {
            $decouverts = $this->lieux_decouverts ?? [];
            if (!in_array($lieuId, $decouverts)) {
                $decouverts[] = $lieuId;
                $this->lieux_decouverts = $decouverts;
            }
        }

        // On ajoute les points de l'énigme au score
        $this->score += ($enigme->points > 0 ? $enigme->points : 10);
        $this->save();
    }

    /**
     * Met la partie en pause
     * Le temps est figé, plus d'actions possibles jusqu'à reprise
     */
    public function mettreEnPause(): void
    {
        $this->statut = 'pause';
        $this->save();
    }

    /**
     * Reprend une partie en pause
     */
    public function reprendre(): void
    {
        $this->statut = 'en_cours';
        $this->save();
    }

    /**
     * Termine la partie (abandon ou fin normale)
     */
    public function terminer(): void
    {
        $this->statut = 'terminee';
        $this->temps_restant = 0;
        $this->save();
    }
}