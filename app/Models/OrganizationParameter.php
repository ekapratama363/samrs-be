<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationParameter extends Model
{
    protected $fillable = [
<<<<<<< HEAD
<<<<<<< HEAD
		'key', 'value', 'role_id', 'created_by', 'updated_by'
=======
        'key',
        'value',
        'role_id',
        'created_by',
        'updated_by'
>>>>>>> ruben_dev
=======
		'key', 'value', 'role_id', 'created_by', 'updated_by'
>>>>>>> master
	];

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id');
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
