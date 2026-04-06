<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { confirm } from '@/routes/two-factor';

const code = defineModel<string>('code', { required: true });

const emit = defineEmits<{
    back: [];
    success: [];
}>();

const pinInputContainerRef = useTemplateRef('pinInputContainerRef');

defineExpose({ focusInput: () => pinInputContainerRef.value?.querySelector('input')?.focus() });
</script>

<template>
    <Form
        v-bind="confirm.form()"
        error-bag="confirmTwoFactorAuthentication"
        reset-on-error
        @finish="code = ''"
        @success="emit('success')"
        v-slot="{ errors, processing }"
    >
        <input type="hidden" name="code" :value="code" />
        <div ref="pinInputContainerRef" class="relative w-full space-y-3">
            <div class="flex w-full flex-col items-center justify-center space-y-3 py-2">
                <InputOTP id="otp" v-model="code" :maxlength="6" :disabled="processing">
                    <InputOTPGroup>
                        <InputOTPSlot v-for="index in 6" :key="index" :index="index - 1" />
                    </InputOTPGroup>
                </InputOTP>
                <InputError :message="errors?.code" />
            </div>
            <div class="flex w-full items-center space-x-5">
                <Button type="button" variant="outline" class="w-auto flex-1" :disabled="processing" @click="emit('back')">
                    Back
                </Button>
                <Button type="submit" class="w-auto flex-1" :disabled="processing || code.length < 6">
                    Confirm
                </Button>
            </div>
        </div>
    </Form>
</template>
