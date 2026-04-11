import { useMediaQuery } from '@vueuse/core';
import type { ComputedRef } from 'vue';

/**
 * Reactive breakpoint detection matching Tailwind CSS 4 defaults.
 *
 * Uses VueUse's useMediaQuery for reactive CSS media query matching.
 * Returns boolean refs that update automatically on window resize.
 */
export function useBreakpoints(): {
    isMobile: ComputedRef<boolean>;
    isTablet: ComputedRef<boolean>;
    isDesktop: ComputedRef<boolean>;
    isWide: ComputedRef<boolean>;
} {
    const sm = useMediaQuery('(min-width: 640px)');
    const md = useMediaQuery('(min-width: 768px)');
    const lg = useMediaQuery('(min-width: 1024px)');
    const xl = useMediaQuery('(min-width: 1280px)');

    return {
        /** Below sm (< 640px) */
        isMobile: useMediaQuery('(max-width: 639px)'),
        /** Between sm and lg (640px–1023px) */
        isTablet: useMediaQuery('(min-width: 640px) and (max-width: 1023px)'),
        /** lg and above (>= 1024px) */
        isDesktop: lg,
        /** xl and above (>= 1280px) */
        isWide: xl,
    };
}
