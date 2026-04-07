<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import { show } from '@/actions/App/Http/Controllers/Admin/InquiryController';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { AdminInquiry, LookupOption } from '@/types/admin';
import type { PaginatedResponse } from '@/types/landing';

const props = defineProps<{
    inquiries: PaginatedResponse<AdminInquiry>;
    statuses: LookupOption[];
    filters: { status?: string };
}>();

const statusFilter = ref(props.filters.status ?? '');

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (statusFilter.value) {
params.status = statusFilter.value;
}

    router.visit('/admin/inquiries', { data: params, preserveState: true });
};

const statusClass = (slug: string | null) => ({
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': slug === 'new',
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': slug === 'read',
    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': slug === 'replied',
    'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400': slug === 'archived',
});
</script>

<template>
    <Head title="Inquiries" />

    <div class="space-y-6 p-6">
        <h1 class="text-2xl font-bold tracking-tight">Inquiries</h1>

        <!-- Filters -->
        <div class="flex items-center gap-3">
            <Select v-model="statusFilter" @update:model-value="applyFilters">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="All Statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="">All Statuses</SelectItem>
                    <SelectItem v-for="s in statuses" :key="s.slug" :value="s.slug">{{ s.name }}</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Table -->
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Property</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Date</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="inquiry in inquiries.data"
                    :key="inquiry.id"
                    class="cursor-pointer hover:bg-muted/50"
                >
                    <TableCell>
                        <Link :href="show.url({ inquiry: inquiry.id })" class="font-medium hover:underline">
                            {{ inquiry.name }}
                        </Link>
                    </TableCell>
                    <TableCell class="text-muted-foreground">{{ inquiry.email }}</TableCell>
                    <TableCell>
                        <Link
                            v-if="inquiry.property_slug"
                            :href="`/properties/${inquiry.property_slug}`"
                            class="text-sm text-muted-foreground hover:underline"
                        >
                            {{ inquiry.property_title }}
                        </Link>
                    </TableCell>
                    <TableCell>
                        <Badge :class="statusClass(inquiry.status_slug)">
                            {{ inquiry.status }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-muted-foreground">
                        {{ new Date(inquiry.created_at).toLocaleDateString() }}
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <p v-if="!inquiries.data.length" class="py-10 text-center text-sm text-muted-foreground">
            No inquiries found.
        </p>
    </div>
</template>
