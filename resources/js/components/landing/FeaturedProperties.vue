<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bath, BedDouble, Maximize } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';
import type { FeaturedProperty } from '@/types/landing';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

defineProps<{
    properties: FeaturedProperty[];
}>();

const hoveredImage = ref<string | null>(null);
</script>

<template>
    <section id="properties" ref="sectionRef" class="relative overflow-hidden py-28">
        <!-- Dynamic blurred background image on hover -->
        <Transition name="bg-fade">
            <div
                v-if="hoveredImage"
                :key="hoveredImage"
                class="absolute inset-0"
            >
                <img
                    :src="hoveredImage"
                    alt=""
                    class="h-full w-full scale-110 object-cover blur-sm"
                />
                <div class="absolute inset-0 bg-black/10 dark:bg-black/15" />
            </div>
        </Transition>

        <!-- Fallback static background when not hovering -->
        <div
            class="absolute inset-0 transition-opacity duration-700"
            :class="hoveredImage ? 'opacity-0' : 'opacity-100'"
        >
            <div class="absolute inset-0 bg-linear-to-b from-landing-deep-teal via-landing-charcoal to-landing-deep-teal" />
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;" />
        </div>

        <div class="relative mx-auto max-w-6xl px-6">
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-body text-sm font-medium uppercase tracking-[0.3em] text-landing-gold">
                    Exclusive Portfolio
                </p>
                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Featured Properties
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    Handpicked selections from our exclusive portfolio
                </p>
                <!-- Gold accent line -->
                <div class="mx-auto mt-6 h-px w-16 bg-landing-gold/40" />
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(property, index) in properties"
                    :key="property.id"
                    class="transition-all duration-500"
                    :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'"
                    :style="{ transitionDelay: isVisible ? `${300 + index * 150}ms` : '0ms' }"
                    @mouseenter="hoveredImage = property.images[0]"
                    @mouseleave="hoveredImage = null"
                >
                    <Card class="group overflow-hidden border border-white/10 bg-white/[0.04] shadow-lg backdrop-blur-sm transition-all duration-500 hover:-translate-y-2 hover:bg-landing-deep-teal/80 hover:shadow-2xl hover:ring-2 hover:ring-landing-gold/40">
                        <!-- Image carousel with overlays -->
                        <div class="relative">
                            <Carousel class="w-full">
                                <CarouselContent>
                                    <CarouselItem
                                        v-for="(image, imgIndex) in property.images"
                                        :key="imgIndex"
                                    >
                                        <div class="aspect-[16/10] overflow-hidden">
                                            <img
                                                :src="image"
                                                :alt="`${property.title} - Photo ${imgIndex + 1}`"
                                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                                loading="lazy"
                                            />
                                        </div>
                                    </CarouselItem>
                                </CarouselContent>
                                <CarouselPrevious class="left-2 size-8 opacity-0 transition-opacity group-hover:opacity-100" />
                                <CarouselNext class="right-2 size-8 opacity-0 transition-opacity group-hover:opacity-100" />
                            </Carousel>

                            <!-- Gradient overlay on image bottom -->
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-black/40 to-transparent" />

                            <!-- Listing type badge -->
                            <Badge
                                v-if="property.listing_type"
                                class="absolute left-3 top-3 border-0 bg-landing-gold px-2.5 py-1 font-body text-[11px] font-semibold uppercase tracking-wider text-white shadow-md"
                            >
                                {{ property.listing_type }}
                            </Badge>

                            <!-- Property type badge -->
                            <Badge
                                v-if="property.property_type"
                                class="absolute right-3 top-3 border-0 bg-black/60 px-2.5 py-1 font-body text-[11px] font-medium text-white/90 backdrop-blur-sm"
                            >
                                {{ property.property_type }}
                            </Badge>

                            <!-- Price overlay on image -->
                            <div class="absolute bottom-3 left-3">
                                <p class="font-serif text-2xl font-bold text-white drop-shadow-lg">
                                    {{ property.price }}
                                </p>
                            </div>
                        </div>

                        <CardContent class="p-5">
                            <h3 class="font-serif text-lg font-semibold transition-colors group-hover:text-landing-gold">
                                {{ property.title }}
                            </h3>
                            <p class="mt-1 font-body text-sm text-muted-foreground">
                                {{ property.location }}
                            </p>

                            <!-- Divider -->
                            <div class="my-4 h-px bg-border" />

                            <div class="flex flex-wrap gap-3">
                                <div class="flex items-center gap-1.5 font-body text-sm text-muted-foreground">
                                    <BedDouble class="size-4 text-landing-gold/70" />
                                    {{ property.bedrooms }} Beds
                                </div>
                                <div class="flex items-center gap-1.5 font-body text-sm text-muted-foreground">
                                    <Bath class="size-4 text-landing-gold/70" />
                                    {{ property.bathrooms }} Baths
                                </div>
                                <div class="flex items-center gap-1.5 font-body text-sm text-muted-foreground">
                                    <Maximize class="size-4 text-landing-gold/70" />
                                    {{ property.area_sqft }} sqft
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div
                class="mt-14 text-center transition-all delay-700 duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <Button
                    as-child
                    size="lg"
                    class="bg-landing-gold font-body text-landing-gold-foreground transition-all duration-300 hover:bg-landing-gold/90"
                >
                    <Link href="/properties">View All Properties</Link>
                </Button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.bg-fade-enter-active,
.bg-fade-leave-active {
    transition: opacity 0.7s ease;
}

.bg-fade-enter-from,
.bg-fade-leave-to {
    opacity: 0;
}
</style>
