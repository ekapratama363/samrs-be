<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ReleaseGroup extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'ReleaseGroup';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
		'code', 'description', 'release_object_id', 'classification_id', 'active', 'created_by', 'updated_by', 'deleted'
	];

	public function classification()
    {
        return $this->belongsTo('App\Models\ClassificationMaterial', 'classification_id');
    }

    public function release_object()
    {
        return $this->belongsTo('App\Models\ReleaseObject', 'release_object_id');
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
