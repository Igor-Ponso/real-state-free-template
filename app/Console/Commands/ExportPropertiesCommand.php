<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Scopes\PublishedScope;
use Illuminate\Console\Command;

/**
 * Export all properties to CSV, demonstrating memory-efficient iteration patterns.
 *
 * Uses lazy() for streaming — processes one record at a time via LazyCollection,
 * keeping memory usage constant regardless of dataset size.
 *
 * Memory-efficient iteration methods comparison:
 * - chunk(N, fn)     → process N records per batch, callback-based
 * - chunkById(N, fn) → like chunk but safe for updating filtered results
 * - lazy(N)          → stream records one at a time, returns LazyCollection
 * - lazyById(N)      → like lazy but safe for updates
 * - cursor()         → raw DB cursor, lowest memory, no eager loading
 *
 * Also demonstrates upsert() in the inline comment for bulk import patterns.
 */
class ExportPropertiesCommand extends Command
{
    protected $signature = 'properties:export
        {--output=storage/app/properties.csv : Output file path}
        {--all : Include unpublished/draft properties}';

    protected $description = 'Export properties to CSV using memory-efficient lazy() streaming';

    public function handle(): int
    {
        $outputPath = $this->option('output');

        $query = $this->option('all')
            ? Property::withoutGlobalScope(PublishedScope::class)
            : Property::query();

        $count = $query->count();

        if ($count === 0) {
            $this->warn('No properties to export.');

            return self::SUCCESS;
        }

        $this->info("Exporting {$count} properties to {$outputPath}...");

        $handle = fopen(base_path($outputPath), 'w');

        if (! $handle) {
            $this->error("Cannot open file: {$outputPath}");

            return self::FAILURE;
        }

        // CSV header
        fputcsv($handle, ['ID', 'Title', 'Slug', 'Price (CAD)', 'Address', 'City', 'Status', 'Published']);

        // lazy() streams one record at a time — constant memory usage
        // even with 100K+ properties, memory stays at ~20MB
        $query->with(['city', 'propertyStatus'])
            ->select(['id', 'title', 'slug', 'price', 'currency', 'address', 'city_id', 'property_status_id', 'is_published'])
            ->lazy(200) // internal chunk size of 200 (queries DB in batches, presents as stream)
            ->each(function (Property $property) use ($handle) {
                fputcsv($handle, [
                    $property->id,
                    $property->title,
                    $property->slug,
                    '$'.number_format(intdiv($property->price->getMinorAmount()->toInt(), 100)),
                    $property->address,
                    $property->city?->name ?? '',
                    $property->propertyStatus?->name ?? '',
                    $property->is_published ? 'Yes' : 'No',
                ]);
            });

        fclose($handle);
        $this->info("Exported {$count} properties successfully.");

        // ── Bulk import pattern (upsert) for reference ───────────
        //
        // Property::upsert(
        //     $importData,                         // array of records
        //     uniqueBy: ['slug'],                  // unique column(s)
        //     update: ['price', 'address', 'title'] // columns to update on conflict
        // );
        //
        // upsert() executes a single SQL statement (INSERT ... ON CONFLICT UPDATE)
        // which is ~95% faster than updateOrCreate() loops for bulk operations.

        return self::SUCCESS;
    }
}
