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
        Schema::create('environnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained()->onDelete('cascade');
            $table->string('nom', 150);
            $table->integer('retention_profils_jours')->default(30);
            $table->integer('duree_vie_lien_heures')->default(24);
            $table->json('messages')->nullable(); // messages de fin, etc.
            $table->json('regles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environnements');
    }
};
