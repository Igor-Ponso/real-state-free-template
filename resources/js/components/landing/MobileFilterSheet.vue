<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import { ref } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

defineProps<{
    activeCount: number;
}>();

const emit = defineEmits<{
    apply: [];
    clear: [];
}>();

const open = ref(false);

const handleApply = () => {
    emit('apply');
    open.value = false;
};

const handleClear = () => {
    emit('clear');
    open.value = false;
};
</script>

<template>
    <div class="md:hidden">
        <Sheet v-model:open="open">
            <SheetTrigger as-child>
                <Button variant="outline" class="border-white/10 bg-white/5 text-white">
                    <Filter class="mr-2 size-4" />
                    Filters
                    <Badge v-if="activeCount" class="ml-2 bg-landing-gold text-white">
                        {{ activeCount }}
                    </Badge>
                </Button>
            </SheetTrigger>
            <SheetContent side="bottom" class="max-h-[85vh] overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Filter Properties</SheetTitle>
                </SheetHeader>
                <div class="py-4">
                    <slot />
                </div>
                <SheetFooter class="flex gap-2">
                    <Button class="flex-1 bg-landing-gold text-white hover:bg-landing-gold/90" @click="handleApply">
                        Apply Filters
                    </Button>
                    <Button variant="outline" class="flex-1" @click="handleClear">
                        Clear All
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </div>
</template>
