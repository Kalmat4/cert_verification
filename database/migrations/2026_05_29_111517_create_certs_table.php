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
        Schema::create('certs', function (Blueprint $table) {
            $table->id();
            $table->string('cert_number');
            $table->string('zavod_number');
            $table->string('make_year');
            $table->string('fio_address');
            $table->decimal('water_data', 12, 3);
            $table->string('class');
            $table->string('check_date');
            $table->string('final_date');
            $table->string('plomb_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certs');
    }
};
