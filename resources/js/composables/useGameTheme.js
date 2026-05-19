import { ref, computed } from 'vue';

export function useGameTheme() {
    /**
     * useGpsStatus
     * Retourne couleur, icône et message selon la précision GPS
     */
    const useGpsStatus = (precision) => {
        return computed(() => {
            if (precision === null || precision === undefined) {
                return {
                    label: 'GPS Indisponible',
                    color: 'var(--game-neon-red)',
                    class: 'neon-text-red',
                    icon: 'pi pi-times-circle',
                    status: 'error'
                };
            }
            if (precision <= 20) {
                return {
                    label: `Précision excellente (${Math.round(precision)}m)`,
                    color: 'var(--game-neon-cyan)',
                    class: 'neon-text-cyan',
                    icon: 'pi pi-check-circle',
                    status: 'success'
                };
            }
            if (precision <= 50) {
                return {
                    label: `Précision moyenne (${Math.round(precision)}m)`,
                    color: 'var(--game-neon-gold)',
                    class: 'neon-text-gold',
                    icon: 'pi pi-exclamation-circle',
                    status: 'warning'
                };
            }
            return {
                label: `Précision faible (${Math.round(precision)}m)`,
                color: 'var(--game-neon-red)',
                class: 'neon-text-red',
                icon: 'pi pi-exclamation-triangle',
                status: 'error',
                message: 'Déplacez-vous en extérieur pour un meilleur signal.'
            };
        });
    };

    /**
     * useGameTransition
     * Configuration pour les transitions Inertia (Inertia ne supporte pas nativement les transitions de groupe complexes, 
     * mais on peut utiliser ce composable pour injecter des classes de transition)
     */
    const pageTransition = ref('fade-in-up');

    return {
        useGpsStatus,
        pageTransition
    };
}
