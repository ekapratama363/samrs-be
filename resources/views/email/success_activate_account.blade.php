@extends('email.master')

@php
    $theme = json_decode(appsetting('THEME'));
    $primary = $theme->primary_color;
    $secondary = $theme->secondary_color;
@endphp

@section('content')

    <div style="margin-left: 20px;margin-right: 20px;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px; text-align: center;">
            <img style="margin-top: 20px;margin-bottom: 0; width:20%; display:block text-align: center;"  alt="Logo" title="Logo" src="{{ url('assets/images/email_key.png') }}"/>
        </div>
    </div>

    <div style="margin-left: 20px;margin-right: 20px;">
        <div class="divider" style="display: block;font-size: 2px;line-height: 2px;margin-left: auto;margin-right: auto;width: 40px;background-color: #b4b4c4;margin-bottom: 20px;">&nbsp;</div>
    </div>

    <div style="margin-left: 20px;margin-right: 20px;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px;">
            <h1 style="margin-top: 5px;margin-bottom: 0;font-style: normal;font-weight: normal;color: {{$primary}};font-size: 16px;line-height: 31px;font-family: Ubuntu,sans-serif;text-align: center;">
            Your Izora account is now active
            </h1>
        </div>
    </div>

    {{--<div style="margin-left: 20px;margin-right: 20px;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px;">
            <h1 style="margin-top: 5px;margin-bottom: 0;font-style: normal;font-weight: normal;color: #3e4751;font-size: 14px;line-height: 24px;font-family: Ubuntu,sans-serif;text-align: center;">
                Izora
            </h1>
        </div>
    </div>--}}
    
    <div style="margin-left: 20px;margin-right: 20px;text-align: center;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px;">
            <h2 style="margin-top: 0;margin-bottom: 0;font-style: normal;font-weight: normal;color: #3e4751;font-size: 14px;line-height: 24px;font-family: Ubuntu,sans-serif;">
                <strong>Hi {{$user->firstname.' '.$user->lastname}}</strong>
            </h2>
            <p style="margin-top: 5px;margin-bottom: 0; font-size: 12px;font-family: Ubuntu,sans-serif;">
                <p style="margin: 0; font-size: 12px; font-family: Ubuntu,sans-serif;">
                    Your account has been successfully activated!

                    <br/>
                    You can now <a href="{{ appsetting('WEB_URL') }}/login" target="_blank">login</a> to view your dashboard
                </p>
            </p>
        </div>
    </div>

@endsection