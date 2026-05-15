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
        Schema::create('progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partie_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->json('lieux_a_visiter');
            $table->json('lieux_decouverts')->nullable();
            $table->json('lieux_restants')->nullable();
            $table->foreignId('enigme_courante_id')->nullable()->constrained('enigmes');
            $table->integer('temps_restant_minutes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progressions');
    }
};
