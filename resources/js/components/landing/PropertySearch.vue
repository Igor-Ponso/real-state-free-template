<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});
</script>

<template>
    <section ref="sectionRef" class="relative overflow-hidden px-6 py-28">
        <!-- Video background -->
        <div class="absolute inset-0">
            <img
                src="/images/landing/search-bg.jpg"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            />
            <video
                v-if="isMounted"
                autoplay
                muted
                loop
                playsinline
                poster="/images/landing/search-bg.jpg"
                class="absolute inset-0 h-full w-full object-cover"
            >
                <source src="/videos/landing/search.webm" type="video/webm" />
                <source src="/videos/landing/search.mp4" type="video/mp4" />
            </video>
            <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px]" />
        </div>

        <div class="relative mx-auto max-w-6xl">
            <!-- Cinematic headline -->
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-body text-sm font-medium uppercase tracking-[0.3em] text-landing-gold">
                    Experience Luxury Living
                </p>
                <h2 class="mt-4 font-serif text-3xl font-semibold tracking-tight text-white md:text-5xl">
                    Find Your Dream Property
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-lg font-light text-white/60">
                    From grand foyers to intimate gardens — search our exclusive
                    collection of properties designed for those who appreciate the extraordinary.
                </p>
            </div>

            <!-- Search filters in glass container -->
            <div
                class="rounded-2xl border border-white/10 bg-white/10 p-8 backdrop-blur-md transition-all delay-300 duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label class="mb-2 block font-body text-xs font-medium text-white/70">Type</label>
                        <Select>
                            <SelectTrigger class="border-white/20 bg-white/10 text-white">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="house">House</SelectItem>
                                <SelectItem value="apartment">Apartment</SelectItem>
                                <SelectItem value="penthouse">Penthouse</SelectItem>
                                <SelectItem value="villa">Villa</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <label class="mb-2 block font-body text-xs font-medium text-white/70">Location</label>
                        <Input
                            placeholder="City or neighborhood..."
                            class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block font-body text-xs font-medium text-white/70">Min Price</label>
                        <Input
                            type="number"
                            placeholder="$Min"
                            class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block font-body text-xs font-medium text-white/70">Max Price</label>
                        <Input
                            type="number"
                            placeholder="$Max"
                            class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block font-body text-xs font-medium text-white/70">Bedrooms</label>
                        <Select>
                            <SelectTrigger class="border-white/20 bg-white/10 text-white">
                                <SelectValue placeholder="Any" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="any">Any</SelectItem>
                                <SelectItem value="1">1+</SelectItem>
                                <SelectItem value="2">2+</SelectItem>
                                <SelectItem value="3">3+</SelectItem>
                                <SelectItem value="4">4+</SelectItem>
                                <SelectItem value="5">5+</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Button
                            size="default"
                            class="w-full bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90"
                        >
                            <Search class="mr-2 size-4" />
                            Search
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
