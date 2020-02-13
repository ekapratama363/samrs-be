<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Company extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'Company';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'code',
        'description',
        'created_by',
        'updated_by',
        'deleted',
        'location_id',
        'chart_of_account_id'
	];

    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id');
    }

    public function chart_of_account()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'chart_of_account_id');
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
