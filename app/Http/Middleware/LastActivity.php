<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PasswordHistory;

class LastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check if user authenticate
        if (Auth::user()) {
            // get authenticate user login
            $user = User::find(Auth::user()->id);

            // auto logout after LAST_ACTIVITY in global setting
            // check if last request time exist
            if ($user->last_request_time) {
                // parse last request time
                $last = Carbon::createFromTimestamp($user->last_request_time);
                // add minute from setting
                $minute = appsetting('LAST_ACTIVITY') ? (int)appsetting('LAST_ACTIVITY') : 30;
                $added = Carbon::parse($last)->addMinutes($minute);
                // now
                $now = Carbon::createFromTimestamp(time());

                // compare now & last activity + minute
                if ($now > $added) {

                    // update last_request_time
                    $user->update([
                        'api_token' => str_random(100),
                        'last_request_time' => null
                    ]);
                } else {
                    // update last_request_time
                    $user->update([
                        'last_request_time' => time()
                    ]);    
                }
            } else {
                $user->update([
                    'last_request_time' => time()
                ]);
            }

            // auto send mail if user not change password over PASS_CYCLE_LIMIT in global setting
            // get last date change password
            $last_pass = PasswordHistory::where('user_id', Auth::user()->id)
                ->orderBy('id', 'desc')
                ->first();

            // except admin
            if ($user->email != 'admin@localhost.com') {
                // if user have changed their own password
                if ($last_pass) {
                    // parse last pass changed time
                    $last_pass_changed = Carbon::parse($last_pass->created_at)->format('Y-m-d');

                    // add day from setting
                    $day = appsetting('PASS_CYCLE_LIMIT') ? (int)appsetting('PASS_CYCLE_LIMIT') : 90;
                    $added_day = Carbon::parse($last_pass_changed)->addDays($day)->format('Y-m-d');

                    // now
                    $noww = Carbon::parse(now())->format('Y-m-d');

                    // compare now & last change password + day
                    if ($noww > $added_day) {
                        // send email & update status suspend
                        $code = str_random(30);

                        $user->update([
                            'api_token' => str_random(100),
                            'confirmation_code' => $code,
                            'status' => 3, //Suspend
                        ]);

                        // \Mail::send(new \App\Mail\ForceChangePassword($user, $code));
                    }
                } else {
                    // else if user never change their own password
                    // user created time
                    $created_time = Carbon::parse($user->created_at)->format('Y-m-d');

                    // add day from setting
                    $day = appsetting('PASS_CYCLE_LIMIT') ? (int)appsetting('PASS_CYCLE_LIMIT') : 90;
                    $added_day = Carbon::parse($created_time)->addDays($day)->format('Y-m-d');

                    // now
                    $noww = Carbon::parse(now())->format('Y-m-d');

                    // compare now & last change password + day
                    if ($noww > $added_day) {
                        // send email & update status suspend
                        $code = str_random(30);

                        $user->update([
                            'api_token' => str_random(100),
                            'confirmation_code' => $code,
                            'status' => 3, //Suspend
                        ]);

                        // \Mail::send(new \App\Mail\ForceChangePassword($user, $code));
                    }
                }
            }
        }
        return $next($request);
    }
}
