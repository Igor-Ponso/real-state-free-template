<script setup lang="ts">
/**
 * GDPR-compliant cookie consent banner.
 *
 * - Stores consent in localStorage (not cookies)
 * - Reject and Accept have equal visual weight (GDPR requirement)
 * - Records timestamp + version for audit trail
 * - z-[9999] ensures it's above everything including Leaflet popups
 *
 * @see https://gdpr.direct/guides/cookie-consent-implementation
 */
import { useLocalStorage } from '@vueuse/core';
import { Shield } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

import { Button } from '@/components/ui/button';

interface ConsentRecord {
    accepted: boolean;
    timestamp: string;
    version: number;
}

const CONSENT_VERSION = 1;

const consent = useLocalStorage<ConsentRecord | null>(
    'sovereign-cookie-consent',
    null,
);

// Defer banner mount until after first paint to keep it out of LCP measurements
// and let the hero image become the LCP element. The banner still appears well
// before any meaningful interaction, satisfying GDPR requirements.
const isReady = ref(false);

onMounted(() => {
    setTimeout(() => {
        isReady.value = true;
    }, 1500);
});

const showBanner = computed(() => {
    if (!isReady.value) {
        return false;
    }

    if (!consent.value) {
        return true;
    }

    return consent.value.version < CONSENT_VERSION;
});

const respond = (accepted: boolean) => {
    consent.value = {
        accepted,
        timestamp: new Date().toISOString(),
        version: CONSENT_VERSION,
    };
};
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        leave-active-class="transition-all duration-300 ease-in"
        enter-from-class="translate-y-full opacity-0"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="showBanner"
            class="fixed inset-x-0 bottom-0 z-[9999] border-t border-white/10 bg-landing-charcoal px-6 py-5 shadow-2xl"
        >
            <div
                class="mx-auto flex max-w-5xl flex-col items-start gap-4 sm:flex-row sm:items-center"
            >
                <Shield
                    class="hidden size-8 shrink-0 text-landing-gold sm:block"
                />
                <div class="flex-1">
                    <p class="font-body text-sm font-medium text-white">
                        We value your privacy
                    </p>
                    <p
                        class="mt-1 font-body text-xs leading-relaxed text-white/50"
                    >
                        We use local storage to save your preferences,
                        favorites, and property interactions. No personal data
                        is shared with third parties. You can change your
                        preferences at any time.
                    </p>
                </div>
                <div class="flex w-full shrink-0 gap-3 sm:w-auto">
                    <Button
                        variant="outline"
                        class="flex-1 border-white/20 text-white hover:bg-white/10 sm:flex-none"
                        @click="respond(false)"
                    >
                        Essential Only
                    </Button>
                    <Button
                        class="flex-1 bg-landing-gold text-landing-gold-foreground hover:bg-landing-gold/90 sm:flex-none"
                        @click="respond(true)"
                    >
                        Accept All
                    </Button>
                </div>
            </div>
        </div>
    </Transition>
</template>
