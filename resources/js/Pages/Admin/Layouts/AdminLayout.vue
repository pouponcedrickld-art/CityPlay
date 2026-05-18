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
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import { onMounted, ref } from 'vue'
import gsap from 'gsap'

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
  background: #0b0e14;
  color: #e2e8f0;
  font-family: 'Inter', sans-serif;
}

.sidebar {
  width: 260px;
  min-height: 100vh;
  background: #111827;
  border-right: 1px solid #1f2937;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0; top: 0;
  z-index: 100;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5);
}

.sidebar-logo {
  padding: 2rem 1.5rem;
  display: flex;
  align-items: center;
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
}

.sidebar-nav {
  padding: 1.5rem 0;
  flex: 1;
  overflow-y: auto;
}

.nav-section-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: rgba(255, 149, 0, 0.85);
  padding: 1.5rem 1.5rem 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
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
}

.main-content {
  margin-left: 260px;
  flex: 1;
  display: flex;
  flex-direction: column;
  width: calc(100% - 260px);
}

.topbar {
  height: 70px;
  background: rgba(17, 24, 39, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #1f2937;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  position: sticky;
  top: 0;
  z-index: 50;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.admin-badge {
  background: rgba(255, 149, 0, 0.1);
  color: #FF9500;
  padding: 0.4rem 1rem;
  border-radius: 4px;
  font-family: 'Rajdhani', sans-serif;
  font-weight: 600;
  font-size: 0.9rem;
  border: 1px solid rgba(255, 149, 0, 0.2);
  text-transform: uppercase;
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
