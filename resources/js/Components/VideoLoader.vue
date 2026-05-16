<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    show: Boolean
});

const videoRef = ref(null);

const handlePlay = async () => {
    if (!videoRef.value) return;
    
    try {
        videoRef.value.currentTime = 0;
        await videoRef.value.play();
    } catch (err) {
        console.warn("Video play failed:", err);
    }
};

watch(() => props.show, async (newVal) => {
    if (newVal) {
        await nextTick();
        handlePlay();
    } else {
        if (videoRef.value) videoRef.value.pause();
    }
});

onMounted(() => {
    if (props.show) {
        handlePlay();
    }
    
    if (videoRef.value) {
        // Boucle personnalisée à 7.1s
        videoRef.value.addEventListener('timeupdate', () => {
            if (videoRef.value && videoRef.value.currentTime >= 7.1) {
                videoRef.value.currentTime = 0;
                videoRef.value.play().catch(() => {});
            }
        });
    }
});
</script>

<template>
    <Transition name="fade">
        <div v-if="show" class="fixed inset-0 z-[9999] bg-black flex items-center justify-center overflow-hidden">
            <video 
                ref="videoRef"
                class="min-w-full min-h-full object-cover"
                muted
                playsinline
                autoplay
                preload="auto"
            >
                <source src="/videos/cityplay_oneShoot.mp4" type="video/mp4">
            </video>
            
            <div class="absolute inset-0 bg-orange-950/20 mix-blend-overlay"></div>
            
            <div class="absolute bottom-12 flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-orange-500/20 border-t-orange-500 rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-white uppercase tracking-[0.4em] animate-pulse">CityPlay est en route...</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>


<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.8s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
