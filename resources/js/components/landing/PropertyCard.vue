<script setup lang="ts">
import { Bath, BedDouble, ImageOff, Maximize } from 'lucide-vue-next';
import { computed } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';

import {
    Carousel,
    CarouselContent,
    CarouselItem,
} from '@/components/ui/carousel';
import type { FeaturedProperty } from '@/types/landing';

type CardVariant = 'featured' | 'grid' | 'compact' | 'sidebar';

const props = withDefaults(
    defineProps<{
        property: FeaturedProperty;
        variant?: CardVariant;
    }>(),
    { variant: 'grid' },
);

const showCarousel = computed(() => props.variant === 'featured' || props.variant === 'grid');
const showBadges = computed(() => props.variant !== 'sidebar');
const showPropertyTypeBadge = computed(() => props.variant === 'featured' || props.variant === 'grid');

const styles = computed(() => {
    const v = props.variant;

    return {
        card: v === 'featured'
            ? 'gap-0 overflow-hidden border border-white/10 bg-white/5 py-0 shadow-lg backdrop-blur-sm transition-all duration-500 hover:-translate-y-2 hover:bg-landing-deep-teal/80 hover:shadow-2xl hover:ring-2 hover:ring-landing-gold/40'
            : 'gap-0 overflow-hidden border border-white/10 bg-white/5 py-0 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:bg-landing-deep-teal/80 hover:shadow-xl hover:ring-2 hover:ring-landing-gold/30',
        carouselBtn: v === 'featured' ? 'size-8' : 'size-7',
        gradient: v === 'featured' ? 'h-20 from-black/40' : 'h-16 from-black/50',
        badgePos: v === 'featured' ? 'left-3 top-3' : 'top-2.5 left-2.5',
        badgeText: v === 'featured' ? 'text-xs' : 'text-2xs',
        badgeTypPos: v === 'featured' ? 'right-3 top-3' : 'top-2.5 right-2.5',
        pricePos: v === 'featured' ? 'bottom-3 left-3' : 'bottom-2 left-2.5',
        priceText: v === 'featured' ? 'text-2xl' : 'text-lg',
        content: v === 'featured' ? 'p-5' : 'p-3.5',
        title: v === 'featured'
            ? 'font-serif text-lg font-semibold transition-colors group-hover:text-landing-gold'
            : 'truncate font-serif text-sm font-semibold text-white transition-colors group-hover:text-landing-gold',
        location: v === 'featured'
            ? 'mt-1 font-body text-sm text-muted-foreground'
            : 'mt-0.5 font-body text-xs text-white/40',
        divider: v === 'featured' ? 'my-4 h-px bg-border' : 'my-2.5 h-px bg-white/10',
        specIcon: v === 'featured' ? 'size-4 text-landing-gold/70' : 'size-3 text-landing-gold/60',
        specText: v === 'featured'
            ? 'flex items-center gap-1.5 font-body text-sm text-muted-foreground'
            : 'flex items-center gap-1 font-body text-xs text-white/40',
    };
});

const showLabels = computed(() => props.variant === 'featured');
const hasImages = computed(() => props.property.images?.length > 0);
</script>

<template>
    <!-- Sidebar variant: horizontal flex layout -->
    <div
        v-if="variant === 'sidebar'"
        class="flex gap-3 rounded-lg border border-white/5 bg-white/5 p-2 transition-all hover:border-landing-gold/30 hover:bg-landing-deep-teal/50"
    >
        <img
            v-if="hasImages"
            :src="property.images[0]"
            :alt="property.title"
            class="size-20 shrink-0 rounded object-cover"
            loading="lazy"
        />
        <div v-else class="flex size-20 shrink-0 items-center justify-center rounded bg-landing-charcoal">
            <ImageOff class="size-6 text-white/20" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="font-serif text-sm font-bold text-landing-gold">
                {{ property.price }}
            </p>
            <p class="truncate font-serif text-sm font-semibold text-white">
                {{ property.title }}
            </p>
            <p class="mt-0.5 font-body text-xs text-white/40">
                {{ property.location }}
            </p>
            <div class="mt-1 flex gap-2 font-body text-2xs text-white/30">
                <span>{{ property.bedrooms }} bd</span>
                <span>{{ property.bathrooms }} ba</span>
                <span>{{ property.area_sqft }} sqft</span>
            </div>
        </div>
    </div>

    <!-- Card variants: featured, grid, compact -->
    <Card
        v-else
        :class="styles.card"
    >
        <div class="relative">
            <!-- No image fallback -->
            <div v-if="!hasImages" class="aspect-property flex items-center justify-center bg-landing-charcoal">
                <div class="flex flex-col items-center gap-2 text-white/20">
                    <ImageOff class="size-10" />
                    <span class="font-body text-xs tracking-wide">No photos</span>
                </div>
            </div>

            <!-- Carousel (featured + grid) -->
            <Carousel v-else-if="showCarousel" v-slot="{ scrollPrev, scrollNext, canScrollPrev, canScrollNext }" class="w-full">
                <CarouselContent class="ml-0">
                    <CarouselItem
                        v-for="(image, i) in property.images"
                        :key="i"
                        class="pl-0"
                    >
                        <div class="aspect-property overflow-hidden">
                            <img
                                :src="image"
                                :alt="`${property.title} — photo ${i + 1}`"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                        </div>
                    </CarouselItem>
                </CarouselContent>
                <button
                    :disabled="!canScrollPrev"
                    :class="['absolute top-1/2 left-2 z-10 -translate-y-1/2 inline-flex items-center justify-center rounded-full border border-white/20 bg-landing-charcoal/80 text-white backdrop-blur-sm transition-all hover:bg-landing-charcoal opacity-0 group-hover:opacity-100 disabled:pointer-events-none disabled:opacity-0', styles.carouselBtn]"
                    @click.stop.prevent="scrollPrev"
                >
                    <ArrowLeft class="size-3.5" />
                </button>
                <button
                    :disabled="!canScrollNext"
                    :class="['absolute top-1/2 right-2 z-10 -translate-y-1/2 inline-flex items-center justify-center rounded-full border border-white/20 bg-landing-charcoal/80 text-white backdrop-blur-sm transition-all hover:bg-landing-charcoal opacity-0 group-hover:opacity-100 disabled:pointer-events-none disabled:opacity-0', styles.carouselBtn]"
                    @click.stop.prevent="scrollNext"
                >
                    <ArrowRight class="size-3.5" />
                </button>
            </Carousel>

            <!-- Single image (compact) -->
            <div v-else class="aspect-property overflow-hidden">
                <img
                    :src="property.images[0]"
                    :alt="property.title"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
            </div>

            <!-- Gradient overlay -->
            <div
                :class="['pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t to-transparent', styles.gradient]"
            />

            <!-- Badges -->
            <Badge
                v-if="showBadges && property.listing_type"
                :class="['absolute border-0 bg-landing-gold px-2 py-0.5 font-body font-semibold tracking-wider text-white uppercase', styles.badgePos, styles.badgeText]"
            >
                {{ property.listing_type }}
            </Badge>
            <Badge
                v-if="showPropertyTypeBadge && property.property_type"
                :class="['absolute border-0 bg-black/60 px-2 py-0.5 font-body text-white/90 backdrop-blur-sm', styles.badgeTypPos, styles.badgeText]"
            >
                {{ property.property_type }}
            </Badge>

            <!-- Price overlay -->
            <div :class="['absolute', styles.pricePos]">
                <p :class="['font-serif font-bold text-white drop-shadow-lg', styles.priceText]">
                    {{ property.price }}
                </p>
            </div>
        </div>

        <CardContent :class="styles.content">
            <h3 :class="styles.title">
                {{ property.title }}
            </h3>
            <p :class="styles.location">
                {{ property.location }}
            </p>
            <div :class="styles.divider" />
            <div class="flex flex-wrap gap-3">
                <span :class="styles.specText">
                    <BedDouble :class="styles.specIcon" />
                    {{ property.bedrooms }}{{ showLabels ? ' Beds' : '' }}
                </span>
                <span :class="styles.specText">
                    <Bath :class="styles.specIcon" />
                    {{ property.bathrooms }}{{ showLabels ? ' Baths' : '' }}
                </span>
                <span :class="styles.specText">
                    <Maximize :class="styles.specIcon" />
                    {{ property.area_sqft }}{{ showLabels ? ' sqft' : '' }}
                </span>
            </div>
        </CardContent>
    </Card>
</template>
