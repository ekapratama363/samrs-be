<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends Model
{
    use LogsActivity;

    /**
     * Enable logging all changes in this model
     *
     * @var boolean
     */
    protected static $logFillable = true;
    protected static $logName = 'ROLE';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }

    protected $fillable = [
		'name', 'created_by', 'updated_by', 'composite'
	];

    public function users()
	{
		return $this->belongsToMany('App\Models\User', 'role_users', 'role_id', 'user_id')
				->withPivot('id')->withTimestamps();
	}

	public function modules()
	{
		return $this->belongsToMany('App\Models\Modules', 'modules_roles', 'role_id', 'modules_id');
	}

	public function organization_parameter()
    {
        return $this->hasMany('App\Models\OrganizationParameter', 'role_id');
    }

    public function childs()
    {
        return $this->hasMany('App\Models\RoleComposite', 'parent_id');
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
