<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lieux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environnement_id')->constrained('environnements')->onDelete('cascade');
            $table->unsignedInteger('ordre')->default(0); // Classement défini par la mairie
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('rayon_metres')->default(50); // Rayon validation GPS
            $table->string('nom', 150);
            $table->string('description', 500)->nullable(); // Max 500 caractères
            $table->json('photos')->nullable(); // Tableau de chemins/URLs, max 4
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieux');
    }
};
