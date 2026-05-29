<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertReading extends Model
{
    protected $fillable = [
        'cert_id', 'sort_order',
        'n', 'dn',
        'qmin_s', 'qmin_e', 'qmin_p',
        'qn_s',   'qn_e',   'qn_p',
        'qmax_s', 'qmax_e', 'qmax_p',
        'before_val', 'after_val',
    ];
}
