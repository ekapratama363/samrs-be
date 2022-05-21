<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassificationParameter extends Model
{
    protected $fillable = [
        'classification_id',
        'name',
        'type',
        'length',
        'decimal',
        'value',
        'created_by',
        'updated_by',
        'deleted',
        'reading_indicator'
	];
    
    protected $casts = [
        'id' => 'integer',
        'classification_id' => 'integer',
        'type' => 'integer',
        'length' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'reading_indicator' => 'integer'
    ];

    public function classification_material()
    {
        return $this->hasOne('App\Models\ClassificationMaterial', 'id', 'classification_id');
    }
}
