<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Bath,
    BedDouble,
    Building2,
    Calendar,
    Car,
    ChevronRight,
    DollarSign,
    MapPin,
    Maximize,
    PawPrint,
    Ruler,
} from 'lucide-vue-next';
import { computed, defineAsyncComponent } from 'vue';

import { show } from '@/actions/App/Http/Controllers/PropertyController';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertyImageGallery from '@/components/landing/PropertyImageGallery.vue';
import PropertyInquiryForm from '@/components/landing/PropertyInquiryForm.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
import { useAmenityIcons } from '@/composables/useAmenityIcons';
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

const { getIcon } = useAmenityIcons();
const { target: amenitiesRef, isVisible: amenitiesVisible } = useFadeInOnScroll();
const { target: descriptionRef, isVisible: descriptionVisible } = useFadeInOnScroll();

const mapCenter = computed<[number, number] | null>(() => {
    if (props.property.latitude && props.property.longitude) {
        return [
            parseFloat(props.property.latitude),
            parseFloat(props.property.longitude),
        ];
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

const featureEntries = computed(() =>
    Object.entries(props.property.features).map(([key, value]) => ({
        label: key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
        value: String(value),
    })),
);

const formatAmenity = (slug: string): string =>
    slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const agentInitials = (name: string): string =>
    name
        .split(' ')
        .map((w) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);

const pageTitle = computed(
    () => props.property.meta_title || props.property.title,
);
const pageDescription = computed(
    () =>
        props.property.meta_description ||
        props.property.description.substring(0, 160),
);
</script>

<template>
    <div class="min-h-screen scroll-smooth font-body">
        <Head :title="pageTitle">
            <meta
                name="description"
                :content="pageDescription"
            />
            <meta property="og:title" :content="pageTitle" />
            <meta
                property="og:description"
                :content="pageDescription"
            />
            <meta
                v-if="property.images.length"
                property="og:image"
                :content="property.images[0]"
            />
        </Head>

        <LandingHeader :can-register="canRegister ?? false" />

        <!-- Hero gallery -->
        <section
            class="bg-linear-to-b from-landing-deep-teal to-landing-charcoal pt-20"
        >
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="mb-4 flex items-center gap-1 font-body text-sm text-white/40">
                    <Link
                        href="/properties"
                        class="transition-colors hover:text-landing-gold"
                    >
                        Properties
                    </Link>
                    <ChevronRight class="size-3.5" />
                    <span class="truncate text-white/60">
                        {{ property.title }}
                    </span>
                </nav>

                <PropertyImageGallery
                    :images="property.images"
                    :title="property.title"
                />
            </div>
        </section>

        <!-- Main content -->
        <section class="bg-landing-charcoal">
            <div
                class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
            >
                <!-- Property header -->
                <div class="mb-8">
                    <div class="flex flex-wrap items-start gap-3">
                        <Badge
                            v-if="property.listing_type"
                            class="border-0 bg-landing-gold px-2.5 py-1 font-body text-xs font-semibold tracking-wider text-white uppercase"
                        >
                            {{ property.listing_type }}
                        </Badge>
                        <Badge
                            v-if="property.property_type"
                            class="border-0 bg-white/10 px-2.5 py-1 font-body text-xs text-white/80 backdrop-blur-sm"
                        >
                            {{ property.property_type }}
                        </Badge>
                        <Badge
                            v-if="property.property_status"
                            class="border-0 bg-landing-deep-teal px-2.5 py-1 font-body text-xs text-white/80"
                        >
                            {{ property.property_status }}
                        </Badge>
                    </div>

                    <h1
                        class="mt-4 font-serif text-3xl font-bold text-white md:text-4xl"
                    >
                        {{ property.title }}
                    </h1>

                    <div
                        class="mt-2 flex flex-wrap items-center gap-4"
                    >
                        <p
                            class="flex items-center gap-1 font-body text-sm text-white/50"
                        >
                            <MapPin class="size-4 text-landing-gold/60" />
                            {{ fullAddress }}
                        </p>
                    </div>

                    <p
                        class="mt-4 font-serif text-3xl font-bold text-landing-gold"
                    >
                        {{ property.price }}
                    </p>
                </div>

                <!-- Specs bar -->
                <div
                    class="mb-10 grid grid-cols-3 gap-4 rounded-xl border border-white/10 bg-white/[0.03] p-4 sm:grid-cols-6"
                >
                    <div class="flex flex-col items-center gap-1">
                        <BedDouble class="size-5 text-landing-gold/70" />
                        <span class="font-body text-lg font-semibold text-white">
                            {{ property.bedrooms }}
                        </span>
                        <span class="font-body text-[10px] tracking-wider text-white/40 uppercase">
                            Bedrooms
                        </span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <Bath class="size-5 text-landing-gold/70" />
                        <span class="font-body text-lg font-semibold text-white">
                            {{ property.bathrooms }}
                        </span>
                        <span class="font-body text-[10px] tracking-wider text-white/40 uppercase">
                            Bathrooms
                        </span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <Maximize class="size-5 text-landing-gold/70" />
                        <span class="font-body text-lg font-semibold text-white">
                            {{ property.area_sqft }}
                        </span>
                        <span class="font-body text-[10px] tracking-wider text-white/40 uppercase">
                            Sq Ft
                        </span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <Car class="size-5 text-landing-gold/70" />
                        <span class="font-body text-lg font-semibold text-white">
                            {{ property.parking_spaces }}
                        </span>
                        <span class="font-body text-[10px] tracking-wider text-white/40 uppercase">
                            Parking
                        </span>
                    </div>
                    <div
                        v-if="property.year_built"
                        class="flex flex-col items-center gap-1"
                    >
                        <Calendar class="size-5 text-landing-gold/70" />
                        <span class="font-body text-lg font-semibold text-white">
                            {{ property.year_built }}
                        </span>
                        <span class="font-body text-[10px] tracking-wider text-white/40 uppercase">
                            Year Built
                        </span>
                    </div>
                    <div
                        v-if="property.lot_size_sqft"
                        class="flex flex-col items-center gap-1"
                    >
                        <Ruler class="size-5 text-landing-gold/70" />
                        <span class="font-body text-lg font-semibold text-white">
                            {{ property.lot_size_sqft }}
                        </span>
                        <span class="font-body text-[10px] tracking-wider text-white/40 uppercase">
                            Lot Size
                        </span>
                    </div>
                </div>

                <!-- Two-column layout -->
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <!-- Left column -->
                    <div class="lg:col-span-2 space-y-10">
                        <!-- Description -->
                        <div
                            ref="descriptionRef"
                            class="transition-all duration-700"
                            :class="
                                descriptionVisible
                                    ? 'translate-y-0 opacity-100'
                                    : 'translate-y-8 opacity-0'
                            "
                        >
                            <h2
                                class="mb-4 font-serif text-xl font-semibold text-white"
                            >
                                About This Property
                            </h2>
                            <p class="whitespace-pre-line font-body leading-relaxed text-white/60">
                                {{ property.description }}
                            </p>
                        </div>

                        <Separator class="bg-white/10" />

                        <!-- Unit Amenities -->
                        <div
                            v-if="property.unit_amenities.length"
                            ref="amenitiesRef"
                            class="transition-all duration-700"
                            :class="
                                amenitiesVisible
                                    ? 'translate-y-0 opacity-100'
                                    : 'translate-y-8 opacity-0'
                            "
                        >
                            <h2
                                class="mb-4 font-serif text-xl font-semibold text-white"
                            >
                                Unit Amenities
                            </h2>
                            <div
                                class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                            >
                                <div
                                    v-for="amenity in property.unit_amenities"
                                    :key="amenity"
                                    class="flex items-center gap-2.5 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2.5"
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

                        <!-- Building Amenities -->
                        <div v-if="property.building_amenities.length">
                            <h2
                                class="mb-4 font-serif text-xl font-semibold text-white"
                            >
                                Building Amenities
                            </h2>
                            <div
                                class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                            >
                                <div
                                    v-for="amenity in property.building_amenities"
                                    :key="amenity"
                                    class="flex items-center gap-2.5 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2.5"
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

                        <!-- Features -->
                        <div v-if="featureEntries.length">
                            <Separator class="mb-10 bg-white/10" />
                            <h2
                                class="mb-4 font-serif text-xl font-semibold text-white"
                            >
                                Property Features
                            </h2>
                            <div
                                class="grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-3"
                            >
                                <div
                                    v-for="feature in featureEntries"
                                    :key="feature.label"
                                >
                                    <p class="font-body text-xs tracking-wider text-white/40 uppercase">
                                        {{ feature.label }}
                                    </p>
                                    <p class="font-body text-sm text-white/80">
                                        {{ feature.value }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rental Details -->
                        <div v-if="property.is_rental">
                            <Separator class="mb-10 bg-white/10" />
                            <h2
                                class="mb-4 font-serif text-xl font-semibold text-white"
                            >
                                Rental Details
                            </h2>
                            <div
                                class="grid grid-cols-2 gap-4 sm:grid-cols-4"
                            >
                                <div
                                    v-if="property.deposit"
                                    class="rounded-lg border border-white/10 bg-white/[0.03] p-4"
                                >
                                    <DollarSign class="mb-1 size-4 text-landing-gold/70" />
                                    <p class="font-body text-xs text-white/40">
                                        Deposit
                                    </p>
                                    <p class="font-body text-sm font-semibold text-white">
                                        {{ property.deposit }}
                                    </p>
                                </div>
                                <div
                                    v-if="property.lease_length_months"
                                    class="rounded-lg border border-white/10 bg-white/[0.03] p-4"
                                >
                                    <Calendar class="mb-1 size-4 text-landing-gold/70" />
                                    <p class="font-body text-xs text-white/40">
                                        Lease Length
                                    </p>
                                    <p class="font-body text-sm font-semibold text-white">
                                        {{ property.lease_length_months }} months
                                    </p>
                                </div>
                                <div
                                    v-if="property.available_from"
                                    class="rounded-lg border border-white/10 bg-white/[0.03] p-4"
                                >
                                    <Calendar class="mb-1 size-4 text-landing-gold/70" />
                                    <p class="font-body text-xs text-white/40">
                                        Available From
                                    </p>
                                    <p class="font-body text-sm font-semibold text-white">
                                        {{ property.available_from }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-lg border border-white/10 bg-white/[0.03] p-4"
                                >
                                    <PawPrint class="mb-1 size-4 text-landing-gold/70" />
                                    <p class="font-body text-xs text-white/40">
                                        Pets
                                    </p>
                                    <p class="font-body text-sm font-semibold text-white">
                                        {{ property.pets_allowed ? 'Allowed' : 'Not Allowed' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Floor info -->
                        <div v-if="property.floor">
                            <Separator class="mb-10 bg-white/10" />
                            <div
                                class="flex items-center gap-6"
                            >
                                <div>
                                    <Building2 class="mb-1 size-4 text-landing-gold/70" />
                                    <p class="font-body text-xs text-white/40">
                                        Floor
                                    </p>
                                    <p class="font-body text-sm font-semibold text-white">
                                        {{ property.floor }}
                                        <span
                                            v-if="property.total_floors"
                                            class="text-white/40"
                                        >
                                            / {{ property.total_floors }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Location map -->
                        <div v-if="mapCenter">
                            <Separator class="mb-10 bg-white/10" />
                            <h2
                                class="mb-4 font-serif text-xl font-semibold text-white"
                            >
                                Location
                            </h2>
                            <div
                                class="overflow-hidden rounded-xl border border-white/10"
                            >
                                <div class="h-80">
                                    <Suspense>
                                        <LeafletMap
                                            :center="mapCenter"
                                            :zoom="15"
                                            :marker-title="property.title"
                                        />
                                        <template #fallback>
                                            <div
                                                class="flex h-80 items-center justify-center bg-landing-deep-teal/30"
                                            >
                                                <div
                                                    class="size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent"
                                                />
                                            </div>
                                        </template>
                                    </Suspense>
                                </div>
                            </div>
                            <p class="mt-2 font-body text-xs text-white/30">
                                {{ fullAddress }}
                            </p>
                        </div>
                    </div>

                    <!-- Right column (sticky sidebar) -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-24 space-y-6">
                            <!-- Agent card -->
                            <Card
                                v-if="property.agent"
                                class="border-white/10 bg-white/[0.04] backdrop-blur-sm"
                            >
                                <CardContent class="p-5">
                                    <h3
                                        class="mb-4 font-serif text-lg font-semibold text-white"
                                    >
                                        Listing Agent
                                    </h3>
                                    <div class="flex items-center gap-4">
                                        <Avatar class="size-14">
                                            <AvatarImage
                                                :src="property.agent.avatar"
                                                :alt="property.agent.name"
                                            />
                                            <AvatarFallback
                                                class="bg-landing-deep-teal font-serif text-white"
                                            >
                                                {{ agentInitials(property.agent.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <p class="font-serif font-semibold text-white">
                                                {{ property.agent.name }}
                                            </p>
                                            <div
                                                v-if="property.agent.specializations.length"
                                                class="mt-1 flex flex-wrap gap-1"
                                            >
                                                <Badge
                                                    v-for="spec in property.agent.specializations.slice(0, 2)"
                                                    :key="spec"
                                                    class="border-0 bg-landing-gold/20 px-1.5 py-0 font-body text-[10px] text-landing-gold"
                                                >
                                                    {{ spec }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                    <p
                                        v-if="property.agent.bio"
                                        class="mt-3 line-clamp-3 font-body text-sm leading-relaxed text-white/50"
                                    >
                                        {{ property.agent.bio }}
                                    </p>
                                </CardContent>
                            </Card>

                            <!-- Inquiry form -->
                            <Card class="border-white/10 bg-white/[0.04] backdrop-blur-sm">
                                <CardContent class="p-5">
                                    <PropertyInquiryForm
                                        :property-id="property.id"
                                    />
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <!-- Similar properties -->
                <div class="mt-16">
                    <Separator class="mb-10 bg-white/10" />
                    <h2
                        class="mb-6 font-serif text-2xl font-semibold text-white"
                    >
                        Similar Properties
                    </h2>
                    <Deferred data="similarProperties">
                        <template #fallback>
                            <div
                                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <Skeleton
                                    v-for="n in 4"
                                    :key="n"
                                    class="h-72 rounded-xl bg-white/10"
                                />
                            </div>
                        </template>

                        <div
                            v-if="similarProperties && similarProperties.length"
                        >
                            <Carousel
                                class="w-full"
                                :opts="{ align: 'start' }"
                            >
                                <CarouselContent class="-ml-4">
                                    <CarouselItem
                                        v-for="similar in similarProperties"
                                        :key="similar.id"
                                        class="basis-full pl-4 sm:basis-1/2 lg:basis-1/4"
                                    >
                                        <Link
                                            :href="show.url(similar.slug)"
                                            prefetch
                                            class="group"
                                        >
                                            <Card
                                                class="overflow-hidden border border-white/10 bg-white/[0.04] backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:bg-landing-deep-teal/80 hover:shadow-xl hover:ring-2 hover:ring-landing-gold/30"
                                            >
                                                <div class="relative">
                                                    <div
                                                        class="aspect-[16/10] overflow-hidden"
                                                    >
                                                        <img
                                                            :src="similar.images[0]"
                                                            :alt="similar.title"
                                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                            loading="lazy"
                                                        />
                                                    </div>
                                                    <div
                                                        class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-black/50 to-transparent"
                                                    />
                                                    <Badge
                                                        v-if="similar.listing_type"
                                                        class="absolute top-2.5 left-2.5 border-0 bg-landing-gold px-2 py-0.5 font-body text-[10px] font-semibold tracking-wider text-white uppercase"
                                                    >
                                                        {{ similar.listing_type }}
                                                    </Badge>
                                                    <div
                                                        class="absolute bottom-2 left-2.5"
                                                    >
                                                        <p
                                                            class="font-serif text-lg font-bold text-white drop-shadow-lg"
                                                        >
                                                            {{ similar.price }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <CardContent class="p-3.5">
                                                    <h3
                                                        class="truncate font-serif text-sm font-semibold text-white transition-colors group-hover:text-landing-gold"
                                                    >
                                                        {{ similar.title }}
                                                    </h3>
                                                    <p
                                                        class="mt-0.5 font-body text-xs text-white/40"
                                                    >
                                                        {{ similar.location }}
                                                    </p>
                                                    <div class="my-2.5 h-px bg-white/10" />
                                                    <div
                                                        class="flex gap-3 font-body text-xs text-white/40"
                                                    >
                                                        <span class="flex items-center gap-1">
                                                            <BedDouble class="size-3 text-landing-gold/60" />
                                                            {{ similar.bedrooms }}
                                                        </span>
                                                        <span class="flex items-center gap-1">
                                                            <Bath class="size-3 text-landing-gold/60" />
                                                            {{ similar.bathrooms }}
                                                        </span>
                                                        <span class="flex items-center gap-1">
                                                            <Maximize class="size-3 text-landing-gold/60" />
                                                            {{ similar.area_sqft }}
                                                        </span>
                                                    </div>
                                                </CardContent>
                                            </Card>
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
                        </div>

                        <div
                            v-else
                            class="py-10 text-center"
                        >
                            <p class="font-body text-sm text-white/40">
                                No similar properties found in this area.
                            </p>
                        </div>
                    </Deferred>
                </div>

                <!-- Back to listings -->
                <div class="mt-12 text-center">
                    <Button
                        as-child
                        variant="outline"
                        class="border-white/20 text-white hover:bg-white/10"
                    >
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
