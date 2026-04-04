<script setup lang="ts">
import { Deferred, Head, Link, router } from '@inertiajs/vue3';
import { Bath, BedDouble, Maximize, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Skeleton } from '@/components/ui/skeleton';
import type {
    AppliedFilters,
    FeaturedProperty,
    PaginatedResponse,
    PropertyFilters,
} from '@/types/landing';

const props = defineProps<{
    properties: PaginatedResponse<FeaturedProperty>;
    filters?: PropertyFilters;
    appliedFilters: AppliedFilters;
    canRegister?: boolean;
}>();

const search = ref(props.appliedFilters.search ?? '');
const selectedType = ref(props.appliedFilters.type ?? 'all');
const selectedCity = ref(props.appliedFilters.city ?? 'all');
const selectedListing = ref(props.appliedFilters.listing ?? 'all');
const selectedBedrooms = ref(props.appliedFilters.bedrooms ?? 'all');
const selectedSort = ref(props.appliedFilters.sort ?? 'newest');
const minPrice = ref(props.appliedFilters.min_price ?? '');
const maxPrice = ref(props.appliedFilters.max_price ?? '');

const isActive = (v: string) => v !== '' && v !== 'all';

const hasActiveFilters = computed(() => {
    return search.value || isActive(selectedType.value) || isActive(selectedCity.value) ||
        isActive(selectedListing.value) || isActive(selectedBedrooms.value) ||
        minPrice.value || maxPrice.value;
});

let searchTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (isActive(selectedType.value)) params.type = selectedType.value;
    if (isActive(selectedCity.value)) params.city = selectedCity.value;
    if (isActive(selectedListing.value)) params.listing = selectedListing.value;
    if (isActive(selectedBedrooms.value)) params.bedrooms = selectedBedrooms.value;
    if (minPrice.value) params.min_price = minPrice.value;
    if (maxPrice.value) params.max_price = maxPrice.value;
    if (selectedSort.value && selectedSort.value !== 'newest') params.sort = selectedSort.value;

    router.reload({
        data: params,
        only: ['properties', 'appliedFilters'],
    });
}

function clearFilters() {
    search.value = '';
    selectedType.value = 'all';
    selectedCity.value = 'all';
    selectedListing.value = 'all';
    selectedBedrooms.value = 'all';
    minPrice.value = '';
    maxPrice.value = '';
    selectedSort.value = 'newest';
    applyFilters();
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([selectedType, selectedCity, selectedListing, selectedBedrooms, selectedSort], applyFilters);

function goToPage(page: number) {
    const params: Record<string, string> = {};
    Object.entries(props.appliedFilters).forEach(([key, value]) => {
        if (value) params[key] = value;
    });
    params.page = String(page);

    router.visit(props.properties.meta.path, {
        data: params,
        only: ['properties', 'appliedFilters'],
        preserveState: true,
    });
}
</script>

<template>
    <div class="min-h-screen font-body">
        <Head title="Properties" />
        <LandingHeader :can-register="canRegister ?? true" />

        <!-- Hero banner -->
        <section class="bg-landing-deep-teal pb-16 pt-28">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <h1 class="font-serif text-4xl font-semibold tracking-tight text-white md:text-5xl">
                    Our Properties
                </h1>
                <p class="mx-auto mt-4 max-w-xl font-body text-white/60">
                    Browse our exclusive portfolio of luxury properties across Canada
                </p>
            </div>
        </section>

        <!-- Filters -->
        <section class="border-b bg-background">
            <div class="mx-auto max-w-6xl px-6 py-6">
                <Deferred data="filters">
                    <template #fallback>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
                            <Skeleton class="h-10" />
                            <Skeleton class="h-10" />
                            <Skeleton class="h-10" />
                            <Skeleton class="h-10" />
                            <Skeleton class="h-10" />
                            <Skeleton class="h-10 col-span-2 md:col-span-1" />
                            <Skeleton class="h-10" />
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
                        <!-- Search -->
                        <div class="relative col-span-2 md:col-span-1">
                            <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search..."
                                class="pl-9"
                            />
                        </div>

                        <!-- Type -->
                        <Select v-model="selectedType">
                            <SelectTrigger>
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem
                                    v-for="t in filters?.propertyTypes"
                                    :key="t.slug"
                                    :value="t.slug"
                                >
                                    {{ t.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- City -->
                        <Select v-model="selectedCity">
                            <SelectTrigger>
                                <SelectValue placeholder="All Cities" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Cities</SelectItem>
                                <SelectItem
                                    v-for="c in filters?.cities"
                                    :key="c.slug"
                                    :value="c.slug"
                                >
                                    {{ c.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Listing Type -->
                        <Select v-model="selectedListing">
                            <SelectTrigger>
                                <SelectValue placeholder="Buy / Rent" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All</SelectItem>
                                <SelectItem
                                    v-for="l in filters?.listingTypes"
                                    :key="l.slug"
                                    :value="l.slug"
                                >
                                    {{ l.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Bedrooms -->
                        <Select v-model="selectedBedrooms">
                            <SelectTrigger>
                                <SelectValue placeholder="Beds" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Any</SelectItem>
                                <SelectItem value="1">1+</SelectItem>
                                <SelectItem value="2">2+</SelectItem>
                                <SelectItem value="3">3+</SelectItem>
                                <SelectItem value="4">4+</SelectItem>
                                <SelectItem value="5">5+</SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Sort -->
                        <Select v-model="selectedSort">
                            <SelectTrigger>
                                <SelectValue placeholder="Sort" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="newest">Newest</SelectItem>
                                <SelectItem value="oldest">Oldest</SelectItem>
                                <SelectItem value="price_asc">Price: Low → High</SelectItem>
                                <SelectItem value="price_desc">Price: High → Low</SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Clear -->
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="sm"
                            class="h-10 text-muted-foreground"
                            @click="clearFilters"
                        >
                            <X class="mr-1 size-4" />
                            Clear
                        </Button>
                    </div>
                </Deferred>
            </div>
        </section>

        <!-- Results -->
        <section class="min-h-[60vh] bg-linear-to-b from-background to-landing-warm-beige/30 px-6 py-12">
            <div class="mx-auto max-w-6xl">
                <!-- Result count -->
                <p class="mb-6 font-body text-sm text-muted-foreground">
                    {{ properties.meta.total }} {{ properties.meta.total === 1 ? 'property' : 'properties' }} found
                </p>

                <!-- Grid -->
                <div
                    v-if="properties.data.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Link
                        v-for="property in properties.data"
                        :key="property.id"
                        :href="`/properties/${property.slug}`"
                        class="group"
                    >
                        <Card class="overflow-hidden border border-border/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-2 hover:ring-landing-gold/30">
                            <div class="relative">
                                <Carousel class="w-full">
                                    <CarouselContent>
                                        <CarouselItem
                                            v-for="(image, imgIndex) in property.images"
                                            :key="imgIndex"
                                        >
                                            <div class="aspect-[16/10] overflow-hidden">
                                                <img
                                                    :src="image"
                                                    :alt="`${property.title} - Photo ${imgIndex + 1}`"
                                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                />
                                            </div>
                                        </CarouselItem>
                                    </CarouselContent>
                                    <CarouselPrevious class="left-2 size-8 opacity-0 transition-opacity group-hover:opacity-100" />
                                    <CarouselNext class="right-2 size-8 opacity-0 transition-opacity group-hover:opacity-100" />
                                </Carousel>

                                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-black/40 to-transparent" />

                                <Badge
                                    v-if="property.listing_type"
                                    class="absolute left-3 top-3 border-0 bg-landing-gold px-2 py-0.5 font-body text-[11px] font-semibold uppercase tracking-wider text-white"
                                >
                                    {{ property.listing_type }}
                                </Badge>

                                <Badge
                                    v-if="property.property_type"
                                    class="absolute right-3 top-3 border-0 bg-black/60 px-2 py-0.5 font-body text-[11px] text-white/90 backdrop-blur-sm"
                                >
                                    {{ property.property_type }}
                                </Badge>

                                <div class="absolute bottom-2 left-3">
                                    <p class="font-serif text-xl font-bold text-white drop-shadow-lg">
                                        {{ property.price }}
                                    </p>
                                </div>
                            </div>

                            <CardContent class="p-4">
                                <h3 class="font-serif text-base font-semibold transition-colors group-hover:text-landing-gold">
                                    {{ property.title }}
                                </h3>
                                <p class="mt-0.5 font-body text-sm text-muted-foreground">
                                    {{ property.location }}
                                </p>

                                <div class="my-3 h-px bg-border" />

                                <div class="flex gap-3 font-body text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <BedDouble class="size-3.5 text-landing-gold/70" />
                                        {{ property.bedrooms }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Bath class="size-3.5 text-landing-gold/70" />
                                        {{ property.bathrooms }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Maximize class="size-3.5 text-landing-gold/70" />
                                        {{ property.area_sqft }} sqft
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-else class="py-20 text-center">
                    <p class="font-serif text-xl text-muted-foreground">No properties found</p>
                    <p class="mt-2 font-body text-sm text-muted-foreground">Try adjusting your filters</p>
                    <Button
                        variant="outline"
                        class="mt-4"
                        @click="clearFilters"
                    >
                        Clear all filters
                    </Button>
                </div>

                <!-- Pagination (shadcn-vue component with Inertia server-side navigation) -->
                <Pagination
                    v-if="properties.meta.last_page > 1"
                    :total="properties.meta.total"
                    :items-per-page="properties.meta.per_page"
                    :default-page="properties.meta.current_page"
                    :sibling-count="1"
                    show-edges
                    class="mt-12"
                    @update:page="goToPage"
                >
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="item.value === properties.meta.current_page"
                            />
                            <PaginationEllipsis v-else :index="index" />
                        </template>

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </section>

        <LandingFooter />
    </div>
</template>
