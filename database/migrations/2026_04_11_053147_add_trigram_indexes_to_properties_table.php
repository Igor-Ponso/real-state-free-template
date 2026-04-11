<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add GIN trigram indexes on description and address for fast ILIKE queries.
 *
 * The pg_trgm extension and title trigram index already exist from the
 * original properties migration. This migration adds the same indexes
 * for description and address to support multi-column search (scopeSearch).
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

        DB::statement('CREATE INDEX properties_description_trgm ON properties USING gin (description gin_trgm_ops)');
        DB::statement('CREATE INDEX properties_address_trgm ON properties USING gin (address gin_trgm_ops)');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS properties_address_trgm');
        DB::statement('DROP INDEX IF EXISTS properties_description_trgm');
    }
};
