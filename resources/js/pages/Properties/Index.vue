<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertyCard from '@/components/landing/PropertyCard.vue';
import PropertyFilters from '@/components/landing/PropertyFilters.vue';
import PropertyPagination from '@/components/landing/PropertyPagination.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { usePropertyFilters } from '@/composables/usePropertyFilters';
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
    perPage,
    search,
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
    goToPage,
    onMapSelect,
} = usePropertyFilters({
    properties: () => props.properties,
    appliedFilters: () => props.appliedFilters,
    filters: () => props.filters,
});
</script>

<template>
    <div class="min-h-screen font-body">
        <Head title="Properties" />
        <LandingHeader :can-register="canRegister ?? true" />

        <!-- Hero -->
        <section class="bg-linear-to-b from-landing-deep-teal to-landing-charcoal pt-28 pb-8">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <h1 class="font-serif text-4xl font-semibold tracking-tight text-white md:text-5xl">
                    Our Properties
                </h1>
                <p class="mx-auto mt-4 max-w-xl font-body text-white/60">
                    Browse our exclusive portfolio of luxury properties across Canada
                </p>
            </div>
        </section>

        <!-- Filters -->
        <section class="sticky top-16 z-40 border-b border-white/10 bg-landing-charcoal/95 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-6 py-4">
                <Deferred data="filters">
                    <template #fallback>
                        <div class="flex gap-3">
                            <Skeleton v-for="i in 7" :key="i" class="h-14 w-32" />
                        </div>
                    </template>

                    <PropertyFilters
                        v-model:search="search"
                        v-model:selected-types="selectedTypes"
                        v-model:selected-cities="selectedCities"
                        v-model:selected-listings="selectedListings"
                        v-model:selected-bedrooms="selectedBedrooms"
                        v-model:selected-bathrooms="selectedBathrooms"
                        v-model:selected-unit-amenities="selectedUnitAmenities"
                        v-model:selected-building-amenities="selectedBuildingAmenities"
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

        <!-- Results -->
        <section
            class="min-h-[70vh]"
            :class="viewMode === 'grid'
                ? 'bg-linear-to-b from-landing-charcoal to-landing-deep-teal px-6 py-10'
                : ''"
        >
            <!-- Grid view -->
            <div v-if="viewMode === 'grid'" class="mx-auto max-w-7xl">
                <!-- Loading overlay -->
                <Transition name="fade">
                    <div
                        v-if="isLoading"
                        class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center bg-landing-charcoal/60 backdrop-blur-sm"
                    >
                        <div class="size-10 animate-spin rounded-full border-2 border-landing-gold border-t-transparent" />
                    </div>
                </Transition>

                <!-- Results header -->
                <div class="mb-6 flex items-center justify-between">
                    <p class="font-body text-sm text-white/40">
                        {{ properties.meta.total }} {{ properties.meta.total === 1 ? 'property' : 'properties' }} found
                    </p>
                    <div class="flex items-center gap-2">
                        <label class="font-body text-xs text-white/30">Show</label>
                        <Select v-model="perPage" @update:model-value="applyFilters">
                            <SelectTrigger class="h-8 w-20 border-white/10 bg-white/5 text-xs text-white">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="12">12</SelectItem>
                                <SelectItem value="24">24</SelectItem>
                                <SelectItem value="48">48</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Grid -->
                <TransitionGroup
                    v-if="properties.data.length"
                    name="card"
                    tag="div"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <Link
                        v-for="property in properties.data"
                        :key="property.id"
                        :href="`/properties/${property.slug}`"
                        prefetch
                        class="group"
                    >
                        <PropertyCard :property="property" variant="grid" />
                    </Link>
                </TransitionGroup>

                <!-- Empty state -->
                <div v-else class="py-20 text-center">
                    <p class="font-serif text-xl text-white/50">No properties found</p>
                    <Button variant="outline" class="mt-4 border-white/20 text-white hover:bg-white/10" @click="clearFilters">
                        Clear filters
                    </Button>
                </div>

                <PropertyPagination :meta="properties.meta" variant="full" @page-change="goToPage" />
            </div>

            <!-- Map view -->
            <div v-else class="flex h-[calc(100vh-8rem)]">
                <div class="w-full overflow-y-auto border-r border-white/10 bg-landing-charcoal p-4 sm:w-80 lg:w-96">
                    <p class="mb-3 font-body text-xs text-white/40">
                        {{ properties.meta.total }} properties
                    </p>
                    <div class="space-y-3">
                        <Link
                            v-for="property in properties.data"
                            :key="property.id"
                            :href="`/properties/${property.slug}`"
                            prefetch
                            class="group block"
                            @mouseenter="hoveredPropertyId = property.id"
                            @mouseleave="hoveredPropertyId = null"
                        >
                            <PropertyCard :property="property" variant="sidebar" />
                        </Link>
                    </div>
                    <PropertyPagination :meta="properties.meta" variant="compact" @page-change="goToPage" />
                </div>
                <div class="hidden flex-1 sm:block">
                    <Suspense>
                        <PropertyMap
                            :properties="properties.data"
                            :hovered-id="hoveredPropertyId"
                            :center="mapCenter"
                            @select="onMapSelect"
                        />
                        <template #fallback>
                            <div class="flex h-full items-center justify-center bg-landing-charcoal">
                                <div class="size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent" />
                            </div>
                        </template>
                    </Suspense>
                </div>
            </div>
        </section>

        <LandingFooter v-if="viewMode === 'grid'" />
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.card-enter-active {
    transition: all 0.4s ease;
}
.card-leave-active {
    transition: all 0.2s ease;
}
.card-enter-from {
    opacity: 0;
    transform: translateY(16px);
}
.card-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
.card-move {
    transition: transform 0.4s ease;
}
</style>
