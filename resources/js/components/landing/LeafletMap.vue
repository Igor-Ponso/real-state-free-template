<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    center: [number, number];
    zoom?: number;
    markerTitle?: string;
}>();

const emit = defineEmits<{
    ready: [];
}>();

const isReady = ref(false);

onMounted(() => {
    isReady.value = true;
    emit('ready');
});
</script>

<template>
    <LMap
        v-if="isReady"
        :zoom="props.zoom ?? 13"
        :center="props.center"
        :use-global-leaflet="false"
        :options="{ zoomControl: true, attributionControl: false }"
        class="h-full w-full"
    >
        <!-- CartoDB Positron — clean, minimal light tiles. Free, no API key -->
        <LTileLayer
            url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
            layer-type="base"
            attribution="&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> &copy; <a href='https://carto.com/'>CARTO</a>"
        />

        <LMarker :lat-lng="props.center">
            <LPopup>
                <div class="font-body text-sm">
                    <strong class="text-foreground">{{
                        props.markerTitle ?? 'Our Office'
                    }}</strong>
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
