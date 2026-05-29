<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cert_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cert_id')->constrained('certs')->onDelete('cascade');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('n')->nullable();
            $table->string('dn')->nullable();
            $table->string('qmin_s')->nullable();
            $table->string('qmin_e')->nullable();
            $table->string('qmin_p')->nullable();
            $table->string('qn_s')->nullable();
            $table->string('qn_e')->nullable();
            $table->string('qn_p')->nullable();
            $table->string('qmax_s')->nullable();
            $table->string('qmax_e')->nullable();
            $table->string('qmax_p')->nullable();
            $table->string('before_val')->nullable();
            $table->string('after_val')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cert_readings');
    }
};
