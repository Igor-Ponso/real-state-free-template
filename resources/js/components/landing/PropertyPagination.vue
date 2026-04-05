<script setup lang="ts">
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PaginatedResponse } from '@/types/landing';

withDefaults(
    defineProps<{
        meta: PaginatedResponse<unknown>['meta'];
        variant?: 'full' | 'compact';
        perPage?: string;
        showResults?: boolean;
    }>(),
    { variant: 'full', showResults: false },
);

const emit = defineEmits<{
    'page-change': [page: number];
    'update:per-page': [value: string];
}>();
</script>

<template>
    <div v-if="meta.last_page > 1 || showResults">
        <!-- Full variant (grid view) — single row: count | pagination | per-page -->
        <div
            v-if="variant === 'full'"
            class="mt-10 grid grid-cols-3 items-center"
        >
            <!-- Results count (left) -->
            <p v-if="showResults" class="font-body text-sm text-white/40">
                {{ meta.total }}
                {{ meta.total === 1 ? 'property' : 'properties' }} found
            </p>
            <span v-else />

            <!-- Page numbers (center) -->
            <Pagination
                v-if="meta.last_page > 1"
                :total="meta.total"
                :items-per-page="meta.per_page"
                :default-page="meta.current_page"
                :sibling-count="1"
                show-edges
                class="w-auto"
                @update:page="(p: number) => emit('page-change', p)"
            >
                <PaginationContent v-slot="{ items }" class="gap-1">
                    <PaginationPrevious
                        class="text-white/60 hover:text-white"
                    />
                    <template v-for="(item, index) in items" :key="index">
                        <button
                            v-if="item.type === 'page'"
                            class="inline-flex size-9 items-center justify-center rounded-md font-body text-sm transition-all"
                            :class="
                                item.value === meta.current_page
                                    ? 'bg-landing-gold font-semibold text-white shadow-md'
                                    : 'text-white/50 hover:bg-white/10 hover:text-white'
                            "
                            @click="emit('page-change', item.value)"
                        >
                            {{ item.value }}
                        </button>
                        <PaginationEllipsis
                            v-else
                            :index="index"
                            class="text-white/30"
                        />
                    </template>
                    <PaginationNext class="text-white/60 hover:text-white" />
                </PaginationContent>
            </Pagination>
            <span v-else />

            <!-- Per-page selector (right) -->
            <div v-if="perPage" class="flex items-center justify-end gap-2">
                <label class="font-body text-xs text-white/30">Show</label>
                <Select
                    :model-value="perPage"
                    @update:model-value="
                        emit('update:per-page', $event as string)
                    "
                >
                    <SelectTrigger
                        class="h-8 w-20 border-white/10 bg-white/5 text-xs text-white"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="12">12</SelectItem>
                        <SelectItem value="24">24</SelectItem>
                        <SelectItem value="48">48</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <span v-else />
        </div>

        <!-- Compact variant (map sidebar) -->
        <Pagination
            v-else-if="meta.last_page > 1"
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
