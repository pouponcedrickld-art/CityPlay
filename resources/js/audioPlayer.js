import { ref } from 'vue';

export const isPlaying = ref(false);

// Create the audio instance. We use the public path to the copied audio file.
export const bgAudio = new Audio('/audio/background_music.mp3');
bgAudio.loop = true;
bgAudio.volume = 0.3; // Default volume 30%

export const toggleAudio = () => {
    if (isPlaying.value) {
        bgAudio.pause();
        isPlaying.value = false;
    } else {
        bgAudio.play()
            .then(() => { isPlaying.value = true; })
            .catch(e => {
                console.error("Erreur de lecture audio (peut nécessiter une interaction utilisateur au préalable):", e);
                isPlaying.value = false;
            });
    }
};

export const playAudio = () => {
    if (!isPlaying.value) {
        bgAudio.play()
            .then(() => { isPlaying.value = true; })
            .catch(e => {
                console.error("Lecture automatique bloquée :", e);
            });
    }
};
