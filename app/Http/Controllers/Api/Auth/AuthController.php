<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function setLoginHistory($uid=null, $ip, $device, $status, $os=null, $imei=null, $imsi=null)
    {
        UserLoginHistory::create([
            'user_id' => $uid,
            'ip_address'=> $ip,
            'device' => $device,
            'os' => $os,
            'imei' => $imei,
            'imsi' => $imsi,
            'status'  => $status,
        ]);
    }

    protected function getMessageStatus($status)
    {
        switch ($status) {
            case 0:
                return 'This Account is pending email, Please Check your email for instruction';
            case 3:
                return 'This Account is suspend, Please contact your Administrator for instruction';
            case 4:
                return 'This Account is blocked by administrator, Please contact your Administrator for instruction';
            default:
                return 'This Account is not active';
        }
    }


    public function login()
    {
        $validator = Validator::make(request(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
        ]);

        if($validator->fails())
        {
            return response()->json([ 'message' => $validator->errors()->toJson()], 400);
        }

        $ip_address = request()->ip();

        //Validasi login dengan email
        if(filter_var(request()->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::whereRaw('LOWER(email) = ?', strtolower(request()->email))->first();

            if (!$user) {
                $this->setLoginHistory(null, $ip_address, request()->header('User-Agent'), LOGIN_FAILED);
                return response()->json([
                    'message' => 'Email / Password doesn\'t match our record'
                ], 401);
            }

            if($user->status != 1) {
                $this->setLoginHistory($user->id, $ip_address, request()->header('User-Agent'), LOGIN_FAILED_INACTIVE);
                return response()->json([
                    'message' => $this->getMessageStatus($user->status)
                ],401);
            }


            //Check Login
            if (!Auth::attempt(['email' => $user->email, 'password' => request()->password])) {
                $user = Auth::user();

                $user->timestamps = false;
                $user->last_request_time = time();
                $user->api_token = str_random(100);
                $user->save();
                $user->token = $user->api_token;

                // Set Login History
                $this->setLoginHistory($user->id, $ip_address,request()->header('User-Agent'), LOGIN_SUCCESS);
                return response()->json([
                    'message' => 'Login Success',
                    'data' => $user
                ]);

            }
            else {

                $this->setLoginHistory($user->id, $ip_address, request()->header('User-Agent'), LOGIN_FAILED);

                // except admin
                if ($user->email != 'admin@localhost.com') {

                    if($this->_loginFailOverLimit($user->id)) {
                        $suspend_code = str_random(30);
                        $user->status = 3; //Suspend
                        $user->confirmation_code = $suspend_code;
                        $user->save();
                    }

                }

                return response()->json([
                    'message' => 'Email / Password doesn\'t match our record'
                ], 401);
            }
        }
        else
        {
            //login using username
            $user = User::whereRaw('LOWER(username) = ?', strtolower(request()->email))->first();

            if (!$user) {
                $this->setLoginHistory(null, $ip_address, request()->header('User-Agent'), LOGIN_FAILED);
                return response()->json([
                    'message' => 'Username / Password doesn\'t match our record'
                ], 401);
            }

            if($user->status != 1) {
                $this->setLoginHistory($user->id, $ip_address, request()->header('User-Agent'), LOGIN_FAILED_INACTIVE);
                return response()->json([
                    'message' => $this->getMessageStatus($user->status)
                ], 401);
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
                $this->setLoginHistory($user->id, $ip_address, request()->header('User-Agent'), LOGIN_SUCCESS);

                return response()->json([
                    'message' => 'Login Success',
                    'data' => $user
                ]);

            }
            else {
                $this->setLoginHistory($user->id, $ip_address, request()->header('User-Agent'), LOGIN_FAILED);

                if ($user->email != 'admin@localhost.com') {
                    if($this->_loginFailOverLimit($user->id)) {
                        $suspend_code = str_random(30);
                        $user->status = 3; //Suspend
                        $user->confirmation_code = $suspend_code;
                        $user->save();
                    }
                }

                return response()->json([
                    'message' => 'Username / Password doesn\'t match our record'
                ], 401);
            }
        }


    }



    private function _loginFailOverLimit($user_id) {
        $failed_limit = (int) appsetting('LOGIN_FAILED_LIMIT');

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
