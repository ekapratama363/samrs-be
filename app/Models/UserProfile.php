<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UserProfile extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'UserProfile';
<<<<<<< HEAD
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
=======
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
>>>>>>> ruben_dev
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
<<<<<<< HEAD
        'user_id', 'value'
    ];
=======
        'user_id', 'address', 'phone_number', 'photo'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

>>>>>>> ruben_dev
}
