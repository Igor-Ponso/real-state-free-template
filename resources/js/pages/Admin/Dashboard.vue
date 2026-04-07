<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import { Building2, Inbox, TrendingUp, Users } from 'lucide-vue-next';

import { show as inquiryShow } from '@/actions/App/Http/Controllers/Admin/InquiryController';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { AdminDashboardStats, AdminInquiry } from '@/types/admin';

defineProps<{
    stats: AdminDashboardStats;
    recentInquiries?: AdminInquiry[];
}>();

const statCards = (stats: AdminDashboardStats) => [
    {
        label: 'Total Properties',
        value: stats.total_properties,
        icon: Building2,
        color: 'text-landing-gold',
    },
    {
        label: 'Active Listings',
        value: stats.active_properties,
        icon: TrendingUp,
        color: 'text-green-500',
    },
    {
        label: 'Unread Inquiries',
        value: stats.unread_inquiries,
        icon: Inbox,
        color: 'text-blue-500',
    },
    {
        label: 'Total Agents',
        value: stats.total_agents,
        icon: Users,
        color: 'text-purple-500',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="space-y-6 p-6">
        <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>

        <!-- Stats grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="stat in statCards(stats)" :key="stat.label">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ stat.label }}
                    </CardTitle>
                    <component
                        :is="stat.icon"
                        :class="['size-5', stat.color]"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold">{{ stat.value }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Secondary stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Card>
                <CardContent class="pt-6">
                    <p class="text-sm text-muted-foreground">
                        Draft Properties
                    </p>
                    <p class="text-2xl font-bold">
                        {{ stats.draft_properties }}
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-sm text-muted-foreground">Sold Properties</p>
                    <p class="text-2xl font-bold">
                        {{ stats.sold_properties }}
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-sm text-muted-foreground">Total Inquiries</p>
                    <p class="text-2xl font-bold">
                        {{ stats.total_inquiries }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Recent inquiries -->
        <Card>
            <CardHeader>
                <CardTitle>Recent Inquiries</CardTitle>
            </CardHeader>
            <CardContent>
                <Deferred data="recentInquiries">
                    <template #fallback>
                        <div class="space-y-3">
                            <Skeleton
                                v-for="n in 5"
                                :key="n"
                                class="h-12 w-full"
                            />
                        </div>
                    </template>

                    <Table v-if="recentInquiries?.length">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Property</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="inquiry in recentInquiries"
                                :key="inquiry.id"
                                class="cursor-pointer hover:bg-muted/50"
                            >
                                <TableCell>
                                    <Link
                                        :href="inquiryShow.url(inquiry.id)"
                                        class="font-medium hover:underline"
                                    >
                                        {{ inquiry.name }}
                                    </Link>
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ inquiry.property_title }}
                                </TableCell>
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400':
                                                inquiry.status_slug === 'new',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400':
                                                inquiry.status_slug === 'read',
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400':
                                                inquiry.status_slug ===
                                                'replied',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400':
                                                inquiry.status_slug ===
                                                'archived',
                                        }"
                                    >
                                        {{ inquiry.status }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{
                                        new Date(
                                            inquiry.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <p
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        No inquiries yet.
                    </p>
                </Deferred>
            </CardContent>
        </Card>
    </div>
</template>
