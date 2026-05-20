<template>
  <div class="admin-layout" :class="{ 'mobile-menu-open': isMobileMenuOpen }">
    <!-- Mobile Overlay -->
    <div v-if="isMobileMenuOpen" class="mobile-overlay" @click="closeMobileMenu"></div>
    <!-- Sidebar -->
    <aside ref="sidebarRef" class="sidebar" :class="{ 'sidebar-open': isMobileMenuOpen }">
      <div class="sidebar-logo">
        <span class="logo-icon">🗺️</span>
        <span class="logo-text">CityQuest Admin</span>
      </div>

      <nav class="sidebar-nav" @click="closeMobileMenu">
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
          <span>Parcours</span>
        </Link>

        <Link v-if="$page.props.environnement" :href="route('admin.lieux.index', $page.props.environnement.id)" class="nav-item" :class="{ active: isActive('admin.lieux') }">
          <i class="pi pi-map-marker" />
          <span>Lieux</span>
        </Link>

        <Link v-if="$page.props.lieu" :href="route('admin.enigmes.index', $page.props.lieu.id)" class="nav-item" :class="{ active: isActive('admin.enigmes') }">
          <i class="pi pi-box" />
          <span>Énigmes</span>
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
        <div class="topbar-left" style="display: flex; align-items: center;">
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



    <!-- Mobile Bottom Navigation (Visible only on mobile) -->
    <div class="mobile-bottom-nav md:hidden flex" :class="isDark ? 'bg-[#1f2937]' : 'bg-white'">
      <div class="nav-items-container">
        <!-- Dashboard -->
        <Link :href="route('admin.dashboard')" class="nav-item-bottom" :class="{ 'active': isActive('admin.dashboard') }">
          <i class="pi pi-home"></i>
          <span>Home</span>
        </Link>

        <!-- Villes -->
        <Link :href="route('admin.villes.index')" class="nav-item-bottom" :class="{ 'active': isActive('admin.villes') }">
          <i class="pi pi-map"></i>
          <span>Villes</span>
        </Link>

        <!-- Player / Jouer (Center FAB) -->
        <div class="nav-item-center-wrapper">
          <Link :href="route('dashboard')" class="nav-item-center">
            <div class="center-btn-inner">
              <i class="pi pi-play"></i>
            </div>
            <span>Jouer</span>
          </Link>
        </div>

        <!-- Users -->
        <Link :href="route('admin.users.index')" class="nav-item-bottom" :class="{ 'active': isActive('admin.users') }">
          <i class="pi pi-users"></i>
          <span>Users</span>
        </Link>

        <!-- Plus Menu -->
        <button class="nav-item-bottom" :class="{ 'active': isMobileMenuOpen }" @click="toggleMobileMenu">
          <i class="pi pi-ellipsis-h"></i>
          <span>Plus</span>
        </button>
      </div>
    </div>

    <!-- Mobile Action Sheet for Extra Links -->
    <div v-if="isMobileMenuOpen" class="mobile-sheet-overlay" @click="closeMobileMenu"></div>
    <div class="mobile-action-sheet" :class="{ 'open': isMobileMenuOpen }">
      <div class="sheet-handle" @click="closeMobileMenu"></div>
      <div class="sheet-content">
        <h3 class="sheet-title">Autres Menus</h3>
        <Link :href="route('admin.parties.index')" class="sheet-item" @click="closeMobileMenu">
          <i class="pi pi-th-large"></i>
          <span>Gestion Parties</span>
        </Link>
        <Link :href="route('admin.environnements.index')" class="sheet-item" @click="closeMobileMenu">
          <i class="pi pi-globe"></i>
          <span>Environnements</span>
        </Link>
        <Link :href="route('admin.invitations.index')" class="sheet-item" @click="closeMobileMenu">
          <i class="pi pi-link"></i>
          <span>Invitations App</span>
        </Link>
      </div>
    </div>

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
import { onMounted, ref } from 'vue'
import gsap from 'gsap'

// Mobile menu state
const isMobileMenuOpen = ref(false)
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}
const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

// Theme management
const isDark = ref(true)
const toggleTheme = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.remove('light-theme')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.add('light-theme')
    localStorage.setItem('theme', 'light')
  }
}

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
  if (document.documentElement.classList.contains('light-theme') || localStorage.getItem('theme') === 'light') {
    isDark.value = false
    document.documentElement.classList.add('light-theme')
  } else {
    isDark.value = true
    document.documentElement.classList.remove('light-theme')
  }

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

/* --- RESPONSIVE MOBILE FIXES --- */
@media (max-width: 768px) {
  .sidebar {
    display: none; /* Hide sidebar completely on mobile */
  }
  .main-content {
    margin-left: 0;
    width: 100%;
    padding-bottom: 80px; /* Space for bottom nav */
  }
  .topbar {
    padding: 0 1rem;
  }
  .admin-badge {
    display: none; /* Cache le nom sur mobile pour gagner de la place */
  }
  .page-content {
    padding: 1rem;
    overflow-x: hidden;
  }

  /* Force global tables to scroll horizontally on mobile */
  :deep(.page-content .p-datatable-wrapper), :deep(.page-content table) {
    overflow-x: auto !important;
    display: block !important;
    width: 100% !important;
  }

  .mobile-bottom-nav {
    display: flex;
  }

  /* Theme toggle float repositioning to not overlap bottom nav */
  .theme-switch-float {
    bottom: 90px !important;
  }
}

@media (min-width: 769px) {
  .mobile-bottom-nav,
  .mobile-action-sheet,
  .mobile-sheet-overlay {
    display: none !important;
  }
}

/* Bottom Nav Styles */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 70px;
  border-radius: 20px 20px 0 0;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.8);
  z-index: 100;
  align-items: center;
}

:root.light-theme .mobile-bottom-nav {
  background: #ffffff;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
}

.nav-items-container {
  display: flex;
  width: 100%;
  justify-content: space-around;
  align-items: flex-end;
  height: 100%;
  padding-bottom: 10px;
  position: relative;
}

.nav-item-bottom {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
  color: #9ca3af;
  text-decoration: none;
  font-size: 10px;
  font-weight: 600;
  width: 20%;
  background: transparent;
  border: none;
  height: 100%;
  position: relative;
}

.nav-item-bottom i {
  font-size: 20px;
  transition: all 0.3s ease;
}

.nav-item-bottom.active {
  color: #3b82f6; /* Blue active state */
}

.nav-item-bottom.active i {
  transform: translateY(-2px);
}

/* Active indicator dash */
.nav-item-bottom.active::before {
  content: '';
  position: absolute;
  top: 8px;
  width: 12px;
  height: 3px;
  background: #3b82f6;
  border-radius: 3px;
}

/* Center Elevated Button */
.nav-item-center-wrapper {
  width: 20%;
  display: flex;
  justify-content: center;
  position: relative;
  height: 100%;
}

.nav-item-center {
  position: absolute;
  bottom: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-decoration: none;
  color: #9ca3af;
  font-size: 10px;
  font-weight: 600;
}

.nav-item-center.active {
  color: #3b82f6;
}

.center-btn-inner {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: #4f46e5;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transform: translateY(-12px);
  border: 6px solid #1f2937; /* Matches bottom nav background */
  box-sizing: content-box;
  box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);
  transition: all 0.3s;
}

:root.light-theme .center-btn-inner {
  border-color: #ffffff;
}

.center-btn-inner i {
  font-size: 22px;
  color: #ffffff;
}

/* Action Sheet for extra items */
.mobile-sheet-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 90;
  backdrop-filter: blur(2px);
}

.mobile-action-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background: #1f2937;
  border-radius: 24px 24px 0 0;
  z-index: 101;
  transform: translateY(100%);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 1rem 1.5rem 2.5rem;
}

:root.light-theme .mobile-action-sheet {
  background: #ffffff;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
}

.mobile-action-sheet.open {
  transform: translateY(0);
}

.sheet-handle {
  width: 40px;
  height: 4px;
  background: #4b5563;
  border-radius: 2px;
  margin: 0 auto 1.5rem;
}

:root.light-theme .sheet-handle {
  background: #e5e7eb;
}

.sheet-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 1rem;
}

.sheet-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
  color: #e5e7eb;
  text-decoration: none;
  font-weight: 600;
  border-bottom: 1px solid #374151;
}

:root.light-theme .sheet-item {
  color: #1f2937;
  border-bottom-color: #f3f4f6;
}

.sheet-item:last-child {
  border-bottom: none;
}

.sheet-item i {
  font-size: 1.25rem;
  color: #3b82f6;
  width: 24px;
  text-align: center;
}
</style>
