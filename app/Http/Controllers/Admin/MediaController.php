<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderMediaRequest;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Property;
use App\Scopes\PublishedScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Handles media upload, deletion, reordering, and primary image selection.
 *
 * Uses Spatie MediaLibrary for file management with collection-based organization.
 * Supports `images` and `floor_plans` collections.
 */
class MediaController extends Controller
{
    /**
     * Upload media files to a property's collection.
     */
    public function store(StoreMediaRequest $request, Property $property): RedirectResponse
    {
        $collection = $request->validated('collection', 'images');

        foreach ($request->file('files') as $file) {
            $property->addMedia($file)->toMediaCollection($collection);
        }

        return back()->with('success', 'Media uploaded successfully.');
    }

    /**
     * Reorder media items within a property's collection.
     *
     * Updates the `order_column` on each media record to match the
     * submitted array order. Spatie MediaLibrary uses this column
     * for default ordering.
     */
    public function reorder(ReorderMediaRequest $request, Property $property): JsonResponse
    {
        foreach ($request->validated('ids') as $order => $mediaId) {
            Media::where('id', $mediaId)
                ->where('model_id', $property->id)
                ->update(['order_column' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a media item as the primary/cover image.
     *
     * Uses Spatie's `custom_properties` JSON column to store the
     * `is_primary` flag. Only one image per property can be primary.
     */
    public function setPrimary(Media $media): JsonResponse
    {
        $property = Property::withoutGlobalScope(PublishedScope::class)->findOrFail($media->model_id);
        $this->authorize('update', $property);

        // Clear primary flag from all media in the same collection
        Media::where('model_id', $property->id)
            ->where('collection_name', $media->collection_name)
            ->each(function (Media $m) {
                $props = $m->custom_properties;
                unset($props['is_primary']);
                $m->custom_properties = $props;
                $m->save();
            });

        // Set primary on selected
        $media->setCustomProperty('is_primary', true);
        $media->save();

        return response()->json(['success' => true]);
    }

    /**
     * Delete a media file from a property.
     */
    public function destroy(Media $media): RedirectResponse
    {
        $property = Property::withoutGlobalScope(PublishedScope::class)->findOrFail($media->model_id);
        $this->authorize('update', $property);

        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }
}
