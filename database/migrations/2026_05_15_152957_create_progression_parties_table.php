<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('progression_parties', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers la partie
            $table->foreignId('partie_id')->constrained('parties')->onDelete('cascade');

            // Lieux à visiter (ordre défini par l'algo) - stocké en JSON
            $table->json('lieux_a_visiter')->nullable();

            // Lieux déjà découverts/résolus
            $table->json('lieux_decouverts')->nullable()->default('[]');

            // Lieux restants à découvrir
            $table->json('lieux_restants')->nullable();

            // Énigme courante (ID de l'énigme)
            $table->foreignId('enigme_courante_id')->nullable()->constrained('enigmes');

            // Temps restant en secondes
            $table->integer('temps_restant')->nullable();

            // Score/nombre d'énigmes résolues
            $table->integer('score')->default(0);

            // Statut de la progression
            $table->enum('statut', ['en_cours', 'pause', 'terminee'])->default('en_cours');

            // Timestamps
            $table->timestamps();

            // Index pour perf
            $table->index('partie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progression_parties');
    }
};