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
        Schema::table('environnements', function (Blueprint $table) {
            $table->string('lien_restauration')->nullable()->after('message_pause');
            $table->string('lien_boutique')->nullable()->after('lien_restauration');
            $table->string('lien_notation')->nullable()->after('lien_boutique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('environnements', function (Blueprint $table) {
            $table->dropColumn(['lien_restauration', 'lien_boutique', 'lien_notation']);
        });
    }
};
