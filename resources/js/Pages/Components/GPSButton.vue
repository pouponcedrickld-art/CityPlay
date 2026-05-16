<script setup>
const emit = defineEmits(['validate']);

const validatePosition = () => {
    if (!navigator.geolocation) {
        alert('GPS non disponible');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (position) => {
            emit('validate', {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                precision: position.coords.accuracy,
            });
        },
        (error) => {
            alert('Erreur GPS: ' + error.message);
        }
    );
};
</script>

<template>
    <button @click="validatePosition" class="bg-green-500 text-white px-6 py-3 rounded-full font-bold">
        📍 Valider ma position
    </button>
</template>
