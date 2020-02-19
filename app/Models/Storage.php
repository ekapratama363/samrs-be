<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Storage extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'Storage';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'location_id',
        'type',
        'name',
        'description',
        'user_id',
        'created_by',
        'updated_by',
        'deleted',
        'code',
        'plant_id'
	];

    public function plant()
    {
        return $this->belongsTo('App\Models\Plant', 'plant_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
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
