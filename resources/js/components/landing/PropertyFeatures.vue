<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    features: Record<string, string>;
}>();

const entries = computed(() =>
    Object.entries(props.features).map(([key, value]) => ({
        label: key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
        value: String(value),
    })),
);
</script>

<template>
    <div v-if="entries.length">
        <h2 class="mb-4 font-serif text-xl font-semibold text-white">
            Property Features
        </h2>
        <div class="grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-3">
            <div v-for="feature in entries" :key="feature.label">
                <p class="font-body text-xs tracking-wider text-white/40 uppercase">
                    {{ feature.label }}
                </p>
                <p class="font-body text-sm text-white/80">
                    {{ feature.value }}
                </p>
            </div>
        </div>
    </div>
</template>
