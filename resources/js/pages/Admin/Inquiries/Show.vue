<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Mail, Phone, Send, User } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import { update } from '@/actions/App/Http/Controllers/Admin/InquiryController';
import RevealablePII from '@/components/admin/RevealablePII.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import {
    index as adminInquiriesIndex,
    reply as adminInquiryReply,
} from '@/routes/admin/inquiries';
import { show as propertiesShow } from '@/routes/properties';
import type { AdminInquiry, LookupOption } from '@/types/admin';

const props = defineProps<{
    inquiry: AdminInquiry;
    statuses: LookupOption[];
}>();

const updateStatus = (statusId: unknown) => {
    router.put(
        update.url({ inquiry: props.inquiry.id }),
        { inquiry_status_id: Number(statusId) },
        {
            onSuccess: () => {
                toast.success('Inquiry status updated.');
            },
        },
    );
};

const replyForm = useForm({
    reply: '',
});

const sendReply = () => {
    replyForm.post(adminInquiryReply.url(props.inquiry.id), {
        onSuccess: () => {
            toast.success('Reply sent successfully.');
            replyForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="`Inquiry from ${inquiry.name}`" />

    <div class="space-y-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child>
                <Link :href="adminInquiriesIndex.url()">
                    <ArrowLeft class="mr-1 size-4" /> Back
                </Link>
            </Button>
            <h1 class="font-serif text-3xl font-semibold tracking-tight">
                Inquiry Details
            </h1>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main content -->
            <div class="space-y-6 lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Message</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p
                            class="leading-relaxed whitespace-pre-line text-muted-foreground"
                        >
                            {{ inquiry.message }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Previous reply (if exists) -->
                <Card v-if="inquiry.reply">
                    <CardHeader>
                        <CardTitle>Your Reply</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p
                            class="leading-relaxed whitespace-pre-line text-muted-foreground"
                        >
                            {{ inquiry.reply }}
                        </p>
                        <p
                            v-if="inquiry.replied_at"
                            class="mt-3 text-xs text-muted-foreground"
                        >
                            Replied
                            {{ new Date(inquiry.replied_at).toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Reply form -->
                <Card>
                    <CardHeader>
                        <CardTitle>{{
                            inquiry.reply ? 'Send Another Reply' : 'Reply'
                        }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="sendReply" class="space-y-4">
                            <Textarea
                                v-model="replyForm.reply"
                                placeholder="Type your reply to the inquirer..."
                                rows="5"
                                class="resize-none"
                            />
                            <p
                                v-if="replyForm.errors.reply"
                                class="text-sm text-destructive"
                            >
                                {{ replyForm.errors.reply }}
                            </p>
                            <div class="flex items-center justify-between">
                                <p class="flex items-center gap-1 text-xs text-muted-foreground">
                                    Reply will be emailed to
                                    <RevealablePII :value="inquiry.email" />
                                </p>
                                <Button
                                    type="submit"
                                    :disabled="
                                        replyForm.processing ||
                                        !replyForm.reply.trim()
                                    "
                                >
                                    <Send class="mr-1.5 size-4" />
                                    {{
                                        replyForm.processing
                                            ? 'Sending...'
                                            : 'Send Reply'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Contact Info</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <p class="text-xs text-muted-foreground">
                            Contact details are blurred by default. Click the eye icon to reveal — auto-hides after 15 seconds.
                        </p>
                        <div class="flex items-center gap-2">
                            <User class="size-4 shrink-0 text-muted-foreground" />
                            <RevealablePII :value="inquiry.name" />
                        </div>
                        <div class="flex items-center gap-2">
                            <Mail class="size-4 shrink-0 text-muted-foreground" />
                            <RevealablePII
                                :value="inquiry.email"
                                :href="`mailto:${inquiry.email}`"
                            />
                        </div>
                        <div
                            v-if="inquiry.phone"
                            class="flex items-center gap-2"
                        >
                            <Phone class="size-4 shrink-0 text-muted-foreground" />
                            <RevealablePII :value="inquiry.phone" />
                        </div>
                        <Separator />
                        <div class="flex items-center gap-2">
                            <Calendar class="size-4 text-muted-foreground" />
                            <span class="text-sm text-muted-foreground">
                                {{
                                    new Date(
                                        inquiry.created_at,
                                    ).toLocaleString()
                                }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Select
                            :model-value="String(inquiry.inquiry_status_id)"
                            @update:model-value="updateStatus"
                        >
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="s in statuses"
                                    :key="s.id"
                                    :value="String(s.id)"
                                >
                                    {{ s.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                <Card v-if="inquiry.property_title">
                    <CardHeader>
                        <CardTitle>Property</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Link
                            :href="propertiesShow.url(inquiry.property_slug!)"
                            class="text-primary hover:underline"
                        >
                            {{ inquiry.property_title }}
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
