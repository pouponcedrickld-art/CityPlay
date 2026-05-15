<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Conditions Générales d'Utilisation" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-orange-600 uppercase tracking-tight">Conditions Générales d'Utilisation (CGU)</h1>
                    
                    <div class="prose prose-orange max-w-none text-gray-700 space-y-4">
                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">1. Présentation du service</h2>
                            <p>CityPlay est une plateforme de chasses au trésor et d'énigmes urbaines permettant aux utilisateurs de découvrir des lieux d'intérêt tout en s'amusant.</p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">2. Utilisation du service</h2>
                            <p>L'utilisateur s'engage à utiliser le service de manière respectueuse des lieux publics et des autres utilisateurs. Toute utilisation abusive pourra entraîner la suspension du compte.</p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">3. Responsabilité</h2>
                            <p>CityPlay ne pourra être tenu responsable des accidents ou dommages survenant lors de la pratique des activités proposées sur la plateforme. Les utilisateurs sont responsables de leur propre sécurité lors de leurs déplacements.</p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-orange-900 uppercase">4. Propriété intellectuelle</h2>
                            <p>Le contenu de CityPlay (énigmes, textes, images, logo) est protégé par le droit d'auteur. Toute reproduction sans autorisation est interdite.</p>
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
