<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Property\CreatePropertyAction;
use App\Actions\Property\UpdatePropertyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Http\Resources\Admin\PropertyResource;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin CRUD for property listings.
 *
 * Agents see only their own properties. Admins see all.
 * Uses policies for authorization and actions for business logic.
 */
class PropertyController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $query = Property::with(['city', 'propertyType', 'propertyStatus', 'listingType'])
            ->withCount('inquiries')
            ->when($request->user()->hasRole('agent'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->query('status'), fn ($q, $s) => $q->whereHas('propertyStatus', fn ($q) => $q->where('slug', $s)))
            ->when($request->query('search'), function ($q, $search) {
                $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
                $q->where('title', 'ilike', "%{$escaped}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Properties/Index', [
            'properties' => PropertyResource::collection($query),
            'statuses' => PropertyStatus::active()->ordered()->get(['name', 'slug']),
            'filters' => (object) $request->only(['status', 'search']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Property::class);

        return Inertia::render('Admin/Properties/Create', $this->formOptions());
    }

    public function store(StorePropertyRequest $request, CreatePropertyAction $action): RedirectResponse
    {
        $property = $action->execute($request->user(), $request->validated());

        return redirect()->route('admin.properties.edit', $property)
            ->with('success', 'Property created successfully.');
    }

    public function edit(Property $property): Response
    {
        $this->authorize('update', $property);

        $property->load(['city', 'propertyType', 'propertyStatus', 'listingType', 'media']);

        return Inertia::render('Admin/Properties/Edit', [
            'property' => (new PropertyResource($property))->resolve(),
            'media' => $property->getMedia('images')->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->size,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property, UpdatePropertyAction $action): RedirectResponse
    {
        $action->execute($property, $request->validated());

        return redirect()->route('admin.properties.edit', $property)
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $property->delete();

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property deleted successfully.');
    }

    /**
     * Shared form options for create/edit pages.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'propertyTypes' => PropertyType::active()->ordered()->get(['id', 'name', 'slug']),
            'cities' => City::ordered()->get(['id', 'name', 'slug']),
            'listingTypes' => ListingType::active()->ordered()->get(['id', 'name', 'slug']),
            'propertyStatuses' => PropertyStatus::active()->ordered()->get(['id', 'name', 'slug']),
            'unitAmenities' => Property::UNIT_AMENITIES,
            'buildingAmenities' => Property::BUILDING_AMENITIES,
        ];
    }
}
