<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

import CookieConsent from '@/components/CookieConsent.vue';
import AboutSection from '@/components/landing/AboutSection.vue';
import FeaturedProperties from '@/components/landing/FeaturedProperties.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import NeighborhoodCarousel from '@/components/landing/NeighborhoodCarousel.vue';
import OfficeLocation from '@/components/landing/OfficeLocation.vue';
import PropertySearch from '@/components/landing/PropertySearch.vue';
import TeamSection from '@/components/landing/TeamSection.vue';
import ValueProposition from '@/components/landing/ValueProposition.vue';
import type {
    FeaturedProperty,
    FilterOption,
    Neighborhood,
    TeamMember,
    LandingStats,
} from '@/types/landing';

const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});

const props = withDefaults(
    defineProps<{
        canRegister?: boolean;
        featuredProperties?: FeaturedProperty[];
        neighborhoods?: Neighborhood[];
        teamMembers?: TeamMember[];
        stats?: LandingStats;
        searchOptions?: {
            propertyTypes: FilterOption[];
            listingTypes: FilterOption[];
            cities: FilterOption[];
        };
    }>(),
    {
        canRegister: true,
        featuredProperties: () => [],
        neighborhoods: () => [],
        teamMembers: () => [],
        stats: () => ({ properties_sold: 0, clients: 0, agents: 0, cities: 0 }),
    },
);
</script>

<template>
    <div class="min-h-screen scroll-smooth font-body">
        <Head title="Luxury Real Estate" />
        <LandingHeader :can-register="props.canRegister" />
        <HeroSection />
        <ValueProposition />
        <PropertySearch
            :property-types="props.searchOptions?.propertyTypes ?? []"
            :listing-types="props.searchOptions?.listingTypes ?? []"
            :cities="props.searchOptions?.cities ?? []"
        />
        <FeaturedProperties :properties="props.featuredProperties" />
        <NeighborhoodCarousel :neighborhoods="props.neighborhoods" />
        <TeamSection :members="props.teamMembers" />
        <AboutSection :stats="props.stats" />
        <OfficeLocation />
        <LandingFooter />
        <CookieConsent v-if="isMounted" />
    </div>
</template>
