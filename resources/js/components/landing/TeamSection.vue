<script setup lang="ts">
import { Linkedin, Mail } from 'lucide-vue-next';
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

function openBio(member: TeamMember) {
    selectedMember.value = member;
    dialogOpen.value = true;
}
</script>

<template>
    <section ref="sectionRef" class="bg-landing-warm-beige px-6 py-28">
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-body text-sm font-medium uppercase tracking-[0.3em] text-landing-gold">
                    The People Behind the Brand
                </p>
                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Meet Our Team
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    Dedicated professionals who turn your real estate dreams into reality
                </p>
            </div>

            <div
                class="transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'"
                :style="{ transitionDelay: '300ms' }"
            >
                <Carousel
                    :opts="{ align: 'start', loop: true }"
                    class="w-full"
                >
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
                                <div class="relative mx-auto mb-5 size-44 overflow-hidden rounded-full shadow-lg ring-4 ring-white dark:ring-neutral-800">
                                    <img
                                        :src="member.image"
                                        :alt="member.name"
                                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                        loading="lazy"
                                    />
                                    <!-- Hover overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center rounded-full bg-black/0 transition-all duration-500 group-hover:bg-black/40">
                                        <span class="font-body text-xs font-medium uppercase tracking-widest text-white opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                                            View Bio
                                        </span>
                                    </div>
                                </div>
                                <h3 class="font-serif text-lg font-semibold transition-colors group-hover:text-landing-gold">
                                    {{ member.name }}
                                </h3>
                                <p class="mt-1 font-body text-sm text-landing-gold">
                                    {{ member.role }}
                                </p>
                                <div class="mt-3 flex justify-center gap-3">
                                    <span class="text-muted-foreground transition-colors group-hover:text-landing-gold">
                                        <Mail class="size-4" />
                                    </span>
                                    <span
                                        v-if="member.social_links?.linkedin"
                                        class="text-muted-foreground transition-colors group-hover:text-landing-gold"
                                    >
                                        <Linkedin class="size-4" />
                                    </span>
                                </div>
                            </button>
                        </CarouselItem>
                    </CarouselContent>
                    <CarouselPrevious class="-left-4 border-landing-gold/20 bg-white shadow-lg hover:bg-landing-gold hover:text-white dark:bg-neutral-900" />
                    <CarouselNext class="-right-4 border-landing-gold/20 bg-white shadow-lg hover:bg-landing-gold hover:text-white dark:bg-neutral-900" />
                </Carousel>
            </div>
        </div>

        <!-- Bio Dialog -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent
                v-if="selectedMember"
                class="sm:max-w-lg"
            >
                <DialogHeader>
                    <div class="flex items-center gap-5">
                        <div class="size-20 shrink-0 overflow-hidden rounded-full ring-2 ring-landing-gold/30">
                            <img
                                :src="selectedMember.image"
                                :alt="selectedMember.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div>
                            <DialogTitle class="font-serif text-xl">
                                {{ selectedMember.name }}
                            </DialogTitle>
                            <DialogDescription class="mt-1 font-body text-sm text-landing-gold">
                                {{ selectedMember.role }}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div class="mt-4 space-y-4">
                    <p class="font-body text-sm leading-relaxed text-muted-foreground">
                        {{ selectedMember.bio }}
                    </p>

                    <div class="flex gap-3 border-t pt-4">
                        <a
                            :href="`mailto:${selectedMember.email}`"
                            class="flex items-center gap-2 rounded-md border px-3 py-1.5 font-body text-xs text-muted-foreground transition-colors hover:border-landing-gold hover:text-landing-gold"
                        >
                            <Mail class="size-3.5" />
                            Email
                        </a>
                        <a
                            v-if="selectedMember.social_links?.linkedin"
                            :href="selectedMember.social_links.linkedin"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 rounded-md border px-3 py-1.5 font-body text-xs text-muted-foreground transition-colors hover:border-landing-gold hover:text-landing-gold"
                        >
                            <Linkedin class="size-3.5" />
                            LinkedIn
                        </a>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </section>
</template>
