<script setup>
import { Head, Link } from "@inertiajs/vue3";
import CavePlayLayout from "@/Layouts/CavePlayLayout.vue";

const props = defineProps({
    users: Array,
    currentUser: Object,
});

const getRankClass = (index) => {
    if (index === 0) return "cave-rank-box--gold";
    if (index === 1) return "cave-rank-box--silver";
    if (index === 2) return "cave-rank-box--bronze";
    return "cave-rank-box--default";
};

const getRankIcon = (index) => {
    if (index === 0) return "pi pi-trophy";
    if (index === 1) return "pi pi-medal";
    if (index === 2) return "pi pi-award";
    return null;
};
</script>

<template>
    <Head title="Classement" />

    <CavePlayLayout wide>
        <div class="max-w-4xl mx-auto space-y-6 pb-12">
            <!-- Header Section -->
            <div class="cave-profile-section overflow-hidden relative" style="padding: 0;">
                <div class="absolute inset-0 z-0 bg-cover bg-center opacity-10" style="background-image: url('/storage/enigmes/images/placeholder_cave.jpg')"></div>
                <div class="relative z-10 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h1 class="cave-result-title" style="margin-bottom: 4px;">Classement Mondial</h1>
                        <p class="cave-result-sub" style="margin-bottom: 0;">Les meilleurs explorateurs</p>
                    </div>

                    <div class="cave-stat-box" style="min-width: 180px; background: rgba(255, 248, 238, 0.4);">
                        <div class="cave-stat-box__label">Votre Score</div>
                        <div class="cave-stat-box__value" style="font-size: 2.5rem; color: var(--cave-gold-dark);">
                            {{ currentUser.total_score || 0 }} <span style="font-size: 0.8rem; font-family: var(--cave-font-body);">PTS</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard List -->
            <div class="cave-profile-section" style="padding: 0; overflow: hidden;">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr style="background: rgba(61, 34, 16, 0.1); border-bottom: 3px solid var(--cave-border-dark);">
                                <th class="px-6 py-4 cave-field-label" style="margin-bottom: 0;">Rang</th>
                                <th class="px-6 py-4 cave-field-label" style="margin-bottom: 0;">Explorateur</th>
                                <th class="px-6 py-4 cave-field-label text-right" style="margin-bottom: 0;">Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-3 divide-[var(--cave-border-dark)]">
                            <tr v-for="(user, index) in users" :key="user.id"
                                class="transition-colors"
                                :class="user.id === currentUser.id ? 'bg-[rgba(255,215,0,0.1)]' : 'hover:bg-[rgba(255,255,255,0.1)]'">
                                <td class="px-6 py-4">
                                    <div class="cave-rank-box" :class="getRankClass(index)">
                                        <i v-if="getRankIcon(index)" :class="getRankIcon(index)"></i>
                                        <span v-else>#{{ index + 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl border-2 border-[var(--cave-border-dark)] bg-[var(--cave-rock-light)] flex items-center justify-center text-[var(--cave-border-dark)] font-black shadow-[0_3px_0_var(--cave-border-darker)]">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-black text-[var(--cave-border-dark)] uppercase text-sm tracking-tight">{{ user.name }}</p>
                                            <span v-if="user.id === currentUser.id" class="cave-badge cave-badge--gold" style="font-size: 0.5rem; padding: 1px 6px;">VOUS</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="cave-stat-box__value" style="font-size: 1.4rem;">{{ user.total_score || 0 }}</span>
                                    <span class="cave-stat-box__label" style="margin-left: 4px;">pts</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="users.length === 0" class="p-12 text-center">
                    <div class="w-16 h-16 bg-[var(--cave-rock-dark)] rounded-2xl border-3 border-[var(--cave-border-dark)] flex items-center justify-center mx-auto mb-4 opacity-40">
                        <i class="pi pi-users text-2xl text-[var(--cave-border-dark)]"></i>
                    </div>
                    <p class="cave-hint">Aucun explorateur dans cette zone...</p>
                </div>
            </div>
        </div>
    </CavePlayLayout>
</template>

<style scoped>
.cave-rank-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    border: 3px solid var(--cave-border-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--cave-font-title);
    font-size: 1.1rem;
    box-shadow: 0 4px 0 var(--cave-border-darker);
}

.cave-rank-box--gold {
    background: linear-gradient(180deg, var(--cave-gold), var(--cave-gold-dark));
    color: var(--cave-border-dark);
}

.cave-rank-box--silver {
    background: linear-gradient(180deg, #E2E8F0, #94A3B8);
    color: var(--cave-border-dark);
}

.cave-rank-box--bronze {
    background: linear-gradient(180deg, #FDBA74, #C2410C);
    color: white;
}

.cave-rank-box--default {
    background: var(--cave-rock-light);
    color: var(--cave-border-dark);
    font-size: 0.8rem;
}

.divide-y-3 > :not([hidden]) ~ :not([hidden]) {
    border-top-width: 3px;
    border-style: dashed;
}
</style>
