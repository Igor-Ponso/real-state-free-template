<x-mail::message>
# Two-factor authentication is on

Hi {{ $name }},

Two-factor authentication was just enabled on your **{{ $appName }}** account. Going forward, you'll need your authenticator code in addition to your password to sign in.

<x-mail::button :url="$securityUrl">
Review security settings
</x-mail::button>

**Didn't do this?** Someone may have access to your account. Reset your password and revoke 2FA immediately from the security page.

Thanks,<br>
{{ $appName }}
</x-mail::message>
