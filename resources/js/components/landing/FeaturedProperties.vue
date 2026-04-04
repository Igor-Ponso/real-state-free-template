<script setup lang="ts">
import { Bath, BedDouble, Maximize } from 'lucide-vue-next';
import { motion } from 'motion-v';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

interface FeaturedProperty {
    id: number;
    name: string;
    location: string;
    price: string;
    beds: number;
    baths: number;
    sqft: string;
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
    },
    {
        id: 2,
        name: 'Oceanfront Villa',
        location: 'Malibu, California',
        price: '$5,900,000',
        beds: 5,
        baths: 4,
        sqft: '4,500',
    },
    {
        id: 3,
        name: 'Contemporary Loft',
        location: 'South Beach, Miami',
        price: '$1,850,000',
        beds: 2,
        baths: 2,
        sqft: '1,900',
    },
];
</script>

<template>
    <section id="properties" class="px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :while-in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6 }"
                :viewport="{ once: true }"
                class="mb-16 text-center"
            >
                <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Featured Properties
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    Handpicked selections from our exclusive portfolio
                </p>
            </motion.div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <motion.div
                    v-for="(property, index) in properties"
                    :key="property.id"
                    :initial="{ opacity: 0, y: 40 }"
                    :while-in-view="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: index * 0.15 }"
                    :viewport="{ once: true }"
                >
                    <Card class="group overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">
                        <div class="aspect-[16/10] bg-muted">
                            <div class="flex h-full items-center justify-center text-muted-foreground/30">
                                <Maximize class="size-12" />
                            </div>
                        </div>
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
                            <div class="mt-4 flex gap-2">
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
                </motion.div>
            </div>

            <div class="mt-12 text-center">
                <Button variant="outline" size="lg" class="font-body">
                    View All Properties
                </Button>
            </div>
        </div>
    </section>
</template>
