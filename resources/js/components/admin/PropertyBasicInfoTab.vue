<script setup lang="ts">
/* eslint-disable vue/no-mutating-props -- Inertia useForm() reactive proxy is designed for child mutation */
import { vMaska } from 'maska/vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import type { LookupOption } from '@/types/admin';

defineProps<{
    form: Record<string, unknown>;
    propertyTypes: LookupOption[];
    listingTypes: LookupOption[];
    propertyStatuses: LookupOption[];
}>();

const priceDisplay = defineModel<string>('priceDisplay', { required: true });
</script>

<template>
    <div class="space-y-6">
        <div>
            <Label for="title">Title</Label>
            <Input id="title" v-model="form.title" class="mt-1" />
            <p v-if="form.errors?.title" class="mt-1 text-sm text-destructive">
                {{ form.errors.title }}
            </p>
        </div>

        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                v-model="form.description"
                rows="6"
                class="mt-1"
            />
            <p
                v-if="form.errors?.description"
                class="mt-1 text-sm text-destructive"
            >
                {{ form.errors.description }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label>Property Type</Label>
                <Select v-model="form.property_type_id">
                    <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="t in propertyTypes"
                            :key="t.id"
                            :value="String(t.id)"
                            >{{ t.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label>Listing Type</Label>
                <Select v-model="form.listing_type_id">
                    <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="t in listingTypes"
                            :key="t.id"
                            :value="String(t.id)"
                            >{{ t.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label>Status</Label>
                <Select v-model="form.property_status_id">
                    <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="s in propertyStatuses"
                            :key="s.id"
                            :value="String(s.id)"
                            >{{ s.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label for="price">Price ($)</Label>
                <Input
                    id="price"
                    v-model="priceDisplay"
                    v-maska="{
                        number: {
                            locale: 'en-US',
                            fraction: 0,
                            unsigned: true,
                        },
                    }"
                    type="text"
                    inputmode="numeric"
                    placeholder="$0"
                    class="mt-1"
                />
                <p
                    v-if="form.errors?.price"
                    class="mt-1 text-sm text-destructive"
                >
                    {{ form.errors.price }}
                </p>
            </div>
            <div>
                <Label for="currency">Currency</Label>
                <Input
                    id="currency"
                    v-model="form.currency"
                    maxlength="3"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="parking">Parking Spaces</Label>
                <Input
                    id="parking"
                    v-model="form.parking_spaces"
                    type="number"
                    class="mt-1"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <Label for="bedrooms">Bedrooms</Label>
                <Input
                    id="bedrooms"
                    v-model="form.bedrooms"
                    type="number"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="bathrooms">Bathrooms</Label>
                <Input
                    id="bathrooms"
                    v-model="form.bathrooms"
                    type="number"
                    step="0.5"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="area">Area (sqft)</Label>
                <Input
                    id="area"
                    v-model="form.area_sqft"
                    type="number"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="year">Year Built</Label>
                <Input
                    id="year"
                    v-model="form.year_built"
                    type="number"
                    class="mt-1"
                />
            </div>
        </div>

        <div class="flex flex-wrap gap-6">
            <div class="flex items-center gap-2">
                <Switch id="published" v-model:checked="form.is_published" />
                <Label for="published">Published</Label>
            </div>
            <div class="flex items-center gap-2">
                <Switch id="featured" v-model:checked="form.is_featured" />
                <Label for="featured">Featured</Label>
            </div>
            <div class="flex items-center gap-2">
                <Switch id="pets" v-model:checked="form.pets_allowed" />
                <Label for="pets">Pets Allowed</Label>
            </div>
        </div>
    </div>
</template>
