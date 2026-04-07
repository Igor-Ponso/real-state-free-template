<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(
    defineProps<{
        variant?: 'grid' | 'sidebar';
    }>(),
    { variant: 'grid' },
);
</script>

<template>
    <!-- Sidebar skeleton -->
    <div
        v-if="variant === 'sidebar'"
        class="flex gap-3 rounded-lg border border-white/5 bg-white/5 p-2"
    >
        <Skeleton class="size-20 shrink-0 rounded" />
        <div class="min-w-0 flex-1 space-y-2 py-1">
            <Skeleton class="h-4 w-20" />
            <Skeleton class="h-3.5 w-full" />
            <Skeleton class="h-3 w-24" />
        </div>
    </div>

    <!-- Card skeleton -->
    <Card v-else class="overflow-hidden border border-white/10 bg-white/5">
        <Skeleton class="aspect-property w-full" />
        <CardContent class="space-y-3 p-3.5">
            <Skeleton class="h-4 w-3/4" />
            <Skeleton class="h-3 w-1/2" />
            <div class="h-px bg-white/10" />
            <div class="flex gap-3">
                <Skeleton class="h-3 w-14" />
                <Skeleton class="h-3 w-14" />
                <Skeleton class="h-3 w-16" />
            </div>
        </CardContent>
    </Card>
</template>
