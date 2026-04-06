<?php

namespace App\Http\Controllers;

use App\Actions\Inquiry\StoreInquiryAction;
use App\Http\Requests\StoreInquiryRequest;
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
    public function store(StoreInquiryRequest $request, StoreInquiryAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $request->user()?->id);

        return response()->json(['message' => $result['message']], 201);
    }
}
