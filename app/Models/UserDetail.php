<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UserDetail extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'UserDetail';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'user_id', 'user_group_id', 'location_id', 'company_id', 'cost_center_id', 'supervisor', 'photo', 'department_id',
        'status', 'join_date', 'retired_date'
    ];

    protected $appends = [
        'photo_url_full'
    ];

    public function getPhotoUrlFullAttribute()
    {
        if ($this->photo && \Storage::disk('public')->exists($this->photo)) {
            return \Storage::disk('public')->url($this->photo);
        } else {
            return null;
        }
    }

    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }

    public function cost_center()
    {
        return $this->belongsTo('App\Models\CostCenter', 'cost_center_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function user_group()
    {
        return $this->belongsTo('App\Models\UserGroup', 'user_group_id');
    }

    public function supervisor()
    {
        return $this->belongsTo('App\Models\User', 'supervisor');
    }
}

