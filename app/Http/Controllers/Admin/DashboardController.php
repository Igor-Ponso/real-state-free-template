<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InquiryResource;
use App\Models\AgentProfile;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use App\Scopes\PublishedScope;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin/agent dashboard with role-aware stats, inquiry list, and trend chart.
 *
 * Admins see aggregate data across the platform. Agents see only their own
 * properties and inquiries — same view, filtered scopes.
 */
class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAgent = $user->hasRole('agent') && ! $user->hasRole('admin');

        return Inertia::render('Admin/Dashboard', [
            'role' => $isAgent ? 'agent' : 'admin',
            'stats' => $this->stats($user, $isAgent),
            'chart' => $this->inquiryTrend($user, $isAgent),
            'recentInquiries' => Inertia::defer(fn () => InquiryResource::collection(
                $this->inquiries($user, $isAgent)
                    ->with(['property', 'inquiryStatus'])
                    ->latest()
                    ->take(5)
                    ->get(),
            )->resolve()),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function stats(Authenticatable $user, bool $isAgent): array
    {
        if ($isAgent) {
            $ownProperties = Property::withoutGlobalScope(PublishedScope::class)
                ->where('user_id', $user->id);

            return [
                'total_properties' => (clone $ownProperties)->count(),
                'active_properties' => (clone $ownProperties)->published()->count(),
                'draft_properties' => (clone $ownProperties)
                    ->whereHas('propertyStatus', fn ($q) => $q->where('slug', 'draft'))->count(),
                'sold_properties' => (clone $ownProperties)
                    ->whereHas('propertyStatus', fn ($q) => $q->where('slug', 'sold'))->count(),
                'total_inquiries' => $this->inquiries($user, true)->count(),
                'unread_inquiries' => $this->inquiries($user, true)->new()->count(),
                // Agent-only signal: response rate = replied / total inquiries.
                'response_rate' => $this->responseRate($user),
                'total_views' => (int) (clone $ownProperties)->withCount('views')->get()->sum('views_count'),
            ];
        }

        return [
            'total_properties' => Property::count(),
            'active_properties' => Property::published()->count(),
            'draft_properties' => Property::whereHas('propertyStatus', fn ($q) => $q->where('slug', 'draft'))->count(),
            'sold_properties' => Property::whereHas('propertyStatus', fn ($q) => $q->where('slug', 'sold'))->count(),
            'total_inquiries' => Inquiry::count(),
            'unread_inquiries' => Inquiry::new()->count(),
            'total_agents' => AgentProfile::count(),
            'total_clients' => User::whereHas('roles', fn ($q) => $q->where('name', 'client'))->count(),
        ];
    }

    /**
     * Inquiry count per day over the last 30 days. Days with zero inquiries
     * are filled so the chart x-axis is continuous.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    private function inquiryTrend(Authenticatable $user, bool $isAgent): array
    {
        $start = CarbonImmutable::now()->subDays(29)->startOfDay();

        $rows = $this->inquiries($user, $isAgent)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $day = $start->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('M j');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return Builder<Inquiry>
     */
    private function inquiries(Authenticatable $user, bool $isAgent): Builder
    {
        /** @var Builder<Inquiry> $query */
        $query = Inquiry::query();

        if ($isAgent) {
            $query->whereHas('property', fn ($q) => $q
                ->withoutGlobalScope(PublishedScope::class)
                ->where('user_id', $user->id));
        }

        return $query;
    }

    private function responseRate(Authenticatable $user): int
    {
        $total = $this->inquiries($user, true)->count();

        if ($total === 0) {
            return 0;
        }

        $replied = $this->inquiries($user, true)->whereNotNull('replied_at')->count();

        return (int) round(($replied / $total) * 100);
    }
}
