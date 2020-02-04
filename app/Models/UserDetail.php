<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Storage;

class UserDetail extends Model
{
    use LogsActivity;

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
        if($this->photo && Storage::disk('public')->exists($this->photo)){
            return Storage::disk('public')->url($this->photo);
        }else {
            return null;
        }
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
