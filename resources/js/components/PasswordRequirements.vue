<script setup lang="ts">
import { Check, X } from 'lucide-vue-next';
import { toRef } from 'vue';

import { usePasswordValidation } from '@/composables/usePasswordValidation';

const props = defineProps<{
    password: string;
    show?: boolean;
}>();

const { rules, hasInput } = usePasswordValidation({
    password: toRef(props, 'password'),
});
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
        <div v-if="show && hasInput" class="space-y-2 pt-1">
            <ul class="space-y-1">
                <li
                    v-for="rule in rules"
                    :key="rule.key"
                    class="flex items-center gap-2 text-sm transition-colors duration-200"
                >
                    <Check
                        v-if="rule.passed"
                        class="size-3.5 shrink-0 text-green-500"
                    />
                    <X
                        v-else
                        class="size-3.5 shrink-0 text-muted-foreground"
                    />
                    <span
                        :class="
                            rule.passed
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-muted-foreground'
                        "
                    >
                        {{ rule.label }}
                    </span>
                </li>
            </ul>
            <p class="text-xs text-muted-foreground">
                Password will also be checked against known data breaches
            </p>
        </div>
    </Transition>
</template>
