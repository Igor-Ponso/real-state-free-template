<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { show } from '@/actions/App/Http/Controllers/PropertyController';
import PropertyCard from '@/components/landing/PropertyCard.vue';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import type { FeaturedProperty } from '@/types/landing';

defineProps<{
    properties: FeaturedProperty[];
}>();
</script>

<template>
    <Carousel class="w-full" :opts="{ align: 'start' }">
        <CarouselContent class="-ml-4">
            <CarouselItem
                v-for="similar in properties"
                :key="similar.id"
                class="basis-full pl-4 sm:basis-1/2 lg:basis-1/4"
            >
                <Link :href="show.url(similar.slug)" prefetch class="group">
                    <PropertyCard :property="similar" variant="compact" />
                </Link>
            </CarouselItem>
        </CarouselContent>
        <CarouselPrevious
            class="-left-4 border-white/20 bg-landing-charcoal text-white hover:bg-landing-deep-teal"
        />
        <CarouselNext
            class="-right-4 border-white/20 bg-landing-charcoal text-white hover:bg-landing-deep-teal"
        />
    </Carousel>
</template>
