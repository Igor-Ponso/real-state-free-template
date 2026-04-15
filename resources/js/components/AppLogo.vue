<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-md bg-gradient-to-br from-landing-deep-teal to-landing-charcoal text-landing-gold shadow-sm"
    >
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-2 grid flex-1 text-left">
        <span
            class="truncate font-serif text-base leading-tight font-semibold tracking-tight"
        >
            Sovereign Estates
        </span>
        <span
            class="truncate text-[10px] tracking-[0.15em] text-muted-foreground uppercase"
        >
            Luxury Real Estate
        </span>
    </div>
</template>
