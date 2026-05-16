<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('progression_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partie_id')->constrained('parties')->onDelete('cascade');
            $table->json('lieux_a_visiter')->nullable();
            $table->json('lieux_decouverts')->nullable();
            $table->json('lieux_restants')->nullable();
            $table->foreignId('enigme_courante_id')->nullable()->constrained('enigmes');
            $table->integer('temps_restant')->nullable();
            $table->integer('score')->default(0);
            $table->enum('statut', ['en_cours', 'pause', 'terminee'])->default('en_cours');
            $table->timestamps();
            $table->index('partie_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progression_parties');
    }
};
