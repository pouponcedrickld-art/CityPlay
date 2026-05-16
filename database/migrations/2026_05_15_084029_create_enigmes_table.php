<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enigmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lieu_id')
                ->constrained('lieux')        // ← Important : on référence 'lieux'
                ->onDelete('cascade');

            $table->enum('type', ['force1', 'force2', 'force3', 'enfant']);
            $table->string('titre')->nullable();
            $table->text('texte');
            $table->string('reponse')->nullable();
            $table->integer('points')->default(0);
            $table->string('image_url')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enigmes');
    }
};
