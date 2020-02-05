<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UserProfile extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'UserProfile';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'user_id', 'address', 'phone_number', 'photo'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

}
