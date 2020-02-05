<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'USER';
    protected static $logOnlyDirty = false;


    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'wrong_pass',
        'status',
        'api_token',
        'mobile',
        'confirmation_code',
        'deleted'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'confirmation_code'
    ];

    protected $with = [
        // 'roles'
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->firstname.' '.$this->lastname;
    }


    public function getDescriptionForEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }


    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_users', 'user_id', 'role_id')
                ->withPivot('id')
                ->withTimestamps();
    }

    public function hasAnyRole($roles)
    {
        return null !== $this->roles()->whereIn('name', $roles)->first();
    }

    public function hasRole($role)
    {
        return null !== $this->roles()->where('name', $role)->first();
    }

    public function authorizeRoles($roles)
    {
        if (is_array($roles))
            return $this->hasAnyRole($roles) || abort(401, 'This action is unauthorized.');

        return $this->hasRole($roles) || abort(401, 'This action is unauthorized.');
    }

    public function authorizeMenu($roles)
    {
        if (is_array($roles))
            return $this->hasAnyRole($roles);

        return $this->hasRole($roles);
    }


    public function cekRoleModules($object)
    {
        $role_id = RoleUser::where('user_id', $this->id)->pluck('role_id');

        $all_role = [];
        foreach($role_id as $role) {
            $role_data = Role::find($role);

            if ($role_data->composite) {
                $child_id = RoleComposite::where('parent_id', $role)->pluck('child_id');

                if (count($child_id) > 0) {
                    foreach ($child_id as $child) {
                        $all_role[] = $child;
                    }
                }
            } else {
                $all_role[] = $role;
            }
        }

        $modules_id = ModulesRole::whereIn('role_id', $all_role)->pluck('modules_id');
        $modules = Modules::whereIn('id', $modules_id)->where('object', $object)->first();
        $object_name = Modules::where('object', $object)->first();
        return $modules || abort(403, $object_name->description);
    }

    public function roleOrgParam($key)
    {
        $role_id = RoleUser::where('user_id', $this->id)->pluck('role_id');

        $all_role = [];
        foreach($role_id as $role)
        {
            $role_data = Role::find($role);

            if ($role_data->composite)
            {
                $child_id = RoleComposite::where('parent_id', $role)->pluck('child_id');

                if (count($child_id) > 0) {
                    foreach ($child_id as $child)
                    {
                        $all_role[] = $child;
                    }
                }
            }
            else
            {
                $all_role[] = $role;
            }
        }

        $org_param = OrganizationParameter::whereIn('role_id', $all_role)->where('key', $key)->pluck('value');

        if (count($org_param) > 0) {
            if (in_array("[null]", json_decode($org_param)))
            {
                $data = [];
            }
            else {
                $param = [];
                foreach ($org_param as $data)
                {
                    $param[] = array_map('intval', json_decode($data));
                }
                $data = call_user_func_array("array_merge", $param);
            }
        }
        else {
            $data = [];
        }

        return $data;
    }

    public function detail()
    {
        return $this->hasOne('App\Models\UserDetail', 'user_id');
    }

    public function profile()
    {
        return $this->hasMany('App\Models\UserProfile', 'user_id');
    }

    public function login_history()
    {
        return $this->hasMany('App\Models\UserLoginHistory', 'user_id');
    }

}
