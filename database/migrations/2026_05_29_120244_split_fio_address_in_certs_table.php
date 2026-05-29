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
        Schema::table('certs', function (Blueprint $table) {
            $table->string('fio')->after('water_data')->default('');
            $table->string('address')->after('fio')->default('');
            $table->dropColumn('fio_address');
        });
    }

    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->string('fio_address')->after('water_data')->default('');
            $table->dropColumn(['fio', 'address']);
        });
    }
};
