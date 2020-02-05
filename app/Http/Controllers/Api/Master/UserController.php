<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\UserLoginHistory;
use App\Models\UserDetail;
use App\Models\UserProfile;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\OrganizationParameter;
use App\Models\Modules;

use App\Helpers\HashId;

class UserController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['user-view']);

        $user = (new User)->newQuery();

        $user->where('deleted', false);

        $user->with(['detail', 'profile', 'roles']);

        $user->with([
            'detail.location', 'detail.company', 'detail.user_group',
            'detail.supervisor', 'detail.department', 'detail.cost_center'
        ]);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $user->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(username)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(firstname)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(lastname)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(mobile)"), 'LIKE', "%".$q."%");
            });
        }

        // filter location
        if (request()->has('location')) {
            $location = request()->input('location');
            $user->whereHas('detail', function ($q) use ($location){
                $q->whereIn('user_details.location_id', $location);
            });
        }

        // filter company
        if (request()->has('company')) {
            $company = request()->input('company');
            $user->whereHas('detail', function ($q) use ($company){
                $q->whereIn('user_details.company_id', $company);
            });
        }

        // filter department
        if (request()->has('department')) {
            $department = request()->input('department');
            $user->whereHas('detail', function ($q) use ($department){
                $q->whereIn('user_details.department_id', $department);
            });
        }

        // filter user group
        if (request()->has('user_group')) {
            $user_group = request()->input('user_group');
            $user->whereHas('detail', function ($q) use ($user_group){
                $q->whereIn('user_details.user_group_id', $user_group);
            });
        }

        // filter firstname
        if (request()->has('firstname')) {
            $user->where(DB::raw("LOWER(users.firstname)"), 'LIKE', "%".strtolower(request()->input('firstname'))."%");
        }

        // filter lastname
        if (request()->has('lastname')) {
            $user->where(DB::raw("LOWER(users.lastname)"), 'LIKE', "%".strtolower(request()->input('lastname'))."%");
        }

        // filter status
        if (request()->has('status')) {
            $user->whereIn('users.status', request()->input('status'));
        }

        // filter created date
        if (request()->has('created_at')) {
            $user->whereBetween('users.created_at', [request()->created_at[0], request()->created_at[1]]);
        }

        // filter roles
        if (request()->has('roles')) {
            $role = request()->input('roles');
            $user->whereHas('roles', function ($q) use ($role) {
                for ($i=0; $i<count(request()->input('roles')); $i++) {
                    if ($i==0) {
                        $q->where('roles.id', $role[$i]);                            
                    } else {
                        $q->orWhere('roles.id', $role[$i]);
                    }
                }
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $user->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $user->orderBy('id', 'desc');
        }

        $user = $user->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        $user->transform(function($data){
            $login = UserLoginHistory::where('user_id', $data->id)
                ->orderBy('updated_at', 'desc')
                ->first();

            $data->last_ip_address = $login ? $login->ip_address : null;

            $data->last_login_date = $login ? $login->updated_at : null;

            return $data;
        });

        return $user;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['user-view']);

        $user = (new User)->newQuery();

        $user->where('users.deleted', false);

        $user->with(['detail', 'profile', 'roles']);

        $user->with([
            'detail.location', 'detail.company', 'detail.user_group',
            'detail.supervisor', 'detail.department', 'detail.cost_center'
        ]);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $user->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(username)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(firstname)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(lastname)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(mobile)"), 'LIKE', "%".$q."%");
            });
        }

        // filter location
        if (request()->has('location')) {
            $location = request()->input('location');
            $user->whereHas('detail', function ($q) use ($location){
                $q->whereIn('user_details.location_id', $location);
            });
        }

        // filter company
        if (request()->has('company')) {
            $company = request()->input('company');
            $user->whereHas('detail', function ($q) use ($company){
                $q->whereIn('user_details.company_id', $company);
            });
        }

        // filter department
        if (request()->has('department')) {
            $department = request()->input('department');
            $user->whereHas('detail', function ($q) use ($department){
                $q->whereIn('user_details.department_id', $department);
            });
        }

        // filter user group
        if (request()->has('user_group')) {
            $user_group = request()->input('user_group');
            $user->whereHas('detail', function ($q) use ($user_group){
                $q->whereIn('user_details.user_group_id', $user_group);
            });
        }

        // filter firstname
        if (request()->has('firstname')) {
            $user->where(DB::raw("LOWER(users.firstname)"), 'LIKE', "%".strtolower(request()->input('firstname'))."%");
        }

        // filter lastname
        if (request()->has('lastname')) {
            $user->where(DB::raw("LOWER(users.lastname)"), 'LIKE', "%".strtolower(request()->input('lastname'))."%");
        }

        // filter status
        if (request()->has('status')) {
            $user->whereIn('users.status', request()->input('status'));
        }

        // filter created date
        if (request()->has('created_at')) {
            $user->whereBetween('users.created_at', [request()->created_at[0], request()->created_at[1]]);
        }

        // filter roles
        if (request()->has('roles')) {
            $role = request()->input('roles');
            $user->whereHas('roles', function ($q) use ($role) {
                for ($i=0; $i<count(request()->input('roles')); $i++) {
                    if ($i==0) {
                        $q->where('roles.id', $role[$i]);                            
                    } else {
                        $q->orWhere('roles.id', $role[$i]);
                    }
                }
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $sort_field = request()->input('sort_field');
            switch ($sort_field) {
                case 'user_group':
                    $user->leftJoin('user_details','user_details.user_id','=','users.id');
                    $user->leftJoin('user_groups','user_groups.id','=','user_details.user_group_id');
                    $user->select('users.*');
                    $user->orderBy('user_groups.code',$sort_order);
                break;

                case 'company':
                    $user->leftJoin('user_details','user_details.user_id','=','users.id');
                    $user->leftJoin('companies','companies.id','=','user_details.company_id');
                    $user->select('users.*','companies.code');
                    $user->orderBy('companies.id',$sort_order);
                break;

                case 'department':
                    $user->leftJoin('user_details', 'user_details.user_id','=','users.id');
                    $user->leftJoin('departments','departments.id','=','user_details.department_id');
                    $user->select('users.*', 'departments.code');
                    $user->orderBy('departments.id',$sort_order);
                break;
                
                case 'last_login_date':
                    $user->join('user_login_histories','user_login_histories.user_id','=','users.id');
                    $user->select('users.*');
                    $user->orderBy('user_login_histories.created_at',$sort_order);
                break;

                case 'last_ip_address': 
                    $user->join('user_login_histories','user_login_histories.user_id','=','users.id');
                    $user->select('users.*');
                    $user->orderBy('user_login_histories.ip_address',$sort_order);
                break;

                default:
                    $user->orderBy($sort_field, $sort_order);
                break;
            }
        } else {
            $user->orderBy('id', 'desc');
        }

        $user = $user->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'))
            ->toArray();

        foreach($user['data'] as $k => $v) {
            try {
                $login = UserLoginHistory::where('user_id', $v['id'])
                    ->orderBy('updated_at', 'desc')
                    ->first();

                $v['id'] = HashId::encode($v['id']);
                $user['data'][$k] = $v;
                $user['data'][$k]['last_ip_address'] = $login ? $login->ip_address : null;
                $user['data'][$k]['last_login_date'] = $login ? Carbon::parse($login->updated_at)->format('Y-m-d H:i:s') : null;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $user;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['user-create']);

        $this->validate(request(),
            [
                'firstname'     => 'required|string|max:191',
                'lastname'      => 'nullable|string|max:191',
                'username'      => 'required|string|max:191',
                'email'         => 'required|string|email|max:191',
                'mobile'        => 'required|min:8|max:15',
                'password'      => 'nullable|min:8',
            ]
        );

        // cek email exist in db
        $existMail = User::whereRaw('LOWER(email) = ?', strtolower($request->email))->first();

        // cek username exist in db
        $existUsername = User::whereRaw('LOWER(username) = ?', strtolower($request->username))->first();

        // generate random code for confirmation
        $code = str_random(30);

        try{
            \DB::beginTransaction();

            // if email exist
            if ($existMail) {
                // if email exist but deleted, remove deleted
                if($existMail->deleted){
                    $existMail->update([
                        'firstname'         => $request->firstname,
                        'lastname'          => $request->lastname,
                        'username'          => $request->username,
                        'email'             => $request->email,
                        'mobile'            => $request->mobile,
                        'api_token'         => str_random(100),
                        'status'            => 0, //pending email
                        'confirmation_code' => $code,
                        'deleted'           => 0,
                    ]);

                    $save = $existMail;
                } else {
                    // error if email exist & not deleted
                    return response()->json([
                        'message' => 'Data invalid',
                        'errors' => [
                            'email' => ['email already taken']
                        ]
                    ],422);
                }
            } else if ($existUsername) {// if username exist
                // if username exist but deleted, remove deleted
                if($existUsername->deleted){
                    $existUsername->update([
                        'firstname'         => $request->firstname,
                        'lastname'          => $request->lastname,
                        'username'          => $request->username,
                        'email'             => $request->email,
                        'mobile'            => $request->mobile,
                        'api_token'         => str_random(100),
                        'status'            => 0, //pending email
                        'confirmation_code' => $code,
                        'deleted'           => 0,
                    ]);

                    $save = $existUsername;
                } else {
                    // error if username exist & not deleted
                    return response()->json([
                        'message' => 'Data invalid',
                        'errors' => [
                            'username' => ['username already taken']
                        ]
                    ],422);
                }
            } else {// if email & username is new
                /** User Status
                * 0 = Pending
                * 1 = Active
                * 2 = 2FA
                * 3 = Suspend
                * 4 = Blocked
                **/

                // create user data
                $user = [
                    'firstname'         => $request->firstname,
                    'lastname'          => $request->lastname,
                    'username'          => $request->username,
                    'email'             => $request->email,
                    'mobile'            => $request->mobile,
                    'password'          => $request->password ? Hash::make($request->password, ['rounds' => 10]) : Hash::make(str_random(((int)appsetting('PASS_LENGTH_MIN'))), ['rounds' => 10]),
                    'api_token'         => str_random(100),
                    'status'            => 0, //pending email
                    'confirmation_code' => $code,
                ];

                $save = User::create($user);

                // record to password history
                \App\Models\PasswordHistory::create(['user_id'=>$save->id, 'password'=>$save->password]);
            }

            \Mail::send(new \App\Mail\RegisterMail($save, $code));

            \DB::commit();
            
            $save->id_hash = HashId::encode($save->id);

            return $save;
        }
        catch(\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message'       => 'Failed Insert data. '.$e->getMessage(),
                'stacktrace'    => strtolower(env('APP_DEBUG'))==='true'?$e->getTrace():''
            ], 400);
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['user-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = User::with(['detail', 'profile', 'roles'])
        ->with([
            'detail.location', 'detail.company', 'detail.user_group',
            'detail.supervisor', 'detail.department', 'detail.cost_center'
        ])
        ->find($id);

        $login = UserLoginHistory::where('user_id', $id)
                ->orderBy('updated_at', 'desc')->first();

        $user->last_ip_address = $login ? $login->ip_address : null;

        $user->last_login = $login ? $login->updated_at : null;

        return $user;
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['user-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'USER')
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
            ->appends(Input::except('page'));

        $log->transform(function ($data) {
            $data->properties = json_decode($data->properties);

            return $data;
        });

        return $log;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = User::findOrFail($id);

        $this->validate(request(), 
            [
                'firstname'     => 'required|string|max:191',
                'lastname'      => 'nullable|string|max:191',
                'username'      => 'required|string|max:191|unique:users,username,'. $id .'',
                'email'         => 'required|string|email|max:191|unique:users,email,'. $id .'',
                'mobile'        => 'required|min:8|max:15',
                // 'password'      => 'required|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).+$/',
                'status'        => 'required'
            ],
            [
                // 'regex'     => 'The :attribute must have lowercase letter, uppercase letter, a number .',
            ]
        );

        $existMail = User::whereRaw('LOWER(email) = ?', strtolower($request->email))
            ->where('id', '!=', $id)
            ->first();

        if ($existMail) {
            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'email' => ['email already taken']
                ]
            ],422);
        }

        $existUsername = User::whereRaw('LOWER(username) = ?', strtolower($request->username))
            ->where('id', '!=', $id)
            ->first();

        if ($existUsername) {
            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'username' => ['username already taken']
                ]
            ],422);
        }

        try{
            \DB::beginTransaction();

            // save user data
            $user->update([
                'firstname'         => $request->firstname,
                'lastname'          => $request->lastname,
                'username'          => $request->username,
                'email'             => $request->email,
                'mobile'            => $request->mobile,
                'status'            => $request->status,
            ]);

            // send email if edit user but status user not active
            if ($user->status != 1) {
                $code = str_random(30);
                $user->update([
                    'api_token'         => str_random(100),
                    'confirmation_code' => $code
                ]);

                \Mail::send(new \App\Mail\RegisterMail($user, $code));
            }

            $user->id_hash = HashId::encode($id);

            \DB::commit();
            return $user;
        }
        catch(\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message'       => 'Failed Update data. '.$e->getMessage(),
                'stacktrace'    => strtolower(env('APP_DEBUG'))==='true'?$e->getTrace():''
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['user-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = User::findOrFail($id)->update([
            'deleted' => 1
        ]);

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
        Auth::user()->cekRoleModules(['user-update']);

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
            'id.*'        => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = User::findOrFail($ids)->update([
                    'deleted' => 1
                ]);
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

    public function activate($id)
    {
        Auth::user()->cekRoleModules(['user-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $code = str_random(30);

        $user = User::find($id);

        $user->update([
            'status' => 3,
            'confirmation_code' => $code,
        ]);

        // kirim konfirmation code ke email
        \Mail::send(new \App\Mail\RegisterMail($user, $code));

        return response()->json([
            'message' => 'Success Activate User',
        ], 200);
    }

    public function block($id)
    {
        Auth::user()->cekRoleModules(['user-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = User::find($id);

        $user->update([
            'status' => 4
        ]);

        return response()->json([
            'message' => 'Success Block User',
        ], 200);
    }

    public function storeProfile($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-update-profile']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'user_group_id' => 'required|exists:user_groups,id',
            'location_id'   => 'required|exists:locations,id',
            'company_id'    => 'required|exists:companies,id',
            'cost_center_id'=> 'nullable|exists:cost_centers,id',
            'department_id' => 'nullable|exists:departments,id',
            'supervisor'    => 'nullable|exists:users,id',
            'photo'         => 'nullable|image',
            'join_date'     => 'nullable|date',
            'parameter'     => 'nullable|array',
        ]);

        $user = User::find($id);

        if ($request->supervisor == $id) {
            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'supervisor' => ['Supervisor cant same with User']
                ]
            ], 422);
        }

        // store photo
        if ($request->has('photo')) {
            $image_data = request()->file('photo');
            $image_ext  = request()->file('photo')->getClientOriginalExtension();
            $image_name = md5(time()). "." .$image_ext;
            $image_path = 'images/user';

            $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);
        } else {
            $uploaded = null;
        }

        // Store | Update User Profile Detail
        if ($user->detail) {
            $save = $user->detail;
            $save->user_group_id = $request->user_group_id;
            $save->location_id   = $request->location_id;
            $save->company_id    = $request->company_id;
            $save->cost_center_id= $request->cost_center_id;
            $save->department_id = $request->department_id;
            $save->supervisor    = $request->supervisor;
            $save->photo         = $uploaded;
            // $save->join_date     = $request->join_date;
            $save->save();
        } else {
            $save = UserDetail::create([
                'user_id'       => $id,
                'user_group_id' => $request->user_group_id,
                'location_id'   => $request->location_id,
                'company_id'    => $request->company_id,
                'cost_center_id'=> $request->cost_center_id,
                'department_id' => $request->department_id,
                'supervisor'    => $request->supervisor,
                'photo'         => $uploaded,
                'join_date'     => $request->join_date ? $request->join_date : now()
            ]);
        }

        // Store User Profile Params
        $parameter = $request->input('parameter');
        
        if (is_array($parameter) || is_object($parameter))
        {
            foreach($parameter as $key => $value)
            {
                $id_parameter   = explode("-",$key)[1];
                $value_parameter= $value;
                $insert_param   = UserProfile::updateOrCreate(
                    [
                        'user_id'                       => $id,
                        'classification_parameter_id'   => $id_parameter,
                    ],
                    [
                        'value'         => $value_parameter
                    ]
                );
            }
        }

        if ($save) {
            return User::with(['detail', 'profile'])->find($id);
        } else {
            return response()->json([
                'message' => 'Failed Insert data',
            ], 400);
        }
    }

    public function storePhotoProfile($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-update-profile']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'photo'         => 'required|image'
        ]);

        $user = User::find($id);

        // store photo
        if ($request->has('photo')) {
            $image_data = request()->file('photo');
            $image_ext  = request()->file('photo')->getClientOriginalExtension();
            $image_name = md5(time()). "." .$image_ext;
            $image_path = 'images/user';

            $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);
        } else {
            $uploaded = null;
        }

        // Store | Update User Profile Detail
        if ($user->detail) {
            $save = $user->detail;
            $save->photo         = $uploaded;
            $save->save();
        } else {
            $save = UserDetail::create([
                'user_id'       => $id,
                'join_date'     => $user->created_at,
                'photo'         => $uploaded,
            ]);
        }

        if ($save) {
            return User::with(['detail', 'profile'])->find($id);
        } else {
            return response()->json([
                'message' => 'Failed Insert data',
            ], 400);
        }
    }

    public function profileParam()
    {
        $type = ClassificationType::where('name', 'User_Profile')->first();

        $classification = ClassificationMaterial::where('classification_type_id', $type->id)
                        ->where('deleted', false)
                        ->pluck('id');

        return ClassificationParameter::whereIn('classification_id', $classification)->get();
    }

    public function storeUserProfileParam($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = User::findOrFail($id);

        $parameter = $request->input('parameter');
        
        if (is_array($parameter) || is_object($parameter))
        {
            foreach($parameter as $key => $value)
            {
                $id_parameter   = explode("-",$key)[1];
                $value_parameter= $value;
                $insert_param   = UserProfile::updateOrCreate(
                    [
                        'user_id'                       => $user->id,
                        'classification_parameter_id'   => $id_parameter,
                    ],
                    [
                        'value'         => $value_parameter
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'Success save data',
        ], 200);
    }

    public function assignRole($id, Request $request)
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
            'role_id'     => 'required|array',
            'role_id.*'   => 'required|exists:roles,id',
        ]);

        $user = User::find($id);

        // if (in_array(null, $request->role_id, true) || in_array('', $request->role_id, true)) {
        //     $user->roles()->syncWithoutDetaching();
        // } else {
        //     $user->roles()->syncWithoutDetaching($request->role_id);
        // }

        foreach ($request->role_id as $key => $value) {
            RoleUser::updateOrCreate(
                [
                    'user_id' => $id,
                    'role_id' => $value
                ],
                [
                    'user_id' => $id,
                    'role_id' => $value,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        return $user->roles;
    }

    public function userLoginHistory($id)
    {
        Auth::user()->cekRoleModules(['user-view-login-history']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = (new UserLoginHistory)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $user->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(ip_address)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(device)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(os)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(imei)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(imsi)"), 'LIKE', "%".$q."%");
            });
        }

        $user->whereHas('user', function ($q) use ($id) {
            $q->where('users.id', $id);
        });

        $user->with(['user' => function($q) use ($id){
            $q->where('users.id', $id);
        }]);

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $user->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $user->orderBy('updated_at', 'desc');
        }

        if (request()->has('per_page')) {
            return $user->paginate(request()->input('per_page'))->appends(Input::except('page'));
        } else {
            return $user->paginate(20)->appends(Input::except('page'));
        }
    }

    public function userRoleList($id)
    {
        Auth::user()->cekRoleModules(['user-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $role = (new Role)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $role->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        $role->whereHas('users', function ($q) use ($id) {
            $q->where('users.id', $id);
        });

        $role->with(['users' => function($q) use ($id){
            $q->where('users.id', $id);
        }]);

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $role->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $role->orderBy('id', 'desc');
        }

        $role = $role->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        $role->transform(function($data) use ($id){
            $role_user = RoleUser::where('role_id', $data->id)
                ->where('user_id', $id)
                ->first();

            $data->assigned_by = User::find($role_user->created_by);
            $data->assigned_at = Carbon::parse($role_user->updated_at)->format('Y-m-d H:i:s');

            return $data;
        });

        return $role;
    }

    public function deleteUserRole($id)
    {
        Auth::user()->cekRoleModules(['user-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'role_id'     => 'nullable|array',
            'role_id.*'   => 'nullable|exists:roles,id',
        ]);

        $user = User::find($id);

        $user->roles()->detach(request()->role_id);

        return response()->json([
            'message' => 'success delete user from role'
        ], 200);
    }

    public function userMatrixDownload()
    {
        return Excel::download(new UsersMatrixExport, 'user-matrix.xlsx');
    }
}
