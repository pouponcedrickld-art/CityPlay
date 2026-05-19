<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside ref="sidebarRef" class="sidebar">
      <div class="sidebar-logo">
        <span class="logo-icon">🗺️</span>
        <span class="logo-text">CityQuest Admin</span>
      </div>

      <nav class="sidebar-nav">
        <Link :href="route('admin.dashboard')" class="nav-item" :class="{ active: isActive('admin.dashboard') }">
          <i class="pi pi-home" />
          <span>Dashboard</span>
        </Link>

        <div class="nav-section-title">Contenu</div>

        <Link :href="route('admin.villes.index')" class="nav-item" :class="{ active: isActive('admin.villes') }">
          <i class="pi pi-map" />
          <span>Villes</span>
        </Link>

        <Link :href="route('admin.environnements.index')" class="nav-item" :class="{ active: isActive('admin.environnements') }">
          <i class="pi pi-globe" />
          <span>Environnements</span>
        </Link>

        <div class="nav-section-title">Gestion</div>

        <Link :href="route('admin.users.index')" class="nav-item" :class="{ active: isActive('admin.users') }">
          <i class="pi pi-users" />
          <span>Utilisateurs</span>
        </Link>

        <Link :href="route('admin.parties.index')" class="nav-item" :class="{ active: isActive('admin.parties') }">
          <i class="pi pi-play" />
          <span>Parties</span>
        </Link>

        <Link :href="route('admin.invitations.index')" class="nav-item" :class="{ active: isActive('admin.invitations') }">
          <i class="pi pi-link" />
          <span>Invitations App</span>
        </Link>

        <div class="nav-section-title">Navigation</div>

        <Link :href="route('dashboard')" class="nav-item">
          <i class="pi pi-external-link" />
          <span>Retour au site</span>
        </Link>
      </nav>
    </aside>

    <!-- Main content -->
    <main ref="mainRef" class="main-content">
      <!-- Topbar -->
      <header ref="topbarRef" class="topbar">
        <div class="topbar-left">
          <slot name="breadcrumb" />
        </div>
        <div class="topbar-right">
          <span class="admin-badge">{{ $page.props.auth.user.name }}</span>

          <Button icon="pi pi-sign-out" text rounded severity="secondary" @click="logout" />
        </div>
      </header>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="flash-success">
        <i class="pi pi-check-circle" />
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="flash-error">
        <i class="pi pi-times-circle" />
        {{ $page.props.flash.error }}
      </div>

      <!-- Page content -->
      <div class="page-content">
        <slot />
      </div>
    </main>

    <!-- Floating Theme Toggle Switch -->
    <button 
      class="theme-switch-float" 
      @click="toggleTheme" 
      aria-label="Toggle Theme"
      title="Changer de Thème"
    >
      <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'" />
    </button>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
<<<<<<< HEAD
import { ref, onMounted } from 'vue'

const isDark = ref(true)

const initTheme = () => {
  isDark.value = localStorage.getItem('theme') !== 'light'
  if (isDark.value) {
    document.documentElement.classList.remove('light-theme')
  } else {
    document.documentElement.classList.add('light-theme')
  }
}

const toggleTheme = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    localStorage.setItem('theme', 'dark')
    document.documentElement.classList.remove('light-theme')
  } else {
    localStorage.setItem('theme', 'light')
    document.documentElement.classList.add('light-theme')
  }
}

onMounted(() => {
  initTheme()
})
=======
import { onMounted, ref } from 'vue'
import gsap from 'gsap'
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164

const page = usePage()
const sidebarRef = ref(null)
const mainRef = ref(null)
const topbarRef = ref(null)

const isActive = (routeName) => {
  // Supprime le ".index" pour la comparaison de base
  const baseRoute = routeName.endsWith('.index') ? routeName.slice(0, -6) : routeName;
  const currentRoute = route().current();
  return currentRoute && currentRoute.startsWith(baseRoute);
}

const logout = () => {
  router.post(route('logout'))
}

onMounted(() => {
  const tl = gsap.timeline()

  tl.from(sidebarRef.value, {
    x: -100,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  })

  tl.from(topbarRef.value, {
    y: -50,
    opacity: 0,
    duration: 0.6,
    ease: 'back.out(1.7)'
  }, '-=0.4')

  tl.from('.page-content', {
    y: 20,
    opacity: 0,
    duration: 0.5,
    ease: 'power2.out'
  }, '-=0.2')

  // Animation des items du menu
  gsap.from('.nav-item', {
    x: -20,
    opacity: 0,
    stagger: 0.05,
    duration: 0.5,
    ease: 'power2.out',
    delay: 0.5
  })
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
<<<<<<< HEAD
  background: var(--bg-main);
  color: var(--text-main);
  transition: all 0.3s ease;
=======
  background: #0b0e14;
  color: #e2e8f0;
  font-family: 'Inter', sans-serif;
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
}

.sidebar {
  width: 260px;
  min-height: 100vh;
<<<<<<< HEAD
  background: #10121b;
  color: var(--text-main);
=======
  background: #111827;
  border-right: 1px solid #1f2937;
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0; top: 0;
  z-index: 100;
<<<<<<< HEAD
  border-right: 1px solid var(--border-glow);
}

:root.light-theme .sidebar {
  background: #ffffff;
  border-right-color: rgba(0, 0, 0, 0.06);
=======
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5);
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
}

.sidebar-logo {
  padding: 2rem 1.5rem;
  display: flex;
  align-items: center;
<<<<<<< HEAD
  gap: 0.75rem;
  border-bottom: 1px solid var(--border-glow);
}

.logo-icon { font-size: 1.5rem; }
.logo-text  { 
  font-family: 'Orbitron', sans-serif !important;
  font-weight: 900 !important;
  letter-spacing: 0.05em !important;
  text-transform: uppercase !important;
  color: var(--accent-primary);
=======
  gap: 1rem;
  border-bottom: 1px solid rgba(255, 149, 0, 0.2);
}

.logo-icon {
  font-size: 1.75rem;
  filter: drop-shadow(0 0 8px rgba(255, 149, 0, 0.5));
}
.logo-text {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #FF9500;
  text-shadow: 0 0 10px rgba(255, 149, 0, 0.3);
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
}

.sidebar-nav {
  padding: 1.5rem 0;
  flex: 1;
  overflow-y: auto;
}

.nav-section-title {
<<<<<<< HEAD
  font-family: 'Orbitron', sans-serif !important;
  font-size: 0.65rem;
  font-weight: 900;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text-muted);
  padding: 1rem 1.25rem 0.4rem;
=======
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: rgba(255, 149, 0, 0.85);
  padding: 1.5rem 1.5rem 0.5rem;
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
}

.nav-item {
  display: flex;
  align-items: center;
<<<<<<< HEAD
  gap: 0.75rem;
  padding: 0.65rem 1.25rem;
  color: var(--text-muted);
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  font-weight: 600;
}

.nav-item:hover {
  color: var(--accent-primary);
  background: rgba(255, 149, 0, 0.04);
}

:root.light-theme .nav-item:hover {
  background: rgba(0, 135, 81, 0.04);
}

.nav-item.active {
  color: var(--accent-primary);
  background: rgba(255, 149, 0, 0.08);
  border-left-color: var(--accent-primary);
}

:root.light-theme .nav-item.active {
  background: rgba(0, 135, 81, 0.08);
  border-left-color: var(--accent-primary);
=======
  gap: 1rem;
  padding: 0.85rem 1.5rem;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border-left: 4px solid transparent;
}

.nav-item i {
  font-size: 1.1rem;
  color: rgba(255, 149, 0, 0.6);
}

.nav-item:hover {
  color: #fff;
  background: rgba(255, 149, 0, 0.1);
  padding-left: 1.75rem;
}

.nav-item:hover i {
  color: #FF9500;
}

.nav-item.active {
  color: #fff;
  background: linear-gradient(90deg, rgba(255, 149, 0, 0.2) 0%, transparent 100%);
  border-left-color: #FF9500;
  font-weight: 600;
}

.nav-item.active i {
  color: #FF9500;
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
}

.main-content {
  margin-left: 260px;
  flex: 1;
  display: flex;
  flex-direction: column;
  width: calc(100% - 260px);
}

.topbar {
<<<<<<< HEAD
  height: 60px;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-glow);
=======
  height: 70px;
  background: rgba(17, 24, 39, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #1f2937;
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  position: sticky;
  top: 0;
  z-index: 50;
}

:root.light-theme .topbar {
  background: #ffffff;
  border-bottom-color: rgba(0, 0, 0, 0.06);
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.admin-badge {
  background: rgba(255, 149, 0, 0.1);
  color: #FF9500;
<<<<<<< HEAD
  font-family: 'Share Tech Mono', monospace !important;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 99px;
  border: 1px solid rgba(255, 149, 0, 0.2);
}

:root.light-theme .admin-badge {
  background: rgba(0, 135, 81, 0.1);
  color: #008751;
  border-color: rgba(0, 135, 81, 0.2);
}

.flash-success, .flash-error {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.flash-success {
  background: rgba(16, 18, 27, 0.9);
  border-bottom: 1px solid rgba(0, 229, 255, 0.2);
  border-left: 4px solid var(--accent-secondary);
  color: #ffffff;
}

:root.light-theme .flash-success {
  background: #f0fdf4;
  border-bottom-color: rgba(0, 135, 81, 0.15);
  border-left-color: var(--accent-primary);
  color: #166534;
}

.flash-error {
  background: rgba(16, 18, 27, 0.9);
  border-bottom: 1px solid rgba(239, 68, 68, 0.2);
  border-left: 4px solid #ef4444;
  color: #f87171;
}

:root.light-theme .flash-error {
  background: #fef2f2;
  border-bottom-color: rgba(239, 68, 68, 0.15);
  border-left-color: #ef4444;
  color: #991b1b;
=======
  padding: 0.4rem 1rem;
  border-radius: 4px;
  font-family: 'Rajdhani', sans-serif;
  font-weight: 600;
  font-size: 0.9rem;
  border: 1px solid rgba(255, 149, 0, 0.2);
  text-transform: uppercase;
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
}

.page-content {
  padding: 2rem;
  flex: 1;
}

.flash-success, .flash-error {
  margin: 1rem 2rem 0;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 500;
  animation: slideIn 0.3s ease-out;
}

.flash-success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.flash-error {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

@keyframes slideIn {
  from { transform: translateY(-10px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
