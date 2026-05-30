<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    protected $fillable = [
        'client_id',
        'zavod_number',
        'type_model',
        'manufacturer',
        'make_year',
        'class',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function certs(): HasMany
    {
        return $this->hasMany(Cert::class)->orderByDesc('id');
    }
}
