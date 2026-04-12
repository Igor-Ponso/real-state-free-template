<x-mail::message>
# Reply to Your Inquiry

**{{ $agentName }}** has replied to your inquiry about **{{ $property?->title ?? 'a property' }}**:

<x-mail::panel>
{{ $inquiry->reply }}
</x-mail::panel>

**Your original message:**
> {{ $inquiry->message }}

<x-mail::button :url="$propertyUrl">
View Property
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
