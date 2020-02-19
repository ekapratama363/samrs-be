<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class LocationType extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'LocationType';
    protected static $logOnlyDirty = false;
    
    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
		'name', 'icon', 'zoom_level', 'zoom_level_end', 'created_by', 'updated_by', 'deleted', 'code', 'description'
	];

    protected $appends = [
        'icon_url_full',
    ];

    protected $hidden = [
        'name'
    ];

    public function getIconUrlFullAttribute()
    {
        if ($this->icon && \Storage::disk('public')->exists($this->icon)) {
            return \Storage::disk('public')->url($this->icon);
        } else {
            return null;
        }
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
