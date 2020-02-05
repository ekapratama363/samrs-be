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
        'user_id', 'address', 'phone', 'photo', 'value'
    ];

}
