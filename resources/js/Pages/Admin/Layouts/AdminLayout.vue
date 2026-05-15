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
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'

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
  background: #f8f9fb;
  font-family: 'Inter', sans-serif;
}

.sidebar {
  width: 240px;
  min-height: 100vh;
  background: #1e2535;
  color: #fff;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0; top: 0;
  z-index: 100;
}

.sidebar-logo {
  padding: 1.5rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.logo-icon { font-size: 1.5rem; }
.logo-text  { font-size: 1rem; font-weight: 700; letter-spacing: 0.02em; }

.sidebar-nav {
  padding: 1rem 0;
  flex: 1;
}

.nav-section-title {
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.35);
  padding: 1rem 1.25rem 0.4rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 1.25rem;
  color: rgba(255,255,255,0.65);
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.15s;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  color: #fff;
  background: rgba(255,255,255,0.06);
}

.nav-item.active {
  color: #fff;
  background: rgba(99, 179, 237, 0.12);
  border-left-color: #63b3ed;
}

.main-content {
  margin-left: 240px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.topbar {
  height: 60px;
  background: #fff;
  border-bottom: 1px solid #e5e9f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  position: sticky;
  top: 0;
  z-index: 50;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.admin-badge {
  background: #ebf8ff;
  color: #2b6cb0;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 99px;
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
  background: #f0fff4;
  color: #276749;
  border-bottom: 1px solid #c6f6d5;
}

.flash-error {
  background: #fff5f5;
  color: #c53030;
  border-bottom: 1px solid #fed7d7;
}

.page-content {
  padding: 1.75rem;
  flex: 1;
}
</style>
