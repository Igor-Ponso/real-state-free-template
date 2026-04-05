<script setup lang="ts">
import { useAmenityIcons } from '@/composables/useAmenityIcons';

defineProps<{
    title: string;
    amenities: string[];
}>();

const { getIcon } = useAmenityIcons();

const formatAmenity = (slug: string): string =>
    slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
</script>

<template>
    <div v-if="amenities.length">
        <h2 class="mb-4 font-serif text-xl font-semibold text-white">
            {{ title }}
        </h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div
                v-for="amenity in amenities"
                :key="amenity"
                class="flex items-center gap-2.5 rounded-lg border border-white/10 bg-white/5 px-3 py-2.5"
            >
                <component
                    :is="getIcon(amenity)"
                    v-if="getIcon(amenity)"
                    class="size-4 shrink-0 text-landing-gold/70"
                />
                <span class="font-body text-sm text-white/70">
                    {{ formatAmenity(amenity) }}
                </span>
            </div>
        </div>
    </div>
</template>
