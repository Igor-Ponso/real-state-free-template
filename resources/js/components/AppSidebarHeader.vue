<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
        title?: string;
        subtitle?: string;
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex shrink-0 flex-col gap-2 border-b border-sidebar-border/70 px-6 py-3 transition-[padding] ease-linear md:px-6"
    >
        <!-- Top row: sidebar trigger + page title with inline subtitle -->
        <div class="flex items-baseline gap-3">
            <SidebarTrigger class="-ml-1 self-center" />
            <h1
                v-if="title"
                class="font-serif text-2xl font-semibold tracking-tight"
            >
                {{ title }}
            </h1>
            <span
                v-if="subtitle"
                aria-hidden="true"
                class="hidden text-muted-foreground/40 sm:inline"
            >
                ·
            </span>
            <p
                v-if="subtitle"
                class="hidden text-sm text-muted-foreground sm:block"
            >
                {{ subtitle }}
            </p>
        </div>

        <!-- Breadcrumbs below — clickable trail for context. -->
        <div v-if="breadcrumbs.length > 0" class="pl-9">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </div>
    </header>
</template>
