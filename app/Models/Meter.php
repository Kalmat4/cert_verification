<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    protected $fillable = [
        'client_id',
        'type_id',
        'zavod_number',
        'make_year',
        'class',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function meterType(): BelongsTo
    {
        return $this->belongsTo(MeterType::class, 'type_id');
    }

    public function certs(): HasMany
    {
        return $this->hasMany(Cert::class)->orderByDesc('id');
    }
}
