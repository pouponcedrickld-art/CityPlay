<script setup>
import { Head, Link } from "@inertiajs/vue3";
import CavePlayLayout from "@/Layouts/CavePlayLayout.vue";

const props = defineProps({
    users: Array,
    currentUser: Object,
});

const getRankColor = (index) => {
    if (index === 0) return "text-yellow-500 bg-yellow-50 border-yellow-200";
    if (index === 1) return "text-slate-400 bg-slate-50 border-slate-200";
    if (index === 2) return "text-orange-600 bg-orange-50 border-orange-200";
    return "text-gray-500 bg-gray-50 border-gray-100";
};

const getRankIcon = (index) => {
    if (index === 0) return "pi pi-trophy text-2xl";
    if (index === 1) return "pi pi-medal text-xl";
    if (index === 2) return "pi pi-award text-lg";
    return "text-xs font-black";
};
</script>

<template>
    <Head title="Classement" />

    <CavePlayLayout wide>
        <div class="max-w-4xl mx-auto space-y-8 pb-12">
            <!-- Header Section -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-orange-600 to-orange-400 p-8 text-white shadow-2xl shadow-orange-200">
                <div class="absolute top-0 right-0 -mt-12 -mr-12 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-black/10 rounded-full blur-2xl"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h1 class="text-4xl font-black tracking-tighter uppercase mb-2">Classement Mondial</h1>
                        <p class="text-orange-50 font-medium text-sm tracking-wide uppercase opacity-80">
                            Les meilleurs explorateurs de CityPlay
                        </p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-md rounded-3xl p-6 border border-white/30 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-orange-600 shadow-xl">
                            <i class="pi pi-user text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Votre Score</p>
                            <p class="text-2xl font-black">{{ currentUser.total_score || 0 }} <span class="text-xs">PTS</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard List -->
            <div class="bg-white rounded-[2rem] shadow-xl shadow-orange-950/5 border border-orange-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-orange-50 bg-orange-50/30">
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40">Rang</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40">Explorateur</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40 text-right">Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-50">
                            <tr v-for="(user, index) in users" :key="user.id"
                                class="group hover:bg-orange-50/50 transition-colors"
                                :class="{'bg-orange-50/20': user.id === currentUser.id}">
                                <td class="px-8 py-6">
                                    <div class="w-12 h-12 rounded-2xl border flex items-center justify-center transition-transform group-hover:scale-110"
                                        :class="getRankColor(index)">
                                        <i v-if="index < 3" :class="getRankIcon(index)"></i>
                                        <span v-else :class="getRankIcon(index)">#{{ index + 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-100 to-orange-50 flex items-center justify-center text-orange-400 font-bold border border-orange-200">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-black text-orange-950 uppercase text-sm">{{ user.name }}</p>
                                            <span v-if="user.id === currentUser.id" class="text-[9px] font-black uppercase tracking-tighter px-2 py-0.5 bg-orange-100 text-orange-600 rounded-full">Vous</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span class="text-lg font-black text-orange-600">{{ user.total_score || 0 }}</span>
                                    <span class="text-[10px] font-black text-orange-900/30 ml-1 uppercase">pts</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="users.length === 0" class="p-12 text-center">
                    <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-users text-3xl text-orange-200"></i>
                    </div>
                    <p class="text-orange-900/40 font-bold uppercase text-xs tracking-widest">Aucun joueur pour le moment</p>
                </div>
            </div>
        </div>
    </CavePlayLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #FF9500;
    border-radius: 10px;
}
</style>
