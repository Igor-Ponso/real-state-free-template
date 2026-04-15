<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\PropertyView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Client-facing dashboard showing personal activity:
 * saved properties (favorites), submitted inquiries with status,
 * and recently viewed listings.
 *
 * Admins and agents hitting this route are redirected to the admin dashboard
 * so the "Dashboard" navigation link routes each role to the right experience.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'agent'])) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'favorites' => Favorite::where('user_id', $user->id)->count(),
                'inquiries' => Inquiry::where('user_id', $user->id)->count(),
                'views' => PropertyView::where('user_id', $user->id)->count(),
            ],
            'favorites' => Inertia::defer(fn () => Favorite::where('user_id', $user->id)
                ->with(['property' => fn ($q) => $q->with(['city', 'propertyType', 'listingType', 'media'])])
                ->latest()
                ->take(6)
                ->get()
                ->map(fn (Favorite $f) => $this->transformPropertyCard($f->property))
                ->filter()
                ->values()
            ),
            'inquiries' => Inertia::defer(fn () => Inquiry::where('user_id', $user->id)
                ->with(['property:id,slug,title', 'inquiryStatus:id,name,slug'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn (Inquiry $i) => [
                    'id' => $i->id,
                    'message' => str($i->message)->limit(120)->toString(),
                    'property_title' => $i->property?->title,
                    'property_slug' => $i->property?->slug,
                    'status' => $i->inquiryStatus?->name,
                    'status_slug' => $i->inquiryStatus?->slug,
                    'replied_at' => $i->replied_at?->toIso8601String(),
                    'created_at' => $i->created_at->toIso8601String(),
                ])
            ),
            'recentlyViewed' => Inertia::defer(fn () => PropertyView::where('user_id', $user->id)
                ->with(['property' => fn ($q) => $q->with(['city', 'propertyType', 'listingType', 'media'])])
                ->latest('viewed_at')
                ->take(6)
                ->get()
                ->map(fn (PropertyView $v) => $this->transformPropertyCard($v->property))
                ->filter()
                ->unique('id')
                ->values()
            ),
        ]);
    }

    /**
     * Minimal property shape for dashboard cards — avoids loading the full
     * FeaturedPropertyResource for a simple list.
     *
     * @return array<string, mixed>|null
     */
    private function transformPropertyCard($property): ?array
    {
        if (! $property) {
            return null;
        }

        $primaryMedia = $property->media->firstWhere('collection_name', 'images');

        return [
            'id' => $property->id,
            'slug' => $property->slug,
            'title' => $property->title,
            'price' => '$'.number_format(intdiv($property->price->getMinorAmount()->toInt(), 100)),
            'address' => $property->address,
            'city' => $property->city?->name,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => (float) $property->bathrooms,
            'area_sqft' => $property->area_sqft,
            'type' => $property->propertyType?->name,
            'listing' => $property->listingType?->name,
            'image' => $primaryMedia?->getUrl('card') ?? $primaryMedia?->getUrl(),
        ];
    }
}
