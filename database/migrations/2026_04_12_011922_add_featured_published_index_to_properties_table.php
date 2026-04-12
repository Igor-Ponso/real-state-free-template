<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add a partial index for featured+published properties.
 *
 * The landing page queries featured published properties frequently.
 * This partial index only indexes the small subset that matches,
 * keeping the index compact and fast.
 *
 * Skipped on non-PostgreSQL drivers (e.g., SQLite in tests).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            'CREATE INDEX idx_featured_published ON properties (published_at DESC) WHERE is_featured = true AND is_published = true',
        );
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS idx_featured_published');
    }
};
