import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import {
    store,
    update,
} from '@/actions/App/Http/Controllers/Admin/PropertyController';
import type { AdminProperty, LookupOption } from '@/types/admin';

/**
 * Convert cents (minor units) from the backend to formatted dollars for display.
 */
const centsToDisplay = (cents: number | null | undefined): string => {
    if (!cents) {
        return '';
    }

    const dollars = Math.floor(cents / 100);

    return dollars > 0
        ? new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(
              dollars,
          )
        : '';
};

/**
 * Convert formatted dollar input (e.g., "1,234,567") to cents for the API.
 */
const displayToCents = (dollars: string): number => {
    const cleaned = dollars.replace(/[^0-9]/g, '');
    const parsed = parseInt(cleaned, 10);

    return isNaN(parsed) ? 0 : parsed * 100;
};

interface UsePropertyFormOptions {
    property?: AdminProperty | null;
    propertyTypes: LookupOption[];
    cities: LookupOption[];
    listingTypes: LookupOption[];
    propertyStatuses: LookupOption[];
    unitAmenities: string[];
    buildingAmenities: string[];
}

export const usePropertyForm = (options: UsePropertyFormOptions) => {
    const isEditing = computed(() => !!options.property);

    // Price display ref — shows formatted dollars in the UI.
    // The form stores cents internally; this ref syncs via watchers.
    const priceDisplay = ref(centsToDisplay(options.property?.price_raw));

    const form = useForm({
        title: options.property?.title ?? '',
        description: options.property?.description ?? '',
        property_type_id:
            options.property?.property_type_id ??
            options.propertyTypes[0]?.id ??
            '',
        listing_type_id:
            options.property?.listing_type_id ??
            options.listingTypes[0]?.id ??
            '',
        property_status_id:
            options.property?.property_status_id ??
            options.propertyStatuses[0]?.id ??
            '',
        price: options.property?.price_raw ?? 0,
        currency: options.property?.currency ?? 'CAD',
        address: options.property?.address ?? '',
        city_id: options.property?.city_id ?? options.cities[0]?.id ?? '',
        neighborhood: options.property?.neighborhood ?? '',
        state: options.property?.state ?? '',
        zip_code: options.property?.zip_code ?? '',
        latitude: options.property?.latitude ?? '',
        longitude: options.property?.longitude ?? '',
        bedrooms: options.property?.bedrooms ?? 1,
        bathrooms: options.property?.bathrooms ?? 1,
        area_sqft: options.property?.area_sqft ?? 500,
        lot_size_sqft: options.property?.lot_size_sqft ?? null,
        year_built: options.property?.year_built ?? null,
        parking_spaces: options.property?.parking_spaces ?? 0,
        floor: options.property?.floor ?? null,
        total_floors: options.property?.total_floors ?? null,
        unit_amenities: options.property?.unit_amenities ?? [],
        building_amenities: options.property?.building_amenities ?? [],
        deposit: options.property?.deposit ?? null,
        lease_length_months: options.property?.lease_length_months ?? null,
        available_from: options.property?.available_from ?? '',
        pets_allowed: options.property?.pets_allowed ?? false,
        is_published: options.property?.is_published ?? false,
        is_featured: options.property?.is_featured ?? false,
        meta_title: options.property?.meta_title ?? '',
        meta_description: options.property?.meta_description ?? '',
    });

    // Sync priceDisplay (dollars) → form.price (cents) on every change
    watch(priceDisplay, (val) => {
        form.price = displayToCents(val);
    });

    const submit = () => {
        // Ensure price is in cents before submission
        form.price = displayToCents(priceDisplay.value);

        if (isEditing.value && options.property) {
            form.put(update.url({ property: options.property.slug }));
        } else {
            form.post(store.url());
        }
    };

    return {
        form,
        isEditing,
        priceDisplay,
        submit,
        propertyTypes: options.propertyTypes,
        cities: options.cities,
        listingTypes: options.listingTypes,
        propertyStatuses: options.propertyStatuses,
        unitAmenities: options.unitAmenities,
        buildingAmenities: options.buildingAmenities,
    };
};
