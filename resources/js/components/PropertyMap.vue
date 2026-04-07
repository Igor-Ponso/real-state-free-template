<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import type { LatLngBoundsExpression } from 'leaflet';
import * as L from 'leaflet';
import { EyeOff, Heart } from 'lucide-vue-next';
import type { ComponentPublicInstance } from 'vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

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

const geoProperties = computed(() =>
    props.properties.filter((p) => p.latitude && p.longitude),
);

const pinSvg = (color: string, symbol: string) => `
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">
        <defs><filter id="s"><feDropShadow dx="0" dy="1" stdDeviation="1.5" flood-opacity="0.3"/></filter></defs>
        <path d="M16 2C8.8 2 3 7.8 3 15c0 10 13 22 13 22s13-12 13-22C29 7.8 23.2 2 16 2z" fill="${color}" stroke="white" stroke-width="2" filter="url(#s)"/>
        <text x="16" y="19" text-anchor="middle" font-size="13" font-weight="bold" fill="white">${symbol}</text>
    </svg>`;

const createIcon = (color: string, symbol: string) =>
    L.divIcon({
        html: pinSvg(color, symbol),
        className: '',
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        popupAnchor: [0, -40],
    });

const icons = {
    default: createIcon('#c5944a', ''),
    favorite: createIcon('#ef4444', '♥'),
    dismissed: createIcon('#9ca3af', '—'),
};

const markerIcon = (propertyId: number) => {
    if (props.isFavorite?.(propertyId)) {
        return icons.favorite;
    }

    if (props.isDismissed?.(propertyId)) {
        return icons.dismissed;
    }

    return icons.default;
};

const markerState = (propertyId: number): string => {
    if (props.isFavorite?.(propertyId)) {
        return 'fav';
    }

    if (props.isDismissed?.(propertyId)) {
        return 'dis';
    }

    return 'def';
};

const markerOpacity = (propertyId: number): number => {
    if (props.isDismissed?.(propertyId)) {
        return 0.6;
    }

    if (props.hoveredId !== null && props.hoveredId !== propertyId) {
        return 0.5;
    }

    return 1;
};

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
                <div class="min-w-44 font-body">
                    <img
                        v-if="property.images[0]"
                        :src="property.images[0]"
                        :alt="property.title"
                        class="mb-2 h-24 w-full rounded object-cover"
                    />
                    <p class="font-serif text-sm font-semibold">
                        {{ property.title }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ property.location }}
                    </p>
                    <p class="mt-1 font-serif font-bold text-landing-gold">
                        {{ property.price }}
                    </p>
                    <p
                        v-if="property.description"
                        class="mt-1 line-clamp-2 text-xs leading-relaxed text-muted-foreground"
                    >
                        {{ property.description }}
                    </p>
                    <!-- Favorite / Dismiss actions -->
                    <div class="mt-2 flex gap-1 border-t pt-2">
                        <button
                            :aria-label="
                                isFavorite?.(property.id)
                                    ? 'Remove from saved'
                                    : 'Save property'
                            "
                            class="flex flex-1 items-center justify-center gap-1 rounded px-2 py-1 text-xs transition-colors hover:bg-muted"
                            :class="
                                isFavorite?.(property.id)
                                    ? 'text-red-500'
                                    : 'text-muted-foreground'
                            "
                            @click.stop="
                                emit('toggle-favorite', property.id);
                                animateMarker(property.id);
                            "
                        >
                            <Heart
                                class="size-3.5"
                                :fill="
                                    isFavorite?.(property.id)
                                        ? 'currentColor'
                                        : 'none'
                                "
                            />
                            {{ isFavorite?.(property.id) ? 'Saved' : 'Save' }}
                        </button>
                        <button
                            :aria-label="
                                isDismissed?.(property.id)
                                    ? 'Show property'
                                    : 'Hide property'
                            "
                            class="flex flex-1 items-center justify-center gap-1 rounded px-2 py-1 text-xs transition-colors hover:bg-muted"
                            :class="
                                isDismissed?.(property.id)
                                    ? 'text-orange-500'
                                    : 'text-muted-foreground'
                            "
                            @click.stop="
                                emit('toggle-dismissed', property.id);
                                animateMarker(property.id);
                            "
                        >
                            <EyeOff class="size-3.5" />
                            {{ isDismissed?.(property.id) ? 'Hidden' : 'Hide' }}
                        </button>
                    </div>
                </div>
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
