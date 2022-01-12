<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attack extends Model
{
    const DOMAIN_FIELD_NAME = 'domain';
    const ATTACK_MODE_FIELD_NAME = 'attack_mode';
    const RATE_LIMITING_FIELD_NAME = 'rate_limiting';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain',
        'attack_mode',
        'rate_limiting'
    ];
}
