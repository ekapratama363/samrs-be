<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialParameter extends Model
{
    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'material_id' => 'integer',
        'classification_parameter_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

	public function classification_parameter()
    {
        return $this->hasOne('App\Models\ClassificationParameter', 'id', 'classification_parameter_id');
    }
    
}
