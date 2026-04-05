<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import type { LatLngBoundsExpression } from 'leaflet';
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { FeaturedProperty } from '@/types/landing';

const props = defineProps<{
    properties: FeaturedProperty[];
    hoveredId: number | null;
    center?: [number, number] | null;
}>();

const emit = defineEmits<{
    'select': [property: FeaturedProperty];
}>();

const isReady = ref(false);
const mapRef = ref<InstanceType<typeof LMap> | null>(null);
const mapCenter = ref<[number, number]>([49.2827, -123.1207]);
const mapZoom = ref(12);
let fitBoundsTimer: ReturnType<typeof setTimeout>;

const geoProperties = computed(() =>
    props.properties.filter(p => p.latitude && p.longitude)
);

onBeforeUnmount(() => {
    clearTimeout(fitBoundsTimer);
});

onMounted(async () => {
    isReady.value = true;

    // Try browser geolocation, fallback to Vancouver
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                if (!hasPropertyCoords()) {
                    mapCenter.value = [pos.coords.latitude, pos.coords.longitude];
                    mapZoom.value = 12;
                }
            },
            () => {}, // Silent fail — use default
            { timeout: 3000 },
        );
    }

    // Wait for map ready, then fit to properties
    await nextTickFitBounds();
});

watch(() => props.properties, nextTickFitBounds);

function hasPropertyCoords(): boolean {
    return props.properties.some(p => p.latitude && p.longitude);
}

async function nextTickFitBounds() {
    // Small delay for map to initialize
    await new Promise(resolve => {
        fitBoundsTimer = setTimeout(resolve, 200);
    });
    fitToProperties();
}

function fitToProperties() {
    // If a city center is provided (from backend), use it
    if (props.center) {
        mapCenter.value = props.center;
        mapZoom.value = 12;

        const leafletMap = mapRef.value?.leafletObject;
        if (leafletMap) {
            leafletMap.setView(props.center, 12);
        }
        return;
    }

    const coords = geoProperties.value
        .map(p => [Number(p.latitude), Number(p.longitude)] as [number, number]);

    if (coords.length === 0) return;

    if (coords.length === 1) {
        mapCenter.value = coords[0];
        mapZoom.value = 14;
        return;
    }

    const lats = coords.map(c => c[0]);
    const lngs = coords.map(c => c[1]);
    const bounds: LatLngBoundsExpression = [
        [Math.min(...lats), Math.min(...lngs)],
        [Math.max(...lats), Math.max(...lngs)],
    ];

    const leafletMap = mapRef.value?.leafletObject;
    if (leafletMap) {
        leafletMap.fitBounds(bounds, { padding: [40, 40] });
    } else {
        mapCenter.value = [
            (Math.min(...lats) + Math.max(...lats)) / 2,
            (Math.min(...lngs) + Math.max(...lngs)) / 2,
        ];
        mapZoom.value = 11;
    }
}
</script>

<template>
    <LMap
        v-if="isReady"
        ref="mapRef"
        :zoom="mapZoom"
        :center="mapCenter"
        :use-global-leaflet="false"
        :options="{ zoomControl: true, attributionControl: false }"
        class="h-full w-full"
    >
        <LTileLayer
            url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
            layer-type="base"
            attribution="&copy; OpenStreetMap &copy; CARTO"
        />

        <LMarker
            v-for="property in geoProperties"
            :key="property.id"
            :lat-lng="[Number(property.latitude), Number(property.longitude)]"
            :options="{
                opacity: hoveredId === null || hoveredId === property.id ? 1 : 0.4,
            }"
            @click="emit('select', property)"
        >
            <LPopup>
                <div class="min-w-48 font-body">
                    <img
                        v-if="property.images[0]"
                        :src="property.images[0]"
                        :alt="property.title"
                        class="mb-2 h-24 w-full rounded object-cover"
                    />
                    <p class="font-serif text-sm font-semibold">{{ property.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ property.location }}</p>
                    <p class="mt-1 font-serif text-base font-bold text-landing-gold">{{ property.price }}</p>
                </div>
            </LPopup>
        </LMarker>
    </LMap>
</template>

<style scoped>
:deep(.leaflet-container) {
    background-color: hsl(220 10% 92%);
}
</style>
