<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\UserLoginHistory;
use App\Models\UserDetail;
use App\Models\UserProfile;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\OrganizationParameter;
use App\Models\Modules;
use App\Models\ClassificationMaterial;
use App\Models\ClassificationType;
use App\Models\ClassificationParameter;

use App\Helpers\HashId;

class RoleController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['role-view']);

        $role = (new Role)->newQuery();

        $role->with([
            'modules',
            'organization_parameter',
            'createdBy',
            'updatedBy'
        ]);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $role->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        // filter composite
        if (request()->has('composite')) {
            $role->where('composite', 1);
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $role->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $role->orderBy('name', 'asc');
        }

        $role = $role->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        $role->transform(function($data){
            // total auth object assigned to role
            $data->total_auth_object = $data->modules->count();
            // total user assigned to role
            $data->total_user = $data->users->count();
            return $data;
        });

        return $role;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['role-view']);

        $role = (new Role)->newQuery();

        $role->with([
            'modules',
            'organization_parameter',
            'users',
            'createdBy',
            'updatedBy'
        ]);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $role->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $sort_field = request()->input('sort_field');
            switch ($sort_field) {
                case 'total_auth_object':
                    $role->leftJoin('modules_roles','modules_roles.role_id','=','roles.id');
                    $role->select('roles.*', DB::raw('count(modules_roles.role_id) as jlh'));
                    $role->groupBy('modules_roles.role_id','roles.id');
                    $role->orderBy('jlh', $sort_order);
                break;

                case 'total_user':
                    $role->leftJoin('role_users','role_users.role_id','=','roles.id');
                    $role->leftJoin('users','users.id','=','role_users.user_id');
                    $role->select('roles.*', DB::raw('count(users.id) as jlh'));
                    $role->groupBy('roles.id');
                    $role->orderBy('jlh', $sort_order);
                break;

                default:
                    $role->orderBy(request()->input('sort_field'), $sort_order);
                break;
            }

        } else {
            $role->orderBy('name', 'asc');
        }

        $role = $role->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($role['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $role['data'][$k] = $v;
                $role['data'][$k]['total_auth_object'] = count($v['modules']);
                $role['data'][$k]['total_user'] = count($v['users']);
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $role;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['role-create']);

        $this->validate(request(), [
            'name'      => 'required|unique:roles,name|alpha_dash',
            'composite' => 'nullable|boolean'
        ]);

        if (is_null($request->composite)) {
            $composite = 0;
        } else if ($request->composite == 0) {
            $composite = 0;
        } else if ($request->composite == 1) {
            $composite = 1;
        }

        $save = Role::create([
            'name' => $request->name,
            'composite' => $composite,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            $save->id_hash = HashId::encode($save->id);
            return $save;
        } else {
            return response()->json([
                'message' => 'Failed Insert data',
            ], 400);
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $role = Role::with(['modules', 'organization_parameter', 'users', 'createdBy', 'updatedBy'])->findOrFail($id);

        // total auth object assigned to this role
        $role->total_auth_object = $role->modules->count();
        // total user assigned to this role
        $role->total_user = $role->users->count();

        // Organization Parameter
        $orgparam = OrganizationParameter::where('role_id', $id)->get();

        $plant = $orgparam->where('key', 'plant')->first();
        $room  = $orgparam->where('key', 'room')->first();

        $datamap = [
            'plant' => $plant ? array_map('intval', json_decode($plant->value)) : null,
            'room' => $room ? array_map('intval', json_decode($room->value)) : null,
        ];

        $role->organization_parameter_value = $datamap;

        return $role;
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'ROLE')
            ->whereNotNull('causer_id')
            ->where('subject_id', $id);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $log->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(properties)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $log->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $log->orderBy('id', 'desc');
        }

        $log = $log->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        $log->transform(function ($data) {
            $data->properties = json_decode($data->properties);

            return $data;
        });

        return $log;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $role = Role::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|alpha_dash|unique:roles,name,'. $id .'',
            'composite' => 'nullable|boolean'
        ]);

        if (is_null($request->composite)) {
            $composite = 0;
        } else if ($request->composite == 0) {
            $composite = 0;
        } else if ($request->composite == 1) {
            $composite = 1;
        }

        $save = $role->update([
            'name' => $request->name,
            'composite' => $composite,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            $role->id_hash = HashId::encode($role->id);
            return $role;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $role = Role::findOrFail($id);

        $delete = $role->delete();

        if ($delete) {
            return response()->json($delete);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 400);
        }
    }

    public function multipleDelete()
    {
        Auth::user()->cekRoleModules(['role-update']);

        $data = [];
        foreach (request()->id as $key => $ids) {
            try {
                $ids = HashId::decode($ids);
            } catch(\Exception $ex) {
                return response()->json([
                    'message'   => 'Data invalid',
                    'errors'    => [
                        'id.'.$key  => ['id not found']
                    ]
                ], 422);
            }

            $data[] = $ids;
        }

        request()->merge(['id' => $data]);

        $this->validate(request(), [
            'id'          => 'required|array',
            'id.*'        => 'required|exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Role::findOrFail($ids)->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Success delete data'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'error delete data',
                'detail' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);
        }
    }

    public function storeRoleAuthObject($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'module'    => 'required|array',
            'module.*'  => 'required|exists:modules,id',
        ]);

        $role = Role::find($id);

        $role->modules()->attach($request->module);

        $role->update([
            'updated_by' => Auth::user()->id,
            'updated_at' => now()
        ]);

        return $role->modules;
    }

    public function roleAuthObjectList($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return Role::with(['modules'])->find($id);
    }

    public function updateRoleAuthObject($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'module'    => 'required|array',
            'module.*'  => 'required|exists:modules,id',
        ]);

        $role = Role::find($id);

        $role->modules()->sync($request->module);

        $role->update([
            'updated_by' => Auth::user()->id,
            'updated_at' => now()
        ]);

        return $role->modules;
    }

    public function storeRoleOrgParam($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'plant'   => 'nullable|array',
            'plant.*' => 'nullable|exists:plants,id',
            'room'    => 'nullable|array',
            'room.*'  => 'nullable|exists:rooms,id',
        ]);

        $role = Role::find($id);

        // Encode Org Param
        $plant = $request->plant ? json_encode($request->plant) : json_encode([]);
        $room = $request->room ? json_encode($request->room) : json_encode([]);

        // Update or create org param
        $rplant = OrganizationParameter::updateOrCreate(
            ['key' => 'plant', 'role_id' => $id],
            ['value' => $plant, 'updated_by' => Auth::user()->id, 'created_by' => Auth::user()->id,]
        );
        $rroom = OrganizationParameter::updateOrCreate(
            ['key' => 'room', 'role_id' => $id],
            ['value' => $room, 'updated_by' => Auth::user()->id, 'created_by' => Auth::user()->id,]
        );

        $role->update([
            'updated_by' => Auth::user()->id,
            'updated_at' => now()
        ]);

        return OrganizationParameter::where('role_id', $id)->get();
    }

    public function roleOrgParamList($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return Role::with(['organization_parameter'])->find($id);
    }

    public function updateRoleOrgParam($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'plant'   => 'nullable|array',
            'plant.*' => 'nullable|exists:plants,id',
            'room'    => 'nullable|array',
            'room.*'  => 'nullable|exists:rooms,id',
        ]);

        $role = Role::find($id);

        // Encode Org Param
        $plant = $request->plant ? json_encode($request->plant) : json_encode([]);
        $room = $request->room ? json_encode($request->room) : json_encode([]);

        // Update or create org param
        $rplant = OrganizationParameter::updateOrCreate(
            ['key' => 'plant', 'role_id' => $id],
            ['value' => $plant, 'updated_by' => Auth::user()->id]
        );
        $rroom = OrganizationParameter::updateOrCreate(
            ['key' => 'room', 'role_id' => $id],
            ['value' => $room, 'updated_by' => Auth::user()->id]
        );
        
        $role->update([
            'updated_by' => Auth::user()->id,
            'updated_at' => now()
        ]);

        return OrganizationParameter::where('role_id', $id)->get();
    }

    public function assignUser($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'user_id'     => 'required|array',
            'user_id.*'   => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($id);

        // if (in_array(null, $request->user_id, true) || in_array('', $request->user_id, true)) {
        //     $role->users()->syncWithoutDetaching($request->user_id);
        // } else {
        //     $role->users()->syncWithoutDetaching($request->user_id);
        // }

        foreach ($request->user_id as $key => $value) {
            RoleUser::updateOrCreate(
                [
                    'role_id' => $id,
                    'user_id' => $value
                ],
                [
                    'role_id' => $id,
                    'user_id' => $value,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $role->update([
            'updated_by' => Auth::user()->id,
            'updated_at' => now()
        ]);

        return $role->users;
    }

    public function roleUserList($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = (new User)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $user->whereHas('roles', function ($data) use ($q) {
                $data->where(function($query) use ($q) {
	                $query->orWhere(DB::raw("LOWER(username)"), 'LIKE', "%".$q."%");
	                $query->orWhere(DB::raw("LOWER(firstname)"), 'LIKE', "%".$q."%");
	                $query->orWhere(DB::raw("LOWER(lastname)"), 'LIKE', "%".$q."%");
	                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
	            });
            });
        }

        $user->whereHas('roles', function ($q) use ($id) {
            $q->where('roles.id', $id);
        });

        $user->with(['roles' => function($q) use ($id){
            $q->where('roles.id', $id);
        }]);

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $user->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $user->orderBy('id', 'desc');
        }

        $user = $user->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        $user->transform(function($data) use ($id){
            $role_user = RoleUser::where('user_id', $data->id)
                ->where('role_id', $id)
                ->first();

            $data->createdBy = User::find($role_user->created_by);
            $data->updatedBy = User::find($role_user->updated_by);

            return $data;
        });

        return $user;
    }

    public function deleteRoleUser($id)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'user_id'     => 'nullable|array',
            'user_id.*'   => 'nullable|exists:users,id',
        ]);

        $role = Role::find($id);

        $role->users()->detach(request()->user_id);

        $role->update([
            'updated_by' => Auth::user()->id,
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'success delete user from role'
        ], 200);
    }

    public function copy(Request $request)
    {
        Auth::user()->cekRoleModules(['role-create']);

        $this->validate(request(), [
            'name'      => 'required|unique:roles,name|alpha_dash',
            'role_id'   => 'required|exists:roles,id'
        ]);

        try {
            DB::beginTransaction();

            // save new role
            $save = Role::create([
                'name' => $request->name,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get role to be copied
            $copy_role = Role::find($request->role_id);

            // get role auth object to be copied
            $copy_auth_object = $copy_role->modules->pluck('id');

            // attach auth object to new role
            $save->modules()->attach($copy_auth_object);

            // get role org param to be copied
            $copy_org_param = OrganizationParameter::where('role_id', $request->role_id)->get();

            // save org param to new role
            foreach ($copy_org_param as $key => $value) {
                OrganizationParameter::create([
                    'key' => $value->key,
                    'role_id' => $save->id,
                    'value' => $value->value,
                    'updated_by' => Auth::user()->id,
                    'created_by' => Auth::user()->id
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Success copy role'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'error copy role',
                'detail' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);
        }
    }

    public function compositeList($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $child_id = Role::find($id)->childs->pluck('child_id');

        $role = (new Role)->newQuery();

        $role->whereIn('id', $child_id);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $role->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $role->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $role->orderBy('name', 'asc');
        }

        $role = $role->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $role;
    }

    public function composite($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $role = Role::with('childs')->findOrFail($id);

        $this->validate(request(), [
            'child_id' => 'required|exists:roles,id',
        ]);

        if (!$role->composite) {
            return response()->json([
                'message' => 'role parent type must composite',
            ], 422);
        }

        if ($id == $request->child_id) {
            return response()->json([
                'message' => 'role parent & child can\'t same',
            ], 422);
        }

        $child_role = Role::findOrFail($request->child_id);

        if ($child_role->composite) {
            return response()->json([
                'message' => 'role child type must not composite',
            ], 422);
        }

        $save = RoleComposite::updateOrCreate(
            [
                'parent_id' => $id,
                'child_id' => $request->child_id
            ],
            [
                'parent_id' => $id,
                'child_id' => $request->child_id
            ]
        );

        if ($save) {
            $response = Role::with('childs')->with('childs.child_role')->findOrFail($id);
            $response->id_hash = HashId::encode($response->id);
            return $response;
        } else {
            return response()->json([
                'message' => 'Failed Composite Role',
            ], 400);
        }
    }

    public function uncomposite($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $role = Role::with('childs')->findOrFail($id);

        $this->validate(request(), [
            'child_id' => 'required|exists:roles,id',
        ]);

        $check = RoleComposite::where('parent_id', $id)
            ->where('child_id', $request->child_id)
            ->first();

        if (!$check) {
            return response()->json([
                'message' => 'data not found',
            ], 422);
        }

        $delete = $check->delete();

        if ($delete) {
            $response = Role::with('childs')->with('childs.child_role')->findOrFail($id);
            $response->id_hash = HashId::encode($response->id);
            return $response;
        } else {
            return response()->json([
                'message' => 'Failed Delete Composite Role',
            ], 400);
        }
    }
}
