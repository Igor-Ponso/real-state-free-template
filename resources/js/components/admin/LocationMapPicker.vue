<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { useHttp } from '@inertiajs/vue3';
import { LMap, LMarker, LTileLayer } from '@vue-leaflet/vue-leaflet';
import { watchDebounced } from '@vueuse/core';
import { Loader2, MapPin } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';

const props = defineProps<{
    address: string;
    cityId: string | number;
    cityLat?: string | number | null;
    cityLng?: string | number | null;
    state: string;
}>();

const latitude = defineModel<string | number | null | undefined>('latitude');
const longitude = defineModel<string | number | null | undefined>('longitude');

const geocodeHttp = useHttp({
    address: '',
    city_id: null as string | number | null,
    state: null as string | null,
});

const mapRef = ref<InstanceType<typeof LMap> | null>(null);
const error = ref('');
const zoom = ref(4);

const hasCoordinates = computed(
    () => latitude.value != null && longitude.value != null,
);

// Default: Canada center. Updates to city → then to geocoded address.
const center = computed<[number, number]>(() => {
    if (hasCoordinates.value) {
        return [Number(latitude.value), Number(longitude.value)];
    }

    if (props.cityLat && props.cityLng) {
        return [Number(props.cityLat), Number(props.cityLng)];
    }

    return [56.1304, -106.3468]; // Canada center
});

// When city coordinates change, center map on the city and clear previous pin
watch(
    () => [props.cityLat, props.cityLng] as const,
    ([lat, lng]) => {
        if (!lat || !lng) {
            return;
        }

        const coords: [number, number] = [Number(lat), Number(lng)];

        // Clear previous coordinates — force fresh geocode from new city
        latitude.value = undefined;
        longitude.value = undefined;
        zoom.value = 12;

        nextTick(() => {
            mapRef.value?.leafletObject?.setView(coords, 12);
        });
    },
);

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
    zoom.value = 15;
};

const geocode = () => {
    if (!props.address || props.address.length < 5) {
        return;
    }

    error.value = '';
    geocodeHttp.address = props.address;
    geocodeHttp.city_id = props.cityId || null;
    geocodeHttp.state = props.state || null;

    geocodeHttp.post('/admin/geocode', {
        onSuccess: (data: unknown) => {
            const result = data as { lat: number; lng: number };
            latitude.value = result.lat.toFixed(7);
            longitude.value = result.lng.toFixed(7);
            zoom.value = 16;

            nextTick(() => {
                mapRef.value?.leafletObject?.setView(
                    [result.lat, result.lng],
                    16,
                );
            });
        },
        onError: () => {
            error.value = 'Could not locate this address.';
        },
    });
};

// Auto-geocode when address/city/state changes (debounced 1s)
watchDebounced(
    () => `${props.address}|${props.cityId}|${props.state}`,
    () => geocode(),
    { debounce: 1000 },
);

// Invalidate map size after mount — fixes tiles not loading inside tabs
onMounted(() => {
    setTimeout(() => {
        mapRef.value?.leafletObject?.invalidateSize();
    }, 200);
});
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="text-sm font-medium">Map Preview</label>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :disabled="geocodeHttp.processing || !address"
                @click="geocode"
            >
                <Loader2
                    v-if="geocodeHttp.processing"
                    class="mr-1.5 size-4 animate-spin"
                />
                <MapPin v-else class="mr-1.5 size-4" />
                Locate
            </Button>
        </div>

        <div class="h-56 overflow-hidden rounded-lg border">
            <LMap
                ref="mapRef"
                :zoom="zoom"
                :center="center"
                :use-global-leaflet="false"
                :options="{ zoomControl: true, attributionControl: false }"
                class="h-full w-full"
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
        <p
            v-else-if="geocodeHttp.processing"
            class="text-xs text-muted-foreground"
        >
            Locating address...
        </p>
        <p v-else-if="!hasCoordinates" class="text-xs text-muted-foreground">
            Fill in the address above — the map will update automatically
        </p>
    </div>
</template>
