<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import {
    create,
    destroy,
    edit,
} from '@/actions/App/Http/Controllers/Admin/PropertyController';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import type { AdminProperty, LookupOption } from '@/types/admin';
import type { PaginatedResponse } from '@/types/landing';

const props = defineProps<{
    properties: PaginatedResponse<AdminProperty>;
    statuses: LookupOption[];
    filters: { status?: string; search?: string };
}>();

const ALL_STATUSES = 'all';
const searchQuery = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? ALL_STATUSES);

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (searchQuery.value) {
        params.search = searchQuery.value;
    }

    if (statusFilter.value && statusFilter.value !== ALL_STATUSES) {
        params.status = statusFilter.value;
    }

    router.visit('/admin/properties', { data: params, preserveState: true });
};

const deleteProperty = (slug: string) => {
    router.delete(destroy.url({ property: slug }));
};
</script>

<template>
    <Head title="Properties" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Properties</h1>
            <Button as-child>
                <Link :href="create.url()">
                    <Plus class="mr-2 size-4" /> New Property
                </Link>
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Search properties..."
                    class="w-64 pl-9"
                    @keyup.enter="applyFilters"
                />
            </div>
            <Select v-model="statusFilter" @update:model-value="applyFilters">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="All Statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL_STATUSES">All Statuses</SelectItem>
                    <SelectItem
                        v-for="s in statuses"
                        :key="s.slug"
                        :value="s.slug"
                        >{{ s.name }}</SelectItem
                    >
                </SelectContent>
            </Select>
        </div>

        <!-- Table -->
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Property</TableHead>
                    <TableHead>Price</TableHead>
                    <TableHead>City</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Inquiries</TableHead>
                    <TableHead class="w-20" />
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="property in properties.data"
                    :key="property.id"
                >
                    <TableCell>
                        <Link
                            :href="edit.url({ property: property.slug })"
                            class="font-medium hover:underline"
                        >
                            {{ property.title }}
                        </Link>
                        <div class="mt-0.5 flex gap-1">
                            <Badge
                                v-if="property.is_featured"
                                variant="secondary"
                                class="text-2xs"
                                >Featured</Badge
                            >
                            <Badge
                                v-if="!property.is_published"
                                variant="outline"
                                class="text-2xs"
                                >Draft</Badge
                            >
                        </div>
                    </TableCell>
                    <TableCell>{{ property.price }}</TableCell>
                    <TableCell class="text-muted-foreground">{{
                        property.city
                    }}</TableCell>
                    <TableCell>
                        <Badge
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400':
                                    property.status_slug === 'active',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400':
                                    property.status_slug === 'draft',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400':
                                    property.status_slug === 'sold',
                            }"
                        >
                            {{ property.status }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-muted-foreground">{{
                        property.inquiries_count
                    }}</TableCell>
                    <TableCell>
                        <AlertDialog>
                            <AlertDialogTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="size-8 p-0 text-destructive hover:text-destructive"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle
                                        >Delete Property</AlertDialogTitle
                                    >
                                    <AlertDialogDescription>
                                        "{{ property.title }}" will be
                                        soft-deleted. It can be restored later.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel
                                        >Cancel</AlertDialogCancel
                                    >
                                    <AlertDialogAction
                                        @click="deleteProperty(property.slug)"
                                        >Delete</AlertDialogAction
                                    >
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <!-- Pagination -->
        <div
            v-if="properties.meta.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Page {{ properties.meta.current_page }} of
                {{ properties.meta.last_page }} ({{ properties.meta.total }}
                properties)
            </p>
            <div class="flex gap-2">
                <Button
                    v-if="properties.links.prev"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link :href="properties.links.prev">Previous</Link>
                </Button>
                <Button
                    v-if="properties.links.next"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link :href="properties.links.next">Next</Link>
                </Button>
            </div>
        </div>
    </div>
</template>
