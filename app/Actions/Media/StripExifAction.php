<?php

namespace App\Actions\Media;

use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Strip EXIF metadata (including GPS coordinates) from an uploaded original image.
 *
 * Why this exists: Spatie MediaLibrary removes EXIF from generated conversions
 * (thumb, card, gallery) via its default jpegoptim/optipng optimizer chain, but
 * leaves the *original* file untouched. Phone cameras embed GPS coordinates in
 * EXIF by default — for a real estate site this leaks the precise location of
 * the listing and, for agent selfies, the agent's home address.
 *
 * What this does: re-saves the original image via spatie/image, which re-encodes
 * the file and drops EXIF in the process. Quality is preserved at 95 (visually
 * lossless for JPEG). PNGs and WebPs are re-encoded without quality loss.
 *
 * Skipped for non-image mime types (PDFs, etc.) and for SVGs (which should have
 * been rejected at validation).
 *
 * Runs synchronously after upload — add to a queue if upload volume demands it.
 */
class StripExifAction
{
    public function execute(Media $media): void
    {
        $mime = $media->mime_type ?? '';

        if (! str_starts_with($mime, 'image/') || $mime === 'image/svg+xml') {
            return;
        }

        $path = $media->getPath();

        if (! file_exists($path)) {
            return;
        }

        Image::load($path)
            ->quality(95)
            ->save($path);
    }
}
