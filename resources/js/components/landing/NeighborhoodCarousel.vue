<script setup lang="ts">
import Autoplay from 'embla-carousel-autoplay';
import { watchOnce } from '@vueuse/core';
import { ref } from 'vue';
import {
    Carousel,
    type CarouselApi,
    CarouselContent,
    CarouselItem,
} from '@/components/ui/carousel';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

interface Neighborhood {
    city: string;
    province: string;
    image: string;
    properties: number;
}

const neighborhoods: Neighborhood[] = [
    {
        city: 'Vancouver',
        province: 'British Columbia',
        image: '/images/cities/vancouver.jpg',
        properties: 84,
    },
    {
        city: 'Toronto',
        province: 'Ontario',
        image: '/images/cities/toronto.jpg',
        properties: 126,
    },
    {
        city: 'Montreal',
        province: 'Quebec',
        image: '/images/cities/montreal.jpg',
        properties: 67,
    },
    {
        city: 'Calgary',
        province: 'Alberta',
        image: '/images/cities/calgary.jpg',
        properties: 43,
    },
    {
        city: 'Whistler',
        province: 'British Columbia',
        image: '/images/cities/whistler.jpg',
        properties: 31,
    },
];

const autoplayPlugin = Autoplay({
    delay: 4000,
    stopOnInteraction: false,
    stopOnMouseEnter: true,
});

const emblaApi = ref<CarouselApi>();
const currentSlide = ref(0);
const totalSlides = ref(0);

function setApi(api: CarouselApi) {
    emblaApi.value = api;
}

watchOnce(emblaApi, (api) => {
    if (!api) return;

    totalSlides.value = api.scrollSnapList().length;
    currentSlide.value = api.selectedScrollSnap();

    api.on('select', () => {
        currentSlide.value = api.selectedScrollSnap();
    });
});

function goToSlide(index: number) {
    emblaApi.value?.scrollTo(index);
}
</script>

<template>
    <section ref="sectionRef" class="relative overflow-hidden">
        <div
            class="transition-all duration-700"
            :class="isVisible ? 'opacity-100' : 'opacity-0'"
        >
            <Carousel
                :opts="{ loop: true }"
                :plugins="[autoplayPlugin]"
                class="w-full"
                @init-api="setApi"
            >
                <CarouselContent>
                    <CarouselItem
                        v-for="neighborhood in neighborhoods"
                        :key="neighborhood.city"
                    >
                        <div class="relative h-[70vh] min-h-96">
                            <img
                                :src="neighborhood.image"
                                :alt="neighborhood.city"
                                class="absolute inset-0 h-full w-full object-cover"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-linear-to-t from-black/70 via-black/30 to-black/10" />

                            <div class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
                                <p class="font-body text-sm font-medium uppercase tracking-[0.3em] text-landing-gold">
                                    Explore Our Locations
                                </p>
                                <h2 class="mt-4 font-serif text-4xl font-bold text-white md:text-6xl">
                                    {{ neighborhood.city }}
                                </h2>
                                <p class="mt-2 font-body text-lg text-white/70">
                                    {{ neighborhood.province }}
                                </p>
                                <p class="mt-4 font-body text-sm font-medium text-landing-gold">
                                    {{ neighborhood.properties }} exclusive properties
                                </p>
                            </div>
                        </div>
                    </CarouselItem>
                </CarouselContent>
            </Carousel>

            <!-- Dots -->
            <div class="absolute bottom-8 left-1/2 z-10 flex -translate-x-1/2 gap-2">
                <button
                    v-for="(_, index) in totalSlides"
                    :key="index"
                    @click="goToSlide(index)"
                    class="size-2.5 rounded-full transition-all duration-300"
                    :class="currentSlide === index
                        ? 'scale-125 bg-landing-gold'
                        : 'bg-white/50 hover:bg-white/80'
                    "
                    :aria-label="`Go to slide ${index + 1}`"
                />
            </div>
        </div>
    </section>
</template>
