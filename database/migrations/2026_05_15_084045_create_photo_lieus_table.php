<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('photo_lieus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lieu_id')
                ->constrained('lieux')
                ->onDelete('cascade');
            $table->string('url');
            $table->string('alt_text')->nullable();
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_lieus');
    }
};