<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginHistory extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'device',
        'os',
        'imei',
        'imsi',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}


/**
 * Status Value
 * 1. LOGIN SUCCESS
 * 2. LOGIN SUCCESS PASSWORD EXPIRED
 * -1. LOGIN FAILED
 * -2. LOGIN FAILED SPAM ATTEMPT
 * -3. LOGIN FAILED INACTIVE
 */
