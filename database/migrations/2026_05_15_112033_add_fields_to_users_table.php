<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pseudo', 100)->nullable()->after('name');
            $table->boolean('consent_cgu')->default(false)->after('pseudo');
            $table->boolean('consent_donnees')->default(false)->after('consent_cgu');
            $table->string('otp_code', 10)->nullable()->after('consent_donnees');
            $table->timestamp('otp_verified_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pseudo',
                'consent_cgu',
                'consent_donnees',
                'otp_code',
                'otp_verified_at',
            ]);
        });
    }
};