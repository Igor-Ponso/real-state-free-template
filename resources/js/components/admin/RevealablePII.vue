<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { onBeforeUnmount, ref } from 'vue';

/**
 * Blurs a PII value (name, email, phone, etc.) by default. Click the inline
 * icon to reveal for AUTO_HIDE_MS, then re-blurs automatically. Clicking again
 * while visible hides immediately.
 *
 * Intended for admin/agent detail views where PII is technically authorized
 * but shoulder-surfing is a real risk (shared screens, desk walkthroughs).
 */
const props = withDefaults(
    defineProps<{
        value: string | null | undefined;
        /** Auto-hide timeout in milliseconds. */
        duration?: number;
        /** Optional href — renders as an <a> when revealed, <span> otherwise. */
        href?: string;
    }>(),
    { duration: 15_000 },
);

const revealed = ref(false);
let hideTimer: ReturnType<typeof setTimeout> | null = null;

const clearTimer = () => {
    if (hideTimer !== null) {
        clearTimeout(hideTimer);
        hideTimer = null;
    }
};

const toggle = () => {
    if (revealed.value) {
        revealed.value = false;
        clearTimer();

        return;
    }

    revealed.value = true;
    clearTimer();
    hideTimer = setTimeout(() => {
        revealed.value = false;
        hideTimer = null;
    }, props.duration);
};

onBeforeUnmount(clearTimer);
</script>

<template>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <component
            :is="href && revealed ? 'a' : 'span'"
            :href="href && revealed ? href : undefined"
            class="min-w-0 truncate transition-[filter] duration-200 select-none"
            :class="[
                revealed ? 'blur-0 select-text' : 'cursor-pointer blur-[5px]',
                href && revealed ? 'text-primary hover:underline' : '',
            ]"
            :title="revealed ? '' : 'Click the eye icon to reveal'"
            @click="revealed ? null : toggle()"
        >
            {{ value ?? '—' }}
        </component>
        <button
            type="button"
            class="shrink-0 text-muted-foreground transition-colors hover:text-landing-gold focus-visible:ring-2 focus-visible:ring-landing-gold focus-visible:outline-none"
            :aria-label="revealed ? 'Hide value' : 'Reveal value'"
            :title="
                revealed
                    ? `Hide (auto-hides in ${Math.round(duration / 1000)}s)`
                    : 'Reveal'
            "
            @click="toggle"
        >
            <component :is="revealed ? EyeOff : Eye" class="size-4" />
        </button>
    </span>
</template>
