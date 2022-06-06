Reset Your Password

Inventaris

Hi {{$user->firstname.' '.$user->lastname}}

You recently requested to reset 

your account's password.

Click the link below to reset it.

{{ appsetting('WEB_URL') }}/reset-password/{{$code}}

RESET PASSWORD