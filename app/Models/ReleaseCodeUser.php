<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ReleaseCodeUser extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'ReleaseCodeUser';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'release_code_id',
        'release_code_desc',
        'release_group_id',
        'user_id',
        'created_by',
        'updated_by',
        'deleted'
	];

	public function release_code()
    {
        return $this->belongsTo('App\Models\ReleaseCode', 'release_code_id');
    }

    public function release_group()
    {
        return $this->belongsTo('App\Models\ReleaseGroup', 'release_group_id');
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
