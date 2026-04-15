<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const {
    breadcrumbs = [],
    title,
    subtitle,
} = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
    subtitle?: string;
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :title="title" :subtitle="subtitle">
        <slot />
    </AppLayout>
    <Toaster />
</template>
