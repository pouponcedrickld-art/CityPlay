<script setup>
defineProps({ enigme: Object });
</script>

<template>
    <div class="border rounded p-4 shadow">
        <h3 class="font-bold text-lg">{{ enigme.type }}</h3>
        <p class="text-gray-600 mt-2">{{ enigme.texte }}</p>
    </div>
</template>
