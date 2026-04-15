<x-mail::message>
# Your password was changed

Hi {{ $name }},

The password on your **{{ $appName }}** account was just changed.

<x-mail::button :url="$securityUrl">
Review security settings
</x-mail::button>

**Didn't do this?** Contact us immediately at {{ $supportEmail }} — your account may be compromised.

Thanks,<br>
{{ $appName }}
</x-mail::message>
