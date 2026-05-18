<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { gsap } from 'gsap';

// Import official logos
import logoWhrite from '@/Layouts/logo_whrite.jpg';
import logoDark from '@/Layouts/logo_dark.jpg';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const introVideo = ref(null);

const handleTimeUpdate = () => {
  if (introVideo.value && introVideo.value.currentTime >= 6.5) {
    introVideo.value.currentTime = 0;
  }
};

// Interactive 3D Card Tilting Effect
const onCardMove = (event) => {
  const card = event.currentTarget;
  const rect = card.getBoundingClientRect();
  const x = event.clientX - rect.left - rect.width / 2;
  const y = event.clientY - rect.top - rect.height / 2;
  
  gsap.to(card, {
    rotateX: -y / 12,
    rotateY: x / 12,
    scale: 1.03,
    borderColor: 'rgba(255, 149, 0, 0.45)',
    boxShadow: '0 20px 40px rgba(255, 149, 0, 0.12)',
    duration: 0.35,
    ease: 'power2.out'
  });
};

const onCardLeave = (event) => {
  const card = event.currentTarget;
  gsap.to(card, {
    rotateX: 0,
    rotateY: 0,
    scale: 1,
    borderColor: 'rgba(255, 149, 0, 0.12)',
    boxShadow: '0 10px 30px rgba(0, 0, 0, 0.5)',
    duration: 0.5,
    ease: 'power3.out'
  });
};

// Dynamic Leaderboard Tab Switching
const leaderboardTab = ref('guilds');
const activeLeaderboardData = ref([
  { rank: 1, name: 'Guilde Dahomey', score: '12,450 XP', city: 'Cotonou' },
  { rank: 2, name: 'Les Chasseurs de Ouidah', score: '10,890 XP', city: 'Ouidah' },
  { rank: 3, name: 'Hogbonou Force', score: '9,750 XP', city: 'Porto-Novo' },
  { rank: 4, name: 'Cotonou Raiders', score: '8,400 XP', city: 'Cotonou' },
  { rank: 5, name: 'Kpassè Warriors', score: '7,900 XP', city: 'Ouidah' }
]);

const changeTab = (tab) => {
  if (leaderboardTab.value === tab) return;
  leaderboardTab.value = tab;

  // Stagger entry animation on leaderboard rows
  gsap.fromTo('.leaderboard-row', 
    { x: -30, opacity: 0 },
    { x: 0, opacity: 1, duration: 0.45, stagger: 0.08, ease: 'back.out(1.2)' }
  );

  if (tab === 'guilds') {
    activeLeaderboardData.value = [
      { rank: 1, name: 'Guilde Dahomey', score: '12,450 XP', city: 'Cotonou' },
      { rank: 2, name: 'Les Chasseurs de Ouidah', score: '10,890 XP', city: 'Ouidah' },
      { rank: 3, name: 'Hogbonou Force', score: '9,750 XP', city: 'Porto-Novo' },
      { rank: 4, name: 'Cotonou Raiders', score: '8,400 XP', city: 'Cotonou' },
      { rank: 5, name: 'Kpassè Warriors', score: '7,900 XP', city: 'Ouidah' }
    ];
  } else {
    activeLeaderboardData.value = [
      { rank: 1, name: 'Sènami_99', score: '5,320 XP', city: 'Cotonou' },
      { rank: 2, name: 'Kofi_Explorer', score: '4,980 XP', city: 'Ouidah' },
      { rank: 3, name: 'Femi_Quest', score: '4,750 XP', city: 'Porto-Novo' },
      { rank: 4, name: 'Ablavi_229', score: '4,200 XP', city: 'Cotonou' },
      { rank: 5, name: 'Bio_Guera_Jr', score: '3,950 XP', city: 'Parakou' }
    ];
  }
};

// FAQ Accordion Expand & Collapse
const activeFaqIndex = ref(null);
const toggleFaq = (index) => {
  if (activeFaqIndex.value === index) {
    gsap.to(`.faq-answer-${index}`, { height: 0, opacity: 0, duration: 0.35, ease: 'power2.inOut' });
    activeFaqIndex.value = null;
  } else {
    if (activeFaqIndex.value !== null) {
      gsap.to(`.faq-answer-${activeFaqIndex.value}`, { height: 0, opacity: 0, duration: 0.35, ease: 'power2.inOut' });
    }
    activeFaqIndex.value = index;
    gsap.fromTo(`.faq-answer-${index}`, 
      { height: 0, opacity: 0 },
      { height: 'auto', opacity: 1, duration: 0.45, ease: 'power2.out' }
    );
  }
};

// Intersection Observer for scroll animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      // Trigger GSAP stagger reveals on children
      const animItems = entry.target.querySelectorAll('.scroll-anim-item');
      if (animItems.length > 0) {
        gsap.fromTo(animItems, 
          { y: 35, opacity: 0 }, 
          { y: 0, opacity: 1, duration: 0.75, stagger: 0.12, ease: 'power2.out' }
        );
      }
    }
  });
}, { threshold: 0.08 });

onMounted(() => {
  // 1. Mouse Tracking Glowing Pointer Follower
  window.addEventListener('mousemove', (e) => {
    gsap.to('.cursor-glow', {
      x: e.clientX,
      y: e.clientY,
      duration: 0.2,
      ease: 'power2.out'
    });
  });

  // 2. Custom Holographic Entrance Timeline
  const tl = gsap.timeline();
  tl.fromTo('.cyber-overlay', 
    { opacity: 1 }, 
    { opacity: 0, duration: 0.8, ease: 'power2.inOut' }
  );

  tl.from('.nav-bar', {
    y: -80,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
  }, '-=0.4');

  tl.from('.hero-logo-container', {
    scale: 0.5,
    rotate: -15,
    opacity: 0,
    duration: 1.2,
    ease: 'back.out(1.4)'
  }, '-=0.6');

  tl.from('.hero-anim', {
    y: 35,
    opacity: 0,
    duration: 0.85,
    stagger: 0.15,
    ease: 'power3.out'
  }, '-=0.8');

  tl.from('.cta-btn', {
    scale: 0.8,
    opacity: 0,
    duration: 0.8,
    stagger: 0.12,
    ease: 'back.out(1.5)'
  }, '-=0.6');

  // Activate scroll observer on all target sections
  document.querySelectorAll('.scroll-reveal-section').forEach(section => {
    observer.observe(section);
  });
});
</script>

<template>
  <Head title="Welcome to CityPlay" />
  
  <div class="welcome-container font-outfit">
    <!-- Cyber Glitch Intro Overlay -->
    <div class="cyber-overlay" />

    <!-- Ambient Floating Cursor Glow -->
    <div class="cursor-glow" />

    <!-- Section 1: Hero Video Landing Page -->
    <section class="section-hero">
      <div class="video-background-container">
        <video 
          ref="introVideo"
          autoplay 
          muted 
          playsinline 
          class="bg-video"
          @timeupdate="handleTimeUpdate"
        >
          <source src="/videos/cityplay_oneShoot.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
        <div class="video-overlay" />
      </div>

      <!-- Main Navigation Bar -->
      <nav class="nav-bar">
        <div class="nav-left">
          <img :src="logoWhrite" alt="CityPlay Logo" class="nav-logo" />
          <div class="brand-info">
            <span class="brand-title">CityPlay</span>
            <span class="brand-tag">AVENTURE BÉNIN</span>
          </div>
        </div>
        
        <div class="nav-center hidden lg:flex">
          <a href="#concept" class="nav-link">Le Concept</a>
          <a href="#cites" class="nav-link">Cités Épiques</a>
          <a href="#leaderboard" class="nav-link">Classement</a>
          <a href="#faq" class="nav-link">F.A.Q</a>
        </div>
        
        <div v-if="canLogin" class="nav-right">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="btn-dashboard"
          >
            <i class="pi pi-th-large" />
            <span>Mon Espace</span>
          </Link>

          <template v-else>
            <Link
              :href="route('login')"
              class="btn-login"
            >
              <span>Connexion</span>
            </Link>

            <Link
              v-if="canRegister"
              :href="route('register')"
              class="btn-register"
            >
              <span>Commencer</span>
            </Link>
          </template>
        </div>
      </nav>

      <!-- Hero Main Area -->
      <div class="hero-main">
        <div class="hero-logo-container">
          <img :src="logoWhrite" alt="CityPlay Emblem" class="hero-logo" />
          <div class="glow-ring" />
        </div>

        <h2 class="hero-subtitle hero-anim">L'Aventure Urbaine Phénoménale</h2>
        <h1 class="hero-title hero-anim">CITYPLAY</h1>
        
        <p class="hero-desc hero-anim">
          Explorez le Bénin à travers une expérience interactive hors du commun. Décryptez des énigmes, parcourez des lieux chargés d'histoire et vivez une quête ludique ultime au cœur de votre ville.
        </p>

        <div class="hero-ctas">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="cta-btn primary-cta"
          >
            <i class="pi pi-play" />
            <span>Reprendre la Quête</span>
          </Link>
          
          <template v-else>
            <Link
              v-if="canRegister"
              :href="route('register')"
              class="cta-btn primary-cta"
            >
              <i class="pi pi-play" />
              <span>Lancer l'Aventure</span>
            </Link>
            <Link
              :href="route('login')"
              class="cta-btn secondary-cta"
            >
              <i class="pi pi-users" />
              <span>Rejoindre une Équipe</span>
            </Link>
          </template>
        </div>

        <!-- Scroll Indicator Bouncing -->
        <a href="#concept" class="scroll-indicator">
          <span>DÉCOUVRIR LE CONCEPT</span>
          <i class="pi pi-angle-down bounce-arrow" />
        </a>
      </div>
    </section>

    <!-- Section 2: Comment ça marche (Gameplay Timeline) -->
    <section id="concept" class="section-gameplay scroll-reveal-section">
      <div class="section-header scroll-anim-item">
        <span class="section-badge">Gameplay</span>
        <h2>Comment ça marche ?</h2>
        <p class="section-intro">Le monde réel devient votre terrain de jeu. Suivez le guide technique pour débuter votre aventure.</p>
      </div>

      <div class="timeline-container">
        <div class="timeline-line"></div>
        
        <div class="timeline-steps">
          <!-- Step 1 -->
          <div class="timeline-step scroll-anim-item">
            <div class="step-badge">01</div>
            <div class="step-card" @mousemove="onCardMove" @mouseleave="onCardLeave">
              <div class="step-icon-wrapper orange-glow">
                <i class="pi pi-user-plus" />
              </div>
              <h3>Rejoignez la Guilde</h3>
              <p>Créez votre compte joueur en quelques secondes, formez votre équipe d'explorateurs ou lancez-vous en loup solitaire.</p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="timeline-step scroll-anim-item">
            <div class="step-badge">02</div>
            <div class="step-card" @mousemove="onCardMove" @mouseleave="onCardLeave">
              <div class="step-icon-wrapper blue-glow">
                <i class="pi pi-map-marker" />
              </div>
              <h3>Explorez sur la Carte</h3>
              <p>Rendez-vous dans les environnements clés réels (monuments, parcs, places historiques) indiqués sur la carte interactive.</p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="timeline-step scroll-anim-item">
            <div class="step-badge">03</div>
            <div class="step-card" @mousemove="onCardMove" @mouseleave="onCardLeave">
              <div class="step-icon-wrapper green-glow">
                <i class="pi pi-search" />
              </div>
              <h3>Décryptez les Énigmes</h3>
              <p>Mettez à contribution votre logique et vos sens pour déchiffrer les secrets locaux cachés dans les détails urbains.</p>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="timeline-step scroll-anim-item">
            <div class="step-badge">04</div>
            <div class="step-card" @mousemove="onCardMove" @mouseleave="onCardLeave">
              <div class="step-icon-wrapper gold-glow">
                <i class="pi pi-trophy" />
              </div>
              <h3>Dominez le Bénin</h3>
              <p>Validez vos étapes de parcours, récoltez des points d'XP précieux et grimpez au sommet du tableau des légendes !</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 3: Les Environnements / Cités Épiques -->
    <section id="cites" class="section-cites scroll-reveal-section">
      <div class="section-header scroll-anim-item">
        <span class="section-badge">Zones de jeu</span>
        <h2>Les Cités Mythiques</h2>
        <p class="section-intro">Plongez dans des environnements urbains uniques façonnés par l'histoire du Bénin.</p>
      </div>

      <div class="cites-grid">
        <!-- City 1 -->
        <div class="city-card scroll-anim-item" @mousemove="onCardMove" @mouseleave="onCardLeave">
          <div class="city-img-wrapper">
            <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=800&q=80" alt="Cotonou" class="city-img" />
            <div class="city-img-overlay" />
          </div>
          <div class="city-info">
            <span class="city-region">Littoral</span>
            <h3>Cotonou</h3>
            <p>De l'Étoile Rouge aux marchés animés, parcourez les ruelles dynamiques de la capitale économique du Bénin.</p>
            <span class="city-stats"><i class="pi pi-map" /> 8 Parcours Actifs</span>
          </div>
        </div>

        <!-- City 2 -->
        <div class="city-card scroll-anim-item" @mousemove="onCardMove" @mouseleave="onCardLeave">
          <div class="city-img-wrapper">
            <img src="https://images.unsplash.com/photo-1473163928189-364b2c4e1135?auto=format&fit=crop&w=800&q=80" alt="Porto-Novo" class="city-img" />
            <div class="city-img-overlay" />
          </div>
          <div class="city-info">
            <span class="city-region">Ouémé</span>
            <h3>Porto-Novo</h3>
            <p>Explorez les mystères de la cité aux trois noms. Admirez l'architecture coloniale et le grand temple vaudou.</p>
            <span class="city-stats"><i class="pi pi-map" /> 5 Parcours Actifs</span>
          </div>
        </div>

        <!-- City 3 -->
        <div class="city-card scroll-anim-item" @mousemove="onCardMove" @mouseleave="onCardLeave">
          <div class="city-img-wrapper">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80" alt="Ouidah" class="city-img" />
            <div class="city-img-overlay" />
          </div>
          <div class="city-info">
            <span class="city-region">Atlantique</span>
            <h3>Ouidah</h3>
            <p>Retracez l'histoire spirituelle et mémorielle de la Route des Esclaves jusqu'au célèbre Temple des Pythons.</p>
            <span class="city-stats"><i class="pi pi-map" /> 6 Parcours Actifs</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 4: Le Panthéon des Aventuriers (Dynamic Leaderboard) -->
    <section id="leaderboard" class="section-leaderboard scroll-reveal-section">
      <div class="section-header scroll-anim-item">
        <span class="section-badge">Hauts Faits</span>
        <h2>Le Panthéon des Aventuriers</h2>
        <p class="section-intro">Découvrez les explorateurs légendaires qui dominent le classement national.</p>
      </div>

      <div class="leaderboard-container scroll-anim-item">
        <!-- Tabs -->
        <div class="leaderboard-tabs">
          <button 
            class="tab-btn" 
            :class="{ active: leaderboardTab === 'guilds' }" 
            @click="changeTab('guilds')"
          >
            <i class="pi pi-shield" />
            <span>Guildes Actives</span>
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: leaderboardTab === 'players' }" 
            @click="changeTab('players')"
          >
            <i class="pi pi-user" />
            <span>Joueurs Solo</span>
          </button>
        </div>

        <!-- Leaderboard Rows -->
        <div class="leaderboard-list">
          <div 
            v-for="(row, idx) in activeLeaderboardData" 
            :key="row.name" 
            class="leaderboard-row"
          >
            <div class="row-rank" :class="'rank-' + row.rank">
              <span v-if="row.rank === 1">🥇</span>
              <span v-else-if="row.rank === 2">🥈</span>
              <span v-else-if="row.rank === 3">🥉</span>
              <span v-else>#{{ row.rank }}</span>
            </div>
            
            <div class="row-name">
              <span>{{ row.name }}</span>
            </div>
            
            <div class="row-city">
              <i class="pi pi-compass" />
              <span>{{ row.city }}</span>
            </div>

            <div class="row-score">
              <span>{{ row.score }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 5: F.A.Q (Interactive Accordions) -->
    <section id="faq" class="section-faq scroll-reveal-section">
      <div class="section-header scroll-anim-item">
        <span class="section-badge">Aide</span>
        <h2>F.A.Q. de la Quête</h2>
        <p class="section-intro">Toutes les réponses à vos questions techniques pour démarrer votre aventure sereinement.</p>
      </div>

      <div class="faq-accordion scroll-anim-item">
        <!-- FAQ 1 -->
        <div class="faq-item" :class="{ open: activeFaqIndex === 0 }" @click="toggleFaq(0)">
          <div class="faq-question">
            <h3>Puis-je jouer à CityPlay en équipe ?</h3>
            <i class="pi pi-chevron-down faq-arrow" />
          </div>
          <div class="faq-answer faq-answer-0">
            <div class="answer-content">
              Absolument ! CityPlay est entièrement optimisé pour les guildes et les équipes de 2 à 5 joueurs. Un seul chef d'équipe peut valider les étapes de parcours sur son smartphone, ou chaque joueur peut participer de son côté pour collaborer sur la résolution d'énigmes.
            </div>
          </div>
        </div>

        <!-- FAQ 2 -->
        <div class="faq-item" :class="{ open: activeFaqIndex === 1 }" @click="toggleFaq(1)">
          <div class="faq-question">
            <h3>Quels sont les prérequis matériels pour jouer ?</h3>
            <i class="pi pi-chevron-down faq-arrow" />
          </div>
          <div class="faq-answer faq-answer-1">
            <div class="answer-content">
              Vous avez uniquement besoin d'un smartphone équipé d'une connexion internet (données mobiles) et d'un GPS activé. Aucune application lourde à installer : la plateforme fonctionne de façon ultra-fluide directement dans votre navigateur web standard.
            </div>
          </div>
        </div>

        <!-- FAQ 3 -->
        <div class="faq-item" :class="{ open: activeFaqIndex === 2 }" @click="toggleFaq(2)">
          <div class="faq-question">
            <h3>Est-ce gratuit de participer aux parcours ?</h3>
            <i class="pi pi-chevron-down faq-arrow" />
          </div>
          <div class="faq-answer faq-answer-2">
            <div class="answer-content">
              Oui, la création de profil, l'accès au tableau de bord général et de nombreux parcours de découverte sont 100% gratuits. Certains parcours d'exploration avancés à vocation culturelle ou touristique premium nécessitent des clés d'activation payantes.
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Bottom HUD Footer -->
    <footer class="hud-footer">
      <div class="footer-left">
        <span class="pulse-indicator" />
        <span>SERVEURS ONLINE</span>
      </div>
      <div class="footer-center">
        <span>© {{ new Date().getFullYear() }} CITYPLAY | L'AVENTURE BÉNIN</span>
      </div>
      <div class="footer-right">
        <span>v{{ laravelVersion }} (PHP v{{ phpVersion }})</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

/* Global Reset & Design Tokens */
.welcome-container {
  min-height: 100vh;
  background: #040508;
  color: #ffffff;
  font-family: 'Outfit', sans-serif;
  overflow-x: hidden;
  position: relative;
  display: flex;
  flex-direction: column;
}

/* Digital Grid Scanning Pattern */
.welcome-container::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: radial-gradient(rgba(255, 149, 0, 0.02) 1px, transparent 1px);
  background-size: 20px 20px;
  pointer-events: none;
  z-index: 3;
}

/* Custom Holographic Mouse Aura */
.cursor-glow {
  position: fixed;
  top: 0; left: 0;
  width: 450px; height: 450px;
  background: radial-gradient(circle, rgba(255, 149, 0, 0.08) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
  transform: translate(-50%, -50%);
  z-index: 2;
  will-change: transform;
}

/* Section Styling */
section {
  position: relative;
  z-index: 5;
  width: 100%;
}

/* Section Header styling */
.section-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto 4rem;
}

.section-badge {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #FF9500;
  letter-spacing: 0.3em;
  border: 1px solid rgba(255, 149, 0, 0.3);
  padding: 0.35rem 1rem;
  border-radius: 99px;
  background: rgba(255, 149, 0, 0.05);
  margin-bottom: 1.25rem;
}

.section-header h2 {
  font-size: 2.75rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: -0.02em;
  line-height: 1.1;
  margin-bottom: 1rem;
}

.section-intro {
  font-size: 1.05rem;
  color: rgba(255, 255, 255, 0.6);
  line-height: 1.6;
}

/* Scroll Animation Base styling */
.scroll-reveal-section .scroll-anim-item {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.8s, transform 0.8s;
}

.scroll-reveal-section.is-visible .scroll-anim-item {
  opacity: 1;
  transform: translateY(0);
}

/* Section 1: Hero Video Landing Page styling */
.section-hero {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
}

.video-background-container {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.bg-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.45; /* Dark subtle presence */
}

.video-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, rgba(4, 5, 8, 0.4) 0%, rgba(4, 5, 8, 0.98) 95%);
  z-index: 2;
}

/* Intro Flash Glitch Overlay */
.cyber-overlay {
  position: fixed;
  inset: 0;
  background: #040508;
  z-index: 9999;
  pointer-events: none;
}

/* Navigation Bar styling */
.nav-bar {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 3rem;
  background: linear-gradient(180deg, rgba(4, 5, 8, 0.8) 0%, transparent 100%);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-logo {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  border: 2px solid #FF9500;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.35);
  object-fit: cover;
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-title {
  font-size: 1.4rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  text-transform: uppercase;
  color: #ffffff;
}

.brand-tag {
  font-size: 0.65rem;
  font-weight: 800;
  color: #FF9500;
  letter-spacing: 0.25em;
  margin-top: -0.1rem;
}

.nav-center {
  gap: 2rem;
}

.nav-link {
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.nav-link:hover {
  color: #FF9500;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.btn-dashboard {
  background: rgba(255, 149, 0, 0.1);
  border: 1px solid rgba(255, 149, 0, 0.3);
  color: #FF9500;
  font-weight: 700;
  padding: 0.6rem 1.25rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.btn-dashboard:hover {
  background: #FF9500;
  color: #040508;
  box-shadow: 0 0 20px rgba(255, 149, 0, 0.35);
  transform: translateY(-2px);
}

.btn-login {
  color: rgba(255, 255, 255, 0.85);
  font-weight: 600;
  transition: all 0.3s;
  padding: 0.5rem 1rem;
}

.btn-login:hover {
  color: #FF9500;
}

.btn-register {
  background: #FF9500;
  color: #040508;
  font-weight: 800;
  padding: 0.65rem 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.3);
  transition: all 0.3s;
}

.btn-register:hover {
  background: #E08400;
  box-shadow: 0 0 25px rgba(255, 149, 0, 0.5);
  transform: translateY(-2px);
}

/* Hero Area styling */
.hero-main {
  position: relative;
  z-index: 10;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  max-width: 900px;
  margin: 0 auto;
  padding: 4rem 2rem 6rem;
}

.hero-logo-container {
  position: relative;
  margin-bottom: 2rem;
}

.hero-logo {
  width: 6.5rem;
  height: 6.5rem;
  border-radius: 50%;
  border: 3px solid #FF9500;
  box-shadow: 0 0 30px rgba(255, 149, 0, 0.4);
  object-fit: cover;
  position: relative;
  z-index: 2;
}

.glow-ring {
  position: absolute;
  inset: -10px;
  border: 1px solid rgba(255, 149, 0, 0.15);
  border-radius: 50%;
  animation: pulse-ring 3s infinite linear;
  pointer-events: none;
}

@keyframes pulse-ring {
  0% { transform: scale(0.9); opacity: 1; }
  100% { transform: scale(1.2); opacity: 0; }
}

.hero-subtitle {
  font-size: 1.1rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #FF9500;
  letter-spacing: 0.4em;
  margin-bottom: 0.5rem;
}

.hero-title {
  font-size: 5.5rem;
  font-weight: 900;
  letter-spacing: -0.04em;
  line-height: 1;
  background: linear-gradient(180deg, #ffffff 60%, rgba(255, 255, 255, 0.4) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 4px 15px rgba(0,0,0,0.5));
  margin-bottom: 1.75rem;
}

.hero-desc {
  font-size: 1.15rem;
  line-height: 1.75;
  color: rgba(255, 255, 255, 0.7);
  max-width: 720px;
  margin-bottom: 3rem;
}

.hero-ctas {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 6rem;
}

.cta-btn {
  padding: 1rem 2.25rem;
  border-radius: 1rem;
  font-size: 1rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.primary-cta {
  background: #FF9500;
  color: #040508;
  box-shadow: 0 0 25px rgba(255, 149, 0, 0.35);
}

.primary-cta:hover {
  background: #E08400;
  box-shadow: 0 0 35px rgba(255, 149, 0, 0.6);
  transform: scale(1.05);
}

.secondary-cta {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

.secondary-cta:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: #FF9500;
  box-shadow: 0 0 20px rgba(255, 149, 0, 0.15);
  transform: scale(1.05);
}

.scroll-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.4);
  text-decoration: none;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.2em;
  transition: color 0.3s;
}

.scroll-indicator:hover {
  color: #FF9500;
}

.bounce-arrow {
  font-size: 1.25rem;
  animation: bounce 2s infinite ease-in-out;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(8px); }
}

/* Section 2: Comment ça marche (Gameplay Timeline) styling */
.section-gameplay {
  padding: 8rem 2rem;
  background: linear-gradient(180deg, #040508 0%, #080a0f 100%);
}

.timeline-container {
  position: relative;
  max-width: 1000px;
  margin: 0 auto;
}

.timeline-line {
  position: absolute;
  left: 50px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, rgba(255, 149, 0, 0.8) 0%, rgba(59, 130, 246, 0.8) 50%, rgba(16, 185, 129, 0.8) 100%);
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.5);
}

@media (min-width: 768px) {
  .timeline-line {
    left: 50%;
    transform: translateX(-50%);
  }
}

.timeline-steps {
  display: flex;
  flex-direction: column;
  gap: 4rem;
}

.timeline-step {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .timeline-step {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
  .timeline-step:nth-child(even) {
    flex-direction: row-reverse;
  }
}

.step-badge {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 50%;
  background: #040508;
  border: 2px solid #FF9500;
  color: #FF9500;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 900;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.3);
  z-index: 10;
  margin-left: 23px;
}

@media (min-width: 768px) {
  .step-badge {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    margin-left: 0;
  }
}

.step-card {
  background: rgba(16, 18, 27, 0.6);
  border: 1px solid rgba(255, 149, 0, 0.12);
  border-radius: 1.5rem;
  padding: 2.25rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(10px);
  max-width: 440px;
  transform-style: preserve-3d;
  transition: border-color 0.35s, box-shadow 0.35s;
}

.step-icon-wrapper {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.gold-glow {
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.05));
  border: 1px solid rgba(212, 175, 55, 0.4);
  color: #D4AF37;
}

.step-card h3 {
  font-size: 1.25rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #ffffff;
  margin-bottom: 0.5rem;
}

.step-card p {
  font-size: 0.9rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.55);
}

/* Section 3: Les Cités Mythiques styling */
.section-cites {
  padding: 8rem 2rem;
  background: linear-gradient(180deg, #080a0f 0%, #040508 100%);
}

.cites-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2.5rem;
  max-width: 1100px;
  margin: 0 auto;
}

@media (min-width: 768px) {
  .cites-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.city-card {
  background: rgba(16, 18, 27, 0.6);
  border: 1px solid rgba(255, 149, 0, 0.12);
  border-radius: 1.5rem;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  transform-style: preserve-3d;
  transition: border-color 0.35s, box-shadow 0.35s;
}

.city-img-wrapper {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.city-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.city-card:hover .city-img {
  transform: scale(1.08);
}

.city-img-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(360deg, rgba(16, 18, 27, 1) 0%, transparent 100%);
}

.city-info {
  padding: 1.75rem;
}

.city-region {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #FF9500;
  letter-spacing: 0.2em;
}

.city-info h3 {
  font-size: 1.5rem;
  font-weight: 900;
  text-transform: uppercase;
  color: #ffffff;
  margin: 0.25rem 0 0.75rem;
}

.city-info p {
  font-size: 0.85rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.55);
  margin-bottom: 1.5rem;
}

.city-stats {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #FF9500;
}

/* Section 4: Le Panthéon des Aventuriers (Dynamic Leaderboard) styling */
.section-leaderboard {
  padding: 8rem 2rem;
  background: linear-gradient(180deg, #040508 0%, #080a0f 100%);
}

.leaderboard-container {
  max-width: 800px;
  margin: 0 auto;
  background: rgba(16, 18, 27, 0.55);
  border: 1px solid rgba(255, 149, 0, 0.15);
  border-radius: 2rem;
  padding: 2rem;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(12px);
}

.leaderboard-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 1rem;
}

.tab-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.tab-btn:hover {
  color: #FF9500;
  background: rgba(255, 149, 0, 0.05);
}

.tab-btn.active {
  color: #040508;
  background: #FF9500;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.35);
}

.leaderboard-list {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.leaderboard-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 1rem;
  padding: 1rem 1.5rem;
  transition: all 0.25s;
}

.leaderboard-row:hover {
  background: rgba(255, 149, 0, 0.04);
  border-color: rgba(255, 149, 0, 0.25);
  transform: translateX(5px);
}

.row-rank {
  width: 2.5rem;
  font-weight: 900;
  font-size: 1.1rem;
}

.rank-1 { color: #FF9500; text-shadow: 0 0 10px rgba(255, 149, 0, 0.3); }
.rank-2 { color: #b4b4b4; }
.rank-3 { color: #ad7c59; }

.row-name {
  flex: 1;
  font-weight: 700;
  font-size: 1rem;
  color: #ffffff;
}

.row-city {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.45);
  margin-right: 2rem;
}

.row-score {
  font-weight: 900;
  color: #FF9500;
  font-size: 1.1rem;
}

/* Section 5: F.A.Q (Interactive Accordions) styling */
.section-faq {
  padding: 8rem 2rem 10rem;
  background: linear-gradient(180deg, #080a0f 0%, #040508 100%);
}

.faq-accordion {
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.faq-item {
  background: rgba(16, 18, 27, 0.5);
  border: 1px solid rgba(255, 149, 0, 0.12);
  border-radius: 1.25rem;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
}

.faq-item:hover {
  background: rgba(255, 149, 0, 0.03);
  border-color: rgba(255, 149, 0, 0.3);
}

.faq-item.open {
  border-color: #FF9500;
  box-shadow: 0 10px 25px rgba(255, 149, 0, 0.05);
}

.faq-question {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
}

.faq-question h3 {
  font-size: 1.1rem;
  font-weight: 800;
  color: #ffffff;
}

.faq-arrow {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.4);
  transition: transform 0.35s;
}

.faq-item.open .faq-arrow {
  transform: rotate(-180deg);
  color: #FF9500;
}

.faq-answer {
  height: 0;
  overflow: hidden;
  opacity: 0;
}

.answer-content {
  padding: 0 2rem 1.5rem;
  font-size: 0.925rem;
  line-height: 1.75;
  color: rgba(255, 255, 255, 0.6);
  border-top: 1px solid rgba(255, 255, 255, 0.03);
  padding-top: 1.25rem;
}

/* HUD bottom footer styling */
.hud-footer {
  position: relative;
  z-index: 10;
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 3rem;
  background: rgba(4, 5, 8, 0.95);
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 0.725rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.4);
}

.footer-left {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #10B981;
}

.pulse-indicator {
  width: 6px;
  height: 6px;
  background: #10B981;
  border-radius: 50%;
  box-shadow: 0 0 10px #10B981;
  animation: pulse-dot 1.5s infinite ease-in-out;
}

@keyframes pulse-dot {
  0%, 100% { opacity: 0.4; transform: scale(0.9); }
  50% { opacity: 1; transform: scale(1.2); }
}

@media (max-width: 991px) {
  .nav-bar {
    padding: 1rem 2rem;
  }
}

@media (max-width: 767px) {
  .nav-bar {
    padding: 1rem 1.5rem;
  }
  .brand-title {
    font-size: 1.2rem;
  }
  .hero-title {
    font-size: 3.5rem;
  }
  .hero-ctas {
    flex-direction: column;
    width: 100%;
    gap: 1rem;
  }
  .cta-btn {
    justify-content: center;
    width: 100%;
  }
  .section-header h2 {
    font-size: 2.25rem;
  }
  .timeline-container {
    padding-left: 2rem;
  }
  .timeline-line {
    left: 2rem;
  }
  .step-badge {
    margin-left: -1.75rem;
  }
  .hud-footer {
    flex-direction: column;
    gap: 0.75rem;
    text-align: center;
    padding: 1.5rem;
  }
}
</style>
