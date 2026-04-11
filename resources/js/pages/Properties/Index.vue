<script setup lang="ts">
import { Deferred, Head, InfiniteScroll, Link } from '@inertiajs/vue3';
import { defineAsyncComponent, onMounted, ref } from 'vue';

import CookieConsent from '@/components/CookieConsent.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertyCard from '@/components/landing/PropertyCard.vue';
import PropertyCardSkeleton from '@/components/landing/PropertyCardSkeleton.vue';
import PropertyFilters from '@/components/landing/PropertyFilters.vue';
import PropertyMapSidebar from '@/components/landing/PropertyMapSidebar.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { usePropertyFilters } from '@/composables/usePropertyFilters';
import { usePropertyInteractions } from '@/composables/usePropertyInteractions';
import type {
    AppliedFilters,
    FeaturedProperty,
    PaginatedResponse,
    PropertyFilters as PropertyFiltersType,
} from '@/types/landing';

const PropertyMap = defineAsyncComponent(
    () => import('@/components/PropertyMap.vue'),
);

const props = defineProps<{
    properties: PaginatedResponse<FeaturedProperty>;
    filters?: PropertyFiltersType;
    appliedFilters: AppliedFilters;
    mapCenter?: [number, number] | null;
    canRegister?: boolean;
}>();

const {
    viewMode,
    hoveredPropertyId,
    isLoading,
    selectedTypes,
    selectedCities,
    selectedListings,
    selectedBedrooms,
    bedroomsExact,
    selectedBathrooms,
    bathroomsExact,
    isStudio,
    selectedUnitAmenities,
    selectedBuildingAmenities,
    selectedSort,
    minPrice,
    maxPrice,
    filteredCities,
    isRental,
    hasActiveFilters,
    applyFilters,
    clearFilters,
    loadMore,
    selectedMapProperty,
    onMapSelect,
    clearMapSelection,
} = usePropertyFilters({
    properties: () => props.properties,
    appliedFilters: () => props.appliedFilters,
    filters: () => props.filters,
});

const { isFavorite, isDismissed, toggleFavorite, toggleDismissed } =
    usePropertyInteractions();

// Client-only flag — prevents SSR hydration mismatch for Leaflet map
// @see https://vuejs.org/guide/scaling-up/ssr.html#hydration-mismatch
const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});
</script>

<template>
    <div class="min-h-screen font-body">
        <Head title="Properties" />
        <LandingHeader :can-register="canRegister ?? true" />

        <!-- Hero -->
        <section
            class="bg-linear-to-b from-landing-deep-teal to-landing-charcoal pt-28 pb-8"
        >
            <div class="mx-auto max-w-7xl px-6 text-center">
                <h1
                    class="font-serif text-4xl font-semibold tracking-tight text-white md:text-5xl"
                >
                    Our Properties
                </h1>
                <p class="mx-auto mt-4 max-w-xl font-body text-white/60">
                    Browse our exclusive portfolio of luxury properties across
                    Canada
                </p>
            </div>
        </section>

        <!-- Filters -->
        <section
            class="sticky top-16 z-40 border-b border-white/10 bg-landing-charcoal/95 backdrop-blur-md"
        >
            <div class="mx-auto max-w-7xl px-6 py-4">
                <Deferred data="filters">
                    <template #fallback>
                        <div class="flex gap-3">
                            <Skeleton
                                v-for="i in 7"
                                :key="i"
                                class="h-14 w-32"
                            />
                        </div>
                    </template>

                    <PropertyFilters
                        v-model:selected-types="selectedTypes"
                        v-model:selected-cities="selectedCities"
                        v-model:selected-listings="selectedListings"
                        v-model:selected-bedrooms="selectedBedrooms"
                        v-model:selected-bathrooms="selectedBathrooms"
                        v-model:selected-unit-amenities="selectedUnitAmenities"
                        v-model:selected-building-amenities="
                            selectedBuildingAmenities
                        "
                        v-model:selected-sort="selectedSort"
                        v-model:min-price="minPrice"
                        v-model:max-price="maxPrice"
                        v-model:view-mode="viewMode"
                        v-model:bedrooms-exact="bedroomsExact"
                        v-model:bathrooms-exact="bathroomsExact"
                        :filters="filters"
                        :is-studio="isStudio"
                        :has-active-filters="hasActiveFilters"
                        :is-rental="isRental"
                        :filtered-cities="filteredCities"
                        @apply="applyFilters"
                        @clear="clearFilters"
                    />
                </Deferred>
            </div>
        </section>

        <!-- Grid view with infinite scroll -->
        <section
            v-show="viewMode === 'grid'"
            class="min-h-[70vh] bg-linear-to-b from-landing-charcoal to-landing-deep-teal px-6 py-10"
        >
            <div class="mx-auto max-w-7xl">
                <!-- Loading skeleton (initial load only) -->
                <div
                    v-if="isLoading && !properties.data.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <PropertyCardSkeleton v-for="n in 12" :key="n" />
                </div>

                <!-- Infinite scroll grid -->
                <template v-else-if="properties.data.length">
                    <InfiniteScroll data="properties" only-next preserve-url>
                        <template #next="{ loading }">
                            <div
                                v-if="loading"
                                class="mt-8 flex justify-center"
                            >
                                <div
                                    class="size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent"
                                />
                            </div>
                        </template>

                        <div
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            <Link
                                v-for="property in properties.data"
                                :key="property.id"
                                :href="`/properties/${property.slug}`"
                                prefetch
                                class="group"
                            >
                                <PropertyCard
                                    :property="property"
                                    variant="grid"
                                />
                            </Link>
                        </div>
                    </InfiniteScroll>

                    <!-- Results counter -->
                    <p class="mt-8 text-center font-body text-sm text-white/40">
                        Showing {{ properties.data.length }} of
                        {{ properties.meta.total }} properties
                    </p>
                </template>

                <!-- Empty state -->
                <div v-else class="py-20 text-center">
                    <p class="font-serif text-xl text-white/50">
                        No properties found
                    </p>
                    <Button
                        variant="outline"
                        class="mt-4 border-white/20 text-white hover:bg-white/10"
                        @click="clearFilters"
                    >
                        Clear filters
                    </Button>
                </div>
            </div>
        </section>

        <!-- Map view — v-if mounts fresh, guarded by isMounted for Leaflet SSR safety -->
        <section
            v-if="viewMode === 'map' && isMounted"
            class="relative z-0 min-h-[70vh]"
        >
            <div class="flex h-[calc(100vh-8rem)]">
                <PropertyMapSidebar
                    v-model:hovered-property-id="hoveredPropertyId"
                    :properties="properties"
                    :selected-property="selectedMapProperty"
                    :is-favorite="isFavorite"
                    :is-dismissed="isDismissed"
                    :has-more-pages="
                        properties.meta.current_page < properties.meta.last_page
                    "
                    :is-loading="isLoading"
                    @clear-selection="clearMapSelection"
                    @toggle-favorite="toggleFavorite"
                    @toggle-dismissed="toggleDismissed"
                    @load-more="loadMore"
                />
                <div class="hidden flex-1 sm:block">
                    <Suspense>
                        <PropertyMap
                            :properties="properties.data"
                            :hovered-id="hoveredPropertyId"
                            :center="mapCenter"
                            :is-favorite="isFavorite"
                            :is-dismissed="isDismissed"
                            @select="onMapSelect"
                            @toggle-favorite="toggleFavorite"
                            @toggle-dismissed="toggleDismissed"
                        />
                        <template #fallback>
                            <div
                                class="flex h-full items-center justify-center bg-landing-charcoal"
                            >
                                <div
                                    class="size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent"
                                />
                            </div>
                        </template>
                    </Suspense>
                </div>
            </div>
        </section>

        <LandingFooter v-show="viewMode === 'grid'" />
        <CookieConsent v-if="isMounted" />
    </div>
</template>
