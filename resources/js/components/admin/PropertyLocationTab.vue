<script setup lang="ts">
/* eslint-disable vue/no-mutating-props -- Inertia useForm() reactive proxy is designed for child mutation */
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { LookupOption } from '@/types/admin';

defineProps<{
    form: Record<string, unknown>;
    cities: LookupOption[];
}>();
</script>

<template>
    <div class="space-y-6">
        <div>
            <Label for="address">Address</Label>
            <Input id="address" v-model="form.address" class="mt-1" />
            <p
                v-if="form.errors?.address"
                class="mt-1 text-sm text-destructive"
            >
                {{ form.errors.address }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label>City</Label>
                <Select v-model="form.city_id">
                    <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="c in cities"
                            :key="c.id"
                            :value="String(c.id)"
                            >{{ c.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label for="state">State/Province</Label>
                <Input
                    id="state"
                    v-model="form.state"
                    maxlength="10"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="zip">ZIP/Postal Code</Label>
                <Input
                    id="zip"
                    v-model="form.zip_code"
                    maxlength="10"
                    class="mt-1"
                />
            </div>
        </div>

        <div>
            <Label for="neighborhood">Neighborhood</Label>
            <Input id="neighborhood" v-model="form.neighborhood" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <Label for="lat">Latitude</Label>
                <Input
                    id="lat"
                    v-model="form.latitude"
                    type="number"
                    step="0.0000001"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="lng">Longitude</Label>
                <Input
                    id="lng"
                    v-model="form.longitude"
                    type="number"
                    step="0.0000001"
                    class="mt-1"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <Label for="lot">Lot Size (sqft)</Label>
                <Input
                    id="lot"
                    v-model="form.lot_size_sqft"
                    type="number"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="floor">Floor</Label>
                <Input
                    id="floor"
                    v-model="form.floor"
                    type="number"
                    class="mt-1"
                />
            </div>
            <div>
                <Label for="total-floors">Total Floors</Label>
                <Input
                    id="total-floors"
                    v-model="form.total_floors"
                    type="number"
                    class="mt-1"
                />
            </div>
        </div>
    </div>
</template>
