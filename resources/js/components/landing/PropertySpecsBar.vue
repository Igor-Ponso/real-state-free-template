<script setup lang="ts">
import {
    Bath,
    BedDouble,
    Calendar,
    Car,
    Maximize,
    Ruler,
} from 'lucide-vue-next';

defineProps<{
    bedrooms: number;
    bathrooms: number;
    areaSqft: string;
    parkingSpaces: number;
    yearBuilt?: number | null;
    lotSizeSqft?: string | null;
}>();
</script>

<template>
    <div class="mb-10 grid grid-cols-3 gap-4 rounded-xl border border-white/10 bg-white/5 p-4 sm:grid-cols-6">
        <div class="flex flex-col items-center gap-1">
            <BedDouble class="size-5 text-landing-gold/70" />
            <span class="font-body text-lg font-semibold text-white">{{ bedrooms }}</span>
            <span class="font-body text-2xs tracking-wider text-white/40 uppercase">Bedrooms</span>
        </div>
        <div class="flex flex-col items-center gap-1">
            <Bath class="size-5 text-landing-gold/70" />
            <span class="font-body text-lg font-semibold text-white">{{ bathrooms }}</span>
            <span class="font-body text-2xs tracking-wider text-white/40 uppercase">Bathrooms</span>
        </div>
        <div class="flex flex-col items-center gap-1">
            <Maximize class="size-5 text-landing-gold/70" />
            <span class="font-body text-lg font-semibold text-white">{{ areaSqft }}</span>
            <span class="font-body text-2xs tracking-wider text-white/40 uppercase">Sq Ft</span>
        </div>
        <div class="flex flex-col items-center gap-1">
            <Car class="size-5 text-landing-gold/70" />
            <span class="font-body text-lg font-semibold text-white">{{ parkingSpaces }}</span>
            <span class="font-body text-2xs tracking-wider text-white/40 uppercase">Parking</span>
        </div>
        <div v-if="yearBuilt" class="flex flex-col items-center gap-1">
            <Calendar class="size-5 text-landing-gold/70" />
            <span class="font-body text-lg font-semibold text-white">{{ yearBuilt }}</span>
            <span class="font-body text-2xs tracking-wider text-white/40 uppercase">Year Built</span>
        </div>
        <div v-if="lotSizeSqft" class="flex flex-col items-center gap-1">
            <Ruler class="size-5 text-landing-gold/70" />
            <span class="font-body text-lg font-semibold text-white">{{ lotSizeSqft }}</span>
            <span class="font-body text-2xs tracking-wider text-white/40 uppercase">Lot Size</span>
        </div>
    </div>
</template>
