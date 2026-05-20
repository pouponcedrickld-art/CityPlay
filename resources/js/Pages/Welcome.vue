<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { gsap } from "gsap";

// Import official logos
import logoWhrite from "@/Layouts/logo_whrite.jpg";
import logoDark from "@/Layouts/logo_dark.jpg";

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

const isDark = ref(true);

const initTheme = () => {
    isDark.value = localStorage.getItem("theme") !== "light";
    if (isDark.value) {
        document.documentElement.classList.remove("light-theme");
    } else {
        document.documentElement.classList.add("light-theme");
    }
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        localStorage.setItem("theme", "dark");
        document.documentElement.classList.remove("light-theme");
    } else {
        localStorage.setItem("theme", "light");
        document.documentElement.classList.add("light-theme");
    }
};

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

    const borderCol = isDark.value
        ? "rgba(0, 229, 255, 0.45)"
        : "rgba(0, 180, 216, 0.45)";
    const shadowCol = isDark.value
        ? "0 20px 40px rgba(0, 229, 255, 0.15)"
        : "0 15px 30px rgba(0, 180, 216, 0.12)";

    gsap.to(card, {
        rotateX: -y / 12,
        rotateY: x / 12,
        scale: 1.03,
        borderColor: borderCol,
        boxShadow: shadowCol,
        duration: 0.35,
        ease: "power2.out",
    });
};

const onCardLeave = (event) => {
    const card = event.currentTarget;
    const borderCol = isDark.value
        ? "rgba(255, 149, 0, 0.12)"
        : "rgba(5, 150, 105, 0.15)";
    const shadowCol = isDark.value
        ? "0 10px 30px rgba(0, 0, 0, 0.5)"
        : "0 10px 30px rgba(0, 0, 0, 0.05)";

    gsap.to(card, {
        rotateX: 0,
        rotateY: 0,
        scale: 1,
        borderColor: borderCol,
        boxShadow: shadowCol,
        duration: 0.5,
        ease: "power3.out",
    });
};

// Dynamic Leaderboard Tab Switching
const leaderboardTab = ref("guilds");
const activeLeaderboardData = ref([
    { rank: 1, name: "Guilde Dahomey", score: "12,450 XP", city: "Cotonou" },
    {
        rank: 2,
        name: "Les Chasseurs de Ouidah",
        score: "10,890 XP",
        city: "Ouidah",
    },
    { rank: 3, name: "Hogbonou Force", score: "9,750 XP", city: "Porto-Novo" },
    { rank: 4, name: "Cotonou Raiders", score: "8,400 XP", city: "Cotonou" },
    { rank: 5, name: "Kpassè Warriors", score: "7,900 XP", city: "Ouidah" },
]);

const changeTab = (tab) => {
    if (leaderboardTab.value === tab) return;
    leaderboardTab.value = tab;

    // Stagger entry animation on leaderboard rows
    gsap.fromTo(
        ".leaderboard-row",
        { x: -30, opacity: 0 },
        {
            x: 0,
            opacity: 1,
            duration: 0.45,
            stagger: 0.08,
            ease: "back.out(1.2)",
        },
    );

    if (tab === "guilds") {
        activeLeaderboardData.value = [
            {
                rank: 1,
                name: "Guilde Dahomey",
                score: "12,450 XP",
                city: "Cotonou",
            },
            {
                rank: 2,
                name: "Les Chasseurs de Ouidah",
                score: "10,890 XP",
                city: "Ouidah",
            },
            {
                rank: 3,
                name: "Hogbonou Force",
                score: "9,750 XP",
                city: "Porto-Novo",
            },
            {
                rank: 4,
                name: "Cotonou Raiders",
                score: "8,400 XP",
                city: "Cotonou",
            },
            {
                rank: 5,
                name: "Kpassè Warriors",
                score: "7,900 XP",
                city: "Ouidah",
            },
        ];
    } else {
        activeLeaderboardData.value = [
            {
                rank: 1,
                name: "kenzoMind_08",
                score: "5,320 XP",
                city: "Cotonou",
            },
            {
                rank: 2,
                name: "Kofi_Explorer",
                score: "4,980 XP",
                city: "Ouidah",
            },
            {
                rank: 3,
                name: "Femi_Quest",
                score: "4,750 XP",
                city: "Porto-Novo",
            },
            { rank: 4, name: "Ablavi_229", score: "4,200 XP", city: "Cotonou" },
            {
                rank: 5,
                name: "Bio_Guera_Jr",
                score: "3,950 XP",
                city: "Parakou",
            },
        ];
    }
};

// FAQ Accordion Expand & Collapse
const activeFaqIndex = ref(null);
const toggleFaq = (index) => {
    if (activeFaqIndex.value === index) {
        gsap.to(`.faq-answer-${index}`, {
            height: 0,
            opacity: 0,
            duration: 0.35,
            ease: "power2.inOut",
        });
        activeFaqIndex.value = null;
    } else {
        if (activeFaqIndex.value !== null) {
            gsap.to(`.faq-answer-${activeFaqIndex.value}`, {
                height: 0,
                opacity: 0,
                duration: 0.35,
                ease: "power2.inOut",
            });
        }
        activeFaqIndex.value = index;
        gsap.fromTo(
            `.faq-answer-${index}`,
            { height: 0, opacity: 0 },
            { height: "auto", opacity: 1, duration: 0.45, ease: "power2.out" },
        );
    }
};

// Intersection Observer for scroll animations
const observer = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                // Trigger GSAP stagger reveals on children
                const animItems =
                    entry.target.querySelectorAll(".scroll-anim-item");
                if (animItems.length > 0) {
                    gsap.fromTo(
                        animItems,
                        { y: 35, opacity: 0 },
                        {
                            y: 0,
                            opacity: 1,
                            duration: 0.75,
                            stagger: 0.12,
                            ease: "power2.out",
                        },
                    );
                }
            }
        });
    },
    { threshold: 0.08 },
);

onMounted(() => {
    initTheme();

    // 1. Mouse Tracking Glowing Pointer Follower
    window.addEventListener("mousemove", (e) => {
        gsap.to(".cursor-glow", {
            x: e.clientX,
            y: e.clientY,
            duration: 0.2,
            ease: "power2.out",
        });
    });

    // 2. Cartoon Hero Entrance Timeline
    const tl = gsap.timeline();
    tl.fromTo(
        ".cyber-overlay",
        { opacity: 1 },
        { opacity: 0, duration: 0.6, ease: "power2.inOut" },
    );
    tl.from(
        ".gsap-brand",
        {
            y: -60,
            opacity: 0,
            duration: 1,
            ease: "back.out(1.4)",
        },
        "-=0.2",
    );
    tl.from(
        ".cartoon-title span",
        {
            y: -40,
            opacity: 0,
            scale: 0.5,
            rotation: -15,
            duration: 0.6,
            stagger: 0.06,
            ease: "back.out(2)",
        },
        "-=0.6",
    );
    tl.from(
        ".gsap-cta",
        {
            y: 60,
            opacity: 0,
            duration: 0.8,
            ease: "back.out(1.5)",
        },
        "-=0.3",
    );
    tl.from(
        ".nav-bar",
        {
            y: -40,
            opacity: 0,
            duration: 0.6,
            ease: "power2.out",
        },
        "-=0.6",
    );

    // Activate scroll observer on all target sections
    document.querySelectorAll(".scroll-reveal-section").forEach((section) => {
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

        <!-- ══ MOBILE HERO (< 1024px) : Cartoon Welcome ══ -->
        <section class="section-hero section-hero--mobile">
            <!-- ══ SVG SCENE BACKGROUND ══ -->
            <div class="hero-scene" aria-hidden="true">
                <svg
                    class="scene-svg"
                    viewBox="0 0 390 844"
                    preserveAspectRatio="xMidYMid slice"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <!-- Sky gradient -->
                    <defs>
                        <linearGradient
                            id="skyGrad"
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="1"
                        >
                            <stop offset="0%" stop-color="#1a6fc4" />
                            <stop offset="45%" stop-color="#4db8e8" />
                            <stop offset="100%" stop-color="#a8e6cf" />
                        </linearGradient>
                        <linearGradient
                            id="groundGrad"
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="1"
                        >
                            <stop offset="0%" stop-color="#4caf50" />
                            <stop offset="100%" stop-color="#2e7d32" />
                        </linearGradient>
                        <linearGradient
                            id="hillGrad"
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="1"
                        >
                            <stop offset="0%" stop-color="#66bb6a" />
                            <stop offset="100%" stop-color="#388e3c" />
                        </linearGradient>
                        <linearGradient
                            id="treeTrunkL"
                            x1="0"
                            y1="0"
                            x2="1"
                            y2="0"
                        >
                            <stop offset="0%" stop-color="#5d4037" />
                            <stop offset="100%" stop-color="#3e2723" />
                        </linearGradient>
                        <linearGradient
                            id="mountainGrad"
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="1"
                        >
                            <stop offset="0%" stop-color="#7986cb" />
                            <stop offset="100%" stop-color="#5c6bc0" />
                        </linearGradient>
                        <linearGradient
                            id="sunGrad"
                            cx="50%"
                            cy="50%"
                            r="50%"
                            fx="50%"
                            fy="50%"
                            gradientUnits="objectBoundingBox"
                        >
                            <stop offset="0%" stop-color="#fff9c4" />
                            <stop offset="100%" stop-color="#ffcc02" />
                        </linearGradient>
                        <radialGradient id="sunGlow" cx="50%" cy="50%" r="50%">
                            <stop
                                offset="0%"
                                stop-color="#fff176"
                                stop-opacity="0.6"
                            />
                            <stop
                                offset="100%"
                                stop-color="#ffcc02"
                                stop-opacity="0"
                            />
                        </radialGradient>
                        <linearGradient
                            id="vignetteGrad"
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="1"
                        >
                            <stop offset="0%" stop-color="rgba(0,0,0,0)" />
                            <stop offset="60%" stop-color="rgba(0,0,0,0)" />
                            <stop offset="100%" stop-color="rgba(0,0,0,0.82)" />
                        </linearGradient>
                        <filter id="blur4">
                            <feGaussianBlur stdDeviation="4" />
                        </filter>
                        <filter id="blur2">
                            <feGaussianBlur stdDeviation="2" />
                        </filter>
                    </defs>

                    <!-- Sky -->
                    <rect width="390" height="844" fill="url(#skyGrad)" />

                    <!-- Sun glow -->
                    <circle
                        cx="195"
                        cy="200"
                        r="90"
                        fill="url(#sunGlow)"
                        filter="url(#blur4)"
                    />
                    <!-- Sun -->
                    <circle cx="195" cy="210" r="38" fill="#fff176" />
                    <circle cx="195" cy="210" r="30" fill="#ffee58" />

                    <!-- Distant mountains -->
                    <polygon
                        points="60,420 160,260 260,420"
                        fill="#7986cb"
                        opacity="0.55"
                    />
                    <polygon
                        points="140,420 230,290 320,420"
                        fill="#9fa8da"
                        opacity="0.45"
                    />
                    <polygon
                        points="200,420 290,310 380,420"
                        fill="#7986cb"
                        opacity="0.4"
                    />

                    <!-- Mid ground hills -->
                    <ellipse
                        cx="195"
                        cy="500"
                        rx="260"
                        ry="100"
                        fill="#81c784"
                    />
                    <ellipse cx="80" cy="520" rx="140" ry="80" fill="#66bb6a" />
                    <ellipse
                        cx="310"
                        cy="510"
                        rx="140"
                        ry="75"
                        fill="#66bb6a"
                    />

                    <!-- Ground -->
                    <rect
                        x="0"
                        y="490"
                        width="390"
                        height="354"
                        fill="url(#groundGrad)"
                    />

                    <!-- Path -->
                    <path
                        d="M155,844 Q175,650 185,530 Q190,490 195,480 Q200,490 205,530 Q215,650 235,844Z"
                        fill="#a5d6a7"
                        opacity="0.7"
                    />
                    <path
                        d="M162,844 Q180,660 188,535 Q192,495 195,485 Q198,495 202,535 Q210,660 228,844Z"
                        fill="#c8e6c9"
                        opacity="0.5"
                    />

                    <!-- ── LEFT BIG TREE ── -->
                    <!-- Trunk -->
                    <rect
                        x="-18"
                        y="280"
                        width="55"
                        height="320"
                        rx="20"
                        fill="url(#treeTrunkL)"
                    />
                    <!-- Roots -->
                    <ellipse
                        cx="10"
                        cy="590"
                        rx="40"
                        ry="12"
                        fill="#3e2723"
                        opacity="0.6"
                    />
                    <!-- Foliage layers -->
                    <ellipse cx="30" cy="200" rx="95" ry="110" fill="#2e7d32" />
                    <ellipse cx="15" cy="170" rx="80" ry="95" fill="#388e3c" />
                    <ellipse cx="40" cy="145" rx="70" ry="80" fill="#43a047" />
                    <ellipse cx="20" cy="125" rx="55" ry="65" fill="#4caf50" />
                    <!-- Highlight -->
                    <ellipse
                        cx="50"
                        cy="140"
                        rx="25"
                        ry="30"
                        fill="#66bb6a"
                        opacity="0.5"
                    />

                    <!-- ── RIGHT BIG TREE ── -->
                    <rect
                        x="353"
                        y="300"
                        width="55"
                        height="300"
                        rx="20"
                        fill="url(#treeTrunkL)"
                    />
                    <ellipse
                        cx="380"
                        cy="590"
                        rx="40"
                        ry="12"
                        fill="#3e2723"
                        opacity="0.6"
                    />
                    <ellipse
                        cx="360"
                        cy="215"
                        rx="90"
                        ry="105"
                        fill="#1b5e20"
                    />
                    <ellipse cx="375" cy="185" rx="78" ry="90" fill="#2e7d32" />
                    <ellipse cx="355" cy="160" rx="68" ry="78" fill="#388e3c" />
                    <ellipse cx="370" cy="140" rx="52" ry="62" fill="#43a047" />
                    <ellipse
                        cx="345"
                        cy="135"
                        rx="28"
                        ry="32"
                        fill="#66bb6a"
                        opacity="0.45"
                    />

                    <!-- Hanging vines left -->
                    <path
                        d="M55,0 Q45,80 60,160 Q70,220 55,300"
                        stroke="#33691e"
                        stroke-width="3"
                        fill="none"
                        opacity="0.8"
                    />
                    <path
                        d="M80,0 Q65,100 75,180 Q85,240 70,320"
                        stroke="#558b2f"
                        stroke-width="2.5"
                        fill="none"
                        opacity="0.7"
                    />
                    <ellipse
                        cx="60"
                        cy="160"
                        rx="8"
                        ry="10"
                        fill="#4caf50"
                        opacity="0.8"
                    />
                    <ellipse
                        cx="75"
                        cy="200"
                        rx="7"
                        ry="9"
                        fill="#66bb6a"
                        opacity="0.7"
                    />
                    <ellipse
                        cx="55"
                        cy="300"
                        rx="9"
                        ry="11"
                        fill="#4caf50"
                        opacity="0.75"
                    />

                    <!-- Hanging vines right -->
                    <path
                        d="M335,0 Q345,90 330,170 Q320,230 335,310"
                        stroke="#33691e"
                        stroke-width="3"
                        fill="none"
                        opacity="0.8"
                    />
                    <path
                        d="M310,0 Q325,110 315,190 Q305,250 320,330"
                        stroke="#558b2f"
                        stroke-width="2.5"
                        fill="none"
                        opacity="0.7"
                    />
                    <ellipse
                        cx="330"
                        cy="170"
                        rx="8"
                        ry="10"
                        fill="#4caf50"
                        opacity="0.8"
                    />
                    <ellipse
                        cx="315"
                        cy="210"
                        rx="7"
                        ry="9"
                        fill="#66bb6a"
                        opacity="0.7"
                    />
                    <ellipse
                        cx="335"
                        cy="310"
                        rx="9"
                        ry="11"
                        fill="#4caf50"
                        opacity="0.75"
                    />

                    <!-- Small bushes foreground -->
                    <ellipse cx="60" cy="600" rx="50" ry="30" fill="#2e7d32" />
                    <ellipse cx="50" cy="590" rx="38" ry="24" fill="#388e3c" />
                    <ellipse cx="330" cy="605" rx="48" ry="28" fill="#2e7d32" />
                    <ellipse cx="340" cy="595" rx="36" ry="22" fill="#388e3c" />

                    <!-- Foreground grass tufts -->
                    <ellipse cx="0" cy="700" rx="80" ry="40" fill="#1b5e20" />
                    <ellipse cx="390" cy="710" rx="80" ry="40" fill="#1b5e20" />
                    <ellipse cx="100" cy="750" rx="60" ry="30" fill="#2e7d32" />
                    <ellipse cx="290" cy="755" rx="60" ry="30" fill="#2e7d32" />

                    <!-- ── EXPLORER CHARACTER (seen from behind) ── -->
                    <!-- Shadow -->
                    <ellipse
                        cx="195"
                        cy="590"
                        rx="28"
                        ry="8"
                        fill="#1b5e20"
                        opacity="0.5"
                    />
                    <!-- Legs -->
                    <rect
                        x="181"
                        y="548"
                        width="12"
                        height="38"
                        rx="6"
                        fill="#6d4c41"
                    />
                    <rect
                        x="197"
                        y="548"
                        width="12"
                        height="38"
                        rx="6"
                        fill="#5d4037"
                    />
                    <!-- Boots -->
                    <ellipse cx="187" cy="586" rx="9" ry="6" fill="#3e2723" />
                    <ellipse cx="203" cy="586" rx="9" ry="6" fill="#3e2723" />
                    <!-- Body -->
                    <rect
                        x="175"
                        y="490"
                        width="40"
                        height="62"
                        rx="14"
                        fill="#8d6e63"
                    />
                    <!-- Belt -->
                    <rect
                        x="175"
                        y="538"
                        width="40"
                        height="8"
                        rx="3"
                        fill="#5d4037"
                    />
                    <rect
                        x="191"
                        y="536"
                        width="8"
                        height="12"
                        rx="2"
                        fill="#ffd54f"
                    />
                    <!-- Backpack -->
                    <rect
                        x="162"
                        y="495"
                        width="22"
                        height="40"
                        rx="8"
                        fill="#4caf50"
                    />
                    <rect
                        x="164"
                        y="497"
                        width="18"
                        height="36"
                        rx="6"
                        fill="#66bb6a"
                    />
                    <!-- Backpack strap -->
                    <path
                        d="M175,495 Q168,510 170,535"
                        stroke="#388e3c"
                        stroke-width="3"
                        fill="none"
                    />
                    <!-- Arms -->
                    <rect
                        x="215"
                        y="498"
                        width="10"
                        height="30"
                        rx="5"
                        fill="#8d6e63"
                    />
                    <rect
                        x="165"
                        y="498"
                        width="10"
                        height="28"
                        rx="5"
                        fill="#8d6e63"
                    />
                    <!-- Hands -->
                    <circle cx="220" cy="530" r="6" fill="#a1887f" />
                    <circle cx="170" cy="528" r="6" fill="#a1887f" />
                    <!-- Neck -->
                    <rect
                        x="188"
                        y="478"
                        width="14"
                        height="14"
                        rx="5"
                        fill="#a1887f"
                    />
                    <!-- Head -->
                    <ellipse cx="195" cy="468" rx="20" ry="18" fill="#a1887f" />
                    <!-- Hair -->
                    <ellipse cx="195" cy="455" rx="18" ry="10" fill="#5d4037" />
                    <!-- Hat brim -->
                    <ellipse cx="195" cy="452" rx="26" ry="7" fill="#f9a825" />
                    <!-- Hat body -->
                    <ellipse cx="195" cy="438" rx="20" ry="16" fill="#fbc02d" />
                    <!-- Hat highlight -->
                    <ellipse
                        cx="200"
                        cy="433"
                        rx="8"
                        ry="5"
                        fill="#fff176"
                        opacity="0.5"
                    />
                    <!-- Hat band -->
                    <rect
                        x="175"
                        y="448"
                        width="40"
                        height="6"
                        rx="3"
                        fill="#e65100"
                    />
                    <!-- Map in hand -->
                    <rect
                        x="222"
                        y="518"
                        width="16"
                        height="20"
                        rx="3"
                        fill="#fff9c4"
                        transform="rotate(-15,230,528)"
                    />
                    <line
                        x1="225"
                        y1="522"
                        x2="235"
                        y2="522"
                        stroke="#bdbdbd"
                        stroke-width="1.5"
                        transform="rotate(-15,230,528)"
                    />
                    <line
                        x1="225"
                        y1="526"
                        x2="233"
                        y2="526"
                        stroke="#bdbdbd"
                        stroke-width="1.5"
                        transform="rotate(-15,230,528)"
                    />

                    <!-- ── MAGICAL SPARKLES ── -->
                    <g class="sparkles">
                        <circle
                            cx="120"
                            cy="350"
                            r="3"
                            fill="#fff9c4"
                            opacity="0.9"
                        />
                        <circle
                            cx="270"
                            cy="320"
                            r="2.5"
                            fill="#e1f5fe"
                            opacity="0.85"
                        />
                        <circle
                            cx="80"
                            cy="420"
                            r="2"
                            fill="#f3e5f5"
                            opacity="0.8"
                        />
                        <circle
                            cx="310"
                            cy="380"
                            r="3"
                            fill="#fff9c4"
                            opacity="0.9"
                        />
                        <circle
                            cx="155"
                            cy="290"
                            r="2"
                            fill="#e8f5e9"
                            opacity="0.75"
                        />
                        <circle
                            cx="240"
                            cy="400"
                            r="2.5"
                            fill="#fff3e0"
                            opacity="0.8"
                        />
                        <circle
                            cx="340"
                            cy="450"
                            r="2"
                            fill="#e1f5fe"
                            opacity="0.7"
                        />
                        <circle
                            cx="50"
                            cy="370"
                            r="2.5"
                            fill="#f3e5f5"
                            opacity="0.8"
                        />
                        <!-- Star sparkles -->
                        <path
                            d="M130,310 L132,305 L134,310 L139,312 L134,314 L132,319 L130,314 L125,312Z"
                            fill="#fff9c4"
                            opacity="0.9"
                        />
                        <path
                            d="M260,360 L262,355 L264,360 L269,362 L264,364 L262,369 L260,364 L255,362Z"
                            fill="#e1f5fe"
                            opacity="0.85"
                        />
                        <path
                            d="M320,290 L322,285 L324,290 L329,292 L324,294 L322,299 L320,294 L315,292Z"
                            fill="#fff176"
                            opacity="0.8"
                        />
                    </g>

                    <!-- Bottom vignette for button readability -->
                    <rect width="390" height="844" fill="url(#vignetteGrad)" />
                </svg>
            </div>

            <!-- ══ FLOATING PARTICLES (CSS animated) ══ -->
            <div class="particles" aria-hidden="true">
                <span class="particle p1">✦</span>
                <span class="particle p2">✦</span>
                <span class="particle p3">⬡</span>
                <span class="particle p4">✦</span>
                <span class="particle p5">⬡</span>
                <span class="particle p6">✦</span>
            </div>

            <!-- ══ TOP BRANDING ══ -->
            <div class="hero-brand gsap-brand">
                <div class="brand-badge">L'aventure commence ici</div>
                <h1 class="cartoon-title">
                    <span class="ct-c">C</span><span class="ct-i">I</span
                    ><span class="ct-t">T</span><span class="ct-y">Y</span
                    ><span class="ct-p">P</span><span class="ct-l">L</span
                    ><span class="ct-a">A</span><span class="ct-y2">Y</span>
                </h1>
                <p class="hero-tagline">Explore · Découvre · Conquiers</p>
            </div>

            <!-- ══ BOTTOM CTA BUTTONS ══ -->
            <div class="hero-cta-zone gsap-cta">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="route('dashboard')"
                        class="btn-cartoon-primary"
                    >
                        <span class="btn-icon">▶</span>
                        <span>Reprendre la Quête</span>
                    </Link>
                </template>
                <template v-else>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn-cartoon-primary"
                    >
                        <span class="btn-icon">▶</span>
                        <span>COMMENCER</span>
                    </Link>
                    <Link :href="route('login')" class="btn-cartoon-secondary">
                        <span>CONNEXION</span>
                    </Link>
                </template>

                <!-- Scroll hint -->
                <a href="#concept" class="scroll-hint">
                    <span>En savoir plus</span>
                    <i class="pi pi-angle-down bounce-arrow"></i>
                </a>
            </div>
        </section>

        <!-- ══ DESKTOP HERO (>= 1024px) : Vidéo + HUD original ══ -->
        <section class="section-hero section-hero--desktop">
            <div class="video-background-container">
                <video
                    ref="introVideo"
                    autoplay
                    muted
                    playsinline
                    class="bg-video"
                    @timeupdate="handleTimeUpdate"
                >
                    <source
                        src="/videos/cityplay_oneShoot.mp4"
                        type="video/mp4"
                    />
                </video>
                <div class="video-overlay" />
            </div>

            <nav class="nav-bar">
                <div class="nav-left">
                    <img
                        :src="logoWhrite"
                        alt="CityPlay Logo"
                        class="nav-logo"
                    />
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
                        <i class="pi pi-th-large" /><span>Mon Espace</span>
                    </Link>
                    <template v-else>
                        <Link
                            v-if="canRegister"
                            :href="route('login')"
                            class="btn-register"
                        >
                            <span>Commencer</span>
                        </Link>
                    </template>
                </div>
            </nav>

            <div class="hero-main">
                <div class="hero-logo-container">
                    <img
                        :src="logoWhrite"
                        alt="CityPlay Emblem"
                        class="hero-logo"
                    />
                    <div class="glow-ring" />
                </div>
                <h2 class="hero-subtitle hero-anim">
                    L'Aventure Urbaine Phénoménale
                </h2>
                <h1 class="hero-title hero-anim">CITYPLAY</h1>
                <p class="hero-desc hero-anim">
                    Explorez le Bénin à travers une expérience interactive hors
                    du commun. Décryptez des énigmes, parcourez des lieux
                    chargés d'histoire et vivez une quête ludique ultime au cœur
                    de votre ville.
                </p>
                <div class="hero-ctas">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="cta-btn primary-cta"
                    >
                        <i class="pi pi-play" /><span>Reprendre la Quête</span>
                    </Link>
                    <template v-else>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="cta-btn primary-cta"
                        >
                            <i class="pi pi-play" /><span
                                >Lancer l'Aventure</span
                            >
                        </Link>
                        <Link
                            :href="route('login')"
                            class="cta-btn secondary-cta"
                        >
                            <i class="pi pi-users" /><span
                                >Rejoindre une Équipe</span
                            >
                        </Link>
                    </template>
                </div>
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
                <p class="section-intro">
                    Le monde réel devient votre terrain de jeu. Suivez le guide
                    technique pour débuter votre aventure.
                </p>
            </div>

            <div class="timeline-container">
                <div class="timeline-line"></div>

                <div class="timeline-steps">
                    <!-- Step 1 -->
                    <div class="timeline-step scroll-anim-item">
                        <div class="step-badge">01</div>
                        <div
                            class="step-card"
                            @mousemove="onCardMove"
                            @mouseleave="onCardLeave"
                        >
                            <div class="step-icon-wrapper orange-glow">
                                <i class="pi pi-user-plus" />
                            </div>
                            <h3>Rejoignez la Guilde</h3>
                            <p>
                                Créez votre compte joueur en quelques secondes,
                                formez votre équipe d'explorateurs ou
                                lancez-vous en loup solitaire.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="timeline-step scroll-anim-item">
                        <div class="step-badge">02</div>
                        <div
                            class="step-card"
                            @mousemove="onCardMove"
                            @mouseleave="onCardLeave"
                        >
                            <div class="step-icon-wrapper blue-glow">
                                <i class="pi pi-map-marker" />
                            </div>
                            <h3>Explorez sur la Carte</h3>
                            <p>
                                Rendez-vous dans les environnements clés réels
                                (monuments, parcs, places historiques) indiqués
                                sur la carte interactive.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="timeline-step scroll-anim-item">
                        <div class="step-badge">03</div>
                        <div
                            class="step-card"
                            @mousemove="onCardMove"
                            @mouseleave="onCardLeave"
                        >
                            <div class="step-icon-wrapper green-glow">
                                <i class="pi pi-search" />
                            </div>
                            <h3>Décryptez les Énigmes</h3>
                            <p>
                                Mettez à contribution votre logique et vos sens
                                pour déchiffrer les secrets locaux cachés dans
                                les détails urbains.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="timeline-step scroll-anim-item">
                        <div class="step-badge">04</div>
                        <div
                            class="step-card"
                            @mousemove="onCardMove"
                            @mouseleave="onCardLeave"
                        >
                            <div class="step-icon-wrapper gold-glow">
                                <i class="pi pi-trophy" />
                            </div>
                            <h3>Dominez le Bénin</h3>
                            <p>
                                Validez vos étapes de parcours, récoltez des
                                points d'XP précieux et grimpez au sommet du
                                tableau des légendes !
                            </p>
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
                <p class="section-intro">
                    Plongez dans des environnements urbains uniques façonnés par
                    l'histoire du Bénin.
                </p>
            </div>

            <div class="cites-grid">
                <!-- City 1 -->
                <div
                    class="city-card scroll-anim-item"
                    @mousemove="onCardMove"
                    @mouseleave="onCardLeave"
                >
                    <div class="city-img-wrapper">
                        <img
                            src="https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=800&q=80"
                            alt="Cotonou"
                            class="city-img"
                        />
                        <div class="city-img-overlay" />
                    </div>
                    <div class="city-info">
                        <span class="city-region">Littoral</span>
                        <h3>Cotonou</h3>
                        <p>
                            De l'Étoile Rouge aux marchés animés, parcourez les
                            ruelles dynamiques de la capitale économique du
                            Bénin.
                        </p>
                        <span class="city-stats"
                            ><i class="pi pi-map" /> 8 Parcours Actifs</span
                        >
                    </div>
                </div>

                <!-- City 2 -->
                <div
                    class="city-card scroll-anim-item"
                    @mousemove="onCardMove"
                    @mouseleave="onCardLeave"
                >
                    <div class="city-img-wrapper">
                        <img
                            src="https://images.unsplash.com/photo-1473163928189-364b2c4e1135?auto=format&fit=crop&w=800&q=80"
                            alt="Porto-Novo"
                            class="city-img"
                        />
                        <div class="city-img-overlay" />
                    </div>
                    <div class="city-info">
                        <span class="city-region">Ouémé</span>
                        <h3>Porto-Novo</h3>
                        <p>
                            Explorez les mystères de la cité aux trois noms.
                            Admirez l'architecture coloniale et le grand temple
                            vaudou.
                        </p>
                        <span class="city-stats"
                            ><i class="pi pi-map" /> 5 Parcours Actifs</span
                        >
                    </div>
                </div>

                <!-- City 3 -->
                <div
                    class="city-card scroll-anim-item"
                    @mousemove="onCardMove"
                    @mouseleave="onCardLeave"
                >
                    <div class="city-img-wrapper">
                        <img
                            src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80"
                            alt="Ouidah"
                            class="city-img"
                        />
                        <div class="city-img-overlay" />
                    </div>
                    <div class="city-info">
                        <span class="city-region">Atlantique</span>
                        <h3>Ouidah</h3>
                        <p>
                            Retracez l'histoire spirituelle et mémorielle de la
                            Route des Esclaves jusqu'au célèbre Temple des
                            Pythons.
                        </p>
                        <span class="city-stats"
                            ><i class="pi pi-map" /> 6 Parcours Actifs</span
                        >
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: Le Panthéon des Aventuriers (Dynamic Leaderboard) -->
        <section
            id="leaderboard"
            class="section-leaderboard scroll-reveal-section"
        >
            <div class="section-header scroll-anim-item">
                <span class="section-badge">Hauts Faits</span>
                <h2>Le Panthéon des Aventuriers</h2>
                <p class="section-intro">
                    Découvrez les explorateurs légendaires qui dominent le
                    classement national.
                </p>
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
                <p class="section-intro">
                    Toutes les réponses à vos questions techniques pour démarrer
                    votre aventure sereinement.
                </p>
            </div>

            <div class="faq-accordion scroll-anim-item">
                <!-- FAQ 1 -->
                <div
                    class="faq-item"
                    :class="{ open: activeFaqIndex === 0 }"
                    @click="toggleFaq(0)"
                >
                    <div class="faq-question">
                        <h3>Puis-je jouer à CityPlay en équipe ?</h3>
                        <i class="pi pi-chevron-down faq-arrow" />
                    </div>
                    <div class="faq-answer faq-answer-0">
                        <div class="answer-content">
                            Absolument ! CityPlay est entièrement optimisé pour
                            les guildes et les équipes de 2 à 5 joueurs. Un seul
                            chef d'équipe peut valider les étapes de parcours
                            sur son smartphone, ou chaque joueur peut participer
                            de son côté pour collaborer sur la résolution
                            d'énigmes.
                        </div>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div
                    class="faq-item"
                    :class="{ open: activeFaqIndex === 1 }"
                    @click="toggleFaq(1)"
                >
                    <div class="faq-question">
                        <h3>Quels sont les prérequis matériels pour jouer ?</h3>
                        <i class="pi pi-chevron-down faq-arrow" />
                    </div>
                    <div class="faq-answer faq-answer-1">
                        <div class="answer-content">
                            Vous avez uniquement besoin d'un smartphone équipé
                            d'une connexion internet (données mobiles) et d'un
                            GPS activé. Aucune application lourde à installer :
                            la plateforme fonctionne de façon ultra-fluide
                            directement dans votre navigateur web standard.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div
                    class="faq-item"
                    :class="{ open: activeFaqIndex === 2 }"
                    @click="toggleFaq(2)"
                >
                    <div class="faq-question">
                        <h3>Est-ce gratuit de participer aux parcours ?</h3>
                        <i class="pi pi-chevron-down faq-arrow" />
                    </div>
                    <div class="faq-answer faq-answer-2">
                        <div class="answer-content">
                            Oui, la création de profil, l'accès au tableau de
                            bord général et de nombreux parcours de découverte
                            sont 100% gratuits. Certains parcours d'exploration
                            avancés à vocation culturelle ou touristique premium
                            nécessitent des clés d'activation payantes.
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
                <span
                    >© {{ new Date().getFullYear() }} CITYPLAY | L'AVENTURE
                    BÉNIN</span
                >
            </div>
            <div class="footer-right">
                <span>v{{ laravelVersion }} (PHP v{{ phpVersion }})</span>
            </div>
        </footer>

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

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap");

/* Global Reset & Design Tokens */
.welcome-container {
    min-height: 100vh;
    background: #040508;
    color: #ffffff;
    font-family: "Outfit", sans-serif;
    overflow-x: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
}

/* Digital Grid Scanning Pattern */
.welcome-container::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image: radial-gradient(
        rgba(255, 149, 0, 0.02) 1px,
        transparent 1px
    );
    background-size: 20px 20px;
    pointer-events: none;
    z-index: 3;
}

/* Custom Holographic Mouse Aura */
.cursor-glow {
    position: fixed;
    top: 0;
    left: 0;
    width: 450px;
    height: 450px;
    background: radial-gradient(
        circle,
        rgba(255, 149, 0, 0.08) 0%,
        transparent 70%
    );
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
    color: #ff9500;
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
    transition:
        opacity 0.8s,
        transform 0.8s;
}

.scroll-reveal-section.is-visible .scroll-anim-item {
    opacity: 1;
    transform: translateY(0);
}

/* ── RESPONSIVE HERO SWITCHING ──
   Mobile  (< 1024px)  → cartoon scene
   Desktop (>= 1024px) → vidéo HUD original
*/
.section-hero--mobile {
    display: flex;
}
.section-hero--desktop {
    display: none;
}

@media (min-width: 1024px) {
    .section-hero--mobile {
        display: none;
    }
    .section-hero--desktop {
        display: flex;
    }
}

/* Section 1: Hero Video Landing Page styling */
.section-hero {
    min-height: 100vh;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

/* ── CARTOON SCENE ── */
.hero-scene {
    position: absolute;
    inset: 0;
    z-index: 1;
}
.scene-svg {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ── SPARKLE ANIMATION ── */
.sparkles circle,
.sparkles path {
    animation: sparkle-pulse 2.5s ease-in-out infinite alternate;
}
.sparkles circle:nth-child(2n) {
    animation-delay: 0.6s;
}
.sparkles circle:nth-child(3n) {
    animation-delay: 1.2s;
}
@keyframes sparkle-pulse {
    0% {
        opacity: 0.3;
        transform: scale(0.8);
    }
    100% {
        opacity: 1;
        transform: scale(1.3);
    }
}

/* ── FLOATING PARTICLES ── */
.particles {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: none;
}
.particle {
    position: absolute;
    font-size: 0.75rem;
    color: #fff9c4;
    opacity: 0;
    animation: float-up 6s ease-in-out infinite;
}
.p1 {
    left: 15%;
    top: 55%;
    animation-delay: 0s;
    color: #fff9c4;
}
.p2 {
    left: 75%;
    top: 60%;
    animation-delay: 1.2s;
    color: #e1f5fe;
}
.p3 {
    left: 30%;
    top: 65%;
    animation-delay: 2.4s;
    color: #f3e5f5;
    font-size: 0.6rem;
}
.p4 {
    left: 60%;
    top: 50%;
    animation-delay: 0.8s;
    color: #fff9c4;
}
.p5 {
    left: 45%;
    top: 70%;
    animation-delay: 3s;
    color: #e8f5e9;
    font-size: 0.6rem;
}
.p6 {
    left: 85%;
    top: 45%;
    animation-delay: 1.8s;
    color: #fff3e0;
}
@keyframes float-up {
    0% {
        opacity: 0;
        transform: translateY(0) scale(0.8);
    }
    20% {
        opacity: 0.9;
    }
    80% {
        opacity: 0.7;
    }
    100% {
        opacity: 0;
        transform: translateY(-80px) scale(1.2);
    }
}

/* ── TOP BRANDING ── */
.hero-brand {
    position: relative;
    z-index: 10;
    text-align: center;
    padding: 3.5rem 2rem 0;
    pointer-events: none;
}
.brand-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 0.4rem 1.1rem;
    border-radius: 99px;
    margin-bottom: 1.2rem;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}

/* ── CARTOON 3D TITLE ── */
.cartoon-title {
    font-family: "Cinzel", serif;
    font-size: clamp(3.8rem, 16vw, 6rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    display: flex;
    justify-content: center;
    gap: 0.02em;
    filter: drop-shadow(0 6px 0 rgba(0, 0, 0, 0.35));
    margin-bottom: 0.6rem;
}
.cartoon-title span {
    display: inline-block;
    -webkit-text-stroke: 2.5px rgba(0, 0, 0, 0.5);
    animation: letter-bob 3s ease-in-out infinite;
}
.ct-c {
    color: #ff6b35;
    animation-delay: 0s;
}
.ct-i {
    color: #ffd700;
    animation-delay: 0.08s;
}
.ct-t {
    color: #4caf50;
    animation-delay: 0.16s;
}
.ct-y {
    color: #29b6f6;
    animation-delay: 0.24s;
}
.ct-p {
    color: #ff6b35;
    animation-delay: 0.32s;
}
.ct-l {
    color: #ffd700;
    animation-delay: 0.4s;
}
.ct-a {
    color: #4caf50;
    animation-delay: 0.48s;
}
.ct-y2 {
    color: #29b6f6;
    animation-delay: 0.56s;
}
@keyframes letter-bob {
    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }
    25% {
        transform: translateY(-5px) rotate(-1deg);
    }
    75% {
        transform: translateY(3px) rotate(1deg);
    }
}

.hero-tagline {
    font-family: "Share Tech Mono", monospace;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.85);
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

/* ── BOTTOM CTA ZONE ── */
.hero-cta-zone {
    position: relative;
    z-index: 10;
    margin-top: auto;
    padding: 0 1.75rem 3rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.9rem;
}

/* Primary button — glossy orange-yellow */
.btn-cartoon-primary {
    width: 100%;
    max-width: 340px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.1rem 2rem;
    border-radius: 1.5rem;
    font-family: "Cinzel", serif;
    font-size: 1.1rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #ffffff;
    background: linear-gradient(180deg, #ffb300 0%, #ff6f00 100%);
    border: none;
    box-shadow:
        0 6px 0 #bf360c,
        0 10px 25px rgba(255, 111, 0, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.35);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}
.btn-cartoon-primary::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 45%;
    background: linear-gradient(
        180deg,
        rgba(255, 255, 255, 0.25) 0%,
        transparent 100%
    );
    border-radius: 1.5rem 1.5rem 0 0;
    pointer-events: none;
}
.btn-cartoon-primary:hover {
    transform: translateY(-3px);
    box-shadow:
        0 9px 0 #bf360c,
        0 15px 30px rgba(255, 111, 0, 0.55),
        inset 0 1px 0 rgba(255, 255, 255, 0.35);
}
.btn-cartoon-primary:active {
    transform: translateY(4px);
    box-shadow:
        0 2px 0 #bf360c,
        0 4px 10px rgba(255, 111, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}
.btn-icon {
    font-size: 1rem;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
}

/* Secondary button — glass white */
.btn-cartoon-secondary {
    width: 100%;
    max-width: 340px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem 2rem;
    border-radius: 1.5rem;
    font-family: "Cinzel", serif;
    font-size: 1rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #ffffff;
    background: rgba(255, 255, 255, 0.12);
    border: 2px solid rgba(255, 255, 255, 0.55);
    box-shadow:
        0 4px 0 rgba(0, 0, 0, 0.2),
        0 8px 20px rgba(0, 0, 0, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
    transition: all 0.15s ease;
}
.btn-cartoon-secondary:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: rgba(255, 255, 255, 0.8);
    transform: translateY(-2px);
}
.btn-cartoon-secondary:active {
    transform: translateY(3px);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);
}

/* Scroll hint */
.scroll-hint {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    color: rgba(255, 255, 255, 0.55);
    text-decoration: none;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-top: 0.5rem;
    transition: color 0.3s;
}
.scroll-hint:hover {
    color: #ffd700;
}

/* Desktop nav — handled by section-hero--desktop */

/* Video background (kept for desktop fallback) */
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
    opacity: 0.45;
}
.video-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(
        circle at center,
        rgba(4, 5, 8, 0.4) 0%,
        rgba(4, 5, 8, 0.98) 95%
    );
    z-index: 2;
}
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
    background: linear-gradient(
        180deg,
        rgba(4, 5, 8, 0.8) 0%,
        transparent 100%
    );
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
    border: 2px solid #ff9500;
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
    color: #ff9500;
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
    color: #ff9500;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.btn-dashboard {
    background: rgba(255, 149, 0, 0.1);
    border: 1px solid rgba(255, 149, 0, 0.3);
    color: #ff9500;
    font-weight: 700;
    padding: 0.6rem 1.25rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-dashboard:hover {
    background: #ff9500;
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
    color: #ff9500;
}

.btn-register {
    background: #ff9500;
    color: #040508;
    font-weight: 800;
    padding: 0.65rem 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 0 15px rgba(255, 149, 0, 0.3);
    transition: all 0.3s;
}

.btn-register:hover {
    background: #e08400;
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
    border: 3px solid #ff9500;
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
    0% {
        transform: scale(0.9);
        opacity: 1;
    }
    100% {
        transform: scale(1.2);
        opacity: 0;
    }
}

.hero-subtitle {
    font-size: 1.1rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #ff9500;
    letter-spacing: 0.4em;
    margin-bottom: 0.5rem;
}

.hero-title {
    font-size: 5.5rem;
    font-weight: 900;
    letter-spacing: -0.04em;
    line-height: 1;
    background: linear-gradient(
        180deg,
        #ffffff 60%,
        rgba(255, 255, 255, 0.4) 100%
    );
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.5));
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
    background: #ff9500;
    color: #040508;
    box-shadow: 0 0 25px rgba(255, 149, 0, 0.35);
}

.primary-cta:hover {
    background: #e08400;
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
    border-color: #ff9500;
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
    color: #ff9500;
}

.bounce-arrow {
    font-size: 1.25rem;
    animation: bounce 2s infinite ease-in-out;
}

@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(8px);
    }
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
    background: linear-gradient(
        180deg,
        rgba(255, 149, 0, 0.8) 0%,
        rgba(59, 130, 246, 0.8) 50%,
        rgba(16, 185, 129, 0.8) 100%
    );
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
    border: 2px solid #ff9500;
    color: #ff9500;
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
    transition:
        border-color 0.35s,
        box-shadow 0.35s;
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
    background: linear-gradient(
        135deg,
        rgba(212, 175, 55, 0.2),
        rgba(212, 175, 55, 0.05)
    );
    border: 1px solid rgba(212, 175, 55, 0.4);
    color: #d4af37;
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
    transition:
        border-color 0.35s,
        box-shadow 0.35s;
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
    background: linear-gradient(
        360deg,
        rgba(16, 18, 27, 1) 0%,
        transparent 100%
    );
}

.city-info {
    padding: 1.75rem;
}

.city-region {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #ff9500;
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
    color: #ff9500;
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
    color: #ff9500;
    background: rgba(255, 149, 0, 0.05);
}

.tab-btn.active {
    color: #040508;
    background: #ff9500;
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

.rank-1 {
    color: #ff9500;
    text-shadow: 0 0 10px rgba(255, 149, 0, 0.3);
}
.rank-2 {
    color: #b4b4b4;
}
.rank-3 {
    color: #ad7c59;
}

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
    color: #ff9500;
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
    border-color: #ff9500;
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
    color: #ff9500;
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
    color: #10b981;
}

.pulse-indicator {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 10px #10b981;
    animation: pulse-dot 1.5s infinite ease-in-out;
}

@keyframes pulse-dot {
    0%,
    100% {
        opacity: 0.4;
        transform: scale(0.9);
    }
    50% {
        opacity: 1;
        transform: scale(1.2);
    }
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
