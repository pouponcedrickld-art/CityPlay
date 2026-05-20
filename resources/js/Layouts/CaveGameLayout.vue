<script setup>
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import VideoLoader from '@/Components/VideoLoader.vue';

const showLoader = ref(false);
let loaderDelayTimer = null;

// Force light theme class for player pages to avoid dark mode conflicts
if (typeof document !== 'undefined') {
    document.documentElement.classList.add('light-theme');
}

onMounted(() => {
    router.on('start', () => {
        if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
        loaderDelayTimer = setTimeout(() => {
            showLoader.value = true;
        }, 350);
    });
    router.on('finish', () => {
        if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
        loaderDelayTimer = null;
        showLoader.value = false;
    });
    router.on('error', () => {
        if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
        loaderDelayTimer = null;
        showLoader.value = false;
    });
});

onUnmounted(() => {
    if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
    loaderDelayTimer = null;
});
</script>

<template>
    <div class="cave-play-hub cave-game-hub">
        <VideoLoader :show="showLoader" />
        <div class="cave-leaves" aria-hidden="true">
            <span v-for="n in 6" :key="n" class="cave-leaf" />
        </div>
        <slot />
    </div>
</template>
