<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ImageIcon, Lock } from 'lucide-vue-next';

import PropertyAmenitiesTab from '@/components/admin/PropertyAmenitiesTab.vue';
import PropertyBasicInfoTab from '@/components/admin/PropertyBasicInfoTab.vue';
import PropertyLocationTab from '@/components/admin/PropertyLocationTab.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { usePropertyForm } from '@/composables/usePropertyForm';
import type { LookupOption } from '@/types/admin';

const props = defineProps<{
    propertyTypes: LookupOption[];
    cities: LookupOption[];
    listingTypes: LookupOption[];
    propertyStatuses: LookupOption[];
    unitAmenities: string[];
    buildingAmenities: string[];
}>();

const {
    form,
    priceDisplay,
    submit,
    propertyTypes,
    cities,
    listingTypes,
    propertyStatuses,
    unitAmenities,
    buildingAmenities,
} = usePropertyForm(props);
</script>

<template>
    <Head title="Create Property" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="font-serif text-3xl font-semibold tracking-tight">
                Create Property
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Fill out the details below. Photos and floor plans can be added
                once the listing is saved.
            </p>
        </div>

        <form @submit.prevent="submit">
            <Tabs default-value="basic" class="w-full">
                <TabsList>
                    <TabsTrigger value="basic">Basic Info</TabsTrigger>
                    <TabsTrigger value="location">Location</TabsTrigger>
                    <TabsTrigger value="amenities"
                        >Amenities &amp; SEO</TabsTrigger
                    >
                    <TabsTrigger value="media" disabled class="gap-1.5">
                        <Lock class="size-3.5" /> Media
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="basic" class="mt-6">
                    <PropertyBasicInfoTab
                        v-model:price-display="priceDisplay"
                        :form="form"
                        :property-types="propertyTypes"
                        :listing-types="listingTypes"
                        :property-statuses="propertyStatuses"
                    />
                </TabsContent>

                <TabsContent value="location" class="mt-6">
                    <PropertyLocationTab :form="form" :cities="cities" />
                </TabsContent>

                <TabsContent value="amenities" class="mt-6">
                    <PropertyAmenitiesTab
                        :form="form"
                        :unit-amenities="unitAmenities"
                        :building-amenities="buildingAmenities"
                    />
                </TabsContent>
            </Tabs>

            <!-- Media pre-save hint -->
            <div
                class="mt-8 flex items-start gap-3 rounded-lg border border-landing-gold/30 bg-landing-gold/5 p-4 text-sm"
            >
                <ImageIcon class="mt-0.5 size-5 shrink-0 text-landing-gold" />
                <div>
                    <p class="font-medium">
                        Photos and floor plans unlock after save
                    </p>
                    <p class="mt-0.5 text-muted-foreground">
                        We need a property ID to attach your uploads to. Once
                        saved, you'll land directly on the Media tab to add
                        photos.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{
                        form.processing ? 'Creating...' : 'Save and add photos'
                    }}
                </Button>
            </div>
        </form>
    </div>
</template>
