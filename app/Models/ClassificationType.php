<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassificationType extends Model
{
    protected $fillable = [
		'name', 'created_by', 'updated_by', 'deleted'
	];

	public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
}
