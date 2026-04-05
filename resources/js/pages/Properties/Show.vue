<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    ChevronRight,
    MapPin,
} from 'lucide-vue-next';
import { computed, defineAsyncComponent } from 'vue';

import { show } from '@/actions/App/Http/Controllers/PropertyController';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertyAgentCard from '@/components/landing/PropertyAgentCard.vue';
import PropertyAmenities from '@/components/landing/PropertyAmenities.vue';
import PropertyCard from '@/components/landing/PropertyCard.vue';
import PropertyFeatures from '@/components/landing/PropertyFeatures.vue';
import PropertyImageGallery from '@/components/landing/PropertyImageGallery.vue';
import PropertyInquiryForm from '@/components/landing/PropertyInquiryForm.vue';
import PropertyRentalDetails from '@/components/landing/PropertyRentalDetails.vue';
import PropertySpecsBar from '@/components/landing/PropertySpecsBar.vue';
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
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';
import type { FeaturedProperty, PropertyDetail } from '@/types/landing';

const LeafletMap = defineAsyncComponent(
    () => import('@/components/landing/LeafletMap.vue'),
);

const props = defineProps<{
    property: PropertyDetail;
    similarProperties?: FeaturedProperty[];
    canRegister?: boolean;
}>();

const { target: descriptionRef, isVisible: descriptionVisible } = useFadeInOnScroll();

const mapCenter = computed<[number, number] | null>(() => {
    if (props.property.latitude && props.property.longitude) {
        return [parseFloat(props.property.latitude), parseFloat(props.property.longitude)];
    }

    return null;
});

const fullAddress = computed(() => {
    const parts = [props.property.address];

    if (props.property.city) {
        parts.push(props.property.city.name);
        parts.push(props.property.city.state);
    }

    parts.push(props.property.zip_code);

    return parts.filter(Boolean).join(', ');
});

const pageTitle = computed(() => props.property.meta_title || props.property.title);
const pageDescription = computed(() => props.property.meta_description || props.property.description.substring(0, 160));
</script>

<template>
    <div class="min-h-screen scroll-smooth font-body">
        <Head :title="pageTitle">
            <meta name="description" :content="pageDescription" />
            <meta property="og:title" :content="pageTitle" />
            <meta property="og:description" :content="pageDescription" />
            <meta v-if="property.images.length" property="og:image" :content="property.images[0]" />
        </Head>

        <LandingHeader :can-register="canRegister ?? false" />

        <!-- Hero gallery -->
        <section class="bg-linear-to-b from-landing-deep-teal to-landing-charcoal pt-20">
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <nav class="mb-4 flex items-center gap-1 font-body text-sm text-white/40">
                    <Link href="/properties" class="transition-colors hover:text-landing-gold">Properties</Link>
                    <ChevronRight class="size-3.5" />
                    <span class="truncate text-white/60">{{ property.title }}</span>
                </nav>
                <PropertyImageGallery :images="property.images" :title="property.title" />
            </div>
        </section>

        <!-- Main content -->
        <section class="bg-landing-charcoal">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <!-- Property header -->
                <div class="mb-8">
                    <div class="flex flex-wrap items-start gap-3">
                        <Badge v-if="property.listing_type" class="border-0 bg-landing-gold px-2.5 py-1 font-body text-xs font-semibold tracking-wider text-white uppercase">
                            {{ property.listing_type }}
                        </Badge>
                        <Badge v-if="property.property_type" class="border-0 bg-white/10 px-2.5 py-1 font-body text-xs text-white/80 backdrop-blur-sm">
                            {{ property.property_type }}
                        </Badge>
                        <Badge v-if="property.property_status" class="border-0 bg-landing-deep-teal px-2.5 py-1 font-body text-xs text-white/80">
                            {{ property.property_status }}
                        </Badge>
                    </div>
                    <h1 class="mt-4 font-serif text-3xl font-bold text-white md:text-4xl">{{ property.title }}</h1>
                    <p class="mt-2 flex items-center gap-1 font-body text-sm text-white/50">
                        <MapPin class="size-4 text-landing-gold/60" />
                        {{ fullAddress }}
                    </p>
                    <p class="mt-4 font-serif text-3xl font-bold text-landing-gold">{{ property.price }}</p>
                </div>

                <!-- Specs bar -->
                <PropertySpecsBar
                    :bedrooms="property.bedrooms"
                    :bathrooms="property.bathrooms"
                    :area-sqft="property.area_sqft"
                    :parking-spaces="property.parking_spaces"
                    :year-built="property.year_built"
                    :lot-size-sqft="property.lot_size_sqft"
                />

                <!-- Two-column layout -->
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <!-- Left column -->
                    <div class="space-y-10 lg:col-span-2">
                        <!-- Description -->
                        <div
                            ref="descriptionRef"
                            class="transition-all duration-700"
                            :class="descriptionVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
                        >
                            <h2 class="mb-4 font-serif text-xl font-semibold text-white">About This Property</h2>
                            <p class="whitespace-pre-line font-body leading-relaxed text-white/60">{{ property.description }}</p>
                        </div>

                        <Separator class="bg-white/10" />

                        <PropertyAmenities title="Unit Amenities" :amenities="property.unit_amenities" />
                        <PropertyAmenities title="Building Amenities" :amenities="property.building_amenities" />

                        <PropertyFeatures v-if="Object.keys(property.features).length" :features="property.features" />

                        <PropertyRentalDetails
                            v-if="property.is_rental"
                            :deposit="property.deposit"
                            :lease-length="property.lease_length_months"
                            :available-from="property.available_from"
                            :pets-allowed="property.pets_allowed"
                        />

                        <!-- Floor info -->
                        <div v-if="property.floor">
                            <Separator class="mb-10 bg-white/10" />
                            <div class="flex items-center gap-6">
                                <div>
                                    <Building2 class="mb-1 size-4 text-landing-gold/70" />
                                    <p class="font-body text-xs text-white/40">Floor</p>
                                    <p class="font-body text-sm font-semibold text-white">
                                        {{ property.floor }}
                                        <span v-if="property.total_floors" class="text-white/40">/ {{ property.total_floors }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Location map -->
                        <div v-if="mapCenter">
                            <Separator class="mb-10 bg-white/10" />
                            <h2 class="mb-4 font-serif text-xl font-semibold text-white">Location</h2>
                            <div class="overflow-hidden rounded-xl border border-white/10">
                                <div class="h-80">
                                    <Suspense>
                                        <LeafletMap :center="mapCenter" :zoom="15" :marker-title="property.title" />
                                        <template #fallback>
                                            <div class="flex h-80 items-center justify-center bg-landing-deep-teal/30">
                                                <div class="size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent" />
                                            </div>
                                        </template>
                                    </Suspense>
                                </div>
                            </div>
                            <p class="mt-2 font-body text-xs text-white/30">{{ fullAddress }}</p>
                        </div>
                    </div>

                    <!-- Right column (sticky sidebar) -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-24 space-y-6">
                            <PropertyAgentCard v-if="property.agent" :agent="property.agent" />
                            <Card class="border-white/10 bg-white/5 backdrop-blur-sm">
                                <CardContent class="p-5">
                                    <PropertyInquiryForm :property-id="property.id" />
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <!-- Similar properties -->
                <div class="mt-16">
                    <Separator class="mb-10 bg-white/10" />
                    <h2 class="mb-6 font-serif text-2xl font-semibold text-white">Similar Properties</h2>
                    <Deferred data="similarProperties">
                        <template #fallback>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                <Skeleton v-for="n in 4" :key="n" class="h-72 rounded-xl bg-white/10" />
                            </div>
                        </template>

                        <div v-if="similarProperties && similarProperties.length">
                            <Carousel class="w-full" :opts="{ align: 'start' }">
                                <CarouselContent class="-ml-4">
                                    <CarouselItem
                                        v-for="similar in similarProperties"
                                        :key="similar.id"
                                        class="basis-full pl-4 sm:basis-1/2 lg:basis-1/4"
                                    >
                                        <Link :href="show.url(similar.slug)" prefetch class="group">
                                            <PropertyCard :property="similar" variant="compact" />
                                        </Link>
                                    </CarouselItem>
                                </CarouselContent>
                                <CarouselPrevious class="-left-4 border-white/20 bg-landing-charcoal text-white hover:bg-landing-deep-teal" />
                                <CarouselNext class="-right-4 border-white/20 bg-landing-charcoal text-white hover:bg-landing-deep-teal" />
                            </Carousel>
                        </div>

                        <div v-else class="py-10 text-center">
                            <p class="font-body text-sm text-white/40">No similar properties found in this area.</p>
                        </div>
                    </Deferred>
                </div>

                <!-- Back to listings -->
                <div class="mt-12 text-center">
                    <Button as-child variant="outline" class="border-white/20 text-white hover:bg-white/10">
                        <Link href="/properties">
                            <ArrowLeft class="mr-2 size-4" />
                            Back to All Properties
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <LandingFooter />
    </div>
</template>
