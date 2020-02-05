<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleComposite extends Model
{
    protected $fillable = [
		'parent_id', 'child_id'
	];

	public function child_role()
    {
        return $this->belongsTo('App\Models\Role', 'child_id');
    }
}
