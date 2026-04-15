<script setup lang="ts">
import { Copy, Mail, MessageCircle, Share2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

/**
 * Inline share dropdown with WhatsApp, Email, and Copy-Link actions.
 *
 * Uses the canonical URL (passed from the server, not constructed client-side)
 * to avoid SSR hydration mismatches and to respect any CDN/custom-domain setup.
 *
 * WhatsApp: opens wa.me share link in a new tab.
 * Email:    mailto with prefilled subject/body.
 * Copy:     navigator.clipboard + toast feedback.
 */
const props = withDefaults(
    defineProps<{
        url: string;
        title: string;
        /** Styling variant: ghost (transparent) or outline. */
        variant?: 'ghost' | 'outline';
    }>(),
    { variant: 'outline' },
);

const shareText = `Check out this property: ${props.title}`;
const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(`${shareText} — ${props.url}`)}`;
const mailtoUrl = `mailto:?subject=${encodeURIComponent(props.title)}&body=${encodeURIComponent(`${shareText}\n\n${props.url}`)}`;

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.url);
        toast.success('Link copied to clipboard.');
    } catch {
        toast.error(
            'Unable to copy. Please copy the URL from the address bar.',
        );
    }
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button :variant="variant" size="sm" class="gap-1.5">
                <Share2 class="size-4" /> Share
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-44">
            <DropdownMenuItem as-child>
                <a :href="whatsappUrl" target="_blank" rel="noopener">
                    <MessageCircle class="size-4" /> WhatsApp
                </a>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <a :href="mailtoUrl"> <Mail class="size-4" /> Email </a>
            </DropdownMenuItem>
            <DropdownMenuItem @select="copyLink">
                <Copy class="size-4" /> Copy link
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
