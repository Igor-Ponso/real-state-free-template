<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import {
    FileText,
    GripVertical,
    Image as ImageIcon,
    Star,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

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
    floorPlans?: AdminMediaItem[];
}>();

// Tuning — must match StoreMediaRequest PHP constants.
const LIMITS = {
    MAX_IMAGES: 30,
    MAX_FLOOR_PLANS: 5,
    IMAGE_MAX_MB: 8,
    FLOOR_PLAN_MAX_MB: 15,
    IMAGE_ACCEPT: 'image/jpeg,image/png,image/webp',
    FLOOR_PLAN_ACCEPT: 'image/jpeg,image/png,image/webp,application/pdf',
};

const uploadProgress = ref(0);
const uploading = ref(false);
const uploadError = ref<string | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
const floorPlanInput = ref<HTMLInputElement | null>(null);
const imageGridRef = ref<HTMLDivElement | null>(null);
const floorPlanGridRef = ref<HTMLDivElement | null>(null);

const localImages = ref<AdminMediaItem[]>([...props.media]);
const localFloorPlans = ref<AdminMediaItem[]>([...(props.floorPlans ?? [])]);

watch(
    () => props.media,
    (m) => {
        localImages.value = [...m];
    },
);
watch(
    () => props.floorPlans,
    (m) => {
        localFloorPlans.value = [...(m ?? [])];
    },
);

const imagesRemaining = computed(
    () => LIMITS.MAX_IMAGES - localImages.value.length,
);
const floorPlansRemaining = computed(
    () => LIMITS.MAX_FLOOR_PLANS - localFloorPlans.value.length,
);

type Collection = 'images' | 'floor_plans';

const upload = (files: FileList | null, collection: Collection) => {
    if (!files?.length) {
        return;
    }

    uploadError.value = null;

    const formData = new FormData();
    Array.from(files).forEach((f) => formData.append('files[]', f));
    formData.append('collection', collection);

    uploading.value = true;
    uploadProgress.value = 0;

    router.post(`/admin/properties/${props.propertySlug}/media`, formData, {
        forceFormData: true,
        onProgress: (event) => {
            if (event?.percentage !== undefined) {
                uploadProgress.value = event.percentage;
            }
        },
        onError: (errors) => {
            const first = Object.values(errors)[0];
            uploadError.value =
                typeof first === 'string'
                    ? first
                    : 'Upload failed. Check file size and dimensions.';
        },
        onFinish: () => {
            uploading.value = false;
            uploadProgress.value = 0;

            if (imageInput.value) {
                imageInput.value.value = '';
            }
            if (floorPlanInput.value) {
                floorPlanInput.value.value = '';
            }
        },
    });
};

const deleteMedia = (mediaId: number) => {
    router.delete(mediaDestroy.url({ media: mediaId }));
};

const isPrimary = (item: AdminMediaItem): boolean => !!item.is_primary;
const isPdf = (item: AdminMediaItem): boolean =>
    item.mime_type === 'application/pdf';

const reorderHttp = useHttp({ ids: [] as number[] });
const primaryHttp = useHttp({});

const saveOrder = (ids: number[]) => {
    reorderHttp.ids = ids;
    reorderHttp.post(`/admin/properties/${props.propertySlug}/media/reorder`);
};

const setPrimary = (mediaId: number) => {
    primaryHttp.post(`/admin/media/${mediaId}/set-primary`, {
        onSuccess: () => {
            localImages.value = localImages.value.map((m) => ({
                ...m,
                is_primary: m.id === mediaId,
            }));
        },
    });
};

const initSortable = (el: HTMLDivElement | null) => {
    if (!el) {
        return;
    }

    Sortable.create(el, {
        animation: 200,
        handle: '.drag-handle',
        ghostClass: 'opacity-30',
        onEnd: () => {
            const ids = Array.from(el.children).map((child) =>
                Number(child.getAttribute('data-id')),
            );
            saveOrder(ids);
        },
    });
};

onMounted(() => {
    nextTick(() => {
        initSortable(imageGridRef.value);
        initSortable(floorPlanGridRef.value);
    });
});

watch(
    () => [localImages.value.length, localFloorPlans.value.length],
    () => {
        nextTick(() => {
            initSortable(imageGridRef.value);
            initSortable(floorPlanGridRef.value);
        });
    },
);
</script>

<template>
    <div class="space-y-10">
        <!-- Upload progress banner -->
        <div
            v-if="uploading"
            class="rounded-lg border border-landing-gold/40 bg-landing-gold/5 p-4"
        >
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-medium">Uploading...</span>
                <span class="tabular-nums">{{ uploadProgress }}%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-muted">
                <div
                    class="h-full bg-landing-gold transition-all duration-200"
                    :style="{ width: `${uploadProgress}%` }"
                />
            </div>
        </div>

        <div
            v-if="uploadError"
            class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive"
        >
            {{ uploadError }}
        </div>

        <!-- Property Photos -->
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold tracking-tight">
                        Property Photos
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        {{ localImages.length }} of
                        {{ LIMITS.MAX_IMAGES }} uploaded — drag to reorder, star
                        the cover photo
                    </p>
                </div>
            </header>

            <button
                type="button"
                :disabled="imagesRemaining <= 0 || uploading"
                class="flex w-full flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-8 transition-colors hover:border-landing-gold/60 focus-visible:ring-2 focus-visible:ring-landing-gold focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                @click="imageInput?.click()"
            >
                <ImageIcon class="mb-2 size-8 text-muted-foreground" />
                <p class="text-sm font-medium">
                    {{
                        imagesRemaining > 0
                            ? 'Click to upload photos'
                            : 'Photo limit reached'
                    }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    JPEG, PNG, or WebP · min 1024×768 · max
                    {{ LIMITS.IMAGE_MAX_MB }}MB · {{ imagesRemaining }} slot{{
                        imagesRemaining === 1 ? '' : 's'
                    }}
                    left
                </p>
                <input
                    ref="imageInput"
                    type="file"
                    multiple
                    :accept="LIMITS.IMAGE_ACCEPT"
                    class="hidden"
                    @change="
                        upload(
                            ($event.target as HTMLInputElement).files,
                            'images',
                        )
                    "
                />
            </button>

            <div v-if="localImages.length">
                <div
                    ref="imageGridRef"
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="item in localImages"
                        :key="item.id"
                        :data-id="item.id"
                        class="group relative overflow-hidden rounded-lg border bg-card"
                        :class="
                            isPrimary(item) ? 'ring-2 ring-landing-gold' : ''
                        "
                    >
                        <div
                            class="drag-handle absolute top-2 left-2 z-10 flex size-7 cursor-grab items-center justify-center rounded bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <GripVertical class="size-4" />
                        </div>

                        <button
                            type="button"
                            class="absolute top-2 right-10 z-10 flex size-7 items-center justify-center rounded transition-opacity"
                            :class="
                                isPrimary(item)
                                    ? 'bg-landing-gold text-white opacity-100'
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
                                :fill="
                                    isPrimary(item) ? 'currentColor' : 'none'
                                "
                            />
                        </button>

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
                                    class="absolute top-2 right-2 size-7 p-0 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle
                                        >Delete image</AlertDialogTitle
                                    >
                                    <AlertDialogDescription>
                                        This will permanently remove this image.
                                        This cannot be undone.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel
                                        >Cancel</AlertDialogCancel
                                    >
                                    <AlertDialogAction
                                        @click="deleteMedia(item.id)"
                                        >Delete</AlertDialogAction
                                    >
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>

                        <p
                            class="truncate px-2 py-1 text-xs"
                            :class="
                                isPrimary(item)
                                    ? 'bg-landing-gold/10 font-medium text-landing-gold'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ isPrimary(item) ? 'Cover Photo' : item.name }}
                        </p>
                    </div>
                </div>
            </div>

            <p v-else class="py-6 text-center text-sm text-muted-foreground">
                No photos uploaded yet.
            </p>
        </section>

        <!-- Floor Plans -->
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold tracking-tight">
                        Floor Plans
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        {{ localFloorPlans.length }} of
                        {{ LIMITS.MAX_FLOOR_PLANS }} uploaded — PDF or image
                    </p>
                </div>
            </header>

            <button
                type="button"
                :disabled="floorPlansRemaining <= 0 || uploading"
                class="flex w-full flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-8 transition-colors hover:border-landing-gold/60 focus-visible:ring-2 focus-visible:ring-landing-gold focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                @click="floorPlanInput?.click()"
            >
                <Upload class="mb-2 size-8 text-muted-foreground" />
                <p class="text-sm font-medium">
                    {{
                        floorPlansRemaining > 0
                            ? 'Click to upload floor plans'
                            : 'Floor plan limit reached'
                    }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    PDF, JPEG, PNG, or WebP · max
                    {{ LIMITS.FLOOR_PLAN_MAX_MB }}MB ·
                    {{ floorPlansRemaining }} slot{{
                        floorPlansRemaining === 1 ? '' : 's'
                    }}
                    left
                </p>
                <input
                    ref="floorPlanInput"
                    type="file"
                    multiple
                    :accept="LIMITS.FLOOR_PLAN_ACCEPT"
                    class="hidden"
                    @change="
                        upload(
                            ($event.target as HTMLInputElement).files,
                            'floor_plans',
                        )
                    "
                />
            </button>

            <div v-if="localFloorPlans.length">
                <div
                    ref="floorPlanGridRef"
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="item in localFloorPlans"
                        :key="item.id"
                        :data-id="item.id"
                        class="group relative overflow-hidden rounded-lg border bg-card"
                    >
                        <div
                            class="drag-handle absolute top-2 left-2 z-10 flex size-7 cursor-grab items-center justify-center rounded bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <GripVertical class="size-4" />
                        </div>

                        <div
                            v-if="isPdf(item)"
                            class="flex aspect-square w-full flex-col items-center justify-center bg-muted/50 p-4"
                        >
                            <FileText
                                class="mb-2 size-12 text-muted-foreground"
                            />
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                PDF
                            </p>
                            <a
                                :href="item.url"
                                target="_blank"
                                rel="noopener"
                                class="mt-2 text-xs text-landing-gold underline underline-offset-2 hover:text-landing-gold/80"
                            >
                                Open
                            </a>
                        </div>
                        <img
                            v-else
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
                                    class="absolute top-2 right-2 size-7 p-0 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle
                                        >Delete floor plan</AlertDialogTitle
                                    >
                                    <AlertDialogDescription>
                                        This will permanently remove this floor
                                        plan. This cannot be undone.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel
                                        >Cancel</AlertDialogCancel
                                    >
                                    <AlertDialogAction
                                        @click="deleteMedia(item.id)"
                                        >Delete</AlertDialogAction
                                    >
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>

                        <p
                            class="truncate px-2 py-1 text-xs text-muted-foreground"
                        >
                            {{ item.name }}
                        </p>
                    </div>
                </div>
            </div>

            <p v-else class="py-6 text-center text-sm text-muted-foreground">
                No floor plans uploaded yet.
            </p>
        </section>
    </div>
</template>
