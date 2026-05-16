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
        Schema::table('enigmes', function (Blueprint $table) {
            if (!Schema::hasColumn('enigmes', 'reponse')) {
                $table->string('reponse')->nullable()->after('texte');
            }
            if (!Schema::hasColumn('enigmes', 'solution')) {
                $table->text('solution')->nullable()->after('reponse');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enigmes', function (Blueprint $table) {
            $table->dropColumn(['reponse', 'solution']);
        });
    }
};
