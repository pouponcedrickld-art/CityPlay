<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('environnements', function (Blueprint $table) {
             $table->id();
            $table->foreignId('ville_id')->constrained('villes')->onDelete('cascade');
            $table->string('nom', 150);
            $table->integer('retention_profils_jours')->default(90); // ex: 30, 90, 365
            $table->text('regles')->nullable();
            $table->unsignedInteger('duree_vie_lien_heures')->default(24); // TTL lien invitation
            // Messages paramétrables
            $table->text('message_bonne_reponse')->nullable();
            $table->text('message_mauvaise_reponse')->nullable();
            $table->text('message_fin')->nullable();
            $table->text('message_pause')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environnements');
    }
};
