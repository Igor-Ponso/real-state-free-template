<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Bath, BedDouble, EyeOff, Heart, Maximize } from 'lucide-vue-next';
import { defineAsyncComponent, onMounted, ref } from 'vue';

import CookieConsent from '@/components/CookieConsent.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertyCard from '@/components/landing/PropertyCard.vue';
import PropertyCardSkeleton from '@/components/landing/PropertyCardSkeleton.vue';
import PropertyFilters from '@/components/landing/PropertyFilters.vue';
import PropertyPagination from '@/components/landing/PropertyPagination.vue';
import { Badge } from '@/components/ui/badge';
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
    perPage,
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
    selectedMapProperty,
    onMapSelect,
    clearMapSelection,
} = usePropertyFilters({
    properties: () => props.properties,
    appliedFilters: () => props.appliedFilters,
    filters: () => props.filters,
});

const {
    isFavorite,
    isDismissed,
    toggleFavorite,
    toggleDismissed,
} = usePropertyInteractions();

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

        <!-- Grid view — v-show keeps DOM stable, avoids insertBefore crash on toggle -->
        <section
            v-show="viewMode === 'grid'"
            class="min-h-[70vh] bg-linear-to-b from-landing-charcoal to-landing-deep-teal px-6 py-10"
        >
            <div class="mx-auto max-w-7xl">
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

                <!-- Loading skeleton -->
                <div v-if="isLoading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <PropertyCardSkeleton v-for="n in 12" :key="n" />
                </div>

                <!-- Grid -->
                <TransitionGroup
                    v-else-if="properties.data.length"
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
        </section>

        <!-- Map view — v-if mounts fresh, guarded by isMounted for Leaflet SSR safety -->
        <section v-if="viewMode === 'map' && isMounted" class="relative z-0 min-h-[70vh]">
            <div class="flex h-[calc(100vh-8rem)]">
                <div class="w-full overflow-y-auto border-r border-white/10 bg-landing-charcoal p-4 sm:w-80 lg:w-96">
                    <!-- Selected property detail -->
                    <div v-if="selectedMapProperty">
                        <button
                            class="mb-3 flex items-center gap-1 font-body text-xs text-white/40 transition-colors hover:text-white"
                            @click="clearMapSelection"
                        >
                            <ArrowLeft class="size-3.5" />
                            Back to list
                        </button>
                        <div class="overflow-hidden rounded-lg border border-white/10">
                            <img
                                :src="selectedMapProperty.images[0]"
                                :alt="selectedMapProperty.title"
                                class="aspect-video w-full object-cover"
                            />
                        </div>
                        <h3 class="mt-3 font-serif text-lg font-semibold text-white">
                            {{ selectedMapProperty.title }}
                        </h3>
                        <p class="mt-1 font-body text-sm text-white/40">
                            {{ selectedMapProperty.location }}
                        </p>
                        <p class="mt-2 font-serif text-xl font-bold text-landing-gold">
                            {{ selectedMapProperty.price }}
                        </p>
                        <div class="mt-3 flex gap-4 font-body text-sm text-white/50">
                            <span class="flex items-center gap-1">
                                <BedDouble class="size-4 text-landing-gold/60" />
                                {{ selectedMapProperty.bedrooms }} bd
                            </span>
                            <span class="flex items-center gap-1">
                                <Bath class="size-4 text-landing-gold/60" />
                                {{ selectedMapProperty.bathrooms }} ba
                            </span>
                            <span class="flex items-center gap-1">
                                <Maximize class="size-4 text-landing-gold/60" />
                                {{ selectedMapProperty.area_sqft }} sqft
                            </span>
                        </div>
                        <p v-if="selectedMapProperty.description" class="mt-3 line-clamp-3 font-body text-xs leading-relaxed text-white/40">
                            {{ selectedMapProperty.description }}
                        </p>
                        <div v-if="selectedMapProperty.listing_type || selectedMapProperty.property_type" class="mt-3 flex gap-2">
                            <Badge v-if="selectedMapProperty.listing_type" class="border-0 bg-landing-gold px-2 py-0.5 font-body text-2xs font-semibold tracking-wider text-white uppercase">
                                {{ selectedMapProperty.listing_type }}
                            </Badge>
                            <Badge v-if="selectedMapProperty.property_type" class="border-0 bg-white/10 px-2 py-0.5 font-body text-2xs text-white/80">
                                {{ selectedMapProperty.property_type }}
                            </Badge>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Button
                                variant="outline"
                                class="flex-1 border-white/10 text-white hover:bg-white/10"
                                :class="isFavorite(selectedMapProperty.id) ? 'border-red-500/30 text-red-400' : ''"
                                @click="toggleFavorite(selectedMapProperty.id)"
                            >
                                <Heart class="mr-1.5 size-4" :fill="isFavorite(selectedMapProperty.id) ? 'currentColor' : 'none'" />
                                {{ isFavorite(selectedMapProperty.id) ? 'Saved' : 'Save' }}
                            </Button>
                            <Button
                                variant="outline"
                                class="border-white/10 text-white/50 hover:bg-white/10"
                                @click="toggleDismissed(selectedMapProperty.id); clearMapSelection();"
                            >
                                <EyeOff class="mr-1.5 size-4" />
                                Hide
                            </Button>
                        </div>
                        <Button as-child class="mt-2 w-full bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90">
                            <Link :href="`/properties/${selectedMapProperty.slug}`" prefetch>
                                View Property Details
                            </Link>
                        </Button>
                    </div>

                    <!-- Property list -->
                    <template v-else>
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
                    </template>
                </div>
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
                            <div class="flex h-full items-center justify-center bg-landing-charcoal">
                                <div class="size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent" />
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

<style scoped>
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
