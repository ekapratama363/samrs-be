<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class RoleUser extends Model
{
    use LogsActivity;

    protected $table = 'role_users';

    protected $fillable = [
        'role_id', 'user_id', 'created_by', 'updated_by'
    ];

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'USER_ROLE';
    protected static $logOnlyDirty = false;
    
    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }
}
