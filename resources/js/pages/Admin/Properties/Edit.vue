<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import PropertyAmenitiesTab from '@/components/admin/PropertyAmenitiesTab.vue';
import PropertyBasicInfoTab from '@/components/admin/PropertyBasicInfoTab.vue';
import PropertyLocationTab from '@/components/admin/PropertyLocationTab.vue';
import PropertyMediaTab from '@/components/admin/PropertyMediaTab.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { usePropertyForm } from '@/composables/usePropertyForm';
import type { AdminMediaItem, AdminProperty, LookupOption } from '@/types/admin';

const props = defineProps<{
    property: AdminProperty;
    media: AdminMediaItem[];
    propertyTypes: LookupOption[];
    cities: LookupOption[];
    listingTypes: LookupOption[];
    propertyStatuses: LookupOption[];
    unitAmenities: string[];
    buildingAmenities: string[];
}>();

const { form, submit, propertyTypes, cities, listingTypes, propertyStatuses, unitAmenities, buildingAmenities } = usePropertyForm(props);
</script>

<template>
    <Head :title="`Edit: ${property.title}`" />

    <div class="space-y-6 p-6">
        <h1 class="text-2xl font-bold tracking-tight">Edit Property</h1>

        <form @submit.prevent="submit">
            <Tabs default-value="basic" class="w-full">
                <TabsList>
                    <TabsTrigger value="basic">Basic Info</TabsTrigger>
                    <TabsTrigger value="location">Location</TabsTrigger>
                    <TabsTrigger value="amenities">Amenities & SEO</TabsTrigger>
                    <TabsTrigger value="media">Media</TabsTrigger>
                </TabsList>

                <TabsContent value="basic" class="mt-6">
                    <PropertyBasicInfoTab
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

                <TabsContent value="media" class="mt-6">
                    <PropertyMediaTab :property-slug="property.slug" :media="media" />
                </TabsContent>
            </Tabs>

            <div class="mt-8 flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>
