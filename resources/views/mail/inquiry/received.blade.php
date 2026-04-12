<x-mail::message>
# New Inquiry Received

You have a new inquiry for **{{ $property?->title ?? 'your property' }}**.

<x-mail::panel>
**From:** {{ $inquiry->name }}<br>
**Email:** {{ $inquiry->email }}<br>
@if($inquiry->phone)
**Phone:** {{ $inquiry->phone }}<br>
@endif

{{ $inquiry->message }}
</x-mail::panel>

<x-mail::button :url="$adminUrl">
View Inquiry
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
