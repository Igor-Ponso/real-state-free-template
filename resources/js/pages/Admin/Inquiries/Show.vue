<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Mail, Phone, User } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import { update } from '@/actions/App/Http/Controllers/Admin/InquiryController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AdminInquiry, LookupOption } from '@/types/admin';

const props = defineProps<{
    inquiry: AdminInquiry;
    statuses: LookupOption[];
}>();

const updateStatus = (statusId: string) => {
    router.put(update.url({ inquiry: props.inquiry.id }), {
        inquiry_status_id: Number(statusId),
    }, {
        onSuccess: () => {
            toast.success('Inquiry status updated.');
        },
    });
};
</script>

<template>
    <Head :title="`Inquiry from ${inquiry.name}`" />

    <div class="space-y-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child>
                <Link href="/admin/inquiries">
                    <ArrowLeft class="mr-1 size-4" /> Back
                </Link>
            </Button>
            <h1 class="text-2xl font-bold tracking-tight">Inquiry Details</h1>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main content -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Message</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-line leading-relaxed text-muted-foreground">
                            {{ inquiry.message }}
                        </p>
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
                        <div class="flex items-center gap-2">
                            <User class="size-4 text-muted-foreground" />
                            <span>{{ inquiry.name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Mail class="size-4 text-muted-foreground" />
                            <a :href="`mailto:${inquiry.email}`" class="text-primary hover:underline">
                                {{ inquiry.email }}
                            </a>
                        </div>
                        <div v-if="inquiry.phone" class="flex items-center gap-2">
                            <Phone class="size-4 text-muted-foreground" />
                            <span>{{ inquiry.phone }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Calendar class="size-4 text-muted-foreground" />
                            <span class="text-muted-foreground">{{ new Date(inquiry.created_at).toLocaleString() }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Select :model-value="String(inquiry.inquiry_status_id)" @update:model-value="updateStatus">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="s in statuses" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                <Card v-if="inquiry.property_title">
                    <CardHeader>
                        <CardTitle>Property</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Link :href="`/properties/${inquiry.property_slug}`" class="text-primary hover:underline">
                            {{ inquiry.property_title }}
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
