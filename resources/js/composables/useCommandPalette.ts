import { useMagicKeys, whenever } from '@vueuse/core';
import { ref, watch } from 'vue';

import { search as propertySearchRoute } from '@/routes/properties';
import type { PropertySearchResult } from '@/types/landing';

export function useCommandPalette() {
    const open = ref(false);
    const query = ref('');
    const results = ref<PropertySearchResult[]>([]);
    const isSearching = ref(false);

    const { meta_k, ctrl_k } = useMagicKeys({
        passive: false,
        onEventFired(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
            }
        },
    });

    whenever(meta_k!, () => {
        open.value = !open.value;
    });
    whenever(ctrl_k!, () => {
        open.value = !open.value;
    });

    let abortController: AbortController | null = null;
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    async function searchProperties(q: string): Promise<void> {
        abortController?.abort();

        if (q.length < 2) {
            results.value = [];
            isSearching.value = false;

            return;
        }

        isSearching.value = true;
        abortController = new AbortController();

        try {
            const url = propertySearchRoute.url({ query: { q } });
            const response = await fetch(url, {
                signal: abortController.signal,
            });

            if (response.ok) {
                results.value = await response.json();
            }
        } catch (e) {
            if (e instanceof DOMException && e.name === 'AbortError') {
                return;
            }
        } finally {
            isSearching.value = false;
        }
    }

    watch(query, (value) => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        debounceTimer = setTimeout(() => searchProperties(value), 300);
    });

    function reset(): void {
        query.value = '';
        results.value = [];
        isSearching.value = false;
    }

    return {
        open,
        query,
        results,
        isSearching,
        reset,
    };
}
