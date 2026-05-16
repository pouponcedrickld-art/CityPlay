<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environnement_id')->constrained();
            $table->foreignId('createur_id')->constrained('users');
            $table->foreignId('team_id')->nullable()->constrained();
            $table->enum('mode', ['solo', 'team']);
            $table->json('parametres'); // durée, locomotion, difficulté, etc.
            $table->enum('statut', ['en_attente', 'en_cours', 'terminee', 'pause'])->default('en_attente');
            $table->string('code_liaison')->unique()->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
