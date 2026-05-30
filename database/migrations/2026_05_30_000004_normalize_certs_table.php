<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Добавляем meter_id как nullable FK
        Schema::table('certs', function (Blueprint $table) {
            $table->unsignedBigInteger('meter_id')->nullable()->after('id');
            $table->foreign('meter_id')->references('id')->on('meters')->cascadeOnDelete();
        });

        // 2. Мигрируем существующие данные: создаём клиентов и счётчики
        DB::table('certs')->whereNull('meter_id')->orderBy('id')->each(function ($cert) {
            $now = now();

            $clientId = DB::table('clients')->insertGetId([
                'fio'        => $cert->fio     ?? '',
                'address'    => $cert->address ?? '',
                'phone'      => $cert->phone   ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $meterId = DB::table('meters')->insertGetId([
                'client_id'    => $clientId,
                'zavod_number' => $cert->zavod_number ?? '',
                'type_model'   => $cert->type_model   ?? null,
                'manufacturer' => $cert->manufacturer ?? null,
                'make_year'    => $cert->make_year    ?? null,
                'class'        => $cert->class        ?? null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            DB::table('certs')->where('id', $cert->id)->update(['meter_id' => $meterId]);
        });

        // 3. Делаем meter_id обязательным
        DB::statement('ALTER TABLE certs MODIFY meter_id BIGINT UNSIGNED NOT NULL');

        // 4. Удаляем старые колонки, перенесённые в clients/meters
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn(['fio', 'address', 'phone', 'zavod_number', 'type_model', 'manufacturer', 'make_year', 'class']);
        });
    }

    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->string('fio')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('zavod_number')->nullable();
            $table->string('type_model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('make_year')->nullable();
            $table->string('class')->nullable();
            $table->dropForeign(['meter_id']);
            $table->dropColumn('meter_id');
        });
    }
};
