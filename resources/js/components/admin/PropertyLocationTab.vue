<script setup lang="ts">
/* eslint-disable vue/no-mutating-props -- Inertia useForm() reactive proxy is designed for child mutation */
import type { InertiaForm } from '@inertiajs/vue3';
import { defineAsyncComponent, onMounted, ref } from 'vue';

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

defineProps<{
    form: InertiaForm<PropertyFormData>;
    cities: LookupOption[];
}>();

// Lazy-load map picker — Leaflet requires browser APIs (SSR-unsafe)
const LocationMapPicker = defineAsyncComponent(
    () => import('@/components/admin/LocationMapPicker.vue'),
);

const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <Label for="address">Address</Label>
            <Input id="address" v-model="form.address" class="mt-1" />
            <p
                v-if="form.errors?.address"
                class="mt-1 text-sm text-destructive"
            >
                {{ form.errors.address }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label>City</Label>
                <Select v-model="form.city_id">
                    <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="c in cities"
                            :key="c.id"
                            :value="String(c.id)"
                            >{{ c.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label for="state">State/Province</Label>
                <Input
                    id="state"
                    v-model="form.state"
                    maxlength="10"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="zip">ZIP/Postal Code</Label>
                <Input
                    id="zip"
                    v-model="form.zip_code"
                    maxlength="10"
                    class="mt-1"
                />
            </div>
        </div>

        <div>
            <Label for="neighborhood">Neighborhood</Label>
            <Input id="neighborhood" v-model="form.neighborhood" class="mt-1" />
        </div>

        <!-- Map picker replaces raw lat/lng inputs -->
        <Suspense v-if="isMounted">
            <LocationMapPicker
                v-model:latitude="form.latitude"
                v-model:longitude="form.longitude"
                :address="String(form.address ?? '')"
                :city-id="String(form.city_id ?? '')"
                :state="String(form.state ?? '')"
            />
            <template #fallback>
                <div
                    class="flex h-64 items-center justify-center rounded-lg border bg-muted"
                >
                    <p class="text-sm text-muted-foreground">Loading map...</p>
                </div>
            </template>
        </Suspense>

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
    </div>
</template>
