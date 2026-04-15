<x-mail::message>
# Confirm your subscription

Thanks for signing up to the **{{ $appName }}** newsletter. Click the button below to confirm your email and start receiving our weekly digest of standout luxury listings.

<x-mail::button :url="$confirmUrl">
Confirm subscription
</x-mail::button>

If you didn't sign up, you can safely ignore this email — we won't contact you again.

Thanks,<br>
{{ $appName }}
</x-mail::message>
