<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Politique de Confidentialité" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-orange-600 uppercase tracking-tight">Politique de Confidentialité</h1>
                    
                    <div class="prose prose-orange max-w-none text-gray-700 space-y-4">
                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">1. Collecte des données</h2>
                            <p>Nous collectons les informations nécessaires à votre inscription (nom, pseudo, email) ainsi que vos données de progression dans les jeux (énigmes résolues, temps, score).</p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">2. Utilisation des données</h2>
                            <p>Vos données sont utilisées pour gérer votre compte, suivre vos performances et améliorer l'expérience de jeu sur CityPlay.</p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">3. Géolocalisation</h2>
                            <p>Le service nécessite l'accès à votre position GPS pour valider votre présence sur les lieux des énigmes. Ces données de position ne sont pas stockées de manière permanente après la validation.</p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">4. Vos droits</h2>
                            <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles depuis votre espace profil.</p>
                        </section>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <Link :href="route('register')" class="text-orange-600 font-bold hover:underline">
                            &larr; Retour à l'inscription
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
