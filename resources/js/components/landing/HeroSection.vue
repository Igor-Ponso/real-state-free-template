<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

import AuthVideoBackground from '@/components/AuthVideoBackground.vue';
import { Button } from '@/components/ui/button';

const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});

const scrollToProperties = () => {
    document
        .querySelector('#properties')
        ?.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <section id="hero" class="relative flex h-dvh items-center justify-center">
        <AuthVideoBackground
            poster-src="/images/auth/luxury-real-estate-poster.jpg"
            video-webm-src="/videos/auth/luxury-real-estate.webm"
            video-mp4-src="/videos/auth/luxury-real-estate.mp4"
        />

        <div
            class="absolute inset-0 bg-linear-to-b from-black/50 via-black/20 to-black/60"
        />

        <div class="relative z-10 mx-auto max-w-4xl px-6 text-center">
            <h1
                class="font-serif text-5xl font-bold tracking-tight text-white transition-all duration-700 md:text-7xl"
                :class="
                    isMounted
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-8 opacity-0'
                "
            >
                Where Luxury Meets Home
            </h1>

            <p
                class="mx-auto mt-6 max-w-2xl font-body text-lg font-light text-white/80 transition-all delay-300 duration-700 md:text-xl"
                :class="
                    isMounted
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-5 opacity-0'
                "
            >
                Discover exceptional properties curated for discerning buyers
            </p>

            <div
                class="mt-10 transition-all delay-500 duration-700"
                :class="
                    isMounted
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-5 opacity-0'
                "
            >
                <Button
                    size="lg"
                    class="bg-landing-gold px-8 py-6 font-body text-base font-medium text-landing-gold-foreground hover:bg-landing-gold/90"
                    @click="scrollToProperties"
                >
                    Explore Properties
                </Button>
            </div>
        </div>

        <div
            class="absolute bottom-8 left-1/2 -translate-x-1/2 transition-opacity delay-1000 duration-700"
            :class="isMounted ? 'opacity-100' : 'opacity-0'"
        >
            <ChevronDown
                class="size-8 animate-bounce text-white/60"
                aria-hidden="true"
            />
        </div>
    </section>
</template>
