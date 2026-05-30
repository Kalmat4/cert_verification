<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cert extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (self $cert) {
            $cert->forceFill([
                'garant_number' => $cert->id . ' / ' . str_replace('.', '_', $cert->check_date),
            ])->saveQuietly();
        });

        static::updated(function (self $cert) {
            if ($cert->wasChanged('check_date')) {
                $cert->forceFill([
                    'garant_number' => $cert->id . ' / ' . str_replace('.', '_', $cert->check_date),
                ])->saveQuietly();
            }
        });
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(CertReading::class)->orderBy('sort_order');
    }

    protected $fillable = [
        'meter_id',
        'cert_number',
        'verification_method',
        'verifier',
        'plomb_number',
        'water_data',
        'check_date',
        'final_date',
    ];
}
