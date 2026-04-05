<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InquiryResource;
use App\Models\AgentProfile;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin dashboard with aggregated statistics and recent activity.
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_properties' => Property::count(),
                'active_properties' => Property::published()->count(),
                'draft_properties' => Property::whereHas('propertyStatus', fn ($q) => $q->where('slug', 'draft'))->count(),
                'sold_properties' => Property::whereHas('propertyStatus', fn ($q) => $q->where('slug', 'sold'))->count(),
                'total_inquiries' => Inquiry::count(),
                'unread_inquiries' => Inquiry::new()->count(),
                'total_agents' => AgentProfile::count(),
                'total_clients' => User::whereHas('roles', fn ($q) => $q->where('name', 'client'))->count(),
            ],
            'recentInquiries' => Inertia::defer(fn () => InquiryResource::collection(
                Inquiry::with(['property', 'inquiryStatus'])
                    ->latest()
                    ->take(5)
                    ->get(),
            )->resolve()),
        ]);
    }
}
