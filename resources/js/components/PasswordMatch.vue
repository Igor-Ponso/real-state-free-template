<script setup lang="ts">
import { Check, X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    password: string;
    confirmation: string;
}>();

const hasInput = computed(() => props.confirmation.length > 0);
const matches = computed(
    () => props.password === props.confirmation && props.password.length > 0,
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
    >
        <div
            v-if="hasInput"
            class="flex items-center gap-2 pt-1 text-sm transition-colors duration-200"
        >
            <Check
                v-if="matches"
                class="size-3.5 shrink-0 text-green-500"
            />
            <X v-else class="size-3.5 shrink-0 text-destructive" />
            <span
                :class="
                    matches
                        ? 'text-green-600 dark:text-green-400'
                        : 'text-destructive'
                "
            >
                Passwords match
            </span>
        </div>
    </Transition>
</template>
