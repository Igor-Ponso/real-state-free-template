<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Handles media upload and deletion for property listings.
 *
 * Uses Spatie MediaLibrary for file management with collection-based organization.
 */
class MediaController extends Controller
{
    public function store(StoreMediaRequest $request, Property $property): RedirectResponse
    {
        $collection = $request->validated('collection', 'images');

        foreach ($request->file('files') as $file) {
            $property->addMedia($file)->toMediaCollection($collection);
        }

        return back()->with('success', 'Media uploaded successfully.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $property = Property::findOrFail($media->model_id);
        $this->authorize('update', $property);

        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }
}
