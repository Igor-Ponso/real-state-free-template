import { createInertiaApp, router } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import { toast } from 'vue-sonner';

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

// Global flash → toast: any backend ->with('success'|'error'|...) shows a Sonner toast
router.on('flash', (event) => {
    const flash = event.detail.flash as Record<string, string>;

    if (flash.success) {
toast.success(flash.success);
}

    if (flash.error) {
toast.error(flash.error);
}

    if (flash.warning) {
toast.warning(flash.warning);
}

    if (flash.info) {
toast.info(flash.info);
}
});
