<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name');
            $table->string('manufacturer');
            $table->unsignedTinyInteger('verify_interval_years')->default(5);
            $table->string('verification_method');
            $table->timestamps();
        });

        $now = now();
        $types = [
            ['manufacturer' => 'ООО "Урал Прибор"',                                                     'type_name' => 'Экомера ЭКО-15',                    'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => '"Ningbo Water Meter Co.',                                                'type_name' => 'МероКом МК-15',                     'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => '"Ningbo Water Meter Co.',                                                'type_name' => 'I\'mPulse Plus СВХ-15А',            'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => '"Ningbo Water Meter Co.',                                                'type_name' => 'Vodomer ВСКМ-15',                   'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО "Кредо"',                                                           'type_name' => 'СВУ-15',                            'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "Кредо"',                                                           'type_name' => 'СВХ-25',                            'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "Кредо"',                                                           'type_name' => 'СВУ-15В',                           'verify_interval_years' => 5, 'verification_method' => 'МП 89-221-2016'],
            ['manufacturer' => 'ТОО "Нурамир-Сауда"',                                                   'type_name' => 'GPF-R-C 15/1,5-20/2,5',             'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ТОО "Нурамир-Сауда"',                                                   'type_name' => 'DHC-R',                             'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ТОО "Нурамир-Сауда"',                                                   'type_name' => 'VLF-R',                             'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ТОО "KSA Engineering"',                                                 'type_name' => 'SE90-15B',                          'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО "Спутник"',                                                         'type_name' => 'MK-U MINKOR',                       'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ITRON FRANCE SAS',                                                      'type_name' => 'TU Unimag',                         'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'Actaris',                                                                'type_name' => 'TU Unimag',                         'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО ПКФ «БЕТАР»',                                                       'type_name' => 'СВМ',                               'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО ПКФ «БЕТАР»',                                                       'type_name' => 'СГВ-15',                            'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО "ГЕРРИДА"',                                                         'type_name' => 'СВКМ',                              'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО "ПК Прибор"',                                                       'type_name' => 'ВСКМ 90 15',                        'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "ПК Прибор"',                                                       'type_name' => 'ВСКМ',                              'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО "Декаст',                                                           'type_name' => 'Декаст',                            'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО "Декаст',                                                           'type_name' => 'Декаст ОСВХ',                       'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'BAYLAN',                                                                 'type_name' => 'серии Woltmann',                    'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'BAYLAN',                                                                 'type_name' => 'серии АК',                          'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'BAYLAN',                                                                 'type_name' => 'KK',                                'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'BAYLAN',                                                                 'type_name' => 'КК',                                'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО "Телематические Решения"',                                          'type_name' => 'АКВА L110 D15 ВЕ',                  'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО «Телематические Решения»',                                          'type_name' => 'АКВА (мод. АКВА, L, D)',             'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО «Телематические Решения»',                                          'type_name' => 'AQUA+',                             'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ТОО "Акваметр"',                                                        'type_name' => 'AQUATECHNICA',                       'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'Diehl Metering GmbH',                                                   'type_name' => 'AQUARIUS S/RS/P',                   'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'Diehl Metering GmbH',                                                   'type_name' => 'AQUARIUS V3',                       'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ТОО "Сантех-Строй Алматы Ко"',                                          'type_name' => '«CASCAD» WM-CW',                    'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ТОО "Сантех-Строй Алматы Ко"',                                          'type_name' => 'CASCAD WM-CW',                      'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО "МЕТЕР"',                                                           'type_name' => 'МЕТЕР ВК',                          'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "МЕТЕР"',                                                           'type_name' => 'МЕТЕР СВ',                          'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "МЕТЕР"',                                                           'type_name' => 'СВ-15Г',                            'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "МЕТЕР"',                                                           'type_name' => 'СВ-15',                             'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "МЕТЕР"',                                                           'type_name' => 'СВ',                                'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО «Аква-С»',                                                          'type_name' => 'ПУЛЬС-15У-80',                      'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО ПК "Норма Измерительные Системы"',                                  'type_name' => 'НОРМА СВКМ',                        'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
            ['manufacturer' => 'ООО ПК "Норма Измерительные Системы"',                                  'type_name' => 'НОРМА СВК',                         'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО "Тайпит-ИП"',                                                       'type_name' => 'Тайпит',                            'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ООО НПО Байкал',                                                        'type_name' => 'С-300 Байкал',                      'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-38'],
            ['manufacturer' => 'ТОО "Сайман-Астана"',                                                   'type_name' => 'Wasserzahler MTK',                  'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156'],
            ['manufacturer' => 'Sensus Metering Systems',                                               'type_name' => 'Residia Jet-C',                     'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'Sensus Metering Systems',                                               'type_name' => 'COSMOS',                            'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156-83'],
            ['manufacturer' => 'ОАО "Бологовский арматурный завод"',                                    'type_name' => 'СВК 15-1,5',                        'verify_interval_years' => 5, 'verification_method' => 'МП РТ 1069-2006'],
            ['manufacturer' => '"Elster Messtechnik GmbH"',                                             'type_name' => 'S100',                              'verify_interval_years' => 5, 'verification_method' => 'ГОСТ 8.156'],
            ['manufacturer' => 'АО «Тепловодомер»',                                                     'type_name' => 'ВСХН',                              'verify_interval_years' => 5, 'verification_method' => 'СТ РК 2.86-2005'],
            ['manufacturer' => 'ООО НПП ИБС',                                                           'type_name' => 'WFK2',                              'verify_interval_years' => 5, 'verification_method' => 'МИ 1592-2015'],
        ];

        foreach ($types as &$t) {
            $t['created_at'] = $now;
            $t['updated_at'] = $now;
        }

        DB::table('meter_types')->insert($types);
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_types');
    }
};
