<script setup lang="ts">
import { EyeOff, Heart } from 'lucide-vue-next';

import type { FeaturedProperty } from '@/types/landing';

defineProps<{
    property: FeaturedProperty;
    isFavorite: boolean;
    isDismissed: boolean;
}>();

const emit = defineEmits<{
    toggleFavorite: [];
    toggleDismissed: [];
}>();
</script>

<template>
    <div class="min-w-44 font-body">
        <img
            v-if="property.images[0]"
            :src="property.images[0]"
            :alt="property.title"
            class="mb-2 h-24 w-full rounded object-cover"
        />
        <p class="font-serif text-sm font-semibold">
            {{ property.title }}
        </p>
        <p class="text-xs text-muted-foreground">
            {{ property.location }}
        </p>
        <p class="mt-1 font-serif font-bold text-landing-gold">
            {{ property.price }}
        </p>
        <p
            v-if="property.description"
            class="mt-1 line-clamp-2 text-xs leading-relaxed text-muted-foreground"
        >
            {{ property.description }}
        </p>
        <!-- Favorite / Dismiss actions -->
        <div class="mt-2 flex gap-1 border-t pt-2">
            <button
                :aria-label="isFavorite ? 'Remove from saved' : 'Save property'"
                class="flex flex-1 items-center justify-center gap-1 rounded px-2 py-1 text-xs transition-colors hover:bg-muted"
                :class="isFavorite ? 'text-red-500' : 'text-muted-foreground'"
                @click.stop="emit('toggleFavorite')"
            >
                <Heart
                    class="size-3.5"
                    :fill="isFavorite ? 'currentColor' : 'none'"
                />
                {{ isFavorite ? 'Saved' : 'Save' }}
            </button>
            <button
                :aria-label="isDismissed ? 'Show property' : 'Hide property'"
                class="flex flex-1 items-center justify-center gap-1 rounded px-2 py-1 text-xs transition-colors hover:bg-muted"
                :class="
                    isDismissed ? 'text-orange-500' : 'text-muted-foreground'
                "
                @click.stop="emit('toggleDismissed')"
            >
                <EyeOff class="size-3.5" />
                {{ isDismissed ? 'Hidden' : 'Hide' }}
            </button>
        </div>
    </div>
</template>
