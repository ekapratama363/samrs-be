<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UserProfile extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'UserProfile';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
<<<<<<< HEAD
        'user_id', 'address', 'phone_number', 'photo'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

=======
        'user_id', 'address', 'phone', 'photo', 'value'
    ];

>>>>>>> master
}
