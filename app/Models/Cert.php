<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cert extends Model
{
    protected $fillable = [
        'cert_number',
        'zavod_number',
        'type_model',
        'make_year',
        'water_data',
        'fio',
        'address',
        'phone',
        'class',
        'check_date',
        'final_date',
        'plomb_number',
    ];
}
