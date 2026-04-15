<script setup lang="ts">
import { Deferred, Head, Link, usePoll } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    Eye,
    FileText,
    Inbox,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

import { show as inquiryShow } from '@/actions/App/Http/Controllers/Admin/InquiryController';
import InquiriesTrendChart from '@/components/admin/InquiriesTrendChart.vue';
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
import type {
    AdminDashboardStats,
    AdminInquiry,
    DashboardChartData,
} from '@/types/admin';

const props = defineProps<{
    role: 'admin' | 'agent';
    stats: AdminDashboardStats;
    chart: DashboardChartData;
    recentInquiries?: AdminInquiry[];
}>();

// Auto-refresh dashboard every 30s. Throttles when tab is hidden.
usePoll(30000, { only: ['stats', 'chart', 'recentInquiries'] });

type StatCard = {
    label: string;
    value: number | string;
    icon: typeof Building2;
    color: string;
};

const statCards = computed<StatCard[]>(() => {
    if (props.role === 'agent') {
        return [
            {
                label: 'My Listings',
                value: props.stats.total_properties,
                icon: Building2,
                color: 'text-landing-gold',
            },
            {
                label: 'Active',
                value: props.stats.active_properties,
                icon: TrendingUp,
                color: 'text-green-500',
            },
            {
                label: 'Unread Inquiries',
                value: props.stats.unread_inquiries,
                icon: Inbox,
                color: 'text-blue-500',
            },
            {
                label: 'Response Rate',
                value: `${props.stats.response_rate ?? 0}%`,
                icon: CheckCircle2,
                color: 'text-landing-deep-teal',
            },
        ];
    }

    return [
        {
            label: 'Total Properties',
            value: props.stats.total_properties,
            icon: Building2,
            color: 'text-landing-gold',
        },
        {
            label: 'Active Listings',
            value: props.stats.active_properties,
            icon: TrendingUp,
            color: 'text-green-500',
        },
        {
            label: 'Unread Inquiries',
            value: props.stats.unread_inquiries,
            icon: Inbox,
            color: 'text-blue-500',
        },
        {
            label: 'Total Agents',
            value: props.stats.total_agents ?? 0,
            icon: Users,
            color: 'text-landing-deep-teal',
        },
    ];
});

const secondaryCards = computed(() => {
    if (props.role === 'agent') {
        return [
            {
                label: 'Draft',
                value: props.stats.draft_properties,
                icon: FileText,
            },
            {
                label: 'Sold',
                value: props.stats.sold_properties,
                icon: CheckCircle2,
            },
            {
                label: 'Total Views',
                value: props.stats.total_views ?? 0,
                icon: Eye,
            },
            {
                label: 'Total Inquiries',
                value: props.stats.total_inquiries,
                icon: Inbox,
            },
        ];
    }

    return [
        {
            label: 'Draft Properties',
            value: props.stats.draft_properties,
            icon: FileText,
        },
        {
            label: 'Sold Properties',
            value: props.stats.sold_properties,
            icon: CheckCircle2,
        },
        {
            label: 'Total Inquiries',
            value: props.stats.total_inquiries,
            icon: Inbox,
        },
        {
            label: 'Total Clients',
            value: props.stats.total_clients ?? 0,
            icon: Users,
        },
    ];
});

const pageTitle = computed(() =>
    props.role === 'agent' ? 'Agent Dashboard' : 'Admin Dashboard',
);
const pageSubtitle = computed(() =>
    props.role === 'agent'
        ? 'Your listings, inquiries, and response performance'
        : 'Platform-wide performance across properties, inquiries, and users',
);
</script>

<template>
    <Head :title="pageTitle" />

    <div class="space-y-8 p-6">
        <div>
            <h1 class="font-serif text-3xl font-semibold tracking-tight">
                {{ pageTitle }}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ pageSubtitle }}</p>
        </div>

        <!-- Primary stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card
                v-for="stat in statCards"
                :key="stat.label"
                class="overflow-hidden"
            >
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
                    <p class="font-serif text-3xl font-semibold tabular-nums">
                        {{ stat.value }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Chart + secondary stats -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle class="text-base font-semibold"
                        >Inquiries — last 30 days</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <InquiriesTrendChart :chart="chart" label="Inquiries" />
                </CardContent>
            </Card>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-1">
                <Card v-for="stat in secondaryCards" :key="stat.label">
                    <CardContent class="flex items-center gap-3 pt-6">
                        <component
                            :is="stat.icon"
                            class="size-5 text-muted-foreground"
                        />
                        <div>
                            <p class="text-xs text-muted-foreground">
                                {{ stat.label }}
                            </p>
                            <p
                                class="font-serif text-xl font-semibold tabular-nums"
                            >
                                {{ stat.value }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Recent inquiries -->
        <Card>
            <CardHeader>
                <CardTitle class="text-base font-semibold"
                    >Recent Inquiries</CardTitle
                >
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
                                        class="font-medium hover:text-landing-gold hover:underline"
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
