<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps<{
    posterSrc: string;
    videoWebmSrc?: string;
    videoMp4Src?: string;
}>();

const isMounted = ref(false);
const isMobile = ref(false);
const videoError = ref(false);
const failedSources = ref(0);
const totalSources = ref(0);

onMounted(() => {
    isMobile.value = window.matchMedia('(max-width: 1023px)').matches;
    isMounted.value = true;
});

const onSourceError = () => {
    failedSources.value++;

    if (failedSources.value >= totalSources.value) {
        videoError.value = true;
    }
};
</script>

<template>
    <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
        <img
            :src="posterSrc"
            alt=""
            class="absolute inset-0 h-full w-full object-cover"
        />

        <video
            v-if="isMounted && !isMobile && !videoError && (videoWebmSrc || videoMp4Src)"
            autoplay
            muted
            loop
            playsinline
            :poster="posterSrc"
            class="absolute inset-0 h-full w-full object-cover"
            :ref="(el) => { if (el) totalSources = (el as HTMLVideoElement).querySelectorAll('source').length }"
        >
            <source
                v-if="videoWebmSrc"
                :src="videoWebmSrc"
                type="video/webm"
                @error="onSourceError"
            />
            <source
                v-if="videoMp4Src"
                :src="videoMp4Src"
                type="video/mp4"
                @error="onSourceError"
            />
        </video>

        <div class="absolute inset-0 bg-black/60" />
    </div>
</template>
