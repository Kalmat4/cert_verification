<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->string('verification_method')
                ->default('СТ РК 2.86-2005')
                ->after('manufacturer');
        });
    }

    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn('verification_method');
        });
    }
};
