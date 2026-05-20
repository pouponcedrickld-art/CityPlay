<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
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
    <main class="main-content">
      <!-- Topbar -->
      <header class="topbar">
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

const page = usePage()

const isActive = (routeName) => {
  // Supprime le ".index" pour la comparaison de base
  const baseRoute = routeName.endsWith('.index') ? routeName.slice(0, -6) : routeName;
  const currentRoute = route().current();
  return currentRoute && currentRoute.startsWith(baseRoute);
}

const logout = () => {
  router.post(route('logout'))
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-main);
  color: var(--text-main);
  transition: all 0.3s ease;
}

.sidebar {
  width: 240px;
  min-height: 100vh;
  background: #10121b;
  color: var(--text-main);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0; top: 0;
  z-index: 100;
  border-right: 1px solid var(--border-glow);
}

:root.light-theme .sidebar {
  background: #ffffff;
  border-right-color: rgba(0, 0, 0, 0.06);
}

.sidebar-logo {
  padding: 1.5rem 1.25rem;
  display: flex;
  align-items: center;
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
}

.sidebar-nav {
  padding: 1rem 0;
  flex: 1;
}

.nav-section-title {
  font-family: 'Orbitron', sans-serif !important;
  font-size: 0.65rem;
  font-weight: 900;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text-muted);
  padding: 1rem 1.25rem 0.4rem;
}

.nav-item {
  display: flex;
  align-items: center;
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
}

.main-content {
  margin-left: 240px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.topbar {
  height: 60px;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-glow);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
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
  gap: 0.75rem;
}

.admin-badge {
  background: rgba(255, 149, 0, 0.1);
  color: #FF9500;
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
}

.page-content {
  padding: 1.75rem;
  flex: 1;
}
</style>
