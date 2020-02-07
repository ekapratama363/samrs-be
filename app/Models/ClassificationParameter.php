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

    public function classification_material()
    {
        return $this->hasOne('App\Models\ClassificationMaterial', 'id', 'classification_id');
    }
}
