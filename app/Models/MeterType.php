<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeterType extends Model
{
    protected $fillable = ['type_name', 'manufacturer', 'verify_interval_years', 'verification_method'];

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class, 'type_id');
    }
}
