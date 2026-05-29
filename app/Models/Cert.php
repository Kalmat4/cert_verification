<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cert extends Model
{
    protected $fillable = [
        'cert_number',
        'zavod_number',
        'make_year',
        'fio_address',
        'water_data',
        'class',
        'check_date',
        'final_date',
        'plomb_number',
    ];
}
