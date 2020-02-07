<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ClassificationMaterial extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'ClassificationMaterial';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'classification_type_id',
        'name',
        'created_by',
        'updated_by',
        'deleted'
	];

    public function classification_type()
    {
        return $this->hasOne('App\Models\ClassificationType', 'id', 'classification_type_id');
    }

    public function parameters()
    {
        return $this->hasMany('App\Models\ClassificationParameter', 'classification_id');
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
