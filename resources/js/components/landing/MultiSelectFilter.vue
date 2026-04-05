<script setup lang="ts">
/**
 * Multi-select filter using shadcn-vue's TagsInput + reka-ui Listbox pattern.
 * @see https://shadcn-vue.com/docs/components/tags-input — "Tags with Listbox" example
 */
import { CheckIcon, ChevronDown } from 'lucide-vue-next';
import {
    ListboxContent,
    ListboxFilter,
    ListboxItem,
    ListboxItemIndicator,
    ListboxRoot,
    useFilter,
} from 'reka-ui';
import { computed, ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverAnchor,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
import type { FilterOption } from '@/types/landing';

const props = withDefaults(
    defineProps<{
        label: string;
        options: FilterOption[];
        placeholder?: string;
        maxVisible?: number;
    }>(),
    { placeholder: 'Search & select...', maxVisible: 2 },
);

const model = defineModel<string[]>({ required: true });

const searchTerm = ref('');
const open = ref(false);

const { contains } = useFilter({ sensitivity: 'base' });

const filteredOptions = computed(() =>
    searchTerm.value === ''
        ? props.options
        : props.options.filter((o) => contains(o.name, searchTerm.value)),
);

const labelForSlug = (slug: string): string =>
    props.options.find((o) => o.slug === slug)?.name ?? slug;

const visibleTags = computed(() => model.value.slice(0, props.maxVisible));
const hiddenCount = computed(() =>
    Math.max(0, model.value.length - props.maxVisible),
);

watch(searchTerm, (val) => {
    if (val) {
        open.value = true;
    }
});
</script>

<template>
    <div>
        <label
            class="mb-1 block font-body text-2xs font-medium tracking-wider text-white/40 uppercase"
        >
            {{ label }}
        </label>
        <Popover v-model:open="open">
            <ListboxRoot v-model="model" multiple highlight-on-hover>
                <PopoverAnchor class="inline-flex w-full">
                    <TagsInput
                        v-slot="{ modelValue: tags }"
                        v-model="model"
                        class="h-auto min-h-9 w-full gap-1 border-white/10 bg-white/5 px-2 text-sm text-white"
                    >
                        <!-- Visible tags (limited) -->
                        <TagsInputItem
                            v-for="item in visibleTags"
                            :key="item"
                            :value="item"
                            class="h-6 gap-1 border-landing-gold/30 bg-landing-gold/20 px-1.5 text-2xs text-white"
                        >
                            <TagsInputItemText>{{
                                labelForSlug(item)
                            }}</TagsInputItemText>
                            <TagsInputItemDelete
                                class="size-3.5 text-white/50 hover:text-white"
                            />
                        </TagsInputItem>

                        <!-- +N badge -->
                        <Badge
                            v-if="hiddenCount > 0"
                            class="h-6 shrink-0 border-0 bg-landing-gold/30 px-1.5 text-2xs text-white"
                        >
                            +{{ hiddenCount }}
                        </Badge>

                        <!-- Hidden tags (rendered but invisible, so TagsInput tracks them) -->
                        <TagsInputItem
                            v-for="item in model.slice(maxVisible)"
                            :key="'hidden-' + item"
                            :value="item"
                            class="hidden"
                        >
                            <TagsInputItemText />
                        </TagsInputItem>

                        <ListboxFilter v-model="searchTerm" as-child>
                            <TagsInputInput
                                :placeholder="!tags.length ? placeholder : ''"
                                class="min-w-16 text-sm text-white placeholder:text-white/30"
                                @keydown.enter.prevent
                                @keydown.down="open = true"
                            />
                        </ListboxFilter>

                        <PopoverTrigger as-child>
                            <Button
                                size="icon"
                                variant="ghost"
                                class="order-last ml-auto size-6 shrink-0 self-center text-white/40 hover:text-white"
                            >
                                <ChevronDown class="size-3.5" />
                            </Button>
                        </PopoverTrigger>
                    </TagsInput>
                </PopoverAnchor>

                <PopoverContent
                    class="w-[--reka-popper-anchor-width] p-1"
                    @open-auto-focus.prevent
                >
                    <ListboxContent
                        class="max-h-75 scroll-py-1 overflow-x-hidden overflow-y-auto"
                        tabindex="0"
                    >
                        <!-- Any (select all / clear) -->
                        <button
                            class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm font-medium outline-hidden select-none"
                            :class="
                                !model.length
                                    ? 'bg-accent text-accent-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            "
                            @click="
                                model = [];
                                searchTerm = '';
                                open = false;
                            "
                        >
                            <CheckIcon v-if="!model.length" class="size-4" />
                            <span :class="!model.length ? '' : 'pl-6'"
                                >Any</span
                            >
                        </button>
                        <div class="my-1 h-px bg-border" />

                        <ListboxItem
                            v-for="opt in filteredOptions"
                            :key="opt.slug"
                            :value="opt.slug"
                            class="relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-highlighted:bg-accent data-highlighted:text-accent-foreground"
                            @select="
                                () => {
                                    searchTerm = '';
                                }
                            "
                        >
                            <span>{{ opt.name }}</span>
                            <ListboxItemIndicator
                                class="ml-auto inline-flex items-center justify-center"
                            >
                                <CheckIcon class="size-4" />
                            </ListboxItemIndicator>
                        </ListboxItem>
                        <p
                            v-if="!filteredOptions.length"
                            class="px-2 py-4 text-center text-sm text-muted-foreground"
                        >
                            No results found.
                        </p>
                    </ListboxContent>
                </PopoverContent>
            </ListboxRoot>
        </Popover>
    </div>
</template>
