<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean
});

const videoRef = ref(null);
const videoLoaded = ref(false);

const handleVideoSuccess = () => {
    videoLoaded.value = true;
};

const handleVideoError = () => {
    videoLoaded.value = false;
};
</script>

<template>
    <Transition name="fade">
        <div v-if="show" class="fixed inset-0 z-[9999] bg-[#0f0f0f] flex items-center justify-center overflow-hidden">
            
            <!-- COUCHE 1 : Vidéo (S'affiche uniquement si elle est trouvée) -->
            <video 
                v-show="videoLoaded"
                ref="videoRef"
                class="absolute inset-0 w-full h-full object-cover object-center opacity-40 transition-opacity duration-1000"
                muted
                playsinline
                autoplay
                loop
                @canplay="handleVideoSuccess"
                @error="handleVideoError"
            >
                <source src="/videos/cityplay_oneShoot.mp4" type="video/mp4">
            </video>

            <!-- COUCHE 2 : Animation CSS (Toujours présente en fond ou en secours) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_#FF9500_0%,_transparent_70%)] opacity-30 animate-pulse"></div>
                <div class="grid-bg absolute inset-0" :style="{ backgroundImage: 'linear-gradient(#FF9500 1px, transparent 1px), linear-gradient(90deg, #FF9500 1px, transparent 1px)', backgroundSize: '40px 40px' }"></div>
            </div>

            <div class="relative z-10 flex flex-col items-center gap-8">
                <!-- Loader Central -->
                <div class="relative">
                    <div class="w-24 h-24 border-2 border-[#FF9500]/10 border-t-[#FF9500] rounded-full animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="pi pi-bolt text-3xl text-[#FF9500] animate-pulse"></i>
                    </div>
                </div>

                <!-- Texte et Barre de Progression -->
                <div class="text-center space-y-4">
                    <div class="space-y-1">
                        <p class="text-[12px] font-black text-white uppercase tracking-[0.6em] animate-pulse">CityPlay: Welcome in Benin</p>
                    </div>
                    
                    <div class="w-48 h-1 bg-white/5 rounded-full overflow-hidden mx-auto border border-white/5">
                        <div class="h-full bg-gradient-to-r from-[#FF9500] to-[#FF7B00] animate-progress-loader"></div>
                    </div>
                </div>
            </div>

            <!-- Scanlines effect -->
            <div class="absolute inset-0 pointer-events-none opacity-[0.03]" style="background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); background-size: 100% 2px, 3px 100%;"></div>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes progress-loader {
    0% { width: 0%; transform: translateX(-100%); }
    50% { width: 70%; transform: translateX(0); }
    100% { width: 100%; transform: translateX(100%); }
}

.animate-progress-loader {
    animation: progress-loader 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

.grid-bg {
    mask-image: radial-gradient(circle at center, black, transparent 80%);
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>

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
