<script setup lang="ts">
import { useIntersectionObserver } from '@vueuse/core';
import { ref, useTemplateRef } from 'vue';

const props = withDefaults(
    defineProps<{
        target: number;
        duration?: number;
        prefix?: string;
        suffix?: string;
    }>(),
    { duration: 2000, prefix: '', suffix: '' },
);

const current = ref(0);
const hasAnimated = ref(false);
const counterRef = useTemplateRef('counterRef');

const easeOutQuad = (t: number): number => t * (2 - t);

const animate = () => {
    if (hasAnimated.value) {
        return;
    }

    hasAnimated.value = true;

    const start = performance.now();

    const step = (now: number) => {
        const elapsed = now - start;
        const progress = Math.min(elapsed / props.duration, 1);
        current.value = Math.round(easeOutQuad(progress) * props.target);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    requestAnimationFrame(step);
};

useIntersectionObserver(counterRef, ([entry]) => {
    if (entry?.isIntersecting) {
        animate();
    }
});
</script>

<template>
    <span ref="counterRef" class="tabular-nums">
        {{ prefix }}{{ hasAnimated ? current : target }}{{ suffix }}
    </span>
</template>
