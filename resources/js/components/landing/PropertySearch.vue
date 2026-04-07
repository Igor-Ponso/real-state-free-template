<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

import BedBathFilter from '@/components/landing/BedBathFilter.vue';
import MultiSelectFilter from '@/components/landing/MultiSelectFilter.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';
import type { FilterOption } from '@/types/landing';

defineProps<{
    propertyTypes: FilterOption[];
    listingTypes: FilterOption[];
    cities: FilterOption[];
}>();

const { target: sectionRef, isVisible } = useFadeInOnScroll();

const isMounted = ref(false);
const selectedTypes = ref<string[]>([]);
const selectedListings = ref<string[]>([]);
const selectedCities = ref<string[]>([]);
const selectedBedrooms = ref<string[]>([]);
const selectedBathrooms = ref<string[]>([]);
const bedroomsExact = ref(false);
const bathroomsExact = ref(false);
const maxPrice = ref('');

onMounted(() => {
    isMounted.value = true;
});

const listingValue = computed({
    get: () => selectedListings.value[0] ?? '',
    set: (val: string) => {
        selectedListings.value = val ? [val] : [];
    },
});

const isRental = computed(
    () =>
        selectedListings.value.includes('rental') &&
        !selectedListings.value.includes('sale'),
);

const handleSearch = () => {
    const params: Record<string, string | string[]> = {};

    if (selectedListings.value.length) {
        params.listing = selectedListings.value;
    }

    if (selectedTypes.value.length) {
        params.type = selectedTypes.value;
    }

    if (selectedCities.value.length) {
        params.city = selectedCities.value;
    }

    if (selectedBedrooms.value.length) {
        params.bedrooms = selectedBedrooms.value;

        if (bedroomsExact.value) {
            params.bedrooms_exact = '1';
        }
    }

    if (selectedBathrooms.value.length) {
        params.bathrooms = selectedBathrooms.value;

        if (bathroomsExact.value) {
            params.bathrooms_exact = '1';
        }
    }

    if (maxPrice.value) {
        params.max_price = maxPrice.value;
    }

    router.visit('/properties', { data: params });
};
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
                    Experience Luxury Living
                </p>
                <h2
                    class="mt-4 font-serif text-3xl font-semibold tracking-tight text-white md:text-5xl"
                >
                    Find Your Dream Property
                </h2>
                <p
                    class="mx-auto mt-4 max-w-xl font-body text-lg font-light text-white/60"
                >
                    From grand foyers to intimate gardens — search our exclusive
                    collection of properties designed for those who appreciate
                    the extraordinary.
                </p>
            </div>

            <form
                class="space-y-4 rounded-2xl border border-white/10 bg-white/10 p-8 backdrop-blur-md transition-all delay-300 duration-700"
                :class="
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-8 opacity-0'
                "
                @submit.prevent="handleSearch"
            >
                <!-- Row 1: Buy/Rent + Type + City -->
                <div class="flex flex-col items-end gap-4 md:flex-row">
                    <div class="w-full shrink-0 md:w-auto">
                        <label
                            class="mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase"
                            >Buy / Rent</label
                        >
                        <ToggleGroup
                            type="single"
                            :model-value="listingValue"
                            class="rounded-md border border-white/20 bg-white/5"
                            @update:model-value="
                                (v: unknown) => {
                                    listingValue = (v as string) ?? '';
                                }
                            "
                        >
                            <ToggleGroupItem
                                v-for="l in listingTypes"
                                :key="l.slug"
                                :value="l.slug"
                                class="h-9 px-5 text-sm text-white/60 data-[state=on]:bg-landing-gold data-[state=on]:text-white"
                            >
                                {{ l.name }}
                            </ToggleGroupItem>
                        </ToggleGroup>
                    </div>

                    <div class="min-w-0 flex-1">
                        <MultiSelectFilter
                            v-model="selectedTypes"
                            label="Property Type"
                            :options="propertyTypes"
                            placeholder="All Types"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <MultiSelectFilter
                            v-model="selectedCities"
                            label="Location"
                            :options="cities"
                            placeholder="All Cities"
                        />
                    </div>
                </div>

                <!-- Row 2: Bed/Bath + Price + Search -->
                <div class="flex flex-col items-end gap-4 md:flex-row">
                    <BedBathFilter
                        v-model:selected-bedrooms="selectedBedrooms"
                        v-model:selected-bathrooms="selectedBathrooms"
                        v-model:bedrooms-exact="bedroomsExact"
                        v-model:bathrooms-exact="bathroomsExact"
                        :is-studio="false"
                    />

                    <div class="w-full shrink-0 md:w-36">
                        <label
                            class="mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase"
                        >
                            {{ isRental ? 'Max Rent' : 'Max Price' }}
                        </label>
                        <Input
                            v-model="maxPrice"
                            type="number"
                            :placeholder="isRental ? '$10,000' : '$10,000,000'"
                            class="h-9 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                        />
                    </div>

                    <div class="w-full shrink-0 md:w-auto">
                        <Button
                            type="submit"
                            class="h-9 w-full bg-landing-gold px-8 font-body text-landing-gold-foreground hover:bg-landing-gold/90 md:w-auto"
                        >
                            <Search class="mr-2 size-4" />
                            Search Properties
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</template>
