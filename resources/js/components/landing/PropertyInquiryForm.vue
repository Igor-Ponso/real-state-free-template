<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { CheckCircle, Loader2, Send } from 'lucide-vue-next';
import { ref } from 'vue';

import { store } from '@/actions/App/Http/Controllers/InquiryController';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    propertyId: number;
}>();

const submitted = ref(false);

const form = useHttp({
    property_id: props.propertyId,
    name: '',
    email: '',
    phone: '',
    message: '',
    honeypot: '',
});

const submit = () => {
    form.post(store.url(), {
        onSuccess: () => {
            submitted.value = true;
        },
    });
};
</script>

<template>
    <div>
        <h3
            class="mb-4 font-serif text-lg font-semibold text-white"
        >
            Inquire About This Property
        </h3>

        <!-- Success state -->
        <Alert
            v-if="submitted"
            class="border-landing-gold/30 bg-landing-gold/10"
        >
            <CheckCircle class="size-4 text-landing-gold" />
            <AlertTitle class="font-serif text-white">
                Inquiry Sent
            </AlertTitle>
            <AlertDescription class="font-body text-white/60">
                Thank you for your inquiry. We will get back to you
                shortly.
            </AlertDescription>
        </Alert>

        <!-- Form -->
        <form
            v-else
            class="space-y-4"
            @submit.prevent="submit"
        >
            <!-- Honeypot (hidden from real users) -->
            <input
                v-model="form.honeypot"
                type="text"
                name="honeypot"
                class="absolute -left-[9999px] opacity-0"
                tabindex="-1"
                autocomplete="off"
            />

            <div>
                <Label
                    for="inquiry-name"
                    class="font-body text-sm text-white/70"
                >
                    Name
                </Label>
                <Input
                    id="inquiry-name"
                    v-model="form.name"
                    type="text"
                    placeholder="Your full name"
                    class="mt-1 border-white/10 bg-white/5 text-white placeholder:text-white/30"
                />
                <p
                    v-if="form.errors.name"
                    class="mt-1 font-body text-xs text-red-400"
                >
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <Label
                    for="inquiry-email"
                    class="font-body text-sm text-white/70"
                >
                    Email
                </Label>
                <Input
                    id="inquiry-email"
                    v-model="form.email"
                    type="email"
                    placeholder="you@example.com"
                    class="mt-1 border-white/10 bg-white/5 text-white placeholder:text-white/30"
                />
                <p
                    v-if="form.errors.email"
                    class="mt-1 font-body text-xs text-red-400"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <Label
                    for="inquiry-phone"
                    class="font-body text-sm text-white/70"
                >
                    Phone
                    <span class="text-white/30">(optional)</span>
                </Label>
                <Input
                    id="inquiry-phone"
                    v-model="form.phone"
                    type="tel"
                    placeholder="+1 (555) 000-0000"
                    class="mt-1 border-white/10 bg-white/5 text-white placeholder:text-white/30"
                />
                <p
                    v-if="form.errors.phone"
                    class="mt-1 font-body text-xs text-red-400"
                >
                    {{ form.errors.phone }}
                </p>
            </div>

            <div>
                <Label
                    for="inquiry-message"
                    class="font-body text-sm text-white/70"
                >
                    Message
                </Label>
                <Textarea
                    id="inquiry-message"
                    v-model="form.message"
                    placeholder="I'm interested in this property..."
                    rows="4"
                    class="mt-1 resize-none border-white/10 bg-white/5 text-white placeholder:text-white/30"
                />
                <p
                    v-if="form.errors.message"
                    class="mt-1 font-body text-xs text-red-400"
                >
                    {{ form.errors.message }}
                </p>
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-landing-gold font-body font-semibold text-white hover:bg-landing-gold/90"
            >
                <Loader2
                    v-if="form.processing"
                    class="mr-2 size-4 animate-spin"
                />
                <Send v-else class="mr-2 size-4" />
                {{ form.processing ? 'Sending...' : 'Send Inquiry' }}
            </Button>
        </form>
    </div>
</template>
