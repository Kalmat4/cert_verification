<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->string('verifier')
                ->default('Карабаев А.')
                ->after('verification_method');
        });
    }

    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn('verifier');
        });
    }
};
