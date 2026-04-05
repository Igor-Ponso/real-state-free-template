<script setup lang="ts">
import { Grid3x3, Map, Search, X } from 'lucide-vue-next';

import FilterPopover from '@/components/landing/FilterPopover.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useAmenityIcons } from '@/composables/useAmenityIcons';
import type { FilterOption, PropertyFilters } from '@/types/landing';

defineProps<{
    filters?: PropertyFilters;
    selectedTypes: string[];
    selectedCities: string[];
    selectedListings: string[];
    selectedBedrooms: string[];
    selectedUnitAmenities: string[];
    selectedBuildingAmenities: string[];
    hasActiveFilters: boolean;
    isRental: boolean;
    filteredCities: FilterOption[];
}>();

const search = defineModel<string>('search', { required: true });
const selectedSort = defineModel<string>('selectedSort', { required: true });
const minPrice = defineModel<string>('minPrice', { required: true });
const maxPrice = defineModel<string>('maxPrice', { required: true });
const viewMode = defineModel<'grid' | 'map'>('viewMode', { required: true });
const citySearch = defineModel<string>('citySearch', { required: true });

const emit = defineEmits<{
    apply: [];
    clear: [];
    'toggle-multi': [arr: string[], val: string];
}>();

const { getIcon } = useAmenityIcons();

const LABEL = 'mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase';
const CHECK = 'flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted';

const multiLabel = (selected: string[], options: FilterOption[] | undefined, fallback: string): string => {
    if (!selected.length) {return fallback;}

    if (selected.length === 1) {return options?.find((o) => o.slug === selected[0])?.name ?? selected[0];}

    return `${selected.length} selected`;
};
</script>

<template>
    <div class="flex flex-wrap items-end gap-3">
        <!-- Search -->
        <div>
            <label :class="LABEL">Search</label>
            <div class="relative">
                <Search class="absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-white/40" />
                <Input v-model="search" placeholder="Address, name..." class="h-9 w-40 border-white/10 bg-white/5 pl-9 text-sm text-white placeholder:text-white/30" />
            </div>
        </div>

        <!-- Property Type -->
        <FilterPopover label="Property Type" :trigger-text="multiLabel(selectedTypes, filters?.propertyTypes, 'All Types')">
            <label v-for="t in filters?.propertyTypes" :key="t.slug" :class="CHECK">
                <Checkbox :checked="selectedTypes.includes(t.slug)" @update:checked="emit('toggle-multi', selectedTypes, t.slug)" />
                {{ t.name }}
            </label>
        </FilterPopover>

        <!-- City -->
        <FilterPopover label="City" :trigger-text="multiLabel(selectedCities, filters?.cities, 'All Cities')">
            <template #default>
                <Input v-model="citySearch" placeholder="Search city..." class="mb-2 h-8 text-xs" />
                <label v-for="c in filteredCities" :key="c.slug" :class="CHECK">
                    <Checkbox :checked="selectedCities.includes(c.slug)" @update:checked="emit('toggle-multi', selectedCities, c.slug)" />
                    {{ c.name }}
                </label>
                <p v-if="!filteredCities.length" class="px-2 py-1.5 text-xs text-muted-foreground">No cities found</p>
            </template>
        </FilterPopover>

        <!-- Buy / Rent -->
        <FilterPopover label="Buy / Rent" :trigger-text="multiLabel(selectedListings, filters?.listingTypes, 'All')" trigger-width="w-28" content-width="w-40" :scrollable="false">
            <label v-for="l in filters?.listingTypes" :key="l.slug" :class="CHECK">
                <Checkbox :checked="selectedListings.includes(l.slug)" @update:checked="emit('toggle-multi', selectedListings, l.slug)" />
                {{ l.name }}
            </label>
        </FilterPopover>

        <!-- Bedrooms -->
        <FilterPopover label="Bedrooms" :trigger-text="selectedBedrooms.length ? selectedBedrooms.join(', ') + '+' : 'Any'" trigger-width="w-28" content-width="w-32" :scrollable="false">
            <label v-for="n in 6" :key="n" :class="CHECK">
                <Checkbox :checked="selectedBedrooms.includes(String(n))" @update:checked="emit('toggle-multi', selectedBedrooms, String(n))" />
                {{ n }}+
            </label>
        </FilterPopover>

        <!-- Unit Features -->
        <FilterPopover label="Unit Features" :trigger-text="selectedUnitAmenities.length ? selectedUnitAmenities.length + ' selected' : 'Any'" content-width="w-52">
            <label v-for="a in filters?.unitAmenities" :key="a.slug" :class="CHECK">
                <Checkbox :checked="selectedUnitAmenities.includes(a.slug)" @update:checked="emit('toggle-multi', selectedUnitAmenities, a.slug)" />
                <component :is="getIcon(a.slug)" v-if="getIcon(a.slug)" class="size-3.5 text-landing-gold/60" />
                {{ a.name }}
            </label>
        </FilterPopover>

        <!-- Building -->
        <FilterPopover label="Building" :trigger-text="selectedBuildingAmenities.length ? selectedBuildingAmenities.length + ' selected' : 'Any'" content-width="w-52">
            <label v-for="a in filters?.buildingAmenities" :key="a.slug" :class="CHECK">
                <Checkbox :checked="selectedBuildingAmenities.includes(a.slug)" @update:checked="emit('toggle-multi', selectedBuildingAmenities, a.slug)" />
                <component :is="getIcon(a.slug)" v-if="getIcon(a.slug)" class="size-3.5 text-landing-gold/60" />
                {{ a.name }}
            </label>
        </FilterPopover>

        <!-- Price -->
        <div v-if="isRental">
            <label :class="LABEL">Min Rent</label>
            <Input v-model="minPrice" type="number" placeholder="$0" class="h-9 w-28 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30" />
        </div>
        <div>
            <label :class="LABEL">{{ isRental ? 'Max Rent' : 'Max Price' }}</label>
            <Input v-model="maxPrice" type="number" :placeholder="isRental ? '$10,000' : '$10,000,000'" class="h-9 w-28 border-white/10 bg-white/5 text-sm text-white placeholder:text-white/30" />
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
</template>
