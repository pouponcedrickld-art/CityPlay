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
            $table->integer('score_total')->default(0)->after('parametres');
        });

        Schema::table('progressions', function (Blueprint $table) {
            $table->integer('score')->default(0)->after('nb_enigmes_resolues');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('keep_account')->default(true)->after('otp_verified_at');
            $table->timestamp('expires_at')->nullable()->after('keep_account');
        });

        Schema::table('team_user', function (Blueprint $table) {
            // Changement du rôle pour inclure challenger/participant
            $table->string('role')->default('participant')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn('score_total');
        });

        Schema::table('progressions', function (Blueprint $table) {
            $table->dropColumn('score');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['keep_account', 'expires_at']);
        });

        Schema::table('team_user', function (Blueprint $table) {
            $table->string('role')->default('membre')->change();
        });
    }
};
