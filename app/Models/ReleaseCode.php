<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;

class ReleaseCode extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'ReleaseCode';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
		'code', 'description', 'release_group_id', 'created_by', 'updated_by', 'deleted'
	];

	public function release_group()
    {
        return $this->belongsTo('App\Models\ReleaseGroup', 'release_group_id');
    }

    public function release_strategy()
    {
        return $this->belongsToMany('App\Models\ReleaseStrategy', 'code_strategies', 'release_code_id', 'release_strategy_id')
                ->withPivot(['id', 'status'])
                ->withTimestamps();
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
