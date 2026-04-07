<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Switch } from '@/components/ui/switch';

defineProps<{
    isStudio: boolean;
}>();

const selectedBedrooms = defineModel<string[]>('selectedBedrooms', {
    required: true,
});
const selectedBathrooms = defineModel<string[]>('selectedBathrooms', {
    required: true,
});
const bedroomsExact = defineModel<boolean>('bedroomsExact', { required: true });
const bathroomsExact = defineModel<boolean>('bathroomsExact', {
    required: true,
});

const LABEL =
    'mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase';
const TRIGGER =
    'flex h-9 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white';
const CHIP =
    'flex h-8 items-center justify-center rounded-full border px-3 text-xs font-medium transition-all cursor-pointer';
const CHIP_ACTIVE = 'border-landing-gold bg-landing-gold text-white';
const CHIP_INACTIVE =
    'border-white/10 bg-white/5 text-white/60 hover:border-white/20 hover:text-white';

const BEDROOM_OPTIONS = [1, 2, 3, 4];
const BATHROOM_OPTIONS = ['1', '1.5', '2', '3'];

const selectSingle = (arr: string[], val: string) => {
    if (arr.includes(val)) {
        arr.splice(0);
    } else {
        arr.splice(0, arr.length, val);
    }
};
</script>

<template>
    <div class="w-44 shrink-0">
        <label :class="LABEL">Bed / Bath</label>
        <Popover>
            <PopoverTrigger as-child>
                <button
                    aria-label="Filter by bedrooms and bathrooms"
                    :class="[TRIGGER, 'w-full gap-2']"
                >
                    <template
                        v-if="
                            selectedBedrooms.length || selectedBathrooms.length
                        "
                    >
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
                    <ChevronDown
                        class="ml-auto size-3.5 shrink-0 text-white/40"
                    />
                </button>
            </PopoverTrigger>
            <PopoverContent class="w-72 p-4" align="start">
                <!-- Bedrooms -->
                <div class="flex items-center justify-between">
                    <p class="font-body text-xs font-medium text-white/50">
                        Bedrooms
                    </p>
                    <label
                        v-if="!isStudio && selectedBedrooms.length"
                        class="flex items-center gap-1.5 font-body text-2xs text-white/40"
                    >
                        <Switch
                            v-model:checked="bedroomsExact"
                            class="scale-75"
                        />
                        Exact
                    </label>
                </div>
                <div
                    v-if="isStudio"
                    class="mt-2 rounded-md bg-white/5 px-3 py-2"
                >
                    <p class="font-body text-xs text-white/40">
                        Studio selected
                    </p>
                </div>
                <div v-else class="mt-2 flex gap-2">
                    <button
                        :class="[
                            CHIP,
                            !selectedBedrooms.length
                                ? CHIP_ACTIVE
                                : CHIP_INACTIVE,
                        ]"
                        @click="selectedBedrooms.splice(0)"
                    >
                        Any
                    </button>
                    <button
                        v-for="n in BEDROOM_OPTIONS"
                        :key="n"
                        :class="[
                            CHIP,
                            selectedBedrooms.includes(String(n))
                                ? CHIP_ACTIVE
                                : CHIP_INACTIVE,
                        ]"
                        @click="selectSingle(selectedBedrooms, String(n))"
                    >
                        {{ n }}{{ bedroomsExact ? '' : '+' }}
                    </button>
                </div>

                <!-- Bathrooms -->
                <div class="mt-4 flex items-center justify-between">
                    <p class="font-body text-xs font-medium text-white/50">
                        Bathrooms
                    </p>
                    <label
                        v-if="selectedBathrooms.length"
                        class="flex items-center gap-1.5 font-body text-2xs text-white/40"
                    >
                        <Switch
                            v-model:checked="bathroomsExact"
                            class="scale-75"
                        />
                        Exact
                    </label>
                </div>
                <div class="mt-2 flex gap-2">
                    <button
                        :class="[
                            CHIP,
                            !selectedBathrooms.length
                                ? CHIP_ACTIVE
                                : CHIP_INACTIVE,
                        ]"
                        @click="selectedBathrooms.splice(0)"
                    >
                        Any
                    </button>
                    <button
                        v-for="n in BATHROOM_OPTIONS"
                        :key="n"
                        :class="[
                            CHIP,
                            selectedBathrooms.includes(n)
                                ? CHIP_ACTIVE
                                : CHIP_INACTIVE,
                        ]"
                        @click="selectSingle(selectedBathrooms, n)"
                    >
                        {{ n }}{{ bathroomsExact ? '' : '+' }}
                    </button>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
