@component('mail::message')
Hello {{ $user->first_name }},

We received a code request, kindly use the OTP below.

<b>{{ $pin->code }}</b>

It will expire in <i>(Expires in {{ $expires_at }})<i><br>

If you did not request for a password reset, ignore this message, no further action is required.

Regards!
<br>
{{ env("APP_NAME") }}
@endcomponent
