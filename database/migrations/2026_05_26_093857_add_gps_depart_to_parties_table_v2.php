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
        Schema::table('parties', function (Blueprint $table) {
            $table->decimal('lat_depart', 10, 8)->nullable()->after('ordre_jeu');
            $table->decimal('lng_depart', 11, 8)->nullable()->after('lat_depart');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['lat_depart', 'lng_depart']);
        });
    }
};
