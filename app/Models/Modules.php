<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    protected $fillable = [
        'object',
        'description',
        'created_by',
        'updated_by'
	];

	public function roles()
	{
		return $this->belongsToMany('App\Models\Role', 'modules_roles', 'modules_id', 'role_id');
	}

	public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
}
