<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'username', 'email', 'password', 'api_token', 'mobile', 'wrong_pass', 'status', 'confirmation_code',
        'last_request_time', 'deleted'
    ]; 

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'password', 'remember_token', 'api_token', 'confirmation_code'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['name'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static $logFillable = true;
    protected static $logName = 'USER';
    protected static $logOnlyDirty = false;


    public function getDescriptionforEvent(string $eventName): string {
        return "Table \"{$this->table}\" is {$eventName}";
    }


    public function getNameAttribute()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function roles()
    {
        return $this->belongsTomany('App\Models\Role', 'role_users', 'user_id', 'role_id')
        ->withPivot('id')
        ->withTimestamps();
    }

    public function authorizeRoles($roles)
    {
        if(is_array($roles))
        {
            return $this->hasAnyRole($roles) ||
                abort(401, 'This Action is unauthorized.');
        }

        return $this->hasRole($roles) ||
            abort(401, 'This action is unauthorized.');
    }

    public function authorizeMenu($roles)
    {
        if(is_array($roles))
        {
            return $this->hasAnyRole($roles);
        }
        return $this->hasRole($roles);
    }

    public function hasAnyRole($roles)
    {
        return null !== $this->roles()->whereIn('name', $roles)->first();
    }

    public function hasRole($roles)
    {
        return null !== $this->roles()->where('name', $roles)->first();
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

    public function checkModules($object)
    {
        $role_id = RoleUser::where('user_id', $this->id)->pluck('role_id');
        
        $all_role = [];
        foreach ($role_id as $role) {
            $role_data = Role::find($role);
            if($role_data->composit)
            {
                $child_id = RoleComposit::where('parent_id', $role)->pluck('child_id');
                if(count($child_id) > 0)
                {
                    foreach ($child_id as $child) {
                        $all_role [] = $child;
                    }
                }
            }else {
                $all_role [] = $role;
            }
        }

        $modules_id = ModulesRole::where('role_id', $all_role)->pluck('modules_id');
        
        $modules = Modules::whereIn('id', $modules_id)->where('object', $object)->first();

        $object_name = Modules::where('object', $object)->first();

        return $modules || 
            abort(401, $object_name->description);

    }

    public function roleOrgParam($key)
    {
        $role_id = RoleUser::where('user_id', $this->id)->pluck('role_id');

        $all_role = [];
        foreach ($role_id as $role) {
            $role_data = Role::find($role);
            if($role_data->composit)
            {
                $child_id = RoleComposit::where('parent_id', $role)->pluck('child_id');

                if(count($child_id) > 0)
                {
                    foreach ($child_id as $child) {
                        $all_role [] = $child;
                    }
                }
            }else {
                $all_role [] = $role;
            }
        }

        $org_param = OrganizationParameter::whereIn('role_id', $all_role)->where('key', $key)->pluck('value');

        if(count($org_param) > 0)
        {
            if(in_array("[null]", json_decode($org_param)))
            {
                $data = [];
            }else {
                $param = [];
                foreach ($org_param as $data) {
                    $param[] = array_map('inval', json_decode($data));
                }
                $data = call_user_func_array("array_merge", $param);
            }
        }else {
            $data = [];
        }

        return $data;
    }

}
