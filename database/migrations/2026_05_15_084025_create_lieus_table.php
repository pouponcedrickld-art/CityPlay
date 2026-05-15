<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lieux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environnement_id')->constrained()->onDelete('cascade');
            $table->integer('ordre')->default(0)->index();
            $table->string('nom', 150);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('rayon_metres')->default(50);
            $table->string('description', 500);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['environnement_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieux');
    }
};