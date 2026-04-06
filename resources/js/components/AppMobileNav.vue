<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

defineProps<{
    mainNavItems: NavItem[];
    rightNavItems: NavItem[];
}>();

const { whenCurrentUrl } = useCurrentUrl();

const activeItemStyles = 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';
</script>

<template>
    <div class="lg:hidden">
        <Sheet>
            <SheetTrigger :as-child="true">
                <slot />
            </SheetTrigger>
            <SheetContent side="left" class="w-[300px] p-6">
                <SheetTitle class="sr-only">Navigation menu</SheetTitle>
                <SheetHeader class="flex justify-start text-left">
                    <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                </SheetHeader>
                <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                    <nav class="-mx-3 space-y-1">
                        <Link
                            v-for="item in mainNavItems"
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                            :class="whenCurrentUrl(item.href, activeItemStyles)"
                        >
                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                            {{ item.title }}
                        </Link>
                    </nav>
                    <div class="flex flex-col space-y-4">
                        <a
                            v-for="item in rightNavItems"
                            :key="item.title"
                            :href="toUrl(item.href)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center space-x-2 text-sm font-medium"
                        >
                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                            <span>{{ item.title }}</span>
                        </a>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
