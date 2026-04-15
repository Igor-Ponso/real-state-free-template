<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAgentProfile } from '@/routes/agent-profile';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

interface SharedProps {
    auth: { user: { id: number; roles?: Array<{ name: string }> } | null };
    [key: string]: unknown;
}

const page = usePage<SharedProps>();

const isAdminOrAgent = computed(() =>
    (page.props.auth.user?.roles ?? []).some(
        (r) => r.name === 'admin' || r.name === 'agent',
    ),
);

const sidebarNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: 'Profile', href: editProfile() },
        { title: 'Security', href: editSecurity() },
        { title: 'Appearance', href: editAppearance() },
    ];

    if (isAdminOrAgent.value) {
        items.splice(1, 0, {
            title: 'Agent profile',
            href: editAgentProfile(),
        });
    }

    return items;
});

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Settings"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-landing-gold/10 text-landing-gold hover:bg-landing-gold/15 hover:text-landing-gold'
                                : '',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
