<script setup>
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import VideoLoader from '@/Components/VideoLoader.vue';

const showLoader = ref(false);

onMounted(() => {
    router.on('start', () => { showLoader.value = true; });
    router.on('finish', () => { setTimeout(() => { showLoader.value = false; }, 600); });
    router.on('error', () => { showLoader.value = false; });
});
</script>

<template>
    <div class="cave-game-hub">
        <VideoLoader :show="showLoader" />
        <div class="cave-leaves" aria-hidden="true">
            <span v-for="n in 6" :key="n" class="cave-leaf" />
        </div>
        <slot />
    </div>
</template>
