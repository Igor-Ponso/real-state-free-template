<script setup lang="ts">
import { Crown, Gem, Shield } from 'lucide-vue-next';
import { motion } from 'motion-v';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

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
    <section class="bg-landing-warm-beige px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :while-in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6 }"
                :viewport="{ once: true }"
                class="mb-16 text-center"
            >
                <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Why Choose Us
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    We combine market expertise with a commitment to excellence
                </p>
            </motion.div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <motion.div
                    v-for="(item, index) in values"
                    :key="item.title"
                    :initial="{ opacity: 0, y: 40 }"
                    :while-in-view="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: index * 0.15 }"
                    :viewport="{ once: true }"
                >
                    <Card class="h-full border-0 bg-transparent text-center shadow-none">
                        <CardHeader class="items-center pb-2">
                            <div class="flex size-14 items-center justify-center rounded-full bg-landing-gold/10">
                                <component
                                    :is="item.icon"
                                    class="size-7 text-landing-gold"
                                />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <h3 class="font-serif text-xl font-semibold">
                                {{ item.title }}
                            </h3>
                            <p class="mt-2 font-body text-sm leading-relaxed text-muted-foreground">
                                {{ item.description }}
                            </p>
                        </CardContent>
                    </Card>
                </motion.div>
            </div>
        </div>
    </section>
</template>
