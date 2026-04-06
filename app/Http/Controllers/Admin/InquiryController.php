<?php

namespace App\Http\Controllers\Admin;

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

    public function show(Inquiry $inquiry): Response
    {
        $this->authorize('view', $inquiry);

        $inquiry->load(['property', 'inquiryStatus', 'user']);

        return Inertia::render('Admin/Inquiries/Show', [
            'inquiry' => (new InquiryResource($inquiry))->resolve(),
            'statuses' => InquiryStatus::active()->ordered()->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(UpdateInquiryStatusRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $statusId = $request->validated('inquiry_status_id');
        $isReplied = InquiryStatus::where('id', $statusId)->where('slug', 'replied')->exists();

        $inquiry->update([
            'inquiry_status_id' => $statusId,
            'replied_at' => $isReplied ? ($inquiry->replied_at ?? now()) : $inquiry->replied_at,
        ]);

        return redirect()->route('admin.inquiries.show', $inquiry)
            ->with('success', 'Inquiry status updated.');
    }
}
