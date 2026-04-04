<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- Sovereign Estates — crown + building silhouette -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <path
            fill="currentColor"
            d="M20 2l4 6 6-3-2 7h4l-3 6 5 4-6 2 1 6-5-3-4 5-4-5-5 3 1-6-6-2 5-4-3-6h4l-2-7 6 3z"
            opacity="0.15"
        />
        <path
            fill="currentColor"
            d="M8 18v16h24V18L20 10 8 18zm2 2l10-6.5L30 20v12H10V20z"
        />
        <rect fill="currentColor" x="17" y="25" width="6" height="7" rx="1" />
        <rect fill="currentColor" x="12" y="22" width="3.5" height="4" rx="0.5" opacity="0.6" />
        <rect fill="currentColor" x="24.5" y="22" width="3.5" height="4" rx="0.5" opacity="0.6" />
    </svg>
</template>
