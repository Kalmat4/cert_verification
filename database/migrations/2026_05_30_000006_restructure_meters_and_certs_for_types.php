<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Добавляем type_id в meters (nullable пока) — если ещё нет
        if (!Schema::hasColumn('meters', 'type_id')) {
            Schema::table('meters', function (Blueprint $table) {
                $table->foreignId('type_id')->nullable()->after('client_id')->constrained('meter_types')->nullOnDelete();
            });
        }

        // 2. Находим ID типа "АКВА L110 D15 ВЕ" и проставляем всем существующим meters
        $defaultTypeId = DB::table('meter_types')->where('type_name', 'АКВА L110 D15 ВЕ')->value('id');
        if ($defaultTypeId) {
            DB::table('meters')->whereNull('type_id')->update(['type_id' => $defaultTypeId]);
        }

        // 3. Пересоздаём FK без SET NULL, затем делаем NOT NULL
        Schema::table('meters', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });
        DB::statement('ALTER TABLE meters MODIFY type_id BIGINT UNSIGNED NOT NULL');
        Schema::table('meters', function (Blueprint $table) {
            $table->foreign('type_id')->references('id')->on('meter_types')->restrictOnDelete();
        });

        // 4. Удаляем старые колонки из meters
        Schema::table('meters', function (Blueprint $table) {
            $table->dropColumn(['type_model', 'manufacturer']);
        });

        // 5. Удаляем verification_method из certs
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn('verification_method');
        });
    }

    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->string('verification_method')->nullable();
        });

        Schema::table('meters', function (Blueprint $table) {
            $table->string('type_model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });
    }
};
