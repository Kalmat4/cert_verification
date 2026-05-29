<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->string('manufacturer')
                ->default('ООО "Телематические Решения", г. Москва, Российская Федерация')
                ->after('type_model');
        });
    }

    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn('manufacturer');
        });
    }
};
