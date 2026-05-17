<script setup>
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    latitude: Number,
    longitude: Number,
    zoom: { type: Number, default: 15 },
});

const mapContainer = ref(null);
let map = null;

onMounted(() => {
    if (!mapContainer.value) return;
    map = L.map(mapContainer.value).setView([props.latitude, props.longitude], props.zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
    }).addTo(map);
    L.marker([props.latitude, props.longitude]).addTo(map);
});
</script>

<template>
    <div ref="mapContainer" style="width: 100%; height: 400px;"></div>
</template>
