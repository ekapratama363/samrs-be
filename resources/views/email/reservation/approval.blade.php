@extends('email.master')

@php
    $theme = json_decode(appsetting('THEME'));
    $primary = $theme->primary_color;
    $secondary = $theme->secondary_color;
@endphp

@section('content')

    {{--<div style="margin-left: 20px;margin-right: 20px;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px; ;text-align: center;">
            <img style="margin-top: 12px;margin-bottom: 0; max-width:500px" src="{{ url('assets/images/email_wrold.png') }}"/>
            <p style="margin-top: 20px;margin-bottom: 20px;">
                <span>
                    
                </span>
            </p>
        </div>
    </div>--}}

    <div style="margin-left: 20px;margin-right: 20px;">
        <div class="divider" style="display: block;font-size: 2px;line-height: 2px;margin-left: auto;margin-right: auto;width: 40px;background-color: #b4b4c4;margin-bottom: 20px;">&nbsp;</div>
    </div>

    <div style="margin-left: 20px;margin-right: 20px;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px;">
        <h1 style="margin-top: 5px;margin-bottom: 0;font-style: normal;font-weight: normal;color: {{$primary}};font-size: 16px;line-height: 31px;font-family: Ubuntu,sans-serif;text-align: center;">
                Reservation Approval
            </h1>
        </div>
    </div>

    <div style="margin-left: 20px;margin-right: 20px;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px;">
            <h1 style="margin-top: 5px;margin-bottom: 0;font-style: normal;font-weight: normal;color: #3e4751;font-size: 14px;line-height: 31px;font-family: Ubuntu,sans-serif;text-align: center;">
                Inventaris
            </h1>
        </div>
    </div>
    
    <div style="margin-left: 20px;margin-right: 20px; text-align: center;">
        <div style="mso-line-height-rule: exactly;mso-text-raise: 4px;">
            <h2 style="margin-top: 0;margin-bottom: 0;font-style: normal;font-weight: normal;color: #3e4751;font-size: 14px;line-height: 24px;font-family: Ubuntu,sans-serif;">
                <strong>Hi {{$user->firstname.' '.$user->lastname}}</strong>
            </h2>
            <p style="margin-top: 5px;margin-bottom: 0; font-size: 12px; font-family: Ubuntu,sans-serif;">
                <p style="margin: 0; font-size: 12px; font-family: Ubuntu,sans-serif;">
                    Your reservation {{$reservation->reservation_code}} recently proccessed by {{$approval->user->firstname.' '.$approval->lastname}} at {{$approval->created_at}}. Click the link below to view reservation.
                </p>
                <br><br>
                <a style="font-family: Helvetica,sans-serif;background-color: {{$primary}};
                    margin-top: 10px;
                    border: none;
                    color: white;
                    padding: 5px 12px;
                    text-align: center;
                    text-decoration: none;
                    display: inline-block;
                    font-size: 12px;
                    cursor: pointer;
                    border-radius: 5px"  
                    href="{{ appsetting('WEB_URL') }}/inventory/reservation/{{$reservation->reservation_code}}/view" target="_blank">
                    VIEW RESERVATION
                </a>
            </p>
        </div>
    </div>
    {{-- <br><br><br> --}}

@endsection