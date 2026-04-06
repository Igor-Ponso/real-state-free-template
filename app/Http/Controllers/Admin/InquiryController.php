<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inquiry\UpdateInquiryStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInquiryStatusRequest;
use App\Http\Resources\Admin\InquiryResource;
use App\Models\Inquiry;
use App\Models\InquiryStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin inquiry management — view and update inquiry statuses.
 *
 * Agents see only inquiries on their own properties.
 */
class InquiryController extends Controller
{
    /**
     * List inquiries with optional status filtering.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Inquiry::class);

        $query = Inquiry::with(['property', 'inquiryStatus'])
            ->when($request->user()->hasRole('agent'), fn ($q) => $q->whereHas('property', fn ($q) => $q->where('user_id', $request->user()->id)))
            ->when($request->query('status'), fn ($q, $s) => $q->whereHas('inquiryStatus', fn ($q) => $q->where('slug', $s)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => InquiryResource::collection($query),
            'statuses' => InquiryStatus::active()->ordered()->get(['name', 'slug']),
            'filters' => (object) $request->only(['status']),
        ]);
    }

    /**
     * Display a single inquiry with available statuses.
     */
    public function show(Inquiry $inquiry): Response
    {
        $this->authorize('view', $inquiry);

        $inquiry->load(['property', 'inquiryStatus', 'user']);

        return Inertia::render('Admin/Inquiries/Show', [
            'inquiry' => (new InquiryResource($inquiry))->resolve(),
            'statuses' => InquiryStatus::active()->ordered()->get(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Update the inquiry status.
     */
    public function update(UpdateInquiryStatusRequest $request, Inquiry $inquiry, UpdateInquiryStatusAction $action): RedirectResponse
    {
        $action->execute($inquiry, $request->validated('inquiry_status_id'));

        return redirect()->route('admin.inquiries.show', $inquiry)
            ->with('success', 'Inquiry status updated.');
    }
}
