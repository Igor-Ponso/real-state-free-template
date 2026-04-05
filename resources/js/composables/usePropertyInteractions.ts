/**
 * Manages property favorites and dismissed states.
 *
 * Guest: persists in localStorage via VueUse's useLocalStorage (reactive, cross-tab synced).
 * Logged in: syncs to backend Favorite model on login (future).
 *
 * @see https://vueuse.org/core/useLocalStorage/
 */
import { useLocalStorage } from '@vueuse/core';
import { computed } from 'vue';

interface PropertyInteractions {
    favoriteIds: ReturnType<typeof useLocalStorage<number[]>>;
    dismissedIds: ReturnType<typeof useLocalStorage<number[]>>;
    isFavorite: (id: number) => boolean;
    isDismissed: (id: number) => boolean;
    toggleFavorite: (id: number) => void;
    toggleDismissed: (id: number) => void;
    favoriteCount: ReturnType<typeof computed<number>>;
    dismissedCount: ReturnType<typeof computed<number>>;
    clearDismissed: () => void;
}

export const usePropertyInteractions = (): PropertyInteractions => {
    const favoriteIds = useLocalStorage<number[]>('sovereign-favorites', []);
    const dismissedIds = useLocalStorage<number[]>('sovereign-dismissed', []);

    const isFavorite = (id: number): boolean => favoriteIds.value.includes(id);
    const isDismissed = (id: number): boolean => dismissedIds.value.includes(id);

    const toggleFavorite = (id: number) => {
        const idx = favoriteIds.value.indexOf(id);

        if (idx >= 0) {
            favoriteIds.value.splice(idx, 1);
        } else {
            favoriteIds.value.push(id);
            // Remove from dismissed if favoriting
            const dIdx = dismissedIds.value.indexOf(id);

            if (dIdx >= 0) {
                dismissedIds.value.splice(dIdx, 1);
            }
        }
    };

    const toggleDismissed = (id: number) => {
        const idx = dismissedIds.value.indexOf(id);

        if (idx >= 0) {
            dismissedIds.value.splice(idx, 1);
        } else {
            dismissedIds.value.push(id);
            // Remove from favorites if dismissing
            const fIdx = favoriteIds.value.indexOf(id);

            if (fIdx >= 0) {
                favoriteIds.value.splice(fIdx, 1);
            }
        }
    };

    const favoriteCount = computed(() => favoriteIds.value.length);
    const dismissedCount = computed(() => dismissedIds.value.length);

    const clearDismissed = () => {
        dismissedIds.value = [];
    };

    return {
        favoriteIds,
        dismissedIds,
        isFavorite,
        isDismissed,
        toggleFavorite,
        toggleDismissed,
        favoriteCount,
        dismissedCount,
        clearDismissed,
    };
};
