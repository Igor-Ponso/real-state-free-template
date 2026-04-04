<script setup lang="ts">
import { motion } from 'motion-v';
import AnimatedCounter from '@/components/landing/AnimatedCounter.vue';

const stats = [
    { target: 500, suffix: '+', label: 'Properties Sold' },
    { target: 150, suffix: '+', label: 'Happy Clients' },
    { target: 15, suffix: '+', label: 'Years of Experience' },
    { target: 25, suffix: '+', label: 'Expert Agents' },
];
</script>

<template>
    <section id="about" class="px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <motion.div
                    :initial="{ opacity: 0, x: -40 }"
                    :while-in-view="{ opacity: 1, x: 0 }"
                    :transition="{ duration: 0.6 }"
                    :viewport="{ once: true }"
                >
                    <div class="overflow-hidden rounded-2xl">
                        <img
                            src="/images/auth/luxury-real-estate-poster.jpg"
                            alt="About our agency"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    </div>
                </motion.div>

                <motion.div
                    :initial="{ opacity: 0, x: 40 }"
                    :while-in-view="{ opacity: 1, x: 0 }"
                    :transition="{ duration: 0.6, delay: 0.2 }"
                    :viewport="{ once: true }"
                >
                    <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">
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

                    <div class="mt-10 grid grid-cols-2 gap-6">
                        <div v-for="stat in stats" :key="stat.label">
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
                </motion.div>
            </div>
        </div>
    </section>
</template>
