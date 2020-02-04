<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $fillable = [
		'user_id', 'password'
	];

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
