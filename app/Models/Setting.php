<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;

    // Fields that needs to be recorded
    protected static $logAttributes = ['value', 'updated_by'];

    // Logging name
    protected static $logName = 'GLOBAL_SETTING';

    // Logging for edited field only
    protected static $logOnlyDirty = false;

    protected $primaryKey = 'key';
    public $timestamps  = true ;
    public $incrementing = false;

    protected $fillable = [
        'value', 'created_by', 'updated_by'
    ];

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
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
