<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AboutSection from '@/components/landing/AboutSection.vue';
import FeaturedProperties from '@/components/landing/FeaturedProperties.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertySearch from '@/components/landing/PropertySearch.vue';
import ValueProposition from '@/components/landing/ValueProposition.vue';

const props = withDefaults(
    defineProps<{
        canRegister?: boolean;
    }>(),
    { canRegister: true },
);
</script>

<template>
    <div class="min-h-screen scroll-smooth font-body">
        <Head title="Luxury Real Estate" />
        <LandingHeader :can-register="props.canRegister" />
        <HeroSection />
        <ValueProposition />
        <FeaturedProperties />
        <PropertySearch />
        <AboutSection />
        <LandingFooter />
    </div>
</template>
