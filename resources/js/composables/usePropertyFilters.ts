import { router } from '@inertiajs/vue3';
import {   computed, onBeforeUnmount, ref, watch } from 'vue';
import type {ComputedRef, Ref} from 'vue';

import type {
    AppliedFilters,
    FeaturedProperty,
    FilterOption,
    PaginatedResponse,
    PropertyFilters,
} from '@/types/landing';

interface UsePropertyFiltersOptions {
    properties: () => PaginatedResponse<FeaturedProperty>;
    appliedFilters: () => AppliedFilters;
    filters: () => PropertyFilters | undefined;
}

interface UsePropertyFiltersReturn {
    viewMode: Ref<'grid' | 'map'>;
    hoveredPropertyId: Ref<number | null>;
    isLoading: Ref<boolean>;
    perPage: Ref<string>;
    search: Ref<string>;
    selectedTypes: Ref<string[]>;
    selectedCities: Ref<string[]>;
    selectedListings: Ref<string[]>;
    selectedBedrooms: Ref<string[]>;
    bedroomsExact: Ref<boolean>;
    selectedBathrooms: Ref<string[]>;
    bathroomsExact: Ref<boolean>;
    isStudio: ComputedRef<boolean>;
    selectedUnitAmenities: Ref<string[]>;
    selectedBuildingAmenities: Ref<string[]>;
    selectedSort: Ref<string>;
    minPrice: Ref<string>;
    maxPrice: Ref<string>;
    citySearch: Ref<string>;
    filteredCities: ComputedRef<FilterOption[]>;
    isRental: ComputedRef<boolean>;
    hasActiveFilters: ComputedRef<boolean>;
    toggleMulti: (arr: string[], val: string) => void;
    multiLabel: (selected: string[], options: FilterOption[] | undefined, fallback: string) => string;
    applyFilters: () => void;
    clearFilters: () => void;
    goToPage: (page: number) => void;
    onMapSelect: (property: FeaturedProperty) => void;
}

const toArr = (val: unknown): string[] => {
    if (Array.isArray(val)) {
        return val;
    }

    if (typeof val === 'string' && val) {
        return [val];
    }

    return [];
};

export const usePropertyFilters = (options: UsePropertyFiltersOptions): UsePropertyFiltersReturn => {
    const applied = options.appliedFilters();
    const meta = options.properties().meta;

    // --- State ---
    const viewMode = ref<'grid' | 'map'>('grid');
    const hoveredPropertyId = ref<number | null>(null);
    const isLoading = ref(false);
    const perPage = ref(String(meta.per_page));
    const search = ref(applied.search ?? '');
    const selectedTypes = ref<string[]>(toArr(applied.type));
    const selectedCities = ref<string[]>(toArr(applied.city));
    const selectedListings = ref<string[]>(toArr(applied.listing));
    const selectedBedrooms = ref<string[]>(toArr(applied.bedrooms));
    const bedroomsExact = ref(applied.bedrooms_exact === '1');
    const selectedBathrooms = ref<string[]>(toArr(applied.bathrooms));
    const bathroomsExact = ref(applied.bathrooms_exact === '1');
    const selectedUnitAmenities = ref<string[]>(toArr(applied.unit_amenities));
    const selectedBuildingAmenities = ref<string[]>(toArr(applied.building_amenities));
    const selectedSort = ref(applied.sort ?? 'newest');
    const minPrice = ref(applied.min_price ?? '');
    const maxPrice = ref(applied.max_price ?? '');

    // --- Computed ---
    const citySearch = ref('');

    const filteredCities = computed(() => {
        const cities = options.filters()?.cities;

        if (!cities) {
            return [];
        }

        if (!citySearch.value) {
            return cities;
        }

        const q = citySearch.value.toLowerCase();

        return cities.filter((c) => c.name.toLowerCase().includes(q));
    });

    const isRental = computed(
        () => selectedListings.value.includes('rental') && !selectedListings.value.includes('sale'),
    );

    const isStudio = computed(
        () => selectedTypes.value.includes('studio'),
    );

    // Clear bedrooms when studio is selected (studios have 0 bedrooms)
    watch(isStudio, (studio) => {
        if (studio) {
            selectedBedrooms.value = [];
            bedroomsExact.value = false;
        }
    });

    const hasActiveFilters = computed(
        () =>
            !!(
                search.value ||
                selectedTypes.value.length ||
                selectedCities.value.length ||
                selectedListings.value.length ||
                selectedBedrooms.value.length ||
                selectedBathrooms.value.length ||
                selectedUnitAmenities.value.length ||
                selectedBuildingAmenities.value.length ||
                minPrice.value ||
                maxPrice.value
            ),
    );

    // --- Methods ---
    const toggleMulti = (arr: string[], val: string) => {
        const idx = arr.indexOf(val);

        if (idx >= 0) {
            arr.splice(idx, 1);
        } else {
            arr.push(val);
        }
    };

    const multiLabel = (selected: string[], opts: FilterOption[] | undefined, fallback: string): string => {
        if (!selected.length) {
            return fallback;
        }

        if (selected.length === 1) {
            return opts?.find((o) => o.slug === selected[0])?.name ?? selected[0];
        }

        return `${selected.length} selected`;
    };

    const applyFilters = () => {
        const params: Record<string, string | string[]> = {};

        if (search.value) {params.search = search.value;}

        if (selectedTypes.value.length) {params.type = selectedTypes.value;}

        if (selectedCities.value.length) {params.city = selectedCities.value;}

        if (selectedListings.value.length) {params.listing = selectedListings.value;}

        if (selectedBedrooms.value.length) {
            params.bedrooms = selectedBedrooms.value;

            if (bedroomsExact.value) {params.bedrooms_exact = '1';}
        }

        if (selectedBathrooms.value.length) {
            params.bathrooms = selectedBathrooms.value;

            if (bathroomsExact.value) {params.bathrooms_exact = '1';}
        }

        if (selectedUnitAmenities.value.length) {params.unit_amenities = selectedUnitAmenities.value;}

        if (selectedBuildingAmenities.value.length) {params.building_amenities = selectedBuildingAmenities.value;}

        if (minPrice.value) {params.min_price = minPrice.value;}

        if (maxPrice.value) {params.max_price = maxPrice.value;}

        if (selectedSort.value !== 'newest') {params.sort = selectedSort.value;}

        if (perPage.value !== '12') {params.per_page = perPage.value;}

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
    };

    const clearFilters = () => {
        search.value = '';
        selectedTypes.value = [];
        selectedCities.value = [];
        selectedListings.value = [];
        selectedBedrooms.value = [];
        bedroomsExact.value = false;
        selectedBathrooms.value = [];
        bathroomsExact.value = false;
        selectedUnitAmenities.value = [];
        selectedBuildingAmenities.value = [];
        minPrice.value = '';
        maxPrice.value = '';
        selectedSort.value = 'newest';
        applyFilters();
    };

    const goToPage = (page: number) => {
        const params: Record<string, unknown> = {
            ...options.appliedFilters(),
            page: String(page),
        };

        if (perPage.value !== '12') {
            params.per_page = perPage.value;
        }

        isLoading.value = true;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        router.visit(options.properties().meta.path, {
            data: params as Record<string, string>,
            only: ['properties', 'appliedFilters', 'mapCenter'],
            preserveState: true,
            onFinish: () => {
                isLoading.value = false;
            },
        });
    };

    const onMapSelect = (property: FeaturedProperty) => {
        router.visit(`/properties/${property.slug}`);
    };

    // --- Watchers ---
    let searchTimeout: ReturnType<typeof setTimeout>;

    watch(search, () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 400);
    });

    onBeforeUnmount(() => {
        clearTimeout(searchTimeout);
    });

    return {
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
        citySearch,
        filteredCities,
        isRental,
        hasActiveFilters,
        toggleMulti,
        multiLabel,
        applyFilters,
        clearFilters,
        goToPage,
        onMapSelect,
    };
};
