<x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

@component('mail::message')
# Admin Role Transferred

Hello {{ $user->name }},

You have been assigned as an Admin on our platform.

@if(isset($user->email))
**Email:** {{ $user->email }}
@endif

Please log in to your account to access your new privileges.

Thanks,<br>
{{ config('app.name') }}
@endcomponent