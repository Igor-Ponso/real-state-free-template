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
    <!-- Sovereign Estates — geometric crown mark -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Crown base -->
        <path
            fill="currentColor"
            d="M6 28h28v3H6z"
        />
        <!-- Crown body — five peaks -->
        <path
            fill="currentColor"
            d="M6 28L2 14l8 6 5-10h0L20 4l5 6 5 10 8-6-4 14H6z"
            fill-rule="evenodd"
        />
        <!-- Inner cut-outs for elegance -->
        <circle fill="currentColor" cx="20" cy="20" r="2" opacity="0" />
        <!-- Gemstones -->
        <circle fill="currentColor" cx="12" cy="22" r="1.5" opacity="0.25" />
        <circle fill="currentColor" cx="20" cy="20" r="2" opacity="0.25" />
        <circle fill="currentColor" cx="28" cy="22" r="1.5" opacity="0.25" />
        <!-- Crown band -->
        <path
            fill="currentColor"
            d="M6 31h28v2H6z"
            opacity="0.5"
        />
    </svg>
</template>
