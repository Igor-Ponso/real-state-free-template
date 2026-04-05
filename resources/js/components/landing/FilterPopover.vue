<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';

import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';

withDefaults(
    defineProps<{
        label: string;
        triggerText: string;
        triggerWidth?: string;
        contentWidth?: string;
        scrollable?: boolean;
    }>(),
    { triggerWidth: 'w-36', contentWidth: 'w-48', scrollable: true },
);

const LABEL = 'mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase';
const TRIGGER = 'flex h-9 items-center justify-between rounded-md border border-white/10 bg-white/5 px-3 text-sm text-white';
</script>

<template>
    <div>
        <label :class="LABEL">{{ label }}</label>
        <Popover>
            <PopoverTrigger as-child>
                <button :class="[TRIGGER, triggerWidth]">
                    <span class="truncate">{{ triggerText }}</span>
                    <ChevronDown class="ml-1 size-3.5 shrink-0 text-white/40" />
                </button>
            </PopoverTrigger>
            <PopoverContent :class="[contentWidth, 'p-2']" align="start">
                <ScrollArea v-if="scrollable" class="max-h-56">
                    <div class="space-y-1">
                        <slot />
                    </div>
                </ScrollArea>
                <div v-else class="space-y-1">
                    <slot />
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
