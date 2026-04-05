<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Building2, FolderGit2, Inbox, LayoutGrid } from 'lucide-vue-next';

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
import type { NavItem } from '@/types';

const page = usePage();
const unreadCount = page.props.unreadInquiriesCount as number;

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Admin Dashboard',
        href: adminDashboard.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Properties',
        href: adminProperties.url(),
        icon: Building2,
    },
    {
        title: `Inquiries${unreadCount ? ` (${unreadCount})` : ''}`,
        href: adminInquiries.url(),
        icon: Inbox,
    },
];

const footerNavItems: NavItem[] = [
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
                        <Link :href="dashboard()">
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
