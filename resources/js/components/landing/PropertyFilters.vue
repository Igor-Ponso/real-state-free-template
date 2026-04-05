<script setup lang="ts">
import { ChevronDown, Grid3x3, Map, Search, X } from 'lucide-vue-next';
import { computed } from 'vue';

import MobileFilterSheet from '@/components/landing/MobileFilterSheet.vue';
import MultiSelectFilter from '@/components/landing/MultiSelectFilter.vue';
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

const search = defineModel<string>('search', { required: true });
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

const toggleChip = (arr: string[], val: string) => {
    const idx = arr.indexOf(val);

    if (idx >= 0) {
        arr.splice(idx, 1);
    } else {
        arr.push(val);
    }
};

const activeFilterCount = computed(() =>
    [selectedTypes.value, selectedCities.value, selectedListings.value, selectedBedrooms.value, selectedBathrooms.value, selectedUnitAmenities.value, selectedBuildingAmenities.value]
        .filter((arr) => arr.length > 0).length
    + (minPrice.value ? 1 : 0)
    + (maxPrice.value ? 1 : 0)
    + (search.value ? 1 : 0),
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
        <div class="hidden flex-wrap items-end gap-3 md:flex">
            <!-- Search -->
            <div>
                <label :class="LABEL">Search</label>
                <div class="relative">
                    <Search class="absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-white/40" />
                    <Input v-model="search" placeholder="Address, name..." class="h-9 w-40 border-white/10 bg-white/5 pl-9 text-sm text-white placeholder:text-white/30" />
                </div>
            </div>

            <!-- Property Type -->
            <div class="w-44">
                <MultiSelectFilter v-model="selectedTypes" label="Property Type" :options="filters?.propertyTypes ?? []" placeholder="All Types" />
            </div>

            <!-- City -->
            <div class="w-44">
                <MultiSelectFilter v-model="selectedCities" label="City" :options="filteredCities" placeholder="All Cities" />
            </div>

            <!-- Buy / Rent -->
            <div class="w-36">
                <MultiSelectFilter v-model="selectedListings" label="Buy / Rent" :options="filters?.listingTypes ?? []" placeholder="All" />
            </div>

            <!-- Bed / Bath -->
            <div>
                <label :class="LABEL">Bed / Bath</label>
                <Popover>
                    <PopoverTrigger as-child>
                        <button :class="[TRIGGER, 'w-36']">
                            <span class="truncate">
                                {{ isStudio ? 'Studio' : selectedBedrooms.length ? selectedBedrooms.join(', ') + (bedroomsExact ? ' bd' : '+ bd') : 'Any' }}
                                {{ selectedBathrooms.length ? ' / ' + selectedBathrooms.join(', ') + (bathroomsExact ? ' ba' : '+ ba') : '' }}
                            </span>
                            <ChevronDown class="ml-1 size-3.5 shrink-0 text-white/40" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent class="w-80 p-4" align="start">
                        <div class="flex items-center justify-between">
                            <p class="font-body text-xs font-medium text-white/50">Bedrooms</p>
                            <label v-if="!isStudio && selectedBedrooms.length" class="flex items-center gap-1.5 font-body text-2xs text-white/40">
                                <Switch v-model:checked="bedroomsExact" class="scale-75" />
                                Exact
                            </label>
                        </div>
                        <div v-if="isStudio" class="mt-2 rounded-md bg-white/5 px-3 py-2">
                            <p class="font-body text-xs text-white/40">Studio selected — bedrooms filter disabled</p>
                        </div>
                        <div v-else class="mt-2 flex flex-wrap gap-2">
                            <button :class="[CHIP, !selectedBedrooms.length ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="selectedBedrooms.splice(0)">Any</button>
                            <button v-for="n in 6" :key="n" :class="[CHIP, selectedBedrooms.includes(String(n)) ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="toggleChip(selectedBedrooms, String(n))">
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
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button :class="[CHIP, !selectedBathrooms.length ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="selectedBathrooms.splice(0)">Any</button>
                            <button v-for="n in ['1', '1.5', '2', '2.5', '3', '4']" :key="n" :class="[CHIP, selectedBathrooms.includes(n) ? CHIP_ACTIVE : CHIP_INACTIVE]" @click="toggleChip(selectedBathrooms, n)">
                                {{ n }}{{ bathroomsExact ? '' : '+' }}
                            </button>
                        </div>
                    </PopoverContent>
                </Popover>
            </div>

            <!-- Unit Features -->
            <div class="w-44">
                <MultiSelectFilter v-model="selectedUnitAmenities" label="Unit Features" :options="filters?.unitAmenities ?? []" />
            </div>

            <!-- Building -->
            <div class="w-44">
                <MultiSelectFilter v-model="selectedBuildingAmenities" label="Building" :options="filters?.buildingAmenities ?? []" />
            </div>

            <!-- Price -->
            <div v-if="isRental">
                <label :class="LABEL">Min Rent</label>
                <Input v-model="minPrice" type="number" placeholder="$0" class="h-9 w-28 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
            </div>
            <div>
                <label :class="LABEL">{{ isRental ? 'Max Rent' : 'Max Price' }}</label>
                <Input v-model="maxPrice" type="number" :placeholder="isRental ? '$10,000' : '$10,000,000'" class="h-9 w-28 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
            </div>

            <!-- Sort -->
            <div>
                <label :class="LABEL">Sort By</label>
                <Select v-model="selectedSort">
                    <SelectTrigger class="h-9 w-36 border-white/10 bg-white/5 text-sm text-white">
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

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <Button size="sm" class="h-9 bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90" @click="emit('apply')">
                    <Search class="mr-1.5 size-3.5" /> Search
                </Button>
                <Button v-if="hasActiveFilters" variant="ghost" size="sm" class="h-9 text-white/50 hover:text-white" @click="emit('clear')">
                    <X class="mr-1 size-3.5" /> Clear
                </Button>
            </div>

            <div class="flex-1" />

            <!-- View toggle -->
            <ToggleGroup type="single" :model-value="viewMode" class="rounded-md border border-white/10" @update:model-value="(v: unknown) => { if (v === 'grid' || v === 'map') viewMode = v; }">
                <ToggleGroupItem value="grid" aria-label="Grid view" class="size-9 rounded-l-md data-[state=on]:bg-landing-gold data-[state=on]:text-white">
                    <Grid3x3 class="size-4" />
                </ToggleGroupItem>
                <ToggleGroupItem value="map" aria-label="Map view" class="size-9 rounded-r-md data-[state=on]:bg-landing-gold data-[state=on]:text-white">
                    <Map class="size-4" />
                </ToggleGroupItem>
            </ToggleGroup>
        </div>
    </div>
</template>
