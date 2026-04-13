<script setup lang="ts">
/* eslint-disable vue/no-mutating-props -- Inertia useForm() reactive proxy is designed for child mutation */
import type { InertiaForm } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';
import { computed, defineAsyncComponent, onMounted, ref, watch } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import type { LookupOption, PropertyFormData } from '@/types/admin';

const props = defineProps<{
    form: InertiaForm<PropertyFormData>;
    cities: LookupOption[];
}>();

/** Canadian provinces and territories */
const PROVINCES = [
    { value: 'AB', label: 'Alberta' },
    { value: 'BC', label: 'British Columbia' },
    { value: 'MB', label: 'Manitoba' },
    { value: 'NB', label: 'New Brunswick' },
    { value: 'NL', label: 'Newfoundland and Labrador' },
    { value: 'NS', label: 'Nova Scotia' },
    { value: 'NT', label: 'Northwest Territories' },
    { value: 'NU', label: 'Nunavut' },
    { value: 'ON', label: 'Ontario' },
    { value: 'PE', label: 'Prince Edward Island' },
    { value: 'QC', label: 'Quebec' },
    { value: 'SK', label: 'Saskatchewan' },
    { value: 'YT', label: 'Yukon' },
] as const;

/** Filter cities by selected province code (dynamic from DB `state` field) */
const filteredCities = computed(() => {
    if (!props.form.state) {
        return props.cities;
    }

    return props.cities.filter((c) => c.state === String(props.form.state));
});

// Clear city when province changes (city might not exist in new province)
watch(
    () => props.form.state,
    () => {
        const currentCityValid = filteredCities.value.some(
            (c) => String(c.id) === String(props.form.city_id),
        );

        if (!currentCityValid) {
            props.form.city_id = '' as unknown as number;
        }
    },
);

const selectedCity = computed(() =>
    props.cities.find((c) => String(c.id) === String(props.form.city_id)),
);

// Lazy-load map — Leaflet requires browser APIs (SSR-unsafe)
const LocationMapPicker = defineAsyncComponent({
    loader: () => import('@/components/admin/LocationMapPicker.vue'),
    suspensible: false,
});

const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Row 1: Street Address -->
        <div>
            <Label for="address">Street Address</Label>
            <Input
                id="address"
                v-model="form.address"
                placeholder="123 Main Street"
                class="mt-1"
            />
            <p
                v-if="form.errors?.address"
                class="mt-1 text-sm text-destructive"
            >
                {{ form.errors.address }}
            </p>
        </div>

        <!-- Row 2: Postal Code → Province → City -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label for="zip">Postal Code</Label>
                <Input
                    id="zip"
                    v-model="form.zip_code"
                    v-maska="'A#A #A#'"
                    type="text"
                    placeholder="V6B 1A1"
                    class="mt-1 uppercase"
                    maxlength="7"
                />
            </div>
            <div>
                <Label>Province</Label>
                <Select v-model="form.state">
                    <SelectTrigger class="mt-1">
                        <SelectValue placeholder="Select province" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="p in PROVINCES"
                            :key="p.value"
                            :value="p.value"
                        >
                            {{ p.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label>City</Label>
                <Select v-model="form.city_id">
                    <SelectTrigger class="mt-1">
                        <SelectValue placeholder="Select city" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="c in filteredCities"
                            :key="c.id"
                            :value="String(c.id)"
                        >
                            {{ c.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Row 3: Neighborhood -->
        <div>
            <Label for="neighborhood">Neighborhood</Label>
            <Input
                id="neighborhood"
                v-model="form.neighborhood"
                placeholder="Kitsilano, Downtown, etc."
                class="mt-1"
            />
        </div>

        <!-- Row 4: Lot Size, Floor, Total Floors -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label for="lot">Lot Size (sqft)</Label>
                <Input
                    id="lot"
                    v-model="form.lot_size_sqft"
                    type="number"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="floor">Floor</Label>
                <Input
                    id="floor"
                    v-model="form.floor"
                    type="number"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="total-floors">Total Floors</Label>
                <Input
                    id="total-floors"
                    v-model="form.total_floors"
                    type="number"
                    class="mt-1"
                />
            </div>
        </div>

        <!-- Row 5: Map preview — LAST, after all fields -->
        <LocationMapPicker
            v-if="isMounted"
            v-model:latitude="form.latitude"
            v-model:longitude="form.longitude"
            :address="String(form.address ?? '')"
            :city-id="String(form.city_id ?? '')"
            :city-lat="selectedCity?.latitude"
            :city-lng="selectedCity?.longitude"
            :state="String(form.state ?? '')"
        />
    </div>
</template>
