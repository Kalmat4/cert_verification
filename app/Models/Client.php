<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['fio', 'address', 'phone'];

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class)->orderByDesc('id');
    }
}
