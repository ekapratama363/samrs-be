<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'classification_id' => 'integer',
        'unit_of_measurement_id' => 'integer',
        'quantity_uom' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

	public function classification()
    {
        return $this->hasOne('App\Models\Classification', 'id', 'classification_id');
    }
    
    public function material_images()
    {
        return $this->hasMany('App\Models\MaterialImage', 'material_id');
    }
    
    public function material_parameters()
    {
        return $this->hasMany('App\Models\MaterialParameter', 'material_id');
    }

	public function uom()
    {
        return $this->hasOne('App\Models\UnitOfMeasurement', 'id', 'unit_of_measurement_id')->withTrashed();
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
