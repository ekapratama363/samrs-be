<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Location extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'Location';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'location_type_id',
        'code',
        'name',
        'address',
        'latitude',
        'longitude',
        'building',
        'unit',
        'contact',
        'phone',
        'email',
        'created_by',
        'updated_by',
        'deleted',
        'province',
        'city',
        'country',
        'postal_code'
	];

    public function location_type()
    {
        return $this->belongsTo('App\Models\LocationType', 'location_type_id');
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
