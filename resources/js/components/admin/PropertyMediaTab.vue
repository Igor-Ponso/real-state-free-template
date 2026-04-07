<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

import { destroy as mediaDestroy } from '@/actions/App/Http/Controllers/Admin/MediaController';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import type { AdminMediaItem } from '@/types/admin';

const props = defineProps<{
    propertySlug: string;
    media: AdminMediaItem[];
}>();

const uploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const handleUpload = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;

    if (!files?.length) {
return;
}

    const formData = new FormData();
    Array.from(files).forEach((f) => formData.append('files[]', f));
    formData.append('collection', 'images');

    uploading.value = true;
    router.post(`/admin/properties/${props.propertySlug}/media`, formData, {
        forceFormData: true,
        onFinish: () => {
            uploading.value = false;

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const deleteMedia = (mediaId: number) => {
    router.delete(mediaDestroy.url({ media: mediaId }));
};
</script>

<template>
    <div class="space-y-6">
        <!-- Upload zone -->
        <button
            type="button"
            class="flex w-full flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-8 transition-colors hover:border-muted-foreground/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            @click="fileInput?.click()"
        >
            <Upload class="mb-2 size-8 text-muted-foreground" />
            <p class="text-sm text-muted-foreground">Click to upload images</p>
            <p class="mt-1 text-xs text-muted-foreground/60">JPEG, PNG, WebP — max 10MB each</p>
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/jpeg,image/png,image/webp"
                class="hidden"
                @change="handleUpload"
            />
        </button>

        <p v-if="uploading" class="text-sm text-muted-foreground">Uploading...</p>

        <!-- Image grid -->
        <div v-if="media.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="item in media"
                :key="item.id"
                class="group relative overflow-hidden rounded-lg border"
            >
                <img
                    :src="item.url"
                    :alt="item.name"
                    class="aspect-square w-full object-cover"
                    loading="lazy"
                />
                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button
                            variant="destructive"
                            size="sm"
                            class="absolute top-2 right-2 size-8 p-0 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete Image</AlertDialogTitle>
                            <AlertDialogDescription>
                                This will permanently remove this image. This action cannot be undone.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click="deleteMedia(item.id)">Delete</AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
                <p class="truncate px-2 py-1 text-xs text-muted-foreground">{{ item.name }}</p>
            </div>
        </div>

        <p v-else class="py-6 text-center text-sm text-muted-foreground">
            No images uploaded yet.
        </p>
    </div>
</template>
