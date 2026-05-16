<?php

namespace Tests\Feature;

use App\Models\Enigme;
use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Partie;
use App\Models\ProgressionPartie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests Feature : Gameplay complet (happy path)
 * 
 * Couvre :
 * - Démarrage d'une partie
 * - Validation GPS (succès et échec)
 * - Passer énigme
 * - Indice et solution
 * - Pause et reprise
 */
class PartieInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected User $joueur;
    protected Partie $partie;
    protected Lieu $lieu;

    /**
     * Setup : crée un environnement complet avec lieux et énigmes
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->joueur = User::factory()->create();

        // Crée un environnement
        $env = Environnement::factory()->create();

        // Crée un lieu avec une énigme
        $this->lieu = Lieu::factory()->create([
            'environnement_id' => $env->id,
            'lat' => 48.8566,  // Paris
            'lng' => 2.3522,
            'rayon' => 50,      // 50 mètres
            'ordre' => 1,
        ]);

        // Crée une énigme pour ce lieu
        Enigme::factory()->create([
            'lieu_id' => $this->lieu->id,
            'type' => 'force2',
            'texte' => 'Trouvez la tour la plus célèbre de Paris',
        ]);

        // Crée la partie
        $this->partie = Partie::factory()->create([
            'createur_id' => $this->joueur->id,
            'environnement_id' => $env->id,
            'mode' => 'solo',
            'parametres' => [
                'duree' => 60,
                'locomotion' => 'pied',
                'difficulte' => 2,
            ],
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Test : Démarrer une partie initialise la progression
     */
    public function test_demarrer_partie_cree_progression(): void
    {
        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        $response->assertStatus(200)
            ->assertJsonFragment(['succes' => true]);

        // Vérifie que la progression existe en BDD
        $this->assertDatabaseHas('progression_parties', [
            'partie_id' => $this->partie->id,
            'statut' => 'en_cours',
        ]);

        // Vérifie que la partie est passée en statut "en_cours"
        $this->partie->refresh();
        $this->assertEquals('en_cours', $this->partie->statut);
    }

    /**
     * Test : Valider GPS avec position correcte (dans le rayon)
     */
    public function test_valider_gps_position_correcte(): void
    {
        // Démarrer la partie d'abord
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        // Position exacte du lieu (48.8566, 2.3522)
        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/valider-gps", [
                'lat' => 48.8566,
                'lng' => 2.3522,
                'precision' => 10,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['succes' => true])
            ->assertJsonPath('data.enigme_resolue', true);
    }

    /**
     * Test : Valider GPS avec position hors rayon
     */
    public function test_valider_gps_position_hors_rayon(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        // Position à 1km du lieu (trop loin)
        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/valider-gps", [
                'lat' => 48.8666, // +0.01 degré ≈ 1.1 km
                'lng' => 2.3522,
                'precision' => 10,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['succes' => false])
            ->assertJsonPath('data.erreur', 'hors_rayon');
    }

    /**
     * Test : Valider GPS sans coordonnées (GPS indisponible)
     */
    public function test_valider_gps_indisponible(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        // Ne pas envoyer lat/lng (simule GPS off)
        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/valider-gps", []);

        $response->assertStatus(422); // Validation échoue
    }

    /**
     * Test : Passer une énigme
     */
    public function test_passer_enigme(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/passer-enigme");

        $response->assertStatus(200)
            ->assertJsonFragment(['succes' => true]);
    }

    /**
     * Test : Demander un indice
     */
    public function test_demander_indice(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/indice");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'succes',
                'data' => ['indice'],
            ]);
    }

    /**
     * Test : Révéler la solution
     */
    public function test_reveler_solution(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/solution");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'succes',
                'data' => ['solution'],
            ]);
    }

    /**
     * Test : Pause et reprise
     */
    public function test_pause_et_reprise(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        // Pause
        $responsePause = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/pause");

        $responsePause->assertStatus(200);

        $this->partie->progression->refresh();
        $this->assertEquals('pause', $this->partie->progression->statut);

        // Reprise
        $responseReprise = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/reprendre");

        $responseReprise->assertStatus(200);

        $this->partie->progression->refresh();
        $this->assertEquals('en_cours', $this->partie->progression->statut);
    }

    /**
     * Test : Terminer une partie
     */
    public function test_terminer_partie(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/terminer");

        $response->assertStatus(200);

        $this->partie->refresh();
        $this->assertEquals('terminee', $this->partie->statut);
        $this->assertNotNull($this->partie->ended_at);
    }

    /**
     * Test : Impossible de jouer une partie terminée
     */
    public function test_impossible_jouer_partie_terminee(): void
    {
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/demarrer");

        // Termine la partie
        $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/terminer");

        // Tente de valider GPS
        $response = $this->actingAs($this->joueur)
            ->postJson("/api/parties/{$this->partie->id}/valider-gps", [
                'lat' => 48.8566,
                'lng' => 2.3522,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'La partie est terminée.']);
    }
}