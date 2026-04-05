<script setup lang="ts">
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import type { PaginatedResponse } from '@/types/landing';

withDefaults(
    defineProps<{
        meta: PaginatedResponse<unknown>['meta'];
        variant?: 'full' | 'compact';
    }>(),
    { variant: 'full' },
);

const emit = defineEmits<{
    'page-change': [page: number];
}>();
</script>

<template>
    <div v-if="meta.last_page > 1">
        <!-- Full variant (grid view) -->
        <div v-if="variant === 'full'" class="mt-10 flex items-center justify-between">
            <p class="font-body text-xs text-white/30">
                Page {{ meta.current_page }} of {{ meta.last_page }}
            </p>
            <Pagination
                :total="meta.total"
                :items-per-page="meta.per_page"
                :default-page="meta.current_page"
                :sibling-count="1"
                show-edges
                @update:page="(p: number) => emit('page-change', p)"
            >
                <PaginationContent v-slot="{ items }" class="gap-1">
                    <PaginationPrevious class="text-white/60 hover:text-white" />
                    <template v-for="(item, index) in items" :key="index">
                        <button
                            v-if="item.type === 'page'"
                            class="inline-flex size-9 items-center justify-center rounded-md font-body text-sm transition-all"
                            :class="item.value === meta.current_page
                                ? 'bg-landing-gold font-semibold text-white shadow-md'
                                : 'text-white/50 hover:bg-white/10 hover:text-white'"
                            @click="emit('page-change', item.value)"
                        >
                            {{ item.value }}
                        </button>
                        <PaginationEllipsis v-else :index="index" class="text-white/30" />
                    </template>
                    <PaginationNext class="text-white/60 hover:text-white" />
                </PaginationContent>
            </Pagination>
        </div>

        <!-- Compact variant (map sidebar) -->
        <Pagination
            v-else
            :total="meta.total"
            :items-per-page="meta.per_page"
            :default-page="meta.current_page"
            :sibling-count="0"
            class="mt-4"
            @update:page="(p: number) => emit('page-change', p)"
        >
            <PaginationContent v-slot="{ items }">
                <PaginationPrevious />
                <template v-for="(item, index) in items" :key="index">
                    <PaginationItem
                        v-if="item.type === 'page'"
                        :value="item.value"
                        :is-active="item.value === meta.current_page"
                    />
                    <PaginationEllipsis v-else :index="index" />
                </template>
                <PaginationNext />
            </PaginationContent>
        </Pagination>
    </div>
</template>
