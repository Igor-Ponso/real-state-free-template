<script setup lang="ts">
import { Bath, BedDouble, Maximize } from 'lucide-vue-next';
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

const { target: sectionRef, isVisible } = useFadeInOnScroll();

interface FeaturedProperty {
    id: number;
    name: string;
    location: string;
    price: string;
    beds: number;
    baths: number;
    sqft: string;
    images: string[];
}

const properties: FeaturedProperty[] = [
    {
        id: 1,
        name: 'Modern Penthouse Suite',
        location: 'Downtown, New York',
        price: '$2,450,000',
        beds: 3,
        baths: 2,
        sqft: '2,800',
        images: ['/images/properties/penthouse.jpg', '/images/properties/penthouse-2.jpg'],
    },
    {
        id: 2,
        name: 'Oceanfront Villa',
        location: 'Malibu, California',
        price: '$5,900,000',
        beds: 5,
        baths: 4,
        sqft: '4,500',
        images: ['/images/properties/villa.jpg', '/images/properties/villa-2.jpg'],
    },
    {
        id: 3,
        name: 'Contemporary Loft',
        location: 'South Beach, Miami',
        price: '$1,850,000',
        beds: 2,
        baths: 2,
        sqft: '1,900',
        images: ['/images/properties/loft.jpg', '/images/properties/loft-2.jpg'],
    },
];
</script>

<template>
    <section id="properties" ref="sectionRef" class="px-6 py-28">
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Featured Properties
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    Handpicked selections from our exclusive portfolio
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(property, index) in properties"
                    :key="property.id"
                    class="transition-all duration-700"
                    :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'"
                    :style="{ transitionDelay: `${300 + index * 150}ms` }"
                >
                    <Card class="group overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl">
                        <Carousel class="w-full">
                            <CarouselContent>
                                <CarouselItem
                                    v-for="(image, imgIndex) in property.images"
                                    :key="imgIndex"
                                >
                                    <div class="aspect-[16/10] overflow-hidden">
                                        <img
                                            :src="image"
                                            :alt="`${property.name} - Photo ${imgIndex + 1}`"
                                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            loading="lazy"
                                        />
                                    </div>
                                </CarouselItem>
                            </CarouselContent>
                            <CarouselPrevious class="left-2" />
                            <CarouselNext class="right-2" />
                        </Carousel>

                        <CardContent class="p-5">
                            <p class="font-serif text-2xl font-bold text-landing-gold">
                                {{ property.price }}
                            </p>
                            <h3 class="mt-1 font-serif text-lg font-semibold">
                                {{ property.name }}
                            </h3>
                            <p class="mt-1 font-body text-sm text-muted-foreground">
                                {{ property.location }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <Badge variant="secondary" class="font-body">
                                    <BedDouble class="mr-1 size-3" />
                                    {{ property.beds }} Beds
                                </Badge>
                                <Badge variant="secondary" class="font-body">
                                    <Bath class="mr-1 size-3" />
                                    {{ property.baths }} Baths
                                </Badge>
                                <Badge variant="secondary" class="font-body">
                                    <Maximize class="mr-1 size-3" />
                                    {{ property.sqft }} sqft
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div
                class="mt-12 text-center transition-all delay-700 duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <Button variant="outline" size="lg" class="font-body">
                    View All Properties
                </Button>
            </div>
        </div>
    </section>
</template>
