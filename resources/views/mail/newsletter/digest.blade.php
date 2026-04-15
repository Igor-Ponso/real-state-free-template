<x-mail::message>
# This week at {{ $appName }}

A hand-picked selection of standout listings from our agents.

@foreach ($properties as $property)
## [{{ $property->title }}]({{ url('/properties/'.$property->slug) }})

{{ $property->city?->name }} · {{ $property->propertyType?->name }}

**{{ '$'.number_format(intdiv($property->price->getMinorAmount()->toInt(), 100)) }}**

{{ \Illuminate\Support\Str::limit($property->description, 180) }}

<x-mail::button :url="url('/properties/'.$property->slug)">
View property
</x-mail::button>

---
@endforeach

[Browse all listings]({{ url('/properties') }})

<small>You're receiving this because you subscribed to our newsletter. [Unsubscribe]({{ $unsubscribeUrl }}).</small>
</x-mail::message>
