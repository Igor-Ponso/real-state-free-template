<script setup lang="ts">
/* eslint-disable vue/no-mutating-props -- Inertia useForm() reactive proxy is designed for child mutation */
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useAmenityIcons } from '@/composables/useAmenityIcons';

defineProps<{
    form: Record<string, unknown>;
    unitAmenities: string[];
    buildingAmenities: string[];
}>();

const { getIcon } = useAmenityIcons();

const formatLabel = (slug: string): string =>
    slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const toggleAmenity = (list: string[], slug: string) => {
    const idx = list.indexOf(slug);

    if (idx >= 0) {
        list.splice(idx, 1);
    } else {
        list.push(slug);
    }
};
</script>

<template>
    <div class="space-y-8">
        <div>
            <h3 class="mb-4 text-sm font-medium">Unit Amenities</h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <label
                    v-for="amenity in unitAmenities"
                    :key="amenity"
                    class="flex cursor-pointer items-center gap-2 rounded-md border p-3 transition-colors hover:bg-muted"
                >
                    <Checkbox
                        :checked="
                            (form.unit_amenities as string[]).includes(amenity)
                        "
                        @update:checked="
                            toggleAmenity(
                                form.unit_amenities as string[],
                                amenity,
                            )
                        "
                    />
                    <component
                        :is="getIcon(amenity)"
                        v-if="getIcon(amenity)"
                        class="size-4 text-muted-foreground"
                    />
                    <span class="text-sm">{{ formatLabel(amenity) }}</span>
                </label>
            </div>
        </div>

        <div>
            <h3 class="mb-4 text-sm font-medium">Building Amenities</h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <label
                    v-for="amenity in buildingAmenities"
                    :key="amenity"
                    class="flex cursor-pointer items-center gap-2 rounded-md border p-3 transition-colors hover:bg-muted"
                >
                    <Checkbox
                        :checked="
                            (form.building_amenities as string[]).includes(
                                amenity,
                            )
                        "
                        @update:checked="
                            toggleAmenity(
                                form.building_amenities as string[],
                                amenity,
                            )
                        "
                    />
                    <component
                        :is="getIcon(amenity)"
                        v-if="getIcon(amenity)"
                        class="size-4 text-muted-foreground"
                    />
                    <span class="text-sm">{{ formatLabel(amenity) }}</span>
                </label>
            </div>
        </div>

        <div>
            <Label for="meta-title">SEO Title</Label>
            <input
                id="meta-title"
                v-model="form.meta_title"
                type="text"
                maxlength="70"
                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
            />
        </div>

        <div>
            <Label for="meta-desc">SEO Description</Label>
            <input
                id="meta-desc"
                v-model="form.meta_description"
                type="text"
                maxlength="160"
                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
            />
        </div>
    </div>
</template>
