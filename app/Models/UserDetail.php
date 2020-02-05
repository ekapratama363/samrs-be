<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
<<<<<<< HEAD
use Storage;
=======
>>>>>>> ruben_dev

class UserDetail extends Model
{
    use LogsActivity;

<<<<<<< HEAD
=======
    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
>>>>>>> ruben_dev
    protected static $logFillable = true;
    protected static $logName = 'UserDetail';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
<<<<<<< HEAD
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

=======
        'user_id',
        'user_group_id',
        'location_id',
        'status',
    ];

>>>>>>> ruben_dev
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function user_group()
    {
        return $this->belongsTo('App\Models\UserGroup', 'user_group_id');
    }

<<<<<<< HEAD
    public function supervisor()
    {
        return $this->belongsTo('App\Models\User', 'supervisor');
    }
=======

>>>>>>> ruben_dev
}
