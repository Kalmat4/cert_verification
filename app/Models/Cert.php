<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cert extends Model
{
    public function readings(): HasMany
    {
        return $this->hasMany(CertReading::class)->orderBy('sort_order');
    }

    protected $fillable = [
        'cert_number',
        'zavod_number',
        'type_model',
        'manufacturer',
        'verification_method',
        'verifier',
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
