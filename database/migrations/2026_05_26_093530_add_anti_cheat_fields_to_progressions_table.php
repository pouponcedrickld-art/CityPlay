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
        Schema::table('progressions', function (Blueprint $table) {
            $table->decimal('last_lat', 10, 8)->nullable()->after('enigme_courante_id');
            $table->decimal('last_lng', 11, 8)->nullable()->after('last_lat');
            $table->timestamp('last_validated_at')->nullable()->after('last_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progressions', function (Blueprint $table) {
            $table->dropColumn(['last_lat', 'last_lng', 'last_validated_at']);
        });
    }
};
