import { router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { computed, ref } from 'vue';
import type { ComputedRef, Ref } from 'vue';

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
    hasMorePages: ComputedRef<boolean>;
    toggleMulti: (arr: string[], val: string) => void;
    multiLabel: (
        selected: string[],
        options: FilterOption[] | undefined,
        fallback: string,
    ) => string;
    applyFilters: () => void;
    clearFilters: () => void;
    loadMore: () => void;
    selectedMapProperty: Ref<FeaturedProperty | null>;
    onMapSelect: (property: FeaturedProperty) => void;
    clearMapSelection: () => void;
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

/**
 * Convert cents (minor units) from the backend to formatted dollars for display.
 * The DB stores prices in cents via MoneyCast, and the API accepts/returns cents.
 * The user interacts with dollar amounts in the UI, formatted with maska
 * (e.g., "1,234,567").
 */
const centsToDisplay = (cents: string | undefined): string => {
    if (!cents) {
        return '';
    }

    const dollars = Math.floor(parseInt(cents, 10) / 100);

    return dollars > 0
        ? new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(
              dollars,
          )
        : '';
};

/**
 * Convert formatted dollar input (e.g., "1,234,567") to cents for the API.
 * Strips any non-digit characters (commas, spaces) before converting.
 */
const displayToCents = (dollars: string): string => {
    const cleaned = dollars.replace(/[^0-9]/g, '');
    const parsed = parseInt(cleaned, 10);

    return isNaN(parsed) || parsed === 0 ? '' : String(parsed * 100);
};

export const usePropertyFilters = (
    options: UsePropertyFiltersOptions,
): UsePropertyFiltersReturn => {
    const applied = options.appliedFilters();

    // --- State ---
    const viewMode = ref<'grid' | 'map'>('grid');
    const hoveredPropertyId = ref<number | null>(null);
    const isLoading = ref(false);
    const search = ref(applied.search ?? '');
    const selectedTypes = ref<string[]>(toArr(applied.type));
    const selectedCities = ref<string[]>(toArr(applied.city));
    const selectedListings = ref<string[]>(toArr(applied.listing));
    const selectedBedrooms = ref<string[]>(toArr(applied.bedrooms));
    const bedroomsExact = ref(applied.bedrooms_exact === '1');
    const selectedBathrooms = ref<string[]>(toArr(applied.bathrooms));
    const bathroomsExact = ref(applied.bathrooms_exact === '1');
    const selectedUnitAmenities = ref<string[]>(toArr(applied.unit_amenities));
    const selectedBuildingAmenities = ref<string[]>(
        toArr(applied.building_amenities),
    );
    const selectedSort = ref(applied.sort ?? 'newest');
    const minPrice = ref(centsToDisplay(applied.min_price));
    const maxPrice = ref(centsToDisplay(applied.max_price));

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
        () =>
            selectedListings.value.includes('rental') &&
            !selectedListings.value.includes('sale'),
    );

    const isStudio = computed(() => selectedTypes.value.includes('studio'));

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

    const hasMorePages = computed(() => {
        const meta = options.properties().meta;

        return meta.current_page < meta.last_page;
    });

    // --- Methods ---
    const toggleMulti = (arr: string[], val: string) => {
        const idx = arr.indexOf(val);

        if (idx >= 0) {
            arr.splice(idx, 1);
        } else {
            arr.push(val);
        }
    };

    const multiLabel = (
        selected: string[],
        opts: FilterOption[] | undefined,
        fallback: string,
    ): string => {
        if (!selected.length) {
            return fallback;
        }

        if (selected.length === 1) {
            return (
                opts?.find((o) => o.slug === selected[0])?.name ?? selected[0]
            );
        }

        return `${selected.length} selected`;
    };

    /**
     * Build the current filter params for API requests.
     */
    const buildParams = (): Record<string, string | string[]> => {
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

            if (bedroomsExact.value) {
                params.bedrooms_exact = '1';
            }
        }

        if (selectedBathrooms.value.length) {
            params.bathrooms = selectedBathrooms.value;

            if (bathroomsExact.value) {
                params.bathrooms_exact = '1';
            }
        }

        if (selectedUnitAmenities.value.length) {
            params.unit_amenities = selectedUnitAmenities.value;
        }

        if (selectedBuildingAmenities.value.length) {
            params.building_amenities = selectedBuildingAmenities.value;
        }

        if (minPrice.value) {
            params.min_price = displayToCents(minPrice.value);
        }

        if (maxPrice.value) {
            params.max_price = displayToCents(maxPrice.value);
        }

        if (selectedSort.value !== 'newest') {
            params.sort = selectedSort.value;
        }

        return params;
    };

    /**
     * Apply filters and reset infinite scroll data.
     * Uses `reset: ['properties']` to clear accumulated scroll data
     * when filters change — prevents merging new results with stale data.
     */
    const applyFilters = () => {
        isLoading.value = true;
        router.visit('/properties', {
            data: buildParams(),
            only: ['properties', 'appliedFilters', 'mapCenter'],
            reset: ['properties'],
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

    /**
     * Load the next page of properties for the map sidebar.
     * Uses partial reload WITHOUT reset — data accumulates via Inertia::scroll().
     * The grid view uses <InfiniteScroll> which handles this automatically.
     */
    const loadMore = () => {
        if (isLoading.value || !hasMorePages.value) {
            return;
        }

        const meta = options.properties().meta;

        isLoading.value = true;
        router.visit(meta.path, {
            data: {
                ...buildParams(),
                page: String(meta.current_page + 1),
            },
            only: ['properties'],
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        });
    };

    const selectedMapProperty = ref<FeaturedProperty | null>(null);

    const onMapSelect = (property: FeaturedProperty) => {
        selectedMapProperty.value = property;
    };

    const clearMapSelection = () => {
        selectedMapProperty.value = null;
    };

    // --- Watchers ---

    // Debounced search — auto-applies after 400ms of inactivity
    watchDebounced(search, applyFilters, { debounce: 400 });

    return {
        viewMode,
        hoveredPropertyId,
        isLoading,
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
        hasMorePages,
        toggleMulti,
        multiLabel,
        applyFilters,
        clearFilters,
        loadMore,
        selectedMapProperty,
        onMapSelect,
        clearMapSelection,
    };
};
