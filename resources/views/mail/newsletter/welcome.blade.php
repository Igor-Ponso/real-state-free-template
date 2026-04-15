<x-mail::message>
# You're subscribed

Welcome to the **{{ $appName }}** newsletter. Each week we'll share a curated selection of new listings, market insights, and featured properties.

<x-mail::button :url="$propertiesUrl">
Browse properties
</x-mail::button>

---

You can [unsubscribe]({{ $unsubscribeUrl }}) at any time. We'll never share your email.

Thanks,<br>
{{ $appName }}
</x-mail::message>
