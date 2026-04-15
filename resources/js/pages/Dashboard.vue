<script setup lang="ts">
import { Deferred, Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import {
    Bath,
    BedDouble,
    Building2,
    Eye,
    Heart,
    Inbox,
    MapPin,
    Maximize,
} from 'lucide-vue-next';
import { computed, watchEffect } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { dashboard } from '@/routes';
import properties from '@/routes/properties';

interface DashboardPropertyCard {
    id: number;
    slug: string;
    title: string;
    price: string;
    address: string;
    city: string | null;
    bedrooms: number;
    bathrooms: number;
    area_sqft: number;
    type: string | null;
    listing: string | null;
    image: string | null;
}

interface DashboardInquiry {
    id: number;
    message: string;
    property_title: string | null;
    property_slug: string | null;
    status: string | null;
    status_slug: string | null;
    replied_at: string | null;
    created_at: string;
}

interface Stats {
    favorites: number;
    inquiries: number;
    views: number;
}

defineProps<{
    stats: Stats;
    favorites?: DashboardPropertyCard[];
    inquiries?: DashboardInquiry[];
    recentlyViewed?: DashboardPropertyCard[];
}>();

const page = usePage<{ auth: { user: { name: string } | null } }>();
const firstName = computed(
    () => page.props.auth.user?.name?.split(' ')[0] ?? 'there',
);

// Personalized title in the header — welcome the user by first name.
watchEffect(() => {
    setLayoutProps({
        title: `Welcome back, ${firstName.value}`,
        subtitle: 'Your saved properties, inquiries, and recent views',
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    });
});

const statusClass = (slug: string | null) => ({
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400':
        slug === 'new',
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400':
        slug === 'read',
    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400':
        slug === 'replied',
    'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400':
        slug === 'archived',
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-10 p-6">
        <!-- Browse CTA — welcome text now lives in the page header. -->
        <header class="flex justify-end">
            <div class="sr-only">
                <h1>{{ firstName }}</h1>
            </div>
            <Button as-child variant="outline">
                <Link :href="properties.index().url">Browse listings</Link>
            </Button>
        </header>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Saved Properties</CardTitle
                    >
                    <Heart class="size-5 text-landing-gold" />
                </CardHeader>
                <CardContent>
                    <p class="font-serif text-3xl font-semibold">
                        {{ stats.favorites }}
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Inquiries Sent</CardTitle
                    >
                    <Inbox class="size-5 text-landing-deep-teal" />
                </CardHeader>
                <CardContent>
                    <p class="font-serif text-3xl font-semibold">
                        {{ stats.inquiries }}
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Properties Viewed</CardTitle
                    >
                    <Eye class="size-5 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="font-serif text-3xl font-semibold">
                        {{ stats.views }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Saved properties -->
        <section class="space-y-4">
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="font-serif text-xl font-semibold">
                        Saved Properties
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Listings you've starred for later
                    </p>
                </div>
            </div>

            <Deferred data="favorites">
                <template #fallback>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <Skeleton
                            v-for="n in 3"
                            :key="n"
                            class="aspect-video w-full"
                        />
                    </div>
                </template>

                <div
                    v-if="favorites?.length"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Link
                        v-for="item in favorites"
                        :key="item.id"
                        :href="`/properties/${item.slug}`"
                        class="group block overflow-hidden rounded-xl border bg-card transition-all hover:-translate-y-0.5 hover:shadow-lg hover:ring-1 hover:ring-landing-gold/40"
                    >
                        <div class="relative aspect-[16/10] bg-muted">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center"
                            >
                                <Building2
                                    class="size-10 text-muted-foreground/40"
                                />
                            </div>
                            <span
                                v-if="item.listing"
                                class="absolute top-2 left-2 rounded-full bg-landing-gold px-2 py-0.5 text-xs font-medium text-black"
                            >
                                {{ item.listing }}
                            </span>
                        </div>
                        <div class="space-y-2 p-4">
                            <p
                                class="font-serif text-lg font-semibold transition-colors group-hover:text-landing-gold"
                            >
                                {{ item.title }}
                            </p>
                            <p
                                class="flex items-center gap-1 text-xs text-muted-foreground"
                            >
                                <MapPin class="size-3" />
                                {{ item.city ?? item.address }}
                            </p>
                            <div class="flex items-center justify-between pt-1">
                                <p
                                    class="text-lg font-semibold text-landing-deep-teal"
                                >
                                    {{ item.price }}
                                </p>
                                <div
                                    class="flex gap-3 text-xs text-muted-foreground"
                                >
                                    <span class="flex items-center gap-1">
                                        <BedDouble class="size-3.5" />
                                        {{ item.bedrooms }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Bath class="size-3.5" />
                                        {{ item.bathrooms }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Maximize class="size-3.5" />
                                        {{ item.area_sqft }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <Card v-else>
                    <CardContent
                        class="flex flex-col items-center gap-3 py-10 text-center"
                    >
                        <Heart class="size-10 text-muted-foreground/40" />
                        <div>
                            <p class="font-medium">No saved properties yet</p>
                            <p class="text-sm text-muted-foreground">
                                Star listings to save them here for quick
                                access.
                            </p>
                        </div>
                        <Button as-child size="sm">
                            <Link :href="properties.index().url"
                                >Browse listings</Link
                            >
                        </Button>
                    </CardContent>
                </Card>
            </Deferred>
        </section>

        <!-- My inquiries -->
        <section class="space-y-4">
            <div>
                <h2 class="font-serif text-xl font-semibold">My Inquiries</h2>
                <p class="text-sm text-muted-foreground">
                    Messages you've sent to agents and their replies
                </p>
            </div>

            <Deferred data="inquiries">
                <template #fallback>
                    <div class="space-y-2">
                        <Skeleton v-for="n in 3" :key="n" class="h-20 w-full" />
                    </div>
                </template>

                <div v-if="inquiries?.length" class="space-y-3">
                    <Card v-for="inq in inquiries" :key="inq.id">
                        <CardContent
                            class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="min-w-0 flex-1 space-y-1">
                                <Link
                                    v-if="inq.property_slug"
                                    :href="`/properties/${inq.property_slug}`"
                                    class="font-medium hover:text-landing-gold"
                                >
                                    {{ inq.property_title }}
                                </Link>
                                <p
                                    class="truncate text-sm text-muted-foreground"
                                >
                                    {{ inq.message }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Sent
                                    {{
                                        new Date(
                                            inq.created_at,
                                        ).toLocaleDateString()
                                    }}
                                    <span v-if="inq.replied_at">
                                        · Replied
                                        {{
                                            new Date(
                                                inq.replied_at,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </p>
                            </div>
                            <span
                                class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusClass(inq.status_slug)"
                            >
                                {{ inq.status ?? 'New' }}
                            </span>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else>
                    <CardContent
                        class="flex flex-col items-center gap-3 py-10 text-center"
                    >
                        <Inbox class="size-10 text-muted-foreground/40" />
                        <div>
                            <p class="font-medium">No inquiries yet</p>
                            <p class="text-sm text-muted-foreground">
                                Contact an agent from any property page to get
                                started.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </Deferred>
        </section>

        <!-- Recently viewed -->
        <section class="space-y-4">
            <div>
                <h2 class="font-serif text-xl font-semibold">
                    Recently Viewed
                </h2>
                <p class="text-sm text-muted-foreground">
                    Pick up where you left off
                </p>
            </div>

            <Deferred data="recentlyViewed">
                <template #fallback>
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <Skeleton
                            v-for="n in 4"
                            :key="n"
                            class="aspect-square w-full"
                        />
                    </div>
                </template>

                <div
                    v-if="recentlyViewed?.length"
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <Link
                        v-for="item in recentlyViewed"
                        :key="item.id"
                        :href="`/properties/${item.slug}`"
                        class="group block overflow-hidden rounded-lg border bg-card transition-all hover:-translate-y-0.5 hover:shadow-md hover:ring-1 hover:ring-landing-gold/30"
                    >
                        <div class="relative aspect-square bg-muted">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center"
                            >
                                <Building2
                                    class="size-8 text-muted-foreground/40"
                                />
                            </div>
                        </div>
                        <div class="p-2.5">
                            <p
                                class="truncate text-sm font-medium transition-colors group-hover:text-landing-gold"
                            >
                                {{ item.title }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ item.price }}
                            </p>
                        </div>
                    </Link>
                </div>

                <p
                    v-else
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    Properties you view will appear here.
                </p>
            </Deferred>
        </section>
    </div>
</template>
