<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Mail, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import properties from '@/routes/properties';

const props = defineProps<{
    status: 'confirmed' | 'unsubscribed' | 'invalid';
    message: string;
}>();

const title = computed(() => {
    switch (props.status) {
        case 'confirmed':
            return 'Subscription confirmed';
        case 'unsubscribed':
            return "You're unsubscribed";
        default:
            return 'Invalid link';
    }
});

const icon = computed(() => {
    switch (props.status) {
        case 'confirmed':
            return CheckCircle2;
        case 'unsubscribed':
            return Mail;
        default:
            return XCircle;
    }
});

const iconClass = computed(() =>
    props.status === 'invalid' ? 'text-destructive' : 'text-landing-gold',
);
</script>

<template>
    <Head :title="title" />

    <div
        class="flex min-h-svh items-center justify-center bg-gradient-to-b from-landing-deep-teal to-landing-charcoal p-6 text-white"
    >
        <div
            class="mx-auto max-w-md rounded-2xl border border-white/10 bg-white/5 p-10 text-center shadow-2xl backdrop-blur-sm"
        >
            <component :is="icon" :class="['mx-auto size-14', iconClass]" />
            <h1 class="mt-6 font-serif text-3xl font-semibold tracking-tight">
                {{ title }}
            </h1>
            <p class="mt-3 text-sm text-white/70">{{ message }}</p>

            <div class="mt-8 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <Button
                    as-child
                    class="bg-landing-gold text-black hover:bg-landing-gold/90"
                >
                    <Link :href="properties.index().url">Browse listings</Link>
                </Button>
                <Button
                    as-child
                    variant="ghost"
                    class="text-white hover:bg-white/10 hover:text-white"
                >
                    <Link :href="home()">Back to home</Link>
                </Button>
            </div>
        </div>
    </div>
</template>
