<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ROLE';
    protected static $logOnlyDirty = false;

    public function getDescriptionForEvent(string $enventName): string
    {
        return "Table \"{$this->table}\" is{$enventName}";
    }

    protected $filleable = ['name', 'created_by', 'updated_by', 'composite'];

    public function user()
    {
        return $this->belongsToMany('App\Models\Users', 'role_users', 'role_id', 'user_id')
            ->withPivot('id')
            ->withTimestamps();
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
        return $this->hasMany('App\Models\RoleComposite','parent_id');
    }

    public function created_by()
    {
        return $this->hasOne('App\Models\User', 'id','created_by');
    }

    public function updated_by()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
}
