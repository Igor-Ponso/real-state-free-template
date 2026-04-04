import { useWindowScroll } from '@vueuse/core';
import { computed } from 'vue';

export function useScrollHeader(threshold = 50) {
    const { y: scrollY } = useWindowScroll();
    const isScrolled = computed(() => scrollY.value > threshold);

    return {
        isScrolled,
        scrollY,
    };
}
