<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';

import { index as adminDashboard } from '@/actions/App/Http/Controllers/Admin/DashboardController';
import {
    edit as adminPropertiesEdit,
    index as adminPropertiesIndex,
} from '@/actions/App/Http/Controllers/Admin/PropertyController';
import PropertyAmenitiesTab from '@/components/admin/PropertyAmenitiesTab.vue';
import PropertyBasicInfoTab from '@/components/admin/PropertyBasicInfoTab.vue';
import PropertyLocationTab from '@/components/admin/PropertyLocationTab.vue';
import PropertyMediaTab from '@/components/admin/PropertyMediaTab.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { usePropertyForm } from '@/composables/usePropertyForm';
import type {
    AdminMediaItem,
    AdminProperty,
    LookupOption,
} from '@/types/admin';

const props = defineProps<{
    property: AdminProperty;
    media: AdminMediaItem[];
    floorPlans: AdminMediaItem[];
    propertyTypes: LookupOption[];
    cities: LookupOption[];
    listingTypes: LookupOption[];
    propertyStatuses: LookupOption[];
    unitAmenities: string[];
    buildingAmenities: string[];
    initialTab?: string;
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

// Dynamic title because it depends on the loaded property — defineOptions
// can't reach reactive props.
watchEffect(() => {
    setLayoutProps({
        title: props.property.title,
        subtitle:
            'Update listing details or manage photos and floor plans in the Media tab.',
        breadcrumbs: [
            { title: 'Dashboard', href: adminDashboard.url() },
            { title: 'Properties', href: adminPropertiesIndex.url() },
            {
                title: props.property.title,
                href: adminPropertiesEdit.url({ property: props.property.slug }),
            },
        ],
    });
});
</script>

<template>
    <Head :title="`Edit: ${property.title}`" />

    <div class="space-y-6 p-6">
        <form @submit.prevent="submit">
            <Tabs :default-value="initialTab ?? 'basic'" class="w-full">
                <TabsList>
                    <TabsTrigger value="basic">Basic Info</TabsTrigger>
                    <TabsTrigger value="location">Location</TabsTrigger>
                    <TabsTrigger value="amenities">Amenities & SEO</TabsTrigger>
                    <TabsTrigger value="media">Media</TabsTrigger>
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

                <TabsContent value="media" class="mt-6">
                    <PropertyMediaTab
                        :property-slug="property.slug"
                        :media="media"
                        :floor-plans="floorPlans"
                    />
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
