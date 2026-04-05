import { createInertiaApp } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

import { initializeTheme } from '@/composables/useAppearance';

const AppLayout = defineAsyncComponent(() => import('@/layouts/AppLayout.vue'));
const AuthLayout = defineAsyncComponent(() => import('@/layouts/AuthLayout.vue'));
const SettingsLayout = defineAsyncComponent(() => import('@/layouts/settings/Layout.vue'));

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'Error':
            case name.startsWith('Properties/'):
                return undefined;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
