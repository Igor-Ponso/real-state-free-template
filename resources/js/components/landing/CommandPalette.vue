<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Bath,
    BedDouble,
    FileText,
    Home,
    LayoutDashboard,
    Loader2,
    MapPin,
    Search,
    Settings,
} from 'lucide-vue-next';
import { watch } from 'vue';

import { Button } from '@/components/ui/button';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import { useCommandPalette } from '@/composables/useCommandPalette';
import { home, dashboard } from '@/routes';
import { index as propertiesIndex, show as propertyShow } from '@/routes/properties';
import { edit as settingsProfile } from '@/routes/profile';

defineProps<{
    scrolled?: boolean;
}>();

const { open, query, results, isSearching, reset } = useCommandPalette();

const pages = [
    { label: 'Home', href: home.url(), icon: Home },
    { label: 'Properties', href: propertiesIndex.url(), icon: Search },
    { label: 'Dashboard', href: dashboard.url(), icon: LayoutDashboard },
    { label: 'Settings', href: settingsProfile.url(), icon: Settings },
];

function navigate(href: string): void {
    open.value = false;
    reset();
    router.visit(href);
}

function navigateToProperty(slug: string): void {
    open.value = false;
    reset();
    router.visit(propertyShow.url(slug));
}

function searchAll(): void {
    if (!query.value) return;
    open.value = false;
    const q = query.value;
    reset();
    router.visit(propertiesIndex.url({ query: { search: q } }));
}

watch(open, (isOpen) => {
    if (!isOpen) reset();
});
</script>

<template>
    <div>
        <Button
            variant="ghost"
            size="icon"
            class="h-9 w-9"
            :class="scrolled ? '' : 'text-white hover:bg-white/10'"
            aria-label="Search"
            @click="open = true"
        >
            <Search class="size-4" />
        </Button>

        <CommandDialog v-model:open="open" title="Search" description="Search properties and navigate pages">
            <CommandInput v-model="query" placeholder="Search properties, pages..." />
            <CommandList>
                <CommandEmpty>
                    <div v-if="isSearching" class="flex items-center justify-center gap-2 py-2">
                        <Loader2 class="size-4 animate-spin text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">Searching...</span>
                    </div>
                    <span v-else>No results found.</span>
                </CommandEmpty>

                <!-- Property results -->
                <CommandGroup v-if="results.length" heading="Properties">
                    <CommandItem
                        v-for="property in results"
                        :key="property.id"
                        :value="`property-${property.slug}-${property.title}`"
                        class="flex items-center gap-3"
                        @select="navigateToProperty(property.slug)"
                    >
                        <img
                            v-if="property.image"
                            :src="property.image"
                            :alt="property.title"
                            class="size-10 shrink-0 rounded-md object-cover"
                        />
                        <FileText v-else class="size-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ property.title }}</p>
                            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                <span v-if="property.location" class="flex items-center gap-1">
                                    <MapPin class="size-3" />
                                    {{ property.location }}
                                </span>
                                <span v-if="property.bedrooms" class="flex items-center gap-1">
                                    <BedDouble class="size-3" />
                                    {{ property.bedrooms }}
                                </span>
                                <span v-if="property.bathrooms" class="flex items-center gap-1">
                                    <Bath class="size-3" />
                                    {{ property.bathrooms }}
                                </span>
                            </div>
                        </div>
                        <span class="shrink-0 text-xs font-medium text-muted-foreground">
                            {{ property.price }}
                        </span>
                    </CommandItem>
                </CommandGroup>

                <!-- Search all action -->
                <CommandGroup v-if="query.length >= 2" heading="Actions">
                    <CommandItem :value="`search-all-${query}`" @select="searchAll">
                        <Search class="mr-2 size-4" />
                        <span>Search all properties for "{{ query }}"</span>
                    </CommandItem>
                </CommandGroup>

                <CommandSeparator v-if="results.length || query.length >= 2" />

                <!-- Page navigation -->
                <CommandGroup heading="Pages">
                    <CommandItem
                        v-for="page in pages"
                        :key="page.href"
                        :value="`page-${page.label}`"
                        @select="navigate(page.href)"
                    >
                        <component :is="page.icon" class="mr-2 size-4" />
                        <span>{{ page.label }}</span>
                    </CommandItem>
                </CommandGroup>
            </CommandList>
        </CommandDialog>
    </div>
</template>
