<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ReleaseStrategy extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'ReleaseStrategy';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'code',
        'description',
        'release_group_id',
        'created_by',
        'updated_by',
        'deleted',
        'active'
	];

	public function release_group()
    {
        return $this->belongsTo('App\Models\ReleaseGroup', 'release_group_id');
    }

    public function release_code()
    {
        return $this->belongsToMany('App\Models\ReleaseCode', 'code_strategies', 'release_strategy_id', 'release_code_id')
                ->withPivot(['id', 'status'])
                ->withTimestamps()
                ->orderBy('code_strategies.created_at', 'asc');
    }

    public function strategy_parameters()
    {
        return $this->hasMany('App\Models\ReleaseStrategyParameter', 'release_strategy_id');
    }

    public function status()
    {
        return $this->hasMany('App\Models\ReleaseStatus', 'release_strategy_id');
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
