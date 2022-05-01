<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use Auth;
use DB;
use Storage;

use App\Models\User;
use App\Models\UserLoginHistory;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\RoleComposite;
use App\Models\ModulesRole;
use App\Models\Modules;
use App\Models\UserProfile;
use App\Models\UserDetail;

use App\Helpers\HashId;

class ProfileController extends Controller
{
    public function profile()
    {
        return User::with(['detail', 'profile', 'roles'])
            ->with([
                'detail.location',
                'detail.company',
                'detail.user_group',
                'detail.supervisor',
                'detail.department',
                'detail.cost_center'
            ])->find(Auth::user()->id);
    }

    public function moduleList()
    {
        $profile_id = Auth::user()->id;

        $role_id = RoleUser::where('user_id', $profile_id)->pluck('role_id');

        // extract all role include composite role
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

        return Modules::select('object', 'description')->whereIn('id', $modules_id)->get();
    }

    public function loginHistory()
    {
        $id = Auth::user()->id;

        $user = (new UserLoginHistory)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $user->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(ip_address)"), 'LIKE', "%".$q."%");
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
            return $user->paginate(request()->input('per_page'))->appends(request()->except('page'));
        } else {
            return $user->paginate(20)->appends(request()->except('page'));
        }
    }

    public function updateLoginDetail(Request $request)
    {
        Auth::user()->cekRoleModules(['user-update-profile-self']);

        $user = Auth::user();

        $this->validate(request(), [
            'firstname'     => 'required|string|max:191',
            'lastname'      => 'nullable|string|max:191',
            'username'      => 'required|string|max:191|unique:users,username,'. $user->id .'',
            'email'         => 'required|string|email|max:191|unique:users,email,'. $user->id .'',
            'mobile'        => 'required|min:8|max:15',
        ]);

        $existMail = User::whereRaw('LOWER(email) = ?', strtolower($request->email))
            ->where('id', '!=', $user->id)
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
            ->where('id', '!=', $user->id)
            ->first();

        if ($existUsername) {
            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'username' => ['username already taken']
                ]
            ],422);
        }

        // save user data
        $save = $user->update([
            'firstname'         => $request->firstname,
            'lastname'          => $request->lastname,
            'username'          => $request->username,
            'email'             => $request->email,
            'mobile'            => $request->mobile,
        ]);

        if ($save) {
            return $user;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 422);
        }
    }

    public function updateProfile(Request $request)
    {
        Auth::user()->cekRoleModules(['user-update-profile-self']);

        $this->validate(request(), [
            'parameter'     => 'nullable|array',
        ]);

        $user = Auth::user();

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
                        'user_id'                       => $user->id,
                        'classification_parameter_id'   => $id_parameter,
                    ],
                    [
                        'value'         => $value_parameter
                    ]
                );
            }
        }

        return User::with(['detail', 'profile'])->find($user->id);
    }

    public function deletePhotoProfile($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-update-profile']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user = UserDetail::where('user_id', $id)->first();

        if (Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->photo = '';
        $user->save();

        if ($user) {
            return $user;
        } else {
            return response()->json([
                'message' => 'Unable to upload profile Image'
            ], 422);
        }
    }

    public function changePhotoProfile($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-update-profile']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $user_detail = UserDetail::with('user')->where('user_id', $id)->first();

        if (!$user_detail) {
            $this->validate($request, [
                'image' => 'required|image'
            ]);

            $id_user = $id;

            if (request()->has('image')) {
                $image_data = request()->file('image');
                $image_name = md5(time()) . $id_user . ".jpg";
                $image_path = 'images/user';

                $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);

                $save = UserDetail::create([
                    'user_id' => $id_user,
                    'photo'   => $uploaded,
                ]);

                if ($uploaded) {
                    return $user_detail;
                } else {
                    return response()->json([
                        'message' => 'Unable to upload profile Image'
                    ], 422);
                }
            }
        } else {
            $this->validate($request, [
                'image' => 'required|image'
            ]);

            $id_user = $id;

            if (request()->has('image')) {
                $image_data = request()->file('image');
                $image_name = md5(time()) . $id_user . ".jpg";
                $image_path = 'images/user';

                $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);

                $profileImg = $user_detail->photo;
                if ($profileImg != null) {
                    if (Storage::disk('public')->exists($user_detail->photo)) {
                        Storage::disk('public')->delete($user_detail->photo);
                    }

                    $detail = $user_detail;
                    $detail->photo = $uploaded;
                    $detail->save();
                } else {
                    $detail = $user_detail;
                    $detail->photo = $uploaded;
                    $detail->save();
                }

                if ($uploaded) {
                    return $user_detail;
                } else {
                    return response()->json([
                        'message' => 'Unable to upload profile Image'
                    ], 422);
                }
            }
        }
    }

    public function changePassword()
    {
        $this->validate(request(),
            [
                'password' => 'required|string|min:'.appsetting('PASS_LENGTH_MIN').'|confirmed|regex:'.appsetting('PASS_REGEX'),
                'oldPassword' => 'required'
            ],
            [
                'regex' => 'The :attribute must have :\n'.appsetting('PASS_REGEX_DESCRIPTION'),
            ]
        );

        $data = User::find(Auth::user()->id);

        if ($data) {
            if (Hash::check(request()->input('oldPassword'), $data->password)) {

                if($this->_newPassInPasswordHistory($data->id, request()->input('password'))) {
                    return response()->json([
                        'message' => 'New password had been used before.',
                        'success' => false
                    ], 422);
                }

                try{
                    \DB::beginTransaction();
                    $data->password     = Hash::make(request()->input('password'), ['rounds' => 10]);
                    $data->api_token    = str_random(100);
                    $data->save();

                    // record to password history table
                    \App\Models\PasswordHistory::create(['user_id'=>$data->id, 'password'=>$data->password]);

                    // Move to Queue
                    \Mail::send(new \App\Mail\ChangePassword($data));

                    \DB::commit();

                    return response()->json([
                        'message' => 'Change Password Success',
                        'success' => true
                    ], 200);
                }
                catch(\Exception $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message'       => 'Failed Update password. '.$e->getMessage(),
                        'success'       => false,
                        'stacktrace'    => strtolower(env('APP_DEBUG'))==='true'?$e->getTrace():''
                    ], 422);
                }
            } else {
                $response = [
                    'message' => 'Old Password did not match',
                    'success' => false
                ];

                return response()->json($response, 422);
            }
        } else {
            $response = [
                'message' => 'User not found',
                'success' => false
            ];
            return response()->json($response, 422);
        }
    }

    private function _newPassInPasswordHistory($user_id, $new_password) {
        if(empty($user_id)) return NULL;
        $history_pass_limit = (int)appsetting('HISTORY_PASS_KEEP', 3);

        $pass_histories = \App\Models\PasswordHistory::where('user_id', $user_id)
            ->orderBy('created_at', 'DESC')
            ->limit($history_pass_limit)
            ->get();

        $is_used = FALSE;
        foreach($pass_histories as $d) {
            if(Hash::check($new_password, $d->password)) {
                $is_used = TRUE;
                break;
            }
        }

        return $is_used;
    }
}
