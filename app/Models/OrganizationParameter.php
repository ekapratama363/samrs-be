<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationParameter extends Model
{
    protected $fillable = [
        'key',
        'value',
        'role_id',
        'created_by',
        'updated_by'
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
