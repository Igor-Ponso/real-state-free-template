<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    status: number;
}>();

const config = computed(() => {
    const map: Record<number, { title: string; heading: string; message: string }> = {
        403: {
            title: '403 — Forbidden',
            heading: 'Access Denied',
            message: "You don't have permission to access this page. If you believe this is an error, please contact our team.",
        },
        404: {
            title: '404 — Not Found',
            heading: 'Page Not Found',
            message: "The property or page you're looking for may have been moved, sold, or doesn't exist. Let's get you back on track.",
        },
        500: {
            title: '500 — Server Error',
            heading: 'Something Went Wrong',
            message: "We're experiencing a temporary issue on our end. Our team has been notified and is working on it.",
        },
        503: {
            title: '503 — Maintenance',
            heading: "We'll Be Right Back",
            message: "We're performing scheduled maintenance to improve your experience. Please check back shortly.",
        },
    };

    return map[props.status] ?? {
        title: `${props.status} — Error`,
        heading: 'Unexpected Error',
        message: 'Something unexpected happened. Please try again or contact our team.',
    };
});
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-landing-charcoal">
        <Head :title="config.title" />

        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;" />

        <!-- Gradient glow -->
        <div class="absolute left-1/2 top-1/3 size-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-landing-deep-teal/30 blur-3xl" />

        <div class="relative z-10 max-w-lg px-6 text-center">
            <!-- Logo -->
            <Link href="/" class="mb-8 inline-block">
                <AppLogoIcon class="mx-auto size-12 text-landing-gold" />
            </Link>

            <!-- Status code -->
            <p class="font-serif text-8xl font-bold text-landing-gold/20">
                {{ status }}
            </p>

            <!-- Heading -->
            <h1 class="mt-4 font-serif text-3xl font-semibold tracking-tight text-white md:text-4xl">
                {{ config.heading }}
            </h1>

            <!-- Message -->
            <p class="mx-auto mt-4 max-w-md font-body text-sm leading-relaxed text-white/50">
                {{ config.message }}
            </p>

            <!-- Actions -->
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Button
                    as-child
                    size="lg"
                    class="bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90"
                >
                    <Link href="/">Back to Home</Link>
                </Button>
                <Button
                    as-child
                    variant="outline"
                    size="lg"
                    class="border-white/20 font-body text-white hover:bg-white/10"
                >
                    <Link href="/properties">Browse Properties</Link>
                </Button>
            </div>
        </div>
    </div>
</template>
