<script setup lang="ts">
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import type { LatLngBoundsExpression } from 'leaflet';
import 'leaflet/dist/leaflet.css';
import type { ComponentPublicInstance } from 'vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

import PropertyMapPopupContent from '@/components/landing/PropertyMapPopupContent.vue';
import { useMapMarkers } from '@/composables/useMapMarkers';
import type { FeaturedProperty } from '@/types/landing';

const props = defineProps<{
    properties: FeaturedProperty[];
    hoveredId: number | null;
    center?: [number, number] | null;
    isFavorite?: (id: number) => boolean;
    isDismissed?: (id: number) => boolean;
}>();

const emit = defineEmits<{
    select: [property: FeaturedProperty];
    'toggle-favorite': [id: number];
    'toggle-dismissed': [id: number];
}>();

const isReady = ref(false);
const mapRef = ref<InstanceType<typeof LMap> | null>(null);
const mapCenter = ref<[number, number]>([49.2827, -123.1207]);
const mapZoom = ref(12);
let fitBoundsTimer: ReturnType<typeof setTimeout>;

const hoveredIdRef = computed(() => props.hoveredId);
const { markerIcon, markerState, markerOpacity } = useMapMarkers(
    props.isFavorite,
    props.isDismissed,
    hoveredIdRef,
);

const geoProperties = computed(() =>
    props.properties.filter((p) => p.latitude && p.longitude),
);

const hasPropertyCoords = (): boolean =>
    props.properties.some((p) => p.latitude && p.longitude);

const fitToProperties = () => {
    if (props.center) {
        mapCenter.value = props.center;
        mapZoom.value = 12;

        const leafletMap = mapRef.value?.leafletObject;

        if (leafletMap) {
            leafletMap.setView(props.center, 12);
        }

        return;
    }

    const coords = geoProperties.value.map(
        (p) => [Number(p.latitude), Number(p.longitude)] as [number, number],
    );

    if (coords.length === 0) {
        return;
    }

    if (coords.length === 1) {
        mapCenter.value = coords[0];
        mapZoom.value = 14;

        return;
    }

    const lats = coords.map((c) => c[0]);
    const lngs = coords.map((c) => c[1]);
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
};

const nextTickFitBounds = async () => {
    await new Promise((resolve) => {
        fitBoundsTimer = setTimeout(resolve, 200);
    });
    fitToProperties();
};

onBeforeUnmount(() => {
    clearTimeout(fitBoundsTimer);
});

onMounted(async () => {
    isReady.value = true;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                if (!hasPropertyCoords()) {
                    mapCenter.value = [
                        pos.coords.latitude,
                        pos.coords.longitude,
                    ];
                    mapZoom.value = 12;
                }
            },
            () => {},
            { timeout: 3000 },
        );
    }

    await nextTickFitBounds();
});

watch(() => props.properties, nextTickFitBounds);

const animateMarker = (propertyId: number) => {
    // Marker remounts on state change (key includes markerState) — wait for new DOM
    nextTick(() => {
        const marker = markerRefs.value[propertyId];
        const el = marker?.leafletObject?.getElement();

        if (!el) {
            return;
        }

        el.classList.add('pin-bounce');
        el.addEventListener(
            'animationend',
            () => el.classList.remove('pin-bounce'),
            { once: true },
        );
    });
};

const markerRefs = ref<Record<number, InstanceType<typeof LMarker>>>({});

const onMarkerMouseEnter = (propertyId: number) => {
    const marker = markerRefs.value[propertyId];

    if (marker?.leafletObject) {
        marker.leafletObject.openPopup();
    }
};

const onMarkerMouseLeave = (propertyId: number) => {
    const marker = markerRefs.value[propertyId];

    if (marker?.leafletObject) {
        marker.leafletObject.closePopup();
    }
};
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
            :key="`${property.id}-${markerState(property.id)}`"
            :ref="
                (el: ComponentPublicInstance | Element | null) => {
                    if (el)
                        markerRefs[property.id] = el as InstanceType<
                            typeof LMarker
                        >;
                }
            "
            :lat-lng="[Number(property.latitude), Number(property.longitude)]"
            :icon="markerIcon(property.id)"
            :options="{ opacity: markerOpacity(property.id) }"
            @click="emit('select', property)"
            @mouseenter="onMarkerMouseEnter(property.id)"
            @mouseleave="onMarkerMouseLeave(property.id)"
        >
            <LPopup
                :options="{
                    closeButton: false,
                    offset: [0, -10],
                    maxWidth: 220,
                }"
            >
                <PropertyMapPopupContent
                    :property="property"
                    :is-favorite="isFavorite?.(property.id) ?? false"
                    :is-dismissed="isDismissed?.(property.id) ?? false"
                    @toggle-favorite="
                        emit('toggle-favorite', property.id);
                        animateMarker(property.id);
                    "
                    @toggle-dismissed="
                        emit('toggle-dismissed', property.id);
                        animateMarker(property.id);
                    "
                />
            </LPopup>
        </LMarker>
    </LMap>
</template>

<style scoped>
:deep(.leaflet-container) {
    background-color: hsl(220 10% 92%);
}

:deep(.pin-bounce) {
    animation: pin-bounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes pin-bounce {
    0% {
        transform: scale(1) translateY(0);
    }
    30% {
        transform: scale(1.3) translateY(-10px);
    }
    60% {
        transform: scale(0.95) translateY(2px);
    }
    100% {
        transform: scale(1) translateY(0);
    }
}
</style>
