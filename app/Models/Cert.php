<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cert extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (self $cert) {
            $cert->updateQuietly([
                'garant_number' => $cert->id . ' / ' . str_replace('.', '_', $cert->check_date),
            ]);
        });

        static::updated(function (self $cert) {
            if ($cert->wasChanged('check_date')) {
                $cert->updateQuietly([
                    'garant_number' => $cert->id . ' / ' . str_replace('.', '_', $cert->check_date),
                ]);
            }
        });
    }

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
