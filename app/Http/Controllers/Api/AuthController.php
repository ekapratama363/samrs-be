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
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;
use App\Models\UserLoginHistory;
use App\Models\RoleUser;
use App\Models\ModulesRole;
use App\Models\Modules;
use App\Models\UserProfile;

class AuthController extends Controller
{
    public function authenticate()
    {
        $this->validate(request(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        $ip_address = request()->ip();

        // login using email
        if(filter_var(request()->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::whereRaw('LOWER(email) = ?', strtolower(request()->email))->first();

            if (!$user) {
                UserLoginHistory::create(
                    [
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_FAILED
                    ]
                );
                return response()->json([
                    'message' => 'Email / Password doesn\'t match our record'
                ], 400);
            }

            if($user->status != 1) {
                UserLoginHistory::create(
                    [
                        'user_id'   => $user->id,
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_FAILED_INACTIVE
                    ]
                );

                if ($user->status === 0) {
                    return response()->json([
                        'message' => 'This Account is pending email, Please Check your email for instruction'
                    ], 400);
                } elseif ($user->status === 3) {
                    return response()->json([
                        'message' => 'This Account is suspend, Please contact your Administrator for instruction'
                    ], 400);
                } elseif ($user->status === 4) {
                    return response()->json([
                        'message' => 'This Account is blocked by administrator, Please contact your Administrator for instruction'
                    ], 400);
                } else {
                    return response()->json([
                        'message' => 'This Account is not active'
                    ], 400);
                }
            }

            $authenticated = Auth::attempt([
                'email' => $user->email,
                'password' => request()->password
            ]);

            if ($authenticated) {
                $user = Auth::user();

                $user->timestamps = false;
                $user->last_request_time = time();
                $user->api_token = str_random(100);
                $user->save();

                $user->token = $user->api_token;

                // Log to user login history
                UserLoginHistory::create(
                    [
                        'user_id'   => $user->id,
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_SUCCESS
                    ]
                );

                return $user;
            } else {
                // Log to user login history if failed
                UserLoginHistory::create(
                    [
                        'user_id'   => $user->id,
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_FAILED
                    ]
                );

                // except admin
                if ($user->email != 'admin@localhost.com') {
                    if($this->_loginFailOverLimit($user->id)) {
                        // send email & update status suspend
                        $suspend_code = str_random(30);

                        $user->status = 3; //Suspend
                        $user->confirmation_code = $suspend_code;
                        $user->save();

                        // \Mail::send(new \App\Mail\SuspendWrongPassword($user, $suspend_code));
                    }
                }

                return response()->json([
                    'message' => 'Email / Password doesn\'t match our record'
                ], 400);
            }
        } else {//login using username
            $user = User::whereRaw('LOWER(username) = ?', strtolower(request()->email))->first();

            if (!$user) {
                UserLoginHistory::create(
                    [
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_FAILED
                    ]
                );
                return response()->json([
                    'message' => 'Username / Password doesn\'t match our record'
                ], 400);
            }

            if($user->status != 1) {
                UserLoginHistory::create(
                    [
                        'user_id'   => $user->id,
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_FAILED_INACTIVE
                    ]
                );

                if ($user->status === 0) {
                    return response()->json([
                        'message' => 'This Account is pending email, Please Check your email for instruction'
                    ], 400);
                } elseif ($user->status === 3) {
                    return response()->json([
                        'message' => 'This Account is suspend, Please contact your Administrator for instruction'
                    ], 400);
                } elseif ($user->status === 4) {
                    return response()->json([
                        'message' => 'This Account is blocked by administrator, Please contact your Administrator for instruction'
                    ], 400);
                } else {
                    return response()->json([
                        'message' => 'This Account is not active'
                    ], 400);
                }
            }

            if (password_verify(request()->password, $user->password)) {
                Auth::attempt([
                    'email' => $user->email,
                    'password' => request()->password
                ]);

                $user->timestamps = false;
                $user->last_request_time = time();
                $user->api_token = str_random(100);
                $user->save();

                $user->token = $user->api_token;

                // Log to user login history
                UserLoginHistory::create(
                    [
                        'user_id'   => $user->id,
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_SUCCESS
                    ]
                );

                return $user;
            } else {
                UserLoginHistory::create(
                    [
                        'user_id'   => $user->id,
                        'ip_address'=> $ip_address,
                        'device'    => request()->header('User-Agent'),
                        'status'    => LOGIN_FAILED
                    ]
                );

                // except admin
                if ($user->email != 'admin@localhost.com') {
                    if($this->_loginFailOverLimit($user->id)) {
                        // send email & update status suspend
                        $suspend_code = str_random(30);

                        $user->status = 3; //Suspend
                        $user->confirmation_code = $suspend_code;
                        $user->save();

                        // \Mail::send(new \App\Mail\SuspendWrongPassword($user, $suspend_code));
                    }
                }

                return response()->json([
                    'message' => 'Username / Password doesn\'t match our record'
                ], 400);
            }
        }
    }

    public function confirmEmail($id, Request $request)
    {
        $user = User::where('confirmation_code', $id)->first();

        $this->validate(request(),
            [
                'password' => 'required|confirmed|min:'.appsetting('PASS_LENGTH_MIN').'|regex:'.appsetting('PASS_REGEX'),
            ],
            [
                'regex' => 'The :attribute must have :\n'.appsetting('PASS_REGEX_DESCRIPTION'),
            ]
        );

        if (!$user) {
            return response()->json([
                'message' => 'Your confirmation link has expired'
            ], 410);
        }

        if($this->_newPassInPasswordHistory($user->id, $request->password)) {
            return response()->json([
                'message' => 'New password had been used before.'
            ], 422);
        }

        // send mail succes confirm email if account is new
        if ($user->status == 0) {
            \Mail::send(new \App\Mail\SuccessCreateAccount($user));
        } else if ($user->status == 3) {
            \Mail::send(new \App\Mail\SuccessActivateAccount($user));
        }

        $user->update([
            'status' => 1,
            'confirmation_code' => null,
            'password' => Hash::make($request->password, ['rounds' => 10])
        ]);

        // record to password history table
        \App\Models\PasswordHistory::create(['user_id' => $user->id, 'password' => $user->password]);

        return response()->json([
            'message' => 'Success activate Your account'
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $this->validate(request(), [
            'email' => 'required|email',
        ]);

        $user = User::whereRaw('LOWER(email) = ?', strtolower($request->email))->first();

        if (!$user) {
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'email'  => ['email not match our record']
                ]
            ], 422);
        }

        // if status blocked prevent forgot password
        if ($user->status === 4) {
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'email'  => ['Your Account is blocked by administrator, Please contact your Administrator for instruction']
                ]
            ], 422);
        }

        // if status suspend prevent forgot password
        if ($user->status === 3) {
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'email'  => ['Your Account is suspended, Please contact your Administrator for instruction']
                ]
            ], 422);
        }

        $code = str_random(30);

        $user->update([
            'confirmation_code' => $code,
        ]);

        // Move to Queue
        \Mail::send(new \App\Mail\ForgotPassword($user, $code));

        return response()->json([
            'message' => 'Check your email & follow the instruction'
        ], 200);
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

    private function _loginFailOverLimit($user_id) {
        $failed_limit = (int)appsetting('LOGIN_FAILED_LIMIT');

        // Block user if login attempt is overlimit
        $login_log = UserLoginHistory::where('user_id', $user_id)
            ->orderBy('created_at', 'DESC')
            ->take($failed_limit)
            ->get();

        $is_login_all_failed = TRUE;
        foreach($login_log as $d) {
            if($d->status > 0) {
                $is_login_all_failed = FALSE;
                break;
            }
        }

        return $is_login_all_failed && $login_log->count()==$failed_limit;
    }


}
