<template>
  <div class="mobile-auth-container md:hidden block h-screen w-full relative overflow-hidden transition-colors duration-500" :class="isDark ? 'bg-black' : 'bg-white'">
    
    <!-- Top Header with Logo -->
    <div class="mobile-auth-header header-wave absolute top-0 left-0 w-full overflow-hidden transition-colors duration-500" :class="isDark ? 'bg-[#040508]' : 'bg-[#f3f4f6]'" :style="{ height: currentView === 'welcome' ? '50vh' : '35vh' }">
      
      <img :src="isDark ? logoWhrite : logoDark" alt="CityPlay Logo" class="auth-logo absolute top-0 left-0 w-full h-full" style="object-fit: fill;" />
      
      <!-- SVG curve transition matching the theme -->
      <svg class="mobile-auth-curve wave-curve absolute bottom-0 left-0 w-full h-[40px] transition-colors duration-500 z-10" style="transform: translateY(99%)" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path :fill="isDark ? '#111827' : '#ffffff'" fill-opacity="1" d="M0,160L80,144C160,128,320,96,480,112C640,128,800,192,960,202.7C1120,213,1280,171,1360,149.3L1440,128L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
      </svg>
    </div>

    <!-- Content Area -->
    <div class="mobile-auth-content form-container absolute bottom-0 left-0 w-full z-10 transition-colors duration-500" :class="isDark ? 'bg-[#111827]' : 'bg-white'" :style="{ height: currentView === 'welcome' ? '50vh' : '65vh', paddingTop: '1rem' }">
      
      <!-- WELCOME VIEW -->
      <div v-if="currentView === 'welcome'" class="welcome-view px-8 h-full flex flex-col justify-center">
        <h1 class="welcome-title text-4xl font-black uppercase tracking-tight mb-2" :class="isDark ? 'text-white' : 'text-[#1F2937]'">
          {{ mode === 'login' ? 'Welcome Back' : 'Join Guild' }}
        </h1>
        <p class="welcome-subtitle text-sm" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'">
          {{ mode === 'login' ? 'Prêt à reprendre votre quête ?' : 'Créez votre légende aujourd\'hui.' }}
        </p>
        
        <div class="mt-auto mb-12 self-end">
          <button class="continue-btn flex items-center gap-3 font-bold active:scale-95 transition-transform" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'" @click="goToForm" @touchstart="hapticFeedback">
            <span class="text-sm">Continuer</span>
            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-[0_4px_12px_rgba(255,149,0,0.3)] text-black" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669] text-white'">
              <i class="pi pi-arrow-right font-bold"></i>
            </div>
          </button>
        </div>
      </div>

      <!-- FORM VIEW -->
      <div v-else class="form-view px-8 h-full flex flex-col pt-2 pb-6 overflow-y-auto">
        <div class="relative inline-block mb-6 form-header-anim">
          <h1 class="text-3xl font-black uppercase tracking-tight" :class="isDark ? 'text-white' : 'text-[#1F2937]'">
            {{ mode === 'login' ? 'Sign in' : 'Register' }}
          </h1>
          <div class="w-10 h-[3px] mt-1 ml-1 rounded-full transition-colors duration-500" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669]'"></div>
        </div>
        
        <form @submit.prevent="handleAuth" class="space-y-5">
          
          <!-- REGISTER FIELDS (Name, Pseudo) -->
          <template v-if="mode === 'register'">
            <div class="form-field">
              <label class="field-label block text-[10px] uppercase font-black tracking-widest mb-1" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'">Nom Réel</label>
              <div class="input-wrapper relative">
                <i class="pi pi-user absolute left-1 top-1/2 -translate-y-1/2 text-sm" :class="isDark ? 'text-gray-500' : 'text-[#6B7280]'"></i>
                <input type="text" class="auth-input w-full pl-8 pr-3 py-2 border-none bg-transparent text-sm font-bold focus:ring-0" :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#111827] placeholder-gray-400'" placeholder="Koffi Mensah" v-model="form.name" @focus="focusInput" @blur="blurInput" />
                <div class="input-border-bottom absolute bottom-0 left-0 w-full h-[1px]" :class="isDark ? 'bg-white/10' : 'bg-[#E5E7EB]'"></div>
                <div class="input-border-focus absolute bottom-0 left-0 w-full h-[2px] scale-x-0 origin-left" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669]'"></div>
              </div>
              <div v-if="form.errors.name" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.name }}</div>
            </div>

            <div class="form-field">
              <label class="field-label block text-[10px] uppercase font-black tracking-widest mb-1" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'">Pseudo</label>
              <div class="input-wrapper relative">
                <i class="pi pi-compass absolute left-1 top-1/2 -translate-y-1/2 text-sm" :class="isDark ? 'text-gray-500' : 'text-[#6B7280]'"></i>
                <input type="text" class="auth-input w-full pl-8 pr-3 py-2 border-none bg-transparent text-sm font-bold focus:ring-0" :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#111827] placeholder-gray-400'" placeholder="Amazone" v-model="form.pseudo" @focus="focusInput" @blur="blurInput" />
                <div class="input-border-bottom absolute bottom-0 left-0 w-full h-[1px]" :class="isDark ? 'bg-white/10' : 'bg-[#E5E7EB]'"></div>
                <div class="input-border-focus absolute bottom-0 left-0 w-full h-[2px] scale-x-0 origin-left" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669]'"></div>
              </div>
              <div v-if="form.errors.pseudo" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.pseudo }}</div>
            </div>
          </template>

          <!-- COMMON FIELDS (Email, Password) -->
          <div class="form-field">
            <label class="field-label block text-[10px] uppercase font-black tracking-widest mb-1" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'">Email</label>
            <div class="input-wrapper relative">
              <i class="pi pi-envelope absolute left-1 top-1/2 -translate-y-1/2 text-sm" :class="isDark ? 'text-gray-500' : 'text-[#6B7280]'"></i>
              <input type="email" placeholder="explorateur@email.com" class="auth-input w-full pl-8 pr-3 py-2 border-none bg-transparent text-sm font-bold focus:ring-0" :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#111827] placeholder-gray-400'" v-model="form.email" @focus="focusInput" @blur="blurInput" />
              <div class="input-border-bottom absolute bottom-0 left-0 w-full h-[1px]" :class="isDark ? 'bg-white/10' : 'bg-[#E5E7EB]'"></div>
              <div class="input-border-focus absolute bottom-0 left-0 w-full h-[2px] scale-x-0 origin-left" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669]'"></div>
            </div>
            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.email }}</div>
          </div>

          <div class="form-field">
            <label class="field-label block text-[10px] uppercase font-black tracking-widest mb-1" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'">Password</label>
            <div class="input-wrapper relative">
              <i class="pi pi-lock absolute left-1 top-1/2 -translate-y-1/2 text-sm" :class="isDark ? 'text-gray-500' : 'text-[#6B7280]'"></i>
              <input :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="auth-input w-full pl-8 pr-8 py-2 border-none bg-transparent text-sm font-bold focus:ring-0" :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#111827] placeholder-gray-400'" v-model="form.password" @focus="focusInput" @blur="blurInput" />
              <i class="pi absolute right-1 top-1/2 -translate-y-1/2 text-sm cursor-pointer p-1" :class="[showPassword ? 'pi-eye-slash' : 'pi-eye', isDark ? 'text-gray-500' : 'text-[#6B7280]']" @click="togglePassword" ref="eyeIcon"></i>
              <div class="input-border-bottom absolute bottom-0 left-0 w-full h-[1px]" :class="isDark ? 'bg-white/10' : 'bg-[#E5E7EB]'"></div>
              <div class="input-border-focus absolute bottom-0 left-0 w-full h-[2px] scale-x-0 origin-left" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669]'"></div>
            </div>
            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password }}</div>
          </div>

          <!-- REGISTER FIELD (Password Confirm) -->
          <template v-if="mode === 'register'">
            <div class="form-field">
              <label class="field-label block text-[10px] uppercase font-black tracking-widest mb-1" :class="isDark ? 'text-gray-400' : 'text-[#6B7280]'">Confirmer Password</label>
              <div class="input-wrapper relative">
                <i class="pi pi-lock absolute left-1 top-1/2 -translate-y-1/2 text-sm" :class="isDark ? 'text-gray-500' : 'text-[#6B7280]'"></i>
                <input :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="auth-input w-full pl-8 pr-3 py-2 border-none bg-transparent text-sm font-bold focus:ring-0" :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#111827] placeholder-gray-400'" v-model="form.password_confirmation" @focus="focusInput" @blur="blurInput" />
                <div class="input-border-bottom absolute bottom-0 left-0 w-full h-[1px]" :class="isDark ? 'bg-white/10' : 'bg-[#E5E7EB]'"></div>
                <div class="input-border-focus absolute bottom-0 left-0 w-full h-[2px] scale-x-0 origin-left" :class="isDark ? 'bg-[#FF9500]' : 'bg-[#059669]'"></div>
              </div>
              <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password_confirmation }}</div>
            </div>
            
            <div class="form-field flex items-start gap-2 mt-2">
              <div class="custom-checkbox mt-0.5 w-4 h-4 rounded-sm border flex items-center justify-center transition-colors flex-shrink-0" :class="[form.consent_cgu ? (isDark ? 'bg-[#FF9500] border-[#FF9500]' : 'bg-[#059669] border-[#059669]') : (isDark ? 'border-gray-600' : 'border-gray-400')]" @click.prevent="form.consent_cgu = !form.consent_cgu">
                <i v-if="form.consent_cgu" class="pi pi-check text-[10px]" :class="isDark ? 'text-black' : 'text-white'"></i>
              </div>
              <span class="text-[10px] font-bold leading-tight" :class="isDark ? 'text-gray-400' : 'text-gray-600'">J'accepte les CGU et la charte de confidentialité</span>
            </div>
            <div class="form-field flex items-start gap-2 mt-2">
              <div class="custom-checkbox mt-0.5 w-4 h-4 rounded-sm border flex items-center justify-center transition-colors flex-shrink-0" :class="[form.consent_donnees ? (isDark ? 'bg-[#FF9500] border-[#FF9500]' : 'bg-[#059669] border-[#059669]') : (isDark ? 'border-gray-600' : 'border-gray-400')]" @click.prevent="form.consent_donnees = !form.consent_donnees">
                <i v-if="form.consent_donnees" class="pi pi-check text-[10px]" :class="isDark ? 'text-black' : 'text-white'"></i>
              </div>
              <span class="text-[10px] font-bold leading-tight" :class="isDark ? 'text-gray-400' : 'text-gray-600'">J'accepte l'utilisation de mes données de géolocalisation</span>
            </div>
          </template>

          <!-- LOGIN FIELDS (Remember Me, Forgot Pass) -->
          <template v-if="mode === 'login'">
            <div class="form-field flex justify-between items-center mt-2">
              <label class="flex items-center gap-2 cursor-pointer remember-label">
                <div class="custom-checkbox w-4 h-4 rounded-sm border flex items-center justify-center transition-colors" :class="[form.remember ? (isDark ? 'bg-[#FF9500] border-[#FF9500]' : 'bg-[#059669] border-[#059669]') : (isDark ? 'border-gray-600' : 'border-gray-400')]">
                  <input type="checkbox" v-model="form.remember" class="hidden" />
                  <i v-if="form.remember" class="pi pi-check text-[10px]" :class="isDark ? 'text-black' : 'text-white'"></i>
                </div>
                <span class="text-xs font-bold" :class="isDark ? 'text-gray-300' : 'text-[#1F2937]'">Rester connecté</span>
              </label>
              <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] uppercase font-black tracking-widest" :class="isDark ? 'text-[#FF9500]' : 'text-[#059669]'">Boussole perdue ?</Link>
            </div>
          </template>

          <!-- SUBMIT BUTTON -->
          <button type="submit" class="login-btn w-full mt-6 text-black font-black uppercase tracking-widest text-sm rounded-xl h-14 shadow-[0_5px_0_rgba(0,0,0,0.2)] active:shadow-none active:translate-y-[5px] relative overflow-hidden transition-all flex items-center justify-center gap-2" :class="isDark ? 'bg-[#FF9500] shadow-[0_5px_0_#cc7a00]' : 'bg-[#059669] text-white shadow-[0_5px_0_#047857]'" ref="loginBtnRef" :disabled="form.processing" @touchstart="hapticFeedback" style="opacity: 1 !important; visibility: visible !important;">
            <span>{{ mode === 'login' ? 'Connexion' : 'Créer mon compte' }}</span>
            <i class="pi" :class="mode === 'login' ? 'pi-sign-in' : 'pi-user-plus'"></i>
          </button>

          <!-- FOOTER LINK -->
         
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';

// Import logos
import logoWhrite from '@/Layouts/logo_whrite.jpg';
import logoDark from '@/Layouts/logo_dark.jpg';

const isDark = inject("isDark", ref(true));

const props = defineProps({
  form: Object,
  submit: Function,
  canResetPassword: { type: Boolean, default: false },
  mode: { type: String, default: 'login' } // 'login' or 'register'
});

const currentView = ref('welcome');
const showPassword = ref(false);
const eyeIcon = ref(null);
const loginBtnRef = ref(null);

onMounted(() => {
  // Intro animations
  const tl = gsap.timeline();
  tl.from(".header-wave", { y: "-10%", opacity: 0, duration: 0.8, ease: "power3.out" })
    .from(".auth-logo", { scale: 0.8, opacity: 0, duration: 1, ease: "elastic.out(1, 0.5)" }, "-=0.6")
    .from(".wave-curve", { y: -20, opacity: 0, duration: 1, ease: "power2.inOut" }, "-=0.8")
    .from(".form-container", { y: 50, opacity: 0, duration: 0.6, ease: "back.out(1.2)" }, "-=0.6")
    .from(".welcome-title, .welcome-subtitle", { x: -20, opacity: 0, stagger: 0.1, duration: 0.5, ease: "power2.out" }, "-=0.4")
    .from(".continue-btn", { scale: 0.8, opacity: 0, duration: 0.4, ease: "back.out(1.5)" }, "-=0.2");
});

onUnmounted(() => {
  gsap.killTweensOf("*");
});

const goToForm = () => {
  const tl = gsap.timeline({
    onComplete: () => {
      currentView.value = 'form';
      nextTick(() => {
        // Cascading stagger form fields
        gsap.set(".form-view", { opacity: 1 });
        gsap.from(".form-header-anim", { x: -30, opacity: 0, duration: 0.4, ease: "power2.out" });
        gsap.from(".form-field", { x: -30, opacity: 0, stagger: 0.08, duration: 0.5, ease: "power2.out" }, "-=0.2");
        gsap.from(".login-btn", { scale: 0.8, opacity: 0, duration: 0.4, ease: "elastic.out(1, 0.5)" }, "-=0.1");
      });
    }
  });

  tl.to(".welcome-title", { x: -50, opacity: 0, duration: 0.3 })
    .to(".welcome-subtitle", { x: -30, opacity: 0, duration: 0.3 }, "-=0.2")
    .to(".continue-btn", { scale: 0.8, opacity: 0, duration: 0.3 }, "-=0.2")
    .to(".header-wave", { height: "35vh", duration: 0.6, ease: "power2.inOut" }, "-=0.1")
    .to(".auth-logo", { scale: 0.85, y: -20, duration: 0.6, ease: "power2.inOut" }, "-=0.6")
    .to(".form-container", { height: "65vh", duration: 0.6, ease: "power2.inOut" }, "-=0.6");
};

const focusInput = (e) => {
  const wrapper = e.target.closest('.input-wrapper');
  if (wrapper) {
    const focusLine = wrapper.querySelector('.input-border-focus');
    gsap.to(focusLine, { scaleX: 1, duration: 0.3, ease: "power1.out" });
  }
};

const blurInput = (e) => {
  const wrapper = e.target.closest('.input-wrapper');
  if (wrapper) {
    const focusLine = wrapper.querySelector('.input-border-focus');
    gsap.to(focusLine, { scaleX: 0, duration: 0.3, ease: "power1.out" });
  }
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
  if (eyeIcon.value) {
    gsap.to(eyeIcon.value, { rotation: "+=180", duration: 0.3 });
  }
};

const hapticFeedback = (e) => {
  gsap.to(e.currentTarget, { scale: 0.96, duration: 0.1, yoyo: true, repeat: 1 });
};

const handleAuth = () => {
  if (loginBtnRef.value) {
    gsap.to(loginBtnRef.value, { scale: 0.95, duration: 0.1, yoyo: true, repeat: 1 });
  }
  props.submit();
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&family=Bungee&display=swap');

.font-bungee { font-family: 'Bungee', cursive; }
.font-fredoka { font-family: 'Fredoka', sans-serif; }

.mobile-auth-container {
  font-family: 'Outfit', sans-serif;
}

.mobile-auth-header {
  position: relative;
  will-change: height, transform;
}

.mobile-auth-content {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  will-change: height;
}

.auth-input:focus {
  outline: none;
  box-shadow: none;
}

.input-border-focus {
  transform-origin: left center;
  will-change: transform;
}

/* Scrollbar styling for mobile forms if needed */
.form-view::-webkit-scrollbar { width: 4px; }
.form-view::-webkit-scrollbar-track { background: transparent; }
.form-view::-webkit-scrollbar-thumb {
    background: rgba(128, 128, 128, 0.2);
    border-radius: 2px;
}
</style>
