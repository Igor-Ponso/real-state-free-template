<script setup lang="ts">
import { Deferred, Head, Link, router } from '@inertiajs/vue3';
import {
    Bath,
    BedDouble,
    ChevronDown,
    Grid3x3,
    Map,
    Maximize,
    Search,
    X,
} from 'lucide-vue-next';
import {
    computed,
    defineAsyncComponent,
    onBeforeUnmount,
    ref,
    watch,
} from 'vue';

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
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { useAmenityIcons } from '@/composables/useAmenityIcons';
import type {
    AppliedFilters,
    FeaturedProperty,
    FilterOption,
    PaginatedResponse,
    PropertyFilters,
} from '@/types/landing';

const { getIcon } = useAmenityIcons();

const PropertyMap = defineAsyncComponent(
    () => import('@/components/PropertyMap.vue'),
);

const props = defineProps<{
    properties: PaginatedResponse<FeaturedProperty>;
    filters?: PropertyFilters;
    appliedFilters: AppliedFilters;
    mapCenter?: [number, number] | null;
    canRegister?: boolean;
}>();

// --- State ---
const viewMode = ref<'grid' | 'map'>('grid');
const hoveredPropertyId = ref<number | null>(null);
const isLoading = ref(false);
const perPage = ref(String(props.properties.meta.per_page));
const search = ref(props.appliedFilters.search ?? '');
const selectedTypes = ref<string[]>(toArr(props.appliedFilters.type));
const selectedCities = ref<string[]>(toArr(props.appliedFilters.city));
const selectedListings = ref<string[]>(toArr(props.appliedFilters.listing));
const selectedBedrooms = ref<string[]>(toArr(props.appliedFilters.bedrooms));
const selectedUnitAmenities = ref<string[]>(
    toArr(props.appliedFilters.unit_amenities),
);
const selectedBuildingAmenities = ref<string[]>(
    toArr(props.appliedFilters.building_amenities),
);
const selectedSort = ref(props.appliedFilters.sort ?? 'newest');
const minPrice = ref(props.appliedFilters.min_price ?? '');
const maxPrice = ref(props.appliedFilters.max_price ?? '');

const citySearch = ref('');
const filteredCities = computed(() => {
    if (!props.filters?.cities) {
        return [];
    }

    if (!citySearch.value) {
        return props.filters.cities;
    }

    const q = citySearch.value.toLowerCase();

    return props.filters.cities.filter((c) => c.name.toLowerCase().includes(q));
});

const isRental = computed(
    () =>
        selectedListings.value.includes('rental') &&
        !selectedListings.value.includes('sale'),
);

const hasActiveFilters = computed(
    () =>
        search.value ||
        selectedTypes.value.length ||
        selectedCities.value.length ||
        selectedListings.value.length ||
        selectedBedrooms.value.length ||
        selectedUnitAmenities.value.length ||
        selectedBuildingAmenities.value.length ||
        minPrice.value ||
        maxPrice.value,
);

function toArr(val: unknown): string[] {
    if (Array.isArray(val)) {
        return val;
    }

    if (typeof val === 'string' && val) {
        return [val];
    }

    return [];
}

function toggleMulti(arr: string[], val: string) {
    const idx = arr.indexOf(val);

    if (idx >= 0) {
        arr.splice(idx, 1);
    } else {
        arr.push(val);
    }
}

function multiLabel(
    selected: string[],
    options: FilterOption[] | undefined,
    fallback: string,
): string {
    if (!selected.length) {
        return fallback;
    }

    if (selected.length === 1) {
        return (
            options?.find((o) => o.slug === selected[0])?.name ?? selected[0]
        );
    }

    return `${selected.length} selected`;
}

// --- Actions ---
let searchTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    const params: Record<string, string | string[]> = {};

    if (search.value) {
        params.search = search.value;
    }

    if (selectedTypes.value.length) {
        params.type = selectedTypes.value;
    }

    if (selectedCities.value.length) {
        params.city = selectedCities.value;
    }

    if (selectedListings.value.length) {
        params.listing = selectedListings.value;
    }

    if (selectedBedrooms.value.length) {
        params.bedrooms = selectedBedrooms.value;
    }

    if (selectedUnitAmenities.value.length) {
        params.unit_amenities = selectedUnitAmenities.value;
    }

    if (selectedBuildingAmenities.value.length) {
        params.building_amenities = selectedBuildingAmenities.value;
    }

    if (minPrice.value) {
        params.min_price = minPrice.value;
    }

    if (maxPrice.value) {
        params.max_price = maxPrice.value;
    }

    if (selectedSort.value !== 'newest') {
        params.sort = selectedSort.value;
    }

    if (perPage.value !== '12') {
        params.per_page = perPage.value;
    }

    isLoading.value = true;
    router.visit('/properties', {
        data: params,
        only: ['properties', 'appliedFilters', 'mapCenter'],
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function clearFilters() {
    search.value = '';
    selectedTypes.value = [];
    selectedCities.value = [];
    selectedListings.value = [];
    selectedBedrooms.value = [];
    selectedUnitAmenities.value = [];
    selectedBuildingAmenities.value = [];
    minPrice.value = '';
    maxPrice.value = '';
    selectedSort.value = 'newest';
    applyFilters();
}

function goToPage(page: number) {
    const params: Record<string, unknown> = {
        ...props.appliedFilters,
        page: String(page),
    };

    if (perPage.value !== '12') {
        params.per_page = perPage.value;
    }

    isLoading.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    router.visit(props.properties.meta.path, {
        data: params as Record<string, string>,
        only: ['properties', 'appliedFilters', 'mapCenter'],
        preserveState: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function onMapSelect(property: FeaturedProperty) {
    router.visit(`/properties/${property.slug}`);
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);
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

                    <div class="flex flex-wrap items-end gap-3">
                        <!-- Search -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Search</label
                            >
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-white/40"
                                />
                                <Input
                                    v-model="search"
                                    placeholder="Address, name..."
                                    class="h-9 w-40 border-white/10 bg-white/5 pl-9 text-sm text-white placeholder:text-white/30"
                                />
                            </div>
                        </div>

                        <!-- Property Type (multi) -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Property Type</label
                            >
                            <Popover>
                                <PopoverTrigger as-child>
                                    <button
                                        class="flex h-9 w-36 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <span class="truncate">{{
                                            multiLabel(
                                                selectedTypes,
                                                filters?.propertyTypes,
                                                'All Types',
                                            )
                                        }}</span>
                                        <ChevronDown
                                            class="ml-1 size-3.5 shrink-0 text-white/40"
                                        />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-48 p-2" align="start">
                                    <div
                                        class="max-h-56 space-y-1 overflow-y-auto"
                                    >
                                        <label
                                            v-for="t in filters?.propertyTypes"
                                            :key="t.slug"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                                        >
                                            <Checkbox
                                                :checked="
                                                    selectedTypes.includes(
                                                        t.slug,
                                                    )
                                                "
                                                @update:checked="
                                                    toggleMulti(
                                                        selectedTypes,
                                                        t.slug,
                                                    )
                                                "
                                            />
                                            {{ t.name }}
                                        </label>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <!-- City (multi) -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >City</label
                            >
                            <Popover>
                                <PopoverTrigger as-child>
                                    <button
                                        class="flex h-9 w-36 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <span class="truncate">{{
                                            multiLabel(
                                                selectedCities,
                                                filters?.cities,
                                                'All Cities',
                                            )
                                        }}</span>
                                        <ChevronDown
                                            class="ml-1 size-3.5 shrink-0 text-white/40"
                                        />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-48 p-2" align="start">
                                    <Input
                                        v-model="citySearch"
                                        placeholder="Search city..."
                                        class="mb-2 h-8 text-xs"
                                    />
                                    <div
                                        class="max-h-56 space-y-1 overflow-y-auto"
                                    >
                                        <label
                                            v-for="c in filteredCities"
                                            :key="c.slug"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                                        >
                                            <Checkbox
                                                :checked="
                                                    selectedCities.includes(
                                                        c.slug,
                                                    )
                                                "
                                                @update:checked="
                                                    toggleMulti(
                                                        selectedCities,
                                                        c.slug,
                                                    )
                                                "
                                            />
                                            {{ c.name }}
                                        </label>
                                        <p
                                            v-if="!filteredCities.length"
                                            class="px-2 py-1.5 text-xs text-muted-foreground"
                                        >
                                            No cities found
                                        </p>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <!-- Buy / Rent -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Buy / Rent</label
                            >
                            <Popover>
                                <PopoverTrigger as-child>
                                    <button
                                        class="flex h-9 w-28 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <span class="truncate">{{
                                            multiLabel(
                                                selectedListings,
                                                filters?.listingTypes,
                                                'All',
                                            )
                                        }}</span>
                                        <ChevronDown
                                            class="ml-1 size-3.5 shrink-0 text-white/40"
                                        />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-40 p-2" align="start">
                                    <div class="space-y-1">
                                        <label
                                            v-for="l in filters?.listingTypes"
                                            :key="l.slug"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                                        >
                                            <Checkbox
                                                :checked="
                                                    selectedListings.includes(
                                                        l.slug,
                                                    )
                                                "
                                                @update:checked="
                                                    toggleMulti(
                                                        selectedListings,
                                                        l.slug,
                                                    )
                                                "
                                            />
                                            {{ l.name }}
                                        </label>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <!-- Bedrooms (multi) -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Bedrooms</label
                            >
                            <Popover>
                                <PopoverTrigger as-child>
                                    <button
                                        class="flex h-9 w-28 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <span>{{
                                            selectedBedrooms.length
                                                ? selectedBedrooms.join(', ') +
                                                  '+'
                                                : 'Any'
                                        }}</span>
                                        <ChevronDown
                                            class="ml-1 size-3.5 shrink-0 text-white/40"
                                        />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-32 p-2" align="start">
                                    <div class="space-y-1">
                                        <label
                                            v-for="n in 6"
                                            :key="n"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                                        >
                                            <Checkbox
                                                :checked="
                                                    selectedBedrooms.includes(
                                                        String(n),
                                                    )
                                                "
                                                @update:checked="
                                                    toggleMulti(
                                                        selectedBedrooms,
                                                        String(n),
                                                    )
                                                "
                                            />
                                            {{ n }}+
                                        </label>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <!-- Unit Amenities (multi) -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Unit Features</label
                            >
                            <Popover>
                                <PopoverTrigger as-child>
                                    <button
                                        class="flex h-9 w-36 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <span class="truncate">{{
                                            selectedUnitAmenities.length
                                                ? selectedUnitAmenities.length +
                                                  ' selected'
                                                : 'Any'
                                        }}</span>
                                        <ChevronDown
                                            class="ml-1 size-3.5 shrink-0 text-white/40"
                                        />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-52 p-2" align="start">
                                    <div
                                        class="max-h-56 space-y-1 overflow-y-auto"
                                    >
                                        <label
                                            v-for="a in filters?.unitAmenities"
                                            :key="a.slug"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                                        >
                                            <Checkbox
                                                :checked="
                                                    selectedUnitAmenities.includes(
                                                        a.slug,
                                                    )
                                                "
                                                @update:checked="
                                                    toggleMulti(
                                                        selectedUnitAmenities,
                                                        a.slug,
                                                    )
                                                "
                                            />
                                            <component
                                                :is="getIcon(a.slug)"
                                                v-if="getIcon(a.slug)"
                                                class="size-3.5 text-landing-gold/60"
                                            />
                                            {{ a.name }}
                                        </label>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <!-- Building Amenities (multi) -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Building</label
                            >
                            <Popover>
                                <PopoverTrigger as-child>
                                    <button
                                        class="flex h-9 w-36 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <span class="truncate">{{
                                            selectedBuildingAmenities.length
                                                ? selectedBuildingAmenities.length +
                                                  ' selected'
                                                : 'Any'
                                        }}</span>
                                        <ChevronDown
                                            class="ml-1 size-3.5 shrink-0 text-white/40"
                                        />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-52 p-2" align="start">
                                    <div
                                        class="max-h-56 space-y-1 overflow-y-auto"
                                    >
                                        <label
                                            v-for="a in filters?.buildingAmenities"
                                            :key="a.slug"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                                        >
                                            <Checkbox
                                                :checked="
                                                    selectedBuildingAmenities.includes(
                                                        a.slug,
                                                    )
                                                "
                                                @update:checked="
                                                    toggleMulti(
                                                        selectedBuildingAmenities,
                                                        a.slug,
                                                    )
                                                "
                                            />
                                            <component
                                                :is="getIcon(a.slug)"
                                                v-if="getIcon(a.slug)"
                                                class="size-3.5 text-landing-gold/60"
                                            />
                                            {{ a.name }}
                                        </label>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <!-- Price -->
                        <div v-if="isRental">
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Min Rent</label
                            >
                            <Input
                                v-model="minPrice"
                                type="number"
                                placeholder="$0"
                                class="h-9 w-28 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >{{
                                    isRental ? 'Max Rent' : 'Max Price'
                                }}</label
                            >
                            <Input
                                v-model="maxPrice"
                                type="number"
                                :placeholder="
                                    isRental ? '$10,000' : '$10,000,000'
                                "
                                class="h-9 w-28 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30"
                            />
                        </div>

                        <!-- Sort -->
                        <div>
                            <label
                                class="mb-1 block font-body text-[10px] font-medium tracking-wider text-white/40 uppercase"
                                >Sort By</label
                            >
                            <Select v-model="selectedSort">
                                <SelectTrigger
                                    class="h-9 w-36 border-white/10 bg-white/5 text-sm text-white"
                                >
                                    <SelectValue placeholder="Newest" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="newest"
                                        >Newest</SelectItem
                                    >
                                    <SelectItem value="oldest"
                                        >Oldest</SelectItem
                                    >
                                    <SelectItem value="price_asc"
                                        >Price: Low → High</SelectItem
                                    >
                                    <SelectItem value="price_desc"
                                        >Price: High → Low</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end gap-2">
                            <Button
                                size="sm"
                                class="h-9 bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90"
                                @click="applyFilters"
                            >
                                <Search class="mr-1.5 size-3.5" /> Search
                            </Button>
                            <Button
                                v-if="hasActiveFilters"
                                variant="ghost"
                                size="sm"
                                class="h-9 text-white/50 hover:text-white"
                                @click="clearFilters"
                            >
                                <X class="mr-1 size-3.5" /> Clear
                            </Button>
                        </div>

                        <div class="flex-1" />

                        <!-- View toggle -->
                        <div class="flex rounded-md border border-white/10">
                            <button
                                aria-label="Grid view"
                                class="flex size-9 items-center justify-center rounded-l-md transition-colors"
                                :class="
                                    viewMode === 'grid'
                                        ? 'bg-landing-gold text-white'
                                        : 'text-white/40 hover:text-white'
                                "
                                @click="viewMode = 'grid'"
                            >
                                <Grid3x3 class="size-4" />
                            </button>
                            <button
                                aria-label="Map view"
                                class="flex size-9 items-center justify-center rounded-r-md transition-colors"
                                :class="
                                    viewMode === 'map'
                                        ? 'bg-landing-gold text-white'
                                        : 'text-white/40 hover:text-white'
                                "
                                @click="viewMode = 'map'"
                            >
                                <Map class="size-4" />
                            </button>
                        </div>
                    </div>
                </Deferred>
            </div>
        </section>

        <!-- Results -->
        <section
            class="min-h-[70vh]"
            :class="
                viewMode === 'grid'
                    ? 'bg-linear-to-b from-landing-charcoal to-landing-deep-teal px-6 py-10'
                    : ''
            "
        >
            <!-- Grid view -->
            <div v-if="viewMode === 'grid'" class="relative mx-auto max-w-7xl">
                <!-- Loading overlay -->
                <Transition name="fade">
                    <div
                        v-if="isLoading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-landing-charcoal/60 backdrop-blur-sm"
                    >
                        <div
                            role="status"
                            aria-label="Loading properties"
                            class="size-10 animate-spin rounded-full border-2 border-landing-gold border-t-transparent"
                        />
                    </div>
                </Transition>

                <!-- Results header -->
                <div class="mb-6 flex items-center justify-between">
                    <p class="font-body text-sm text-white/40">
                        {{ properties.meta.total }}
                        {{
                            properties.meta.total === 1
                                ? 'property'
                                : 'properties'
                        }}
                        found
                    </p>
                    <div class="flex items-center gap-2">
                        <label class="font-body text-xs text-white/30"
                            >Show</label
                        >
                        <Select
                            v-model="perPage"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger
                                class="h-8 w-20 border-white/10 bg-white/5 text-xs text-white"
                            >
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
                        class="group"
                    >
                        <Card
                            class="overflow-hidden border border-white/10 bg-white/[0.04] backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:bg-landing-deep-teal/80 hover:shadow-xl hover:ring-2 hover:ring-landing-gold/30"
                        >
                            <div class="relative">
                                <Carousel class="w-full">
                                    <CarouselContent>
                                        <CarouselItem
                                            v-for="(
                                                image, i
                                            ) in property.images"
                                            :key="i"
                                        >
                                            <div
                                                class="aspect-[16/10] overflow-hidden"
                                            >
                                                <img
                                                    :src="image"
                                                    :alt="property.title"
                                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                />
                                            </div>
                                        </CarouselItem>
                                    </CarouselContent>
                                    <CarouselPrevious
                                        class="left-2 size-7 opacity-0 transition-opacity group-hover:opacity-100"
                                    />
                                    <CarouselNext
                                        class="right-2 size-7 opacity-0 transition-opacity group-hover:opacity-100"
                                    />
                                </Carousel>
                                <div
                                    class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-black/50 to-transparent"
                                />
                                <Badge
                                    v-if="property.listing_type"
                                    class="absolute top-2.5 left-2.5 border-0 bg-landing-gold px-2 py-0.5 font-body text-[10px] font-semibold tracking-wider text-white uppercase"
                                    >{{ property.listing_type }}</Badge
                                >
                                <Badge
                                    v-if="property.property_type"
                                    class="absolute top-2.5 right-2.5 border-0 bg-black/60 px-2 py-0.5 font-body text-[10px] text-white/90 backdrop-blur-sm"
                                    >{{ property.property_type }}</Badge
                                >
                                <div class="absolute bottom-2 left-2.5">
                                    <p
                                        class="font-serif text-lg font-bold text-white drop-shadow-lg"
                                    >
                                        {{ property.price }}
                                    </p>
                                </div>
                            </div>
                            <CardContent class="p-3.5">
                                <h3
                                    class="truncate font-serif text-sm font-semibold text-white transition-colors group-hover:text-landing-gold"
                                >
                                    {{ property.title }}
                                </h3>
                                <p
                                    class="mt-0.5 font-body text-xs text-white/40"
                                >
                                    {{ property.location }}
                                </p>
                                <div class="my-2.5 h-px bg-white/10" />
                                <div
                                    class="flex gap-3 font-body text-xs text-white/40"
                                >
                                    <span class="flex items-center gap-1"
                                        ><BedDouble
                                            class="size-3 text-landing-gold/60"
                                        />{{ property.bedrooms }}</span
                                    >
                                    <span class="flex items-center gap-1"
                                        ><Bath
                                            class="size-3 text-landing-gold/60"
                                        />{{ property.bathrooms }}</span
                                    >
                                    <span class="flex items-center gap-1"
                                        ><Maximize
                                            class="size-3 text-landing-gold/60"
                                        />{{ property.area_sqft }}</span
                                    >
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                </TransitionGroup>

                <div v-else class="py-20 text-center">
                    <p class="font-serif text-xl text-white/50">
                        No properties found
                    </p>
                    <Button
                        variant="outline"
                        class="mt-4 border-white/20 text-white hover:bg-white/10"
                        @click="clearFilters"
                        >Clear filters</Button
                    >
                </div>

                <!-- Pagination -->
                <div
                    v-if="properties.meta.last_page > 1"
                    class="mt-10 flex items-center justify-between"
                >
                    <p class="font-body text-xs text-white/30">
                        Page {{ properties.meta.current_page }} of
                        {{ properties.meta.last_page }}
                    </p>
                    <Pagination
                        :total="properties.meta.total"
                        :items-per-page="properties.meta.per_page"
                        :default-page="properties.meta.current_page"
                        :sibling-count="1"
                        show-edges
                        @update:page="goToPage"
                    >
                        <PaginationContent v-slot="{ items }" class="gap-1">
                            <PaginationPrevious
                                class="text-white/60 hover:text-white"
                            />
                            <template
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <button
                                    v-if="item.type === 'page'"
                                    class="inline-flex size-9 items-center justify-center rounded-md font-body text-sm transition-all"
                                    :class="
                                        item.value ===
                                        properties.meta.current_page
                                            ? 'bg-landing-gold font-semibold text-white shadow-md'
                                            : 'text-white/50 hover:bg-white/10 hover:text-white'
                                    "
                                    @click="goToPage(item.value)"
                                >
                                    {{ item.value }}
                                </button>
                                <PaginationEllipsis
                                    v-else
                                    :index="index"
                                    class="text-white/30"
                                />
                            </template>
                            <PaginationNext
                                class="text-white/60 hover:text-white"
                            />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>

            <!-- Map view -->
            <div v-else class="flex h-[calc(100vh-8rem)]">
                <div
                    class="w-full overflow-y-auto border-r border-white/10 bg-landing-charcoal p-4 sm:w-80 lg:w-96"
                >
                    <p class="mb-3 font-body text-xs text-white/40">
                        {{ properties.meta.total }} properties
                    </p>
                    <div class="space-y-3">
                        <Link
                            v-for="property in properties.data"
                            :key="property.id"
                            :href="`/properties/${property.slug}`"
                            class="group block"
                            @mouseenter="hoveredPropertyId = property.id"
                            @mouseleave="hoveredPropertyId = null"
                        >
                            <div
                                class="flex gap-3 rounded-lg border border-white/5 bg-white/[0.03] p-2 transition-all hover:border-landing-gold/30 hover:bg-landing-deep-teal/50"
                            >
                                <img
                                    :src="property.images[0]"
                                    :alt="property.title"
                                    class="size-20 shrink-0 rounded object-cover"
                                    loading="lazy"
                                />
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="font-serif text-sm font-bold text-landing-gold"
                                    >
                                        {{ property.price }}
                                    </p>
                                    <p
                                        class="truncate font-serif text-sm font-semibold text-white"
                                    >
                                        {{ property.title }}
                                    </p>
                                    <p
                                        class="mt-0.5 font-body text-xs text-white/40"
                                    >
                                        {{ property.location }}
                                    </p>
                                    <div
                                        class="mt-1 flex gap-2 font-body text-[10px] text-white/30"
                                    >
                                        <span>{{ property.bedrooms }} bd</span>
                                        <span>{{ property.bathrooms }} ba</span>
                                        <span
                                            >{{ property.area_sqft }} sqft</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <Pagination
                        v-if="properties.meta.last_page > 1"
                        :total="properties.meta.total"
                        :items-per-page="properties.meta.per_page"
                        :default-page="properties.meta.current_page"
                        :sibling-count="0"
                        class="mt-4"
                        @update:page="goToPage"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />
                            <template
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="
                                        item.value ===
                                        properties.meta.current_page
                                    "
                                />
                                <PaginationEllipsis v-else :index="index" />
                            </template>
                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>
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
                            <div
                                class="flex h-full items-center justify-center bg-landing-charcoal"
                            >
                                <div
                                    class="mx-auto mb-3 size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent"
                                />
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
