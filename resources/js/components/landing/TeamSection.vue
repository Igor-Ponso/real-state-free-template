<script setup lang="ts">
import { Mail } from 'lucide-vue-next';
import { ref } from 'vue';

import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';
import type { TeamMember } from '@/types/landing';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

defineProps<{
    members: TeamMember[];
}>();

const selectedMember = ref<TeamMember | null>(null);
const dialogOpen = ref(false);

const openBio = (member: TeamMember) => {
    selectedMember.value = member;
    dialogOpen.value = true;
};
</script>

<template>
    <section ref="sectionRef" class="bg-landing-warm-beige px-6 py-28">
        <div class="mx-auto max-w-6xl">
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
                    The People Behind the Brand
                </p>
                <h2
                    class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl"
                >
                    Meet Our Team
                </h2>
                <p
                    class="mx-auto mt-4 max-w-xl font-body text-muted-foreground"
                >
                    Dedicated professionals who turn your real estate dreams
                    into reality
                </p>
            </div>

            <div
                class="transition-all duration-700"
                :class="
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0'
                "
                :style="{ transitionDelay: '300ms' }"
            >
                <Carousel :opts="{ align: 'start', loop: true }" class="w-full">
                    <CarouselContent class="-ml-6">
                        <CarouselItem
                            v-for="member in members"
                            :key="member.id"
                            class="basis-full pl-6 sm:basis-1/2 lg:basis-1/4"
                        >
                            <button
                                class="group w-full cursor-pointer text-center"
                                @click="openBio(member)"
                            >
                                <div
                                    class="relative mx-auto mb-5 size-44 overflow-hidden rounded-full shadow-lg ring-4 ring-white dark:ring-neutral-800"
                                >
                                    <img
                                        :src="member.image"
                                        :alt="member.name"
                                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                        loading="lazy"
                                    />
                                    <!-- Hover overlay -->
                                    <div
                                        class="absolute inset-0 flex items-center justify-center rounded-full bg-black/0 transition-all duration-500 group-hover:bg-black/40"
                                    >
                                        <span
                                            class="font-body text-xs font-medium tracking-widest text-white uppercase opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                                        >
                                            View Bio
                                        </span>
                                    </div>
                                </div>
                                <h3
                                    class="font-serif text-lg font-semibold transition-colors group-hover:text-landing-gold"
                                >
                                    {{ member.name }}
                                </h3>
                                <p
                                    class="mt-1 font-body text-sm text-landing-gold"
                                >
                                    {{ member.role }}
                                </p>
                                <div class="mt-3 flex justify-center gap-3">
                                    <span
                                        class="text-muted-foreground transition-colors group-hover:text-landing-gold"
                                    >
                                        <Mail class="size-4" />
                                    </span>
                                    <span
                                        v-if="member.social_links?.linkedin"
                                        class="text-muted-foreground transition-colors group-hover:text-landing-gold"
                                    >
                                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" /></svg>
                                    </span>
                                </div>
                            </button>
                        </CarouselItem>
                    </CarouselContent>
                    <CarouselPrevious
                        class="-left-4 border-landing-gold/20 bg-white shadow-lg hover:bg-landing-gold hover:text-white dark:bg-neutral-900"
                    />
                    <CarouselNext
                        class="-right-4 border-landing-gold/20 bg-white shadow-lg hover:bg-landing-gold hover:text-white dark:bg-neutral-900"
                    />
                </Carousel>
            </div>
        </div>

        <!-- Bio Dialog — split layout: photo left, info right -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent
                v-if="selectedMember"
                class="overflow-hidden p-0 sm:max-w-2xl"
            >
                <div class="grid grid-cols-1 sm:grid-cols-5">
                    <!-- Full photo -->
                    <div class="relative sm:col-span-2">
                        <img
                            :src="selectedMember.image"
                            :alt="selectedMember.name"
                            class="h-64 w-full object-cover sm:h-full sm:min-h-[400px]"
                        />
                        <div
                            class="absolute inset-x-0 bottom-0 bg-linear-to-t from-black/60 to-transparent p-4 sm:hidden"
                        >
                            <p
                                class="font-serif text-lg font-semibold text-white"
                            >
                                {{ selectedMember.name }}
                            </p>
                            <p class="font-body text-sm text-landing-gold">
                                {{ selectedMember.role }}
                            </p>
                        </div>
                    </div>

                    <!-- Bio content -->
                    <div
                        class="flex flex-col justify-between p-6 sm:col-span-3"
                    >
                        <div>
                            <DialogHeader class="hidden sm:block">
                                <DialogTitle class="font-serif text-2xl">
                                    {{ selectedMember.name }}
                                </DialogTitle>
                                <DialogDescription
                                    class="mt-1 font-body text-sm text-landing-gold"
                                >
                                    {{ selectedMember.role }}
                                </DialogDescription>
                            </DialogHeader>

                            <div class="mt-5 h-px bg-border" />

                            <p
                                class="mt-5 font-body text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ selectedMember.bio }}
                            </p>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <a
                                :href="`mailto:${selectedMember.email}`"
                                class="flex items-center gap-2 rounded-md border px-4 py-2 font-body text-xs font-medium transition-colors hover:border-landing-gold hover:text-landing-gold"
                            >
                                <Mail class="size-3.5" />
                                Email
                            </a>
                            <a
                                v-if="selectedMember.social_links?.linkedin"
                                :href="selectedMember.social_links.linkedin"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center gap-2 rounded-md border px-4 py-2 font-body text-xs font-medium transition-colors hover:border-landing-gold hover:text-landing-gold"
                            >
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" /></svg>
                                LinkedIn
                            </a>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </section>
</template>
