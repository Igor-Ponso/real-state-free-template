<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    FolderGit2,
    Globe,
    Inbox,
    LayoutGrid,
} from 'lucide-vue-next';
import { computed } from 'vue';

import { index as adminDashboard } from '@/actions/App/Http/Controllers/Admin/DashboardController';
import { index as adminInquiries } from '@/actions/App/Http/Controllers/Admin/InquiryController';
import { index as adminProperties } from '@/actions/App/Http/Controllers/Admin/PropertyController';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import properties from '@/routes/properties';
import type { NavItem } from '@/types';

interface SharedProps {
    auth: { user: { id: number; roles?: Array<{ name: string }> } | null };
    unreadInquiriesCount: number;
    [key: string]: unknown;
}

const page = usePage<SharedProps>();

const roles = computed(
    () => page.props.auth.user?.roles?.map((r) => r.name) ?? [],
);
const isAdminOrAgent = computed(() =>
    roles.value.some((r) => r === 'admin' || r === 'agent'),
);

const mainNavItems = computed<NavItem[]>(() => {
    const unread = page.props.unreadInquiriesCount;

    if (isAdminOrAgent.value) {
        return [
            {
                title: 'Dashboard',
                href: adminDashboard.url(),
                icon: LayoutGrid,
            },
            {
                title: 'Properties',
                href: adminProperties.url(),
                icon: Building2,
            },
            {
                title: `Inquiries${unread ? ` (${unread})` : ''}`,
                href: adminInquiries.url(),
                icon: Inbox,
            },
        ];
    }

    return [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        {
            title: 'Browse Listings',
            href: properties.index().url,
            icon: Building2,
        },
    ];
});

const footerNavItems: NavItem[] = [
    { title: 'Public Site', href: '/', icon: Globe },
    {
        title: 'Repository',
        href: 'https://github.com/Igor-Ponso/real-state-free-template',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link
                            :href="
                                isAdminOrAgent
                                    ? adminDashboard.url()
                                    : dashboard()
                            "
                        >
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
