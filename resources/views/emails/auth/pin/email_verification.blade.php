@component('mail::message')
<p>Hi,</p>

Help us secure your account as you verify it is authentic.

<b>{{ $pin->code }}</b>

It will expire in <i>(Expires in {{ $expires_at }})<i><br>

Regards!
<br>
{{ env("APP_NAME") }}
@endcomponent

