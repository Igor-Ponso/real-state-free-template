<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { GripVertical, Star, Trash2, Upload } from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { nextTick, onMounted, ref, watch } from 'vue';

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
const gridRef = ref<HTMLDivElement | null>(null);
const localMedia = ref<AdminMediaItem[]>([...props.media]);

// Sync when parent updates (e.g., after upload)
watch(
    () => props.media,
    (newMedia) => {
        localMedia.value = [...newMedia];
    },
);

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

const isPrimary = (item: AdminMediaItem): boolean =>
    !!(item as Record<string, unknown>).is_primary;

const saveOrder = async (ids: number[]) => {
    const csrfToken = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    )?.content;

    await fetch(`/admin/properties/${props.propertySlug}/media/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
            Accept: 'application/json',
        },
        body: JSON.stringify({ ids }),
    });
};

const setPrimary = async (mediaId: number) => {
    const csrfToken = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    )?.content;

    await fetch(`/admin/media/${mediaId}/set-primary`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
            Accept: 'application/json',
        },
    });

    // Update local state
    localMedia.value = localMedia.value.map((m) => ({
        ...m,
        is_primary: m.id === mediaId,
    })) as AdminMediaItem[];
};

// Initialize SortableJS on the grid
const initSortable = () => {
    if (!gridRef.value) {
        return;
    }

    Sortable.create(gridRef.value, {
        animation: 200,
        handle: '.drag-handle',
        ghostClass: 'opacity-30',
        onEnd: () => {
            const ids = Array.from(gridRef.value!.children).map((el) =>
                Number(el.getAttribute('data-id')),
            );
            saveOrder(ids);
        },
    });
};

onMounted(() => {
    nextTick(initSortable);
});

watch(
    () => localMedia.value.length,
    () => {
        nextTick(initSortable);
    },
);
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
            <p class="mt-1 text-xs text-muted-foreground/60">
                JPEG, PNG, WebP — max 10MB each
            </p>
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/jpeg,image/png,image/webp"
                class="hidden"
                @change="handleUpload"
            />
        </button>

        <p v-if="uploading" class="text-sm text-muted-foreground">
            Uploading...
        </p>

        <!-- Image grid with drag-and-drop reorder -->
        <div v-if="localMedia.length">
            <p class="mb-3 text-xs text-muted-foreground">
                Drag to reorder · Click star to set cover photo
            </p>
            <div
                ref="gridRef"
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
            >
                <div
                    v-for="item in localMedia"
                    :key="item.id"
                    :data-id="item.id"
                    class="group relative overflow-hidden rounded-lg border"
                    :class="
                        isPrimary(item)
                            ? 'ring-2 ring-yellow-500'
                            : ''
                    "
                >
                    <!-- Drag handle -->
                    <div
                        class="drag-handle absolute top-2 left-2 z-10 flex size-7 cursor-grab items-center justify-center rounded bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100"
                    >
                        <GripVertical class="size-4" />
                    </div>

                    <!-- Primary star -->
                    <button
                        type="button"
                        class="absolute top-2 right-10 z-10 flex size-7 items-center justify-center rounded transition-opacity"
                        :class="
                            isPrimary(item)
                                ? 'bg-yellow-500 text-white opacity-100'
                                : 'bg-black/50 text-white opacity-0 group-hover:opacity-100'
                        "
                        :title="
                            isPrimary(item)
                                ? 'Cover photo'
                                : 'Set as cover photo'
                        "
                        @click="setPrimary(item.id)"
                    >
                        <Star
                            class="size-4"
                            :fill="isPrimary(item) ? 'currentColor' : 'none'"
                        />
                    </button>

                    <img
                        :src="item.url"
                        :alt="item.name"
                        class="aspect-square w-full object-cover"
                        loading="lazy"
                    />

                    <!-- Delete -->
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button
                                variant="destructive"
                                size="sm"
                                class="absolute top-2 right-2 size-7 p-0 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle
                                    >Delete Image</AlertDialogTitle
                                >
                                <AlertDialogDescription>
                                    This will permanently remove this image.
                                    This action cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction
                                    @click="deleteMedia(item.id)"
                                    >Delete</AlertDialogAction
                                >
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <!-- Primary badge -->
                    <p
                        class="truncate px-2 py-1 text-xs"
                        :class="
                            isPrimary(item)
                                ? 'bg-yellow-500/10 font-medium text-yellow-700'
                                : 'text-muted-foreground'
                        "
                    >
                        {{ isPrimary(item) ? 'Cover Photo' : item.name }}
                    </p>
                </div>
            </div>
        </div>

        <p v-else class="py-6 text-center text-sm text-muted-foreground">
            No images uploaded yet.
        </p>
    </div>
</template>
