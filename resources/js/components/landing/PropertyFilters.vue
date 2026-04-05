<script setup lang="ts">
import { ChevronDown, Grid3x3, Map, Search, X } from 'lucide-vue-next';
import { computed } from 'vue';

import MobileFilterSheet from '@/components/landing/MobileFilterSheet.vue';
import MultiSelectFilter from '@/components/landing/MultiSelectFilter.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type { FilterOption, PropertyFilters } from '@/types/landing';

defineProps<{
    filters?: PropertyFilters;
    isStudio: boolean;
    isRental: boolean;
    hasActiveFilters: boolean;
    filteredCities: FilterOption[];
}>();

const selectedTypes = defineModel<string[]>('selectedTypes', { required: true });
const selectedCities = defineModel<string[]>('selectedCities', { required: true });
const selectedListings = defineModel<string[]>('selectedListings', { required: true });
const selectedBedrooms = defineModel<string[]>('selectedBedrooms', { required: true });
const selectedBathrooms = defineModel<string[]>('selectedBathrooms', { required: true });
const selectedUnitAmenities = defineModel<string[]>('selectedUnitAmenities', { required: true });
const selectedBuildingAmenities = defineModel<string[]>('selectedBuildingAmenities', { required: true });
const selectedSort = defineModel<string>('selectedSort', { required: true });
const minPrice = defineModel<string>('minPrice', { required: true });
const maxPrice = defineModel<string>('maxPrice', { required: true });
const viewMode = defineModel<'grid' | 'map'>('viewMode', { required: true });
const bedroomsExact = defineModel<boolean>('bedroomsExact', { required: true });
const bathroomsExact = defineModel<boolean>('bathroomsExact', { required: true });

const emit = defineEmits<{
    apply: [];
    clear: [];
}>();

const LABEL = 'mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase';
const TRIGGER = 'flex h-9 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white';
const CHIP = 'flex h-8 items-center justify-center rounded-full border px-3 text-xs font-medium transition-all cursor-pointer';
const CHIP_ACTIVE = 'border-landing-gold bg-landing-gold text-white';
const CHIP_INACTIVE = 'border-white/10 bg-white/5 text-white/60 hover:border-white/20 hover:text-white';

const selectSingle = (arr: string[], val: string) => {
    if (arr.includes(val)) {
        arr.splice(0);
    } else {
        arr.splice(0, arr.length, val);
    }
};

const listingValue = computed({
    get: () => selectedListings.value[0] ?? '',
    set: (val: string) => {
        if (val) {
            selectedListings.value = [val];
        } else {
            selectedListings.value = [];
        }
    },
});

const activeFilterCount = computed(() =>
    [selectedTypes.value, selectedCities.value, selectedListings.value, selectedBedrooms.value, selectedBathrooms.value, selectedUnitAmenities.value, selectedBuildingAmenities.value]
        .filter((arr) => arr.length > 0).length
    + (minPrice.value ? 1 : 0)
    + (maxPrice.value ? 1 : 0),
);
</script>

<template>
    <div>
        <!-- Mobile -->
        <MobileFilterSheet :active-count="activeFilterCount" @apply="emit('apply')" @clear="emit('clear')">
            <div class="space-y-4">
                <slot />
            </div>
        </MobileFilterSheet>

        <!-- Desktop -->
        <div class="hidden space-y-3 md:block">
            <!-- Row 1: Buy/Rent toggle + Property Type + City + View toggle -->
            <div class="flex items-end gap-3">
                <!-- Buy / Rent: ToggleGroup single -->
                <div class="shrink-0">
                    <label :class="LABEL">Buy / Rent</label>
                    <ToggleGroup
                        type="single"
                        :model-value="listingValue"
                        class="rounded-md border border-white/10"
                        @update:model-value="(v: unknown) => { listingValue = (v as string) ?? ''; }"
                    >
                        <ToggleGroupItem
                            v-for="l in filters?.listingTypes"
                            :key="l.slug"
                            :value="l.slug"
                            class="h-9 px-4 text-sm data-[state=on]:bg-landing-gold data-[state=on]:text-white"
                        >
                            {{ l.name }}
                        </ToggleGroupItem>
                    </ToggleGroup>
                </div>

                <!-- Property Type -->
                <div class="min-w-52 flex-1">
                    <MultiSelectFilter v-model="selectedTypes" label="Property Type" :options="filters?.propertyTypes ?? []" placeholder="All Types" />
                </div>

                <!-- City -->
                <div class="min-w-52 flex-1">
                    <MultiSelectFilter v-model="selectedCities" label="City" :options="filteredCities" placeholder="All Cities" />
                </div>

                <!-- View toggle -->
                <ToggleGroup type="single" :model-value="viewMode" class="shrink-0 rounded-md border border-white/10" @update:model-value="(v: unknown) => { if (v === 'grid' || v === 'map') viewMode = v; }">
                    <ToggleGroupItem value="grid" aria-label="Grid view" class="size-9 rounded-l-md data-[state=on]:bg-landing-gold data-[state=on]:text-white">
                        <Grid3x3 class="size-4" />
                    </ToggleGroupItem>
                    <ToggleGroupItem value="map" aria-label="Map view" class="size-9 rounded-r-md data-[state=on]:bg-landing-gold data-[state=on]:text-white">
                        <Map class="size-4" />
                    </ToggleGroupItem>
                </ToggleGroup>
            </div>

            <!-- Row 2: Bed/Bath + Amenities + Price + Sort -->
            <div class="flex items-end gap-3">
                <!-- Bed / Bath -->
                <div class="w-52 shrink-0">
                    <label :class="LABEL">Bed / Bath</label>
                    <Popover>
                        <PopoverTrigger as-child>
                            <button :class="[TRIGGER, 'w-full gap-2']">
                                <template v-if="selectedBedrooms.length || selectedBathrooms.length">
                                    <Badge
                                        v-for="bd in selectedBedrooms"
                                        :key="'bd' + bd"
                                        class="h-6 border-0 bg-landing-gold/20 px-1.5 text-2xs text-landing-gold"
                                    >
                                        {{ bd }}{{ bedroomsExact ? '' : '+' }} bd
                                    </Badge>
                                    <Badge
                                        v-for="ba in selectedBathrooms"
                                        :key="'ba' + ba"
                                        class="h-6 border-0 bg-landing-gold/20 px-1.5 text-2xs text-landing-gold"
                                    >
                                        {{ ba }}{{ bathroomsExact ? '' : '+' }} ba
                                    </Badge>
                                </template>
                                <span v-else class="text-white/50">Any</span>
                                <ChevronDown class="ml-auto size-3.5 shrink-0 text-white/40" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-72 p-4" align="start">
                            <div class="flex items-center justify-between">
                                <p class="font-body text-xs font-medium text-white/50">Bedrooms</p>
                                <label v-if="!isStudio && selectedBedrooms.length" class="flex items-center gap-1.5 font-body text-2xs text-white/40">
                                    <Switch v-model:checked="bedroomsExact" class="scale-75" />
                                    Exact
                                </label>
                            </div>
                            <div v-if="isStudio" class="mt-2 rounded-md bg-white/5 px-3 py-2">
                                <p class="font-body text-xs text-white/40">Studio selected</p>
                            </div>
                            <div v-else class="mt-2 flex gap-2">
                                <button :class="[CHIP, !selectedBedrooms.length ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="selectedBedrooms.splice(0)">Any</button>
                                <button v-for="n in 4" :key="n" :class="[CHIP, selectedBedrooms.includes(String(n)) ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="selectSingle(selectedBedrooms, String(n))">
                                    {{ n }}{{ bedroomsExact ? '' : '+' }}
                                </button>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="font-body text-xs font-medium text-white/50">Bathrooms</p>
                                <label v-if="selectedBathrooms.length" class="flex items-center gap-1.5 font-body text-2xs text-white/40">
                                    <Switch v-model:checked="bathroomsExact" class="scale-75" />
                                    Exact
                                </label>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <button :class="[CHIP, !selectedBathrooms.length ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="selectedBathrooms.splice(0)">Any</button>
                                <button v-for="n in ['1', '1.5', '2', '3']" :key="n" :class="[CHIP, selectedBathrooms.includes(n) ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="selectSingle(selectedBathrooms, n)">
                                    {{ n }}{{ bathroomsExact ? '' : '+' }}
                                </button>
                            </div>
                        </PopoverContent>
                    </Popover>
                </div>

                <!-- Unit Features -->
                <div class="min-w-40 flex-1">
                    <MultiSelectFilter v-model="selectedUnitAmenities" label="Unit Features" :options="filters?.unitAmenities ?? []" />
                </div>

                <!-- Building -->
                <div class="min-w-40 flex-1">
                    <MultiSelectFilter v-model="selectedBuildingAmenities" label="Building" :options="filters?.buildingAmenities ?? []" />
                </div>

                <!-- Price -->
                <div v-if="isRental" class="w-28 shrink-0">
                    <label :class="LABEL">Min Rent</label>
                    <Input v-model="minPrice" type="number" placeholder="$0" class="h-9 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                </div>
                <div class="w-32 shrink-0">
                    <label :class="LABEL">{{ isRental ? 'Max Rent' : 'Max Price' }}</label>
                    <Input v-model="maxPrice" type="number" :placeholder="isRental ? '$10,000' : '$10,000,000'" class="h-9 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                </div>

                <!-- Sort -->
                <div class="w-36 shrink-0">
                    <label :class="LABEL">Sort By</label>
                    <Select v-model="selectedSort">
                        <SelectTrigger class="h-9 border-white/10 bg-white/5 text-sm text-white">
                            <SelectValue placeholder="Newest" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="newest">Newest</SelectItem>
                            <SelectItem value="oldest">Oldest</SelectItem>
                            <SelectItem value="price_asc">Price: Low → High</SelectItem>
                            <SelectItem value="price_desc">Price: High → Low</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Row 3: Actions -->
            <div class="flex items-center gap-3">
                <Button class="h-9 bg-landing-gold px-6 font-body text-landing-gold-foreground hover:bg-landing-gold/90" @click="emit('apply')">
                    <Search class="mr-2 size-4" /> Search Properties
                </Button>
                <Button v-if="hasActiveFilters" variant="ghost" class="h-9 text-white/50 hover:text-white" @click="emit('clear')">
                    <X class="mr-1.5 size-4" /> Clear All Filters
                </Button>
            </div>
        </div>
    </div>
</template>
