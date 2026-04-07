<script setup lang="ts">
import { ref } from 'vue';

import type { CarouselApi } from '@/components/ui/carousel';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import { Dialog, DialogContent } from '@/components/ui/dialog';

const props = defineProps<{
    images: string[];
    title: string;
}>();

const mainApi = ref<CarouselApi>();
const lightboxApi = ref<CarouselApi>();
const currentIndex = ref(0);
const lightboxOpen = ref(false);

const onMainApiInit = (api: CarouselApi) => {
    mainApi.value = api;
    api?.on('select', () => {
        currentIndex.value = api.selectedScrollSnap();
    });
};

const goToSlide = (index: number) => {
    mainApi.value?.scrollTo(index);
};

const openLightbox = (index: number) => {
    currentIndex.value = index;
    lightboxOpen.value = true;

    // Wait for dialog to render, then scroll lightbox carousel
    setTimeout(() => {
        lightboxApi.value?.scrollTo(index, true);
    }, 50);
};
</script>

<template>
    <div>
        <!-- Main gallery carousel -->
        <Carousel class="w-full" @init-api="onMainApiInit">
            <CarouselContent>
                <CarouselItem v-for="(image, i) in props.images" :key="i">
                    <button
                        class="block w-full cursor-zoom-in"
                        @click="openLightbox(i)"
                    >
                        <div class="aspect-video overflow-hidden">
                            <img
                                :src="image"
                                :alt="`${props.title} — photo ${i + 1}`"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                :loading="i === 0 ? 'eager' : 'lazy'"
                            />
                        </div>
                    </button>
                </CarouselItem>
            </CarouselContent>
            <CarouselPrevious
                class="left-3 size-9 border-white/20 bg-black/40 text-white backdrop-blur-sm hover:bg-black/60"
            />
            <CarouselNext
                class="right-3 size-9 border-white/20 bg-black/40 text-white backdrop-blur-sm hover:bg-black/60"
            />
        </Carousel>

        <!-- Thumbnail strip -->
        <div
            v-if="props.images.length > 1"
            class="mt-3 flex gap-2 overflow-x-auto px-1"
        >
            <button
                v-for="(image, i) in props.images.slice(0, 6)"
                :key="i"
                class="aspect-property w-20 shrink-0 overflow-hidden rounded-md border transition-all"
                :class="
                    currentIndex === i
                        ? 'border-landing-gold opacity-100'
                        : 'border-white/10 opacity-60 hover:opacity-100'
                "
                @click="goToSlide(i)"
            >
                <img
                    :src="image"
                    :alt="`Thumbnail ${i + 1}`"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
            </button>
            <button
                v-if="props.images.length > 6"
                class="flex w-20 shrink-0 items-center justify-center rounded-md border border-white/10 bg-white/5 font-body text-xs text-white/50 hover:border-white/20"
                @click="openLightbox(6)"
            >
                +{{ props.images.length - 6 }}
            </button>
        </div>

        <!-- Lightbox dialog (single, outside carousel) -->
        <Dialog v-model:open="lightboxOpen">
            <DialogContent
                class="max-w-5xl border-0 bg-black/95 p-0 backdrop-blur-md"
            >
                <Carousel
                    class="w-full"
                    :opts="{ startIndex: currentIndex }"
                    @init-api="
                        (api: CarouselApi) => {
                            lightboxApi = api;
                        }
                    "
                >
                    <CarouselContent>
                        <CarouselItem v-for="(img, j) in props.images" :key="j">
                            <div class="flex items-center justify-center p-2">
                                <img
                                    :src="img"
                                    :alt="`${props.title} — photo ${j + 1}`"
                                    class="max-h-[80vh] w-auto rounded-md object-contain"
                                />
                            </div>
                        </CarouselItem>
                    </CarouselContent>
                    <CarouselPrevious
                        class="left-2 border-white/20 bg-black/50 text-white hover:bg-black/70"
                    />
                    <CarouselNext
                        class="right-2 border-white/20 bg-black/50 text-white hover:bg-black/70"
                    />
                </Carousel>
                <p class="pb-3 text-center font-body text-xs text-white/50">
                    {{ props.images.length }} photos
                </p>
            </DialogContent>
        </Dialog>
    </div>
</template>
