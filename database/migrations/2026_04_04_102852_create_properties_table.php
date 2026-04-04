<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_type_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->foreignId('listing_type_id')->constrained('listing_types');
            $table->foreignId('property_status_id')->constrained('property_statuses');

            // Money (elegantly/laravel-money)
            $table->bigInteger('price');
            $table->string('currency', 3)->default('CAD');
            $table->bigInteger('price_min')->nullable();
            $table->bigInteger('price_max')->nullable();

            // Location
            $table->string('address');
            $table->foreignId('city_id')->constrained();
            $table->string('neighborhood')->nullable();
            $table->string('state');
            $table->string('zip_code');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Details
            $table->smallInteger('bedrooms');
            $table->decimal('bathrooms', 3, 1);
            $table->integer('area_sqft');
            $table->integer('lot_size_sqft')->nullable();
            $table->smallInteger('year_built')->nullable();
            $table->smallInteger('parking_spaces')->default(0);
            $table->smallInteger('floor')->nullable();
            $table->smallInteger('total_floors')->nullable();

            // JSONB
            $table->jsonb('amenities')->default('[]');
            $table->jsonb('features')->default('{}');

            // Rental-specific
            $table->bigInteger('deposit')->nullable();
            $table->smallInteger('lease_length_months')->nullable();
            $table->date('available_from')->nullable();
            $table->boolean('pets_allowed')->default(false);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['property_status_id', 'is_published']);
            $table->index('listing_type_id');
            $table->index('city_id');
            $table->index('property_type_id');
            $table->index('price');
            $table->index('is_featured');
            $table->index('is_published');
        });

        // PostgreSQL-specific: GIN index for JSONB amenities containment queries
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX properties_amenities_gin ON properties USING GIN (amenities)');
            DB::statement('CREATE INDEX properties_published_partial ON properties (id) WHERE is_published = true');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
