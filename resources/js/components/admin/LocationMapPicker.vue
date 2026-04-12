<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { useHttp } from '@inertiajs/vue3';
import { LMap, LMarker, LTileLayer } from '@vue-leaflet/vue-leaflet';
import { MapPin } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    address: string;
    cityId: string | number;
    state: string;
}>();

const latitude = defineModel<string | number | null>('latitude');
const longitude = defineModel<string | number | null>('longitude');

const geocodeHttp = useHttp<
    { address: string; city_id: string | number | null; state: string | null },
    { lat: number; lng: number }
>({
    address: '',
    city_id: null,
    state: null,
});

const error = ref('');
const zoom = ref(13);

const hasCoordinates = computed(() => latitude.value && longitude.value);

const center = computed<[number, number]>(() => {
    if (hasCoordinates.value) {
        return [Number(latitude.value), Number(longitude.value)];
    }

    return [49.2827, -123.1207]; // Vancouver default
});

const onMarkerDragEnd = (event: {
    target: { getLatLng: () => { lat: number; lng: number } };
}) => {
    const { lat, lng } = event.target.getLatLng();
    latitude.value = lat.toFixed(7);
    longitude.value = lng.toFixed(7);
};

const onMapClick = (event: { latlng: { lat: number; lng: number } }) => {
    latitude.value = event.latlng.lat.toFixed(7);
    longitude.value = event.latlng.lng.toFixed(7);
};

const geocode = () => {
    if (!props.address) {
        error.value = 'Enter an address first';

        return;
    }

    error.value = '';
    geocodeHttp.address = props.address;
    geocodeHttp.city_id = props.cityId || null;
    geocodeHttp.state = props.state || null;

    geocodeHttp.post('/admin/geocode', {
        onSuccess: (data: { lat: number; lng: number }) => {
            latitude.value = data.lat.toFixed(7);
            longitude.value = data.lng.toFixed(7);
            zoom.value = 15;
        },
        onError: () => {
            error.value =
                'Could not locate address. Try adjusting it or place the pin manually.';
        },
    });
};

// Auto-geocode when address changes (debounced via parent)
watch(
    () => props.address,
    () => {
        error.value = '';
    },
);
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="text-sm font-medium">Property Location</label>
            <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="geocodeHttp.processing || !address"
                @click="geocode"
            >
                <Spinner v-if="geocodeHttp.processing" class="mr-1.5" />
                <MapPin v-else class="mr-1.5 size-4" />
                Locate on Map
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-lg border"
            :class="error ? 'border-destructive' : ''"
        >
            <LMap
                :zoom="zoom"
                :center="center"
                :use-global-leaflet="false"
                :options="{ zoomControl: true, attributionControl: false }"
                class="h-64 w-full"
                @click="onMapClick"
            >
                <LTileLayer
                    url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
                    layer-type="base"
                    attribution="&copy; OpenStreetMap &copy; CARTO"
                />
                <LMarker
                    v-if="hasCoordinates"
                    :lat-lng="[Number(latitude), Number(longitude)]"
                    draggable
                    @dragend="onMarkerDragEnd"
                />
            </LMap>
        </div>

        <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
        <p v-else-if="hasCoordinates" class="text-xs text-muted-foreground">
            {{ Number(latitude).toFixed(5) }},
            {{ Number(longitude).toFixed(5) }}
            — drag pin or click map to adjust
        </p>
        <p v-else class="text-xs text-muted-foreground">
            Click "Locate on Map" or click the map to set the property location
        </p>
    </div>
</template>
