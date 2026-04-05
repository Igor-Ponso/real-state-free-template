<script setup lang="ts">
import {
    AirVent,
    Eye,
    Flame,
    Footprints,
    Snowflake,
    Thermometer,
} from 'lucide-vue-next';
import { computed } from 'vue';
import type { Component } from 'vue';

const props = defineProps<{
    features: Record<string, string>;
}>();

const featureIcons: Record<string, Component> = {
    flooring: Footprints,
    heating: Flame,
    cooling: Snowflake,
    view: Eye,
    ventilation: AirVent,
    insulation: Thermometer,
};

const formatValue = (val: string): string =>
    val.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const entries = computed(() =>
    Object.entries(props.features).map(([key, value]) => ({
        label: key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
        value: formatValue(String(value)),
        icon: featureIcons[key] ?? null,
    })),
);
</script>

<template>
    <div v-if="entries.length">
        <h2 class="mb-4 font-serif text-xl font-semibold text-white">
            Property Features
        </h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="feature in entries"
                :key="feature.label"
                class="flex items-start gap-3 rounded-lg border border-white/10 bg-white/5 px-3 py-2.5"
            >
                <component
                    :is="feature.icon"
                    v-if="feature.icon"
                    class="mt-0.5 size-4 shrink-0 text-landing-gold/70"
                />
                <div>
                    <p class="font-body text-xs tracking-wider text-white/40 uppercase">
                        {{ feature.label }}
                    </p>
                    <p class="font-body text-sm font-medium text-white/80">
                        {{ feature.value }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
