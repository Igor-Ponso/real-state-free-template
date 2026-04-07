<script setup lang="ts">
import { defineAsyncComponent, ref } from 'vue';

import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';

/**
 * defineAsyncComponent — Vue 3 hidden gem for lazy-loading heavy components.
 * Leaflet requires browser APIs (window, document) so it can't run during SSR.
 * This pattern defers the import until the component is actually rendered on the client,
 * keeping the initial bundle small and SSR-safe.
 */
const LeafletMap = defineAsyncComponent(
    () => import('@/components/landing/LeafletMap.vue'),
);

const { target: sectionRef, isVisible } = useFadeInOnScroll();

const office = {
    name: 'Sovereign Estates',
    address: '1055 W Georgia St, Suite 2100',
    city: 'Vancouver, BC V6E 3P3',
    coordinates: [49.2849, -123.1215] as [number, number],
    phone: '+1 (604) 555-0192',
    email: 'info@sovereignestates.com',
};

const mapReady = ref(false);
</script>

<template>
    <section
        ref="sectionRef"
        class="bg-linear-to-b from-landing-charcoal to-landing-deep-teal px-6 py-28 text-white"
    >
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
                    Visit Our Office
                </p>
                <h2
                    class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl"
                >
                    Find Us in Downtown Vancouver
                </h2>
            </div>

            <div
                class="grid grid-cols-1 gap-12 transition-all duration-700 lg:grid-cols-5"
                :class="
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0'
                "
                :style="{ transitionDelay: '300ms' }"
            >
                <!-- Contact info -->
                <div
                    class="flex flex-col justify-center space-y-8 lg:col-span-2"
                >
                    <div>
                        <h3 class="font-serif text-xl font-semibold">
                            {{ office.name }}
                        </h3>
                        <p
                            class="mt-2 font-body text-sm leading-relaxed text-white/60"
                        >
                            {{ office.address }}<br />
                            {{ office.city }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        <a
                            :href="`tel:${office.phone}`"
                            class="flex items-center gap-3 font-body text-sm text-white/60 transition-colors hover:text-landing-gold"
                        >
                            <span
                                class="flex size-8 items-center justify-center rounded-full border border-white/10"
                            >
                                <svg
                                    class="size-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
                                    />
                                </svg>
                            </span>
                            {{ office.phone }}
                        </a>
                        <a
                            :href="`mailto:${office.email}`"
                            class="flex items-center gap-3 font-body text-sm text-white/60 transition-colors hover:text-landing-gold"
                        >
                            <span
                                class="flex size-8 items-center justify-center rounded-full border border-white/10"
                            >
                                <svg
                                    class="size-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                                    />
                                </svg>
                            </span>
                            {{ office.email }}
                        </a>
                    </div>

                    <div class="border-t border-white/10 pt-6">
                        <p
                            class="font-body text-xs tracking-widest text-white/40 uppercase"
                        >
                            Office Hours
                        </p>
                        <p class="mt-2 font-body text-sm text-white/60">
                            Monday – Friday: 9:00 AM – 6:00 PM<br />
                            Saturday: 10:00 AM – 4:00 PM<br />
                            Sunday: By appointment only
                        </p>
                    </div>
                </div>

                <!-- Map -->
                <div class="relative overflow-hidden rounded-xl lg:col-span-3">
                    <div
                        class="aspect-[4/3] w-full lg:aspect-auto lg:h-[420px]"
                    >
                        <Suspense v-if="isVisible">
                            <LeafletMap
                                :center="office.coordinates"
                                :zoom="15"
                                :marker-title="office.name"
                                @ready="mapReady = true"
                            />
                            <template #fallback>
                                <div
                                    class="flex h-full w-full items-center justify-center bg-landing-charcoal/50"
                                >
                                    <div class="text-center">
                                        <div
                                            class="mx-auto mb-3 size-8 animate-spin rounded-full border-2 border-landing-gold border-t-transparent"
                                        />
                                        <p
                                            class="font-body text-sm text-white/40"
                                        >
                                            Loading map...
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </Suspense>
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-landing-charcoal/50"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
