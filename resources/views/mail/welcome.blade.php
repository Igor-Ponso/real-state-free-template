<x-mail::message>
# Welcome, {{ $name }}!

Thank you for joining **{{ config('app.name') }}**. We're excited to help you find your perfect property.

<x-mail::button :url="$propertiesUrl">
Browse Properties
</x-mail::button>

You can also access your dashboard to manage your profile and saved properties:

<x-mail::button :url="$dashboardUrl" color="success">
Go to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
