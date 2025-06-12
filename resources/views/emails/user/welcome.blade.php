@component('mail::message')
Hello {{ $user->first_name }},

Welcome to ....

Regards!
<br>
{{ env("APP_NAME") }}
@endcomponent
