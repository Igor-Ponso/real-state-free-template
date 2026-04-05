<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Models\InquiryStatus;
use Illuminate\Http\JsonResponse;

/**
 * Handles inquiry submissions from property detail pages.
 *
 * Returns JSON (not Inertia) because the frontend uses Inertia v3's
 * useHttp() hook for standalone POST requests without page navigation.
 */
class InquiryController extends Controller
{
    /**
     * Store a new property inquiry.
     */
    public function store(StoreInquiryRequest $request): JsonResponse
    {
        if ($request->filled('honeypot')) {
            return response()->json(['message' => 'Thank you for your inquiry.'], 201);
        }

        $newStatus = InquiryStatus::where('slug', 'new')->firstOrFail();

        Inquiry::create([
            'property_id' => $request->validated('property_id'),
            'user_id' => $request->user()?->id,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'message' => $request->validated('message'),
            'inquiry_status_id' => $newStatus->id,
        ]);

        return response()->json(['message' => 'Thank you for your inquiry. We will get back to you shortly.'], 201);
    }
}
