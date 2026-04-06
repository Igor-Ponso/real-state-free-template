<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import PropertyCard from '@/components/landing/PropertyCard.vue';
import { Button } from '@/components/ui/button';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';
import type { FeaturedProperty } from '@/types/landing';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

defineProps<{
    properties: FeaturedProperty[];
}>();

const hoveredImage = ref<string | null>(null);
</script>

<template>
    <section
        id="properties"
        ref="sectionRef"
        class="relative overflow-hidden py-28"
    >
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

        <!-- Fallback static background -->
        <div
            class="absolute inset-0 transition-opacity duration-700"
            :class="hoveredImage ? 'opacity-0' : 'opacity-100'"
        >
            <div
                class="absolute inset-0 bg-linear-to-b from-landing-deep-teal via-landing-charcoal to-landing-deep-teal"
            />
            <div
                class="absolute inset-0 opacity-[0.02]"
                style="
                    background-image: radial-gradient(
                        circle at 1px 1px,
                        white 1px,
                        transparent 0
                    );
                    background-size: 24px 24px;
                "
            />
        </div>

        <div class="relative mx-auto max-w-6xl px-6">
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-8 opacity-0'
                "
            >
                <p
                    class="font-body text-sm font-medium tracking-[0.3em] text-landing-gold uppercase"
                >
                    Exclusive Portfolio
                </p>
                <h2
                    class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl"
                >
                    Featured Properties
                </h2>
                <p
                    class="mx-auto mt-4 max-w-xl font-body text-muted-foreground"
                >
                    Handpicked selections from our exclusive portfolio
                </p>
                <div class="mx-auto mt-6 h-px w-16 bg-landing-gold/40" />
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(property, index) in properties"
                    :key="property.id"
                    class="group transition-all duration-500"
                    :class="
                        isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-12 opacity-0'
                    "
                    :style="{
                        transitionDelay: isVisible
                            ? `${300 + index * 150}ms`
                            : '0ms',
                    }"
                    @mouseenter="hoveredImage = property.images[0]"
                    @mouseleave="hoveredImage = null"
                >
                    <PropertyCard :property="property" variant="featured" />
                </div>
            </div>

            <div
                class="mt-14 text-center transition-all delay-700 duration-700"
                :class="
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-8 opacity-0'
                "
            >
                <Button
                    as-child
                    size="lg"
                    class="bg-landing-gold font-body text-landing-gold-foreground transition-all duration-300 hover:bg-landing-gold/90"
                >
                    <Link href="/properties" prefetch>View All Properties</Link>
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
