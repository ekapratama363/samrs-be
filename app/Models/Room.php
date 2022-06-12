<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'plant_id' => 'integer',
        'responsible_person' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

	public function plant()
    {
        return $this->hasOne('App\Models\Plant', 'id', 'plant_id');
    }

	public function responsible_person()
    {
        return $this->hasOne('App\Models\User', 'id', 'responsible_person');
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
