<script setup lang="ts">
import { Crown, Gem, Shield } from 'lucide-vue-next';
import { ref } from 'vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

const values = [
    {
        icon: Gem,
        title: 'Curated Collection',
        description: 'Hand-picked luxury properties that meet the highest standards of quality, design, and location.',
    },
    {
        icon: Shield,
        title: 'Trusted Expertise',
        description: 'Our experienced agents provide personalized guidance through every step of your real estate journey.',
    },
    {
        icon: Crown,
        title: 'Premium Experience',
        description: 'White-glove service from your first inquiry to the final closing — nothing less than exceptional.',
    },
];
</script>

<template>
    <section
        ref="sectionRef"
        class="relative overflow-hidden bg-fixed bg-cover bg-center px-6 py-28"
        style="background-image: url('/images/landing/value-bg.jpg')"
    >
        <div class="absolute inset-0 bg-white/85 backdrop-blur-sm dark:bg-black/80" />

        <div class="relative mx-auto max-w-6xl">
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Why Choose Us
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    We combine market expertise with a commitment to excellence
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div
                    v-for="(item, index) in values"
                    :key="item.title"
                    class="transition-all duration-700"
                    :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'"
                    :style="{ transitionDelay: `${300 + index * 150}ms` }"
                >
                    <Card class="h-full border-0 bg-white/70 text-center shadow-lg backdrop-blur-sm dark:bg-black/50">
                        <CardHeader class="items-center pb-2">
                            <div class="flex size-16 items-center justify-center rounded-full bg-landing-gold/15">
                                <component
                                    :is="item.icon"
                                    class="size-8 text-landing-gold"
                                />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <h3 class="font-serif text-xl font-semibold">
                                {{ item.title }}
                            </h3>
                            <p class="mt-3 font-body text-sm leading-relaxed text-muted-foreground">
                                {{ item.description }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </section>
</template>
