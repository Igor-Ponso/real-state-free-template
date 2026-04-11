<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Bath,
    BedDouble,
    EyeOff,
    Heart,
    Loader2,
    Maximize,
} from 'lucide-vue-next';

import PropertyCard from '@/components/landing/PropertyCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { FeaturedProperty, PaginatedResponse } from '@/types/landing';

defineProps<{
    properties: PaginatedResponse<FeaturedProperty>;
    selectedProperty: FeaturedProperty | null;
    isFavorite: (id: number) => boolean;
    isDismissed: (id: number) => boolean;
    hasMorePages: boolean;
    isLoading: boolean;
}>();

const hoveredPropertyId = defineModel<number | null>('hoveredPropertyId', {
    required: true,
});

const emit = defineEmits<{
    clearSelection: [];
    toggleFavorite: [id: number];
    toggleDismissed: [id: number];
    loadMore: [];
}>();
</script>

<template>
    <div
        class="w-full overflow-y-auto border-r border-white/10 bg-landing-charcoal p-4 sm:w-80 lg:w-96"
    >
        <!-- Selected property detail -->
        <div v-if="selectedProperty">
            <button
                class="mb-3 flex items-center gap-1 font-body text-xs text-white/40 transition-colors hover:text-white"
                @click="emit('clearSelection')"
            >
                <ArrowLeft class="size-3.5" />
                Back to list
            </button>
            <div class="overflow-hidden rounded-lg border border-white/10">
                <img
                    :src="selectedProperty.images[0]"
                    :alt="selectedProperty.title"
                    class="aspect-video w-full object-cover"
                />
            </div>
            <h3 class="mt-3 font-serif text-lg font-semibold text-white">
                {{ selectedProperty.title }}
            </h3>
            <p class="mt-1 font-body text-sm text-white/40">
                {{ selectedProperty.location }}
            </p>
            <p class="mt-2 font-serif text-xl font-bold text-landing-gold">
                {{ selectedProperty.price }}
            </p>
            <div class="mt-3 flex gap-4 font-body text-sm text-white/50">
                <span class="flex items-center gap-1">
                    <BedDouble class="size-4 text-landing-gold/60" />
                    {{ selectedProperty.bedrooms }} bd
                </span>
                <span class="flex items-center gap-1">
                    <Bath class="size-4 text-landing-gold/60" />
                    {{ selectedProperty.bathrooms }} ba
                </span>
                <span class="flex items-center gap-1">
                    <Maximize class="size-4 text-landing-gold/60" />
                    {{ selectedProperty.area_sqft }} sqft
                </span>
            </div>
            <p
                v-if="selectedProperty.description"
                class="mt-3 line-clamp-3 font-body text-xs leading-relaxed text-white/40"
            >
                {{ selectedProperty.description }}
            </p>
            <div
                v-if="
                    selectedProperty.listing_type ||
                    selectedProperty.property_type
                "
                class="mt-3 flex gap-2"
            >
                <Badge
                    v-if="selectedProperty.listing_type"
                    class="border-0 bg-landing-gold px-2 py-0.5 font-body text-2xs font-semibold tracking-wider text-white uppercase"
                >
                    {{ selectedProperty.listing_type }}
                </Badge>
                <Badge
                    v-if="selectedProperty.property_type"
                    class="border-0 bg-white/10 px-2 py-0.5 font-body text-2xs text-white/80"
                >
                    {{ selectedProperty.property_type }}
                </Badge>
            </div>
            <div class="mt-4 flex gap-2">
                <Button
                    variant="outline"
                    class="flex-1 border-white/10 text-white hover:bg-white/10"
                    :class="
                        isFavorite(selectedProperty.id)
                            ? 'border-red-500/30 text-red-400'
                            : ''
                    "
                    @click="emit('toggleFavorite', selectedProperty.id)"
                >
                    <Heart
                        class="mr-1.5 size-4"
                        :fill="
                            isFavorite(selectedProperty.id)
                                ? 'currentColor'
                                : 'none'
                        "
                    />
                    {{ isFavorite(selectedProperty.id) ? 'Saved' : 'Save' }}
                </Button>
                <Button
                    variant="outline"
                    class="border-white/10 text-white/50 hover:bg-white/10"
                    @click="
                        emit('toggleDismissed', selectedProperty.id);
                        emit('clearSelection');
                    "
                >
                    <EyeOff class="mr-1.5 size-4" />
                    Hide
                </Button>
            </div>
            <Button
                as-child
                class="mt-2 w-full bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90"
            >
                <Link :href="`/properties/${selectedProperty.slug}`" prefetch>
                    View Property Details
                </Link>
            </Button>
        </div>

        <!-- Property list -->
        <template v-else>
            <p class="mb-3 font-body text-xs text-white/40">
                {{ properties.meta.total }} properties
            </p>
            <div class="space-y-3">
                <Link
                    v-for="property in properties.data"
                    :key="property.id"
                    :href="`/properties/${property.slug}`"
                    prefetch
                    class="group block"
                    @mouseenter="hoveredPropertyId = property.id"
                    @mouseleave="hoveredPropertyId = null"
                >
                    <PropertyCard :property="property" variant="sidebar" />
                </Link>
            </div>

            <!-- Load more -->
            <div v-if="hasMorePages" class="mt-4">
                <Button
                    variant="outline"
                    class="w-full border-white/10 text-white/50 hover:bg-white/10 hover:text-white"
                    :disabled="isLoading"
                    @click="emit('loadMore')"
                >
                    <Loader2
                        v-if="isLoading"
                        class="mr-2 size-4 animate-spin"
                    />
                    {{ isLoading ? 'Loading...' : 'Load more properties' }}
                </Button>
            </div>

            <!-- Results count -->
            <p
                v-if="properties.data.length"
                class="mt-3 text-center font-body text-2xs text-white/30"
            >
                Showing {{ properties.data.length }} of
                {{ properties.meta.total }}
            </p>
        </template>
    </div>
</template>
