<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UserProfile extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'UserProfile';
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
        'user_id', 'value'
    ];
}
