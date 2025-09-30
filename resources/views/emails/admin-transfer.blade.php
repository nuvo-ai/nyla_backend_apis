<x-mail::message>
# Admin Role Transferred

Hello {{ $user->name }},

You have been assigned as an Admin on our platform.

@if(isset($user->email))
**Email:** {{ $user->email }}
@endif

Please log in to your account to access your new privileges.

<x-mail::button :url="('https:://www.providers.nyla.africa')">
Log in to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
