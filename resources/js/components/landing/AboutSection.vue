<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import AnimatedCounter from '@/components/landing/AnimatedCounter.vue';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';
import type { LandingStats } from '@/types/landing';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});

const props = defineProps<{
    stats: LandingStats;
}>();

const statsDisplay = computed(() => [
    { target: props.stats.properties_sold, suffix: '+', label: 'Properties Sold' },
    { target: props.stats.clients, suffix: '+', label: 'Happy Clients' },
    { target: props.stats.agents, suffix: '+', label: 'Expert Agents' },
    { target: props.stats.cities, suffix: '', label: 'Cities Served' },
]);
</script>

<template>
    <section id="about" ref="sectionRef" class="overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- Video side — full bleed, no padding -->
            <div
                class="relative min-h-80 transition-all duration-700 lg:min-h-0"
                :class="isVisible ? 'translate-x-0 opacity-100' : '-translate-x-12 opacity-0'"
            >
                <img
                    src="/images/landing/about-bg.jpg"
                    alt="About our agency"
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <video
                    v-if="isMounted"
                    autoplay
                    muted
                    loop
                    playsinline
                    poster="/images/landing/about-bg.jpg"
                    class="absolute inset-0 h-full w-full object-cover"
                >
                    <source src="/videos/landing/about.webm" type="video/webm" />
                    <source src="/videos/landing/about.mp4" type="video/mp4" />
                </video>
                <div class="absolute inset-0 bg-linear-to-t from-black/20 to-transparent" />
            </div>

            <!-- Content side -->
            <div
                class="flex flex-col justify-center px-6 py-20 transition-all delay-200 duration-700 lg:px-16 lg:py-28"
                :class="isVisible ? 'translate-x-0 opacity-100' : 'translate-x-12 opacity-0'"
            >
                <p class="font-body text-sm font-medium uppercase tracking-[0.3em] text-landing-gold">
                    Our Story
                </p>
                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    About Our Agency
                </h2>
                <p class="mt-6 font-body leading-relaxed text-muted-foreground">
                    With over a decade of experience in luxury real estate, we
                    specialize in connecting discerning buyers with
                    extraordinary properties. Our team of dedicated agents
                    brings deep market knowledge and an unwavering commitment
                    to excellence.
                </p>
                <p class="mt-4 font-body leading-relaxed text-muted-foreground">
                    From waterfront estates to urban penthouses, we curate a
                    portfolio that reflects the highest standards of luxury
                    living. Every property we represent tells a story — and
                    we're here to help you find yours.
                </p>

                <div
                    class="mt-10 grid grid-cols-2 gap-6 transition-all delay-500 duration-700"
                    :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
                >
                    <div v-for="stat in statsDisplay" :key="stat.label">
                        <div class="font-serif text-3xl font-bold text-landing-gold">
                            <AnimatedCounter
                                :target="stat.target"
                                :suffix="stat.suffix"
                            />
                        </div>
                        <p class="mt-1 font-body text-sm text-muted-foreground">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
