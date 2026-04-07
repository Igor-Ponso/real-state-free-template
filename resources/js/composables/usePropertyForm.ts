import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import {
    store,
    update,
} from '@/actions/App/Http/Controllers/Admin/PropertyController';
import type { AdminProperty, LookupOption } from '@/types/admin';

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

    const submit = () => {
        if (isEditing.value && options.property) {
            form.put(update.url({ property: options.property.slug }));
        } else {
            form.post(store.url());
        }
    };

    return {
        form,
        isEditing,
        submit,
        propertyTypes: options.propertyTypes,
        cities: options.cities,
        listingTypes: options.listingTypes,
        propertyStatuses: options.propertyStatuses,
        unitAmenities: options.unitAmenities,
        buildingAmenities: options.buildingAmenities,
    };
};
