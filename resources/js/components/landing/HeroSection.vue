<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { motion } from 'motion-v';
import AuthVideoBackground from '@/components/AuthVideoBackground.vue';
import { Button } from '@/components/ui/button';

function scrollToProperties() {
    document.querySelector('#properties')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <section id="hero" class="relative flex h-dvh items-center justify-center">
        <AuthVideoBackground
            poster-src="/images/auth/luxury-real-estate-poster.jpg"
            video-webm-src="/videos/auth/luxury-real-estate.webm"
            video-mp4-src="/videos/auth/luxury-real-estate.mp4"
        />

        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/20 to-black/60" />

        <div class="relative z-10 mx-auto max-w-4xl px-6 text-center">
            <motion.h1
                :initial="{ opacity: 0, y: 30 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.8, delay: 0.2 }"
                class="font-serif text-5xl font-bold tracking-tight text-white md:text-7xl"
            >
                Where Luxury Meets Home
            </motion.h1>

            <motion.p
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.8, delay: 0.5 }"
                class="mx-auto mt-6 max-w-2xl font-body text-lg font-light text-white/80 md:text-xl"
            >
                Discover exceptional properties curated for discerning buyers
            </motion.p>

            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.8, delay: 0.8 }"
                class="mt-10"
            >
                <Button
                    size="lg"
                    class="bg-landing-gold px-8 py-6 font-body text-base font-medium text-landing-gold-foreground hover:bg-landing-gold/90"
                    @click="scrollToProperties"
                >
                    Explore Properties
                </Button>
            </motion.div>
        </div>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2">
            <motion.div
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 1, delay: 1.2 }"
            >
                <ChevronDown
                    class="size-8 animate-bounce text-white/60"
                    aria-hidden="true"
                />
            </motion.div>
        </div>
    </section>
</template>
