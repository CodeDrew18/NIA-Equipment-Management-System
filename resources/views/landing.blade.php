<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NIA Equipment Management System</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "outline": "#737781",
              "on-background": "#191c1e",
              "tertiary-container": "#00554a",
              "tertiary-fixed-dim": "#84d5c5",
              "on-surface": "#191c1e",
              "on-tertiary": "#ffffff",
              "tertiary": "#003c34",
              "surface-container": "#eceef1",
              "on-primary-fixed-variant": "#144780",
              "primary": "#003466",
              "on-tertiary-fixed-variant": "#005046",
              "outline-variant": "#c3c6d1",
              "on-secondary-fixed-variant": "#22502d",
              "on-error-container": "#93000a",
              "surface-container-highest": "#e0e3e6",
              "primary-container": "#1a4b84",
              "secondary": "#3a6843",
              "on-secondary-fixed": "#00210a",
              "primary-fixed": "#d5e3ff",
              "secondary-fixed-dim": "#a0d3a5",
              "secondary-fixed": "#bcefc0",
              "on-error": "#ffffff",
              "error": "#ba1a1a",
              "on-surface-variant": "#424750",
              "surface": "#f7f9fc",
              "secondary-container": "#b9ecbd",
              "on-primary-container": "#93bcfc",
              "surface-tint": "#335f99",
              "surface-variant": "#e0e3e6",
              "surface-container-low": "#f2f4f7",
              "inverse-surface": "#2d3133",
              "on-primary-fixed": "#001c3b",
              "on-tertiary-fixed": "#00201b",
              "on-tertiary-container": "#78caba",
              "primary-fixed-dim": "#a6c8ff",
              "on-secondary-container": "#3e6d47",
              "on-primary": "#ffffff",
              "surface-container-lowest": "#ffffff",
              "background": "#f7f9fc",
              "error-container": "#ffdad6",
              "inverse-primary": "#a6c8ff",
              "tertiary-fixed": "#a0f2e1",
              "on-secondary": "#ffffff",
              "surface-dim": "#d8dadd",
              "inverse-on-surface": "#eff1f4",
              "surface-container-high": "#e6e8eb",
              "surface-bright": "#f7f9fc"
            },
            fontFamily: {
              "headline": ["Public Sans", "sans-serif"],
              "body": ["Public Sans", "sans-serif"],
              "label": ["Public Sans", "sans-serif"]
            },
            borderRadius: {"DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem"},
          },
        },
      }
    </script>
<style>
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background text-on-background selection:bg-primary-fixed selection:text-on-primary-fixed">
<!-- TopAppBar -->
<header class="fixed top-0 z-50 w-full bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm">
<div class="flex justify-between items-center w-full px-8 py-4 max-w-full">
<div class="flex items-center gap-8">
<span class="text-xl font-bold text-blue-900 dark:text-white tracking-tight">EMS</span>
<nav class="hidden lg:flex items-center gap-6">
<a class="text-blue-900 dark:text-blue-300 font-semibold border-b-2 border-blue-900 dark:border-blue-300 pb-1" href="#">Dashboard</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors" href="#">Inventory</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors" href="#">Maintenance</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors" href="#">Logistics</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors" href="#">Reports</a>
</nav>
</div>
<div class="flex items-center gap-4">
<button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-slate-600 dark:text-slate-400">notifications</span>
</button>
<button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-slate-600 dark:text-slate-400">settings</span>
{{-- </button>
<button class="ml-2 bg-primary text-on-primary px-5 py-2 rounded-lg font-medium text-sm scale-95 active:scale-90 transition-transform">
                    System Login
                </button> --}}
</div>
</div>
</header>
<main class="pt-24">
<!-- Hero Section -->
<section class="relative min-h-[870px] flex items-center px-8 lg:px-24 overflow-hidden">
<div class="absolute inset-0 z-0">
<img class="w-full h-full object-cover opacity-20 grayscale brightness-75" data-alt="Heavy excavator working on a large scale irrigation canal project" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA7yZYrVsU-LsmUdn_So_BH_ptxzelJ6guZ079QLKK6RIOqIWRq6nkdgMdgGcOgZ2amjmuyOLWPl0rBHarKat0m4XZIKd00MpjneV77prssUUUylalbSINy-zY2At3iIr2vvnbIzK8tOymmWmr58-pHZySPiNxNxCN4dCZAjeGAoqXIIUAuSJ14AcHL-_a2smqr1l3yZsMvbDOmPRXaWJ4plGadG0QbLIbOn00fdAaOzRCSgzEVjubj86DkmhitiicyG6znlZ5MUkwR"/>
<div class="absolute inset-0 bg-gradient-to-tr from-background via-background/90 to-transparent"></div>
</div>
<div class="relative z-10 max-w-4xl">
<span class="inline-block px-4 py-1.5 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold tracking-widest uppercase mb-6">
                    Precision Engineering
                </span>
<h1 class="text-5xl md:text-7xl font-extrabold text-primary leading-[1.1] tracking-tight mb-8">
                    Optimizing Equipment for <span class="text-secondary italic">National Growth.</span>
</h1>
<p class="text-xl md:text-2xl text-on-surface-variant max-w-2xl font-light leading-relaxed mb-10">
                    The NIA Equipment Management System delivers enterprise-grade precision for the Philippines' irrigation infrastructure. Monitor, maintain, and mobilize with absolute certainty.
                </p>
<div class="flex flex-col sm:flex-row gap-4">
<button class="px-8 py-4 bg-primary text-on-primary rounded-xl font-semibold shadow-lg hover:shadow-primary/20 transition-all flex items-center justify-center gap-2">
                        View Fleet Status
                        <span class="material-symbols-outlined">arrow_forward</span>
</button>
<button class="px-8 py-4 bg-surface-container-lowest text-primary border border-outline-variant/30 rounded-xl font-semibold hover:bg-surface-container-low transition-all">
                        Maintenance Reports
                    </button>
</div>
</div>
<!-- Floating Data Card (Asymmetry) -->
<div class="hidden xl:block absolute right-24 top-1/2 -translate-y-1/2 w-80 p-6 bg-surface-container-lowest/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20">
<div class="flex items-center justify-between mb-6">
<span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Active Assets</span>
<span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
</div>
<div class="space-y-4">
<div class="flex items-end gap-2">
<span class="text-4xl font-extrabold text-primary tracking-tighter">1,284</span>
<span class="text-secondary font-medium text-sm mb-1">+12.4%</span>
</div>
<div class="w-full bg-surface-container-highest h-2 rounded-full overflow-hidden">
<div class="bg-secondary h-full w-[82%]"></div>
</div>
<p class="text-xs text-on-surface-variant leading-relaxed">
                        Fleet efficiency is currently operating at <span class="font-bold text-primary italic">optimal capacity</span> across all regional offices.
                    </p>
</div>
</div>
</section>
<!-- Key Features Section (Bento Grid) -->
<section class="py-24 px-8 lg:px-24 bg-surface-container-low">
<div class="max-w-7xl mx-auto">
<div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
<div>
<h2 class="text-4xl font-bold text-primary mb-4">Precision Management</h2>
<p class="text-on-surface-variant max-w-lg text-lg">Integrated tools designed for the rigorous demands of civil engineering and agricultural support.</p>
</div>
<div class="h-px bg-outline-variant/30 flex-grow mx-12 hidden md:block"></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
<!-- Feature 1 -->
<div class="md:col-span-8 bg-surface-container-lowest p-10 rounded-2xl flex flex-col md:flex-row gap-8 items-center border border-outline-variant/10">
<div class="flex-1">
<div class="w-12 h-12 rounded-xl bg-primary-fixed flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary">location_on</span>
</div>
<h3 class="text-2xl font-bold text-primary mb-3">Real-Time Fleet Tracking</h3>
<p class="text-on-surface-variant leading-relaxed">Monitor every unit's geographical position and operational status across the archipelago with millimetric precision GPS integration.</p>
</div>
<div class="flex-1 h-64 w-full bg-slate-100 rounded-xl overflow-hidden relative">
<img class="w-full h-full object-cover" data-alt="Modern satellite map interface showing equipment locations" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDQ56a3STm5YJ6s9KhmLZ3ed1tLXiXMIHlq-uaG8tyzdc2N02tgLzBBrmVESuVryypnmlQBth9B8203tjxxGyFaB7IS4MFNAvHPFL-WjmU1NEyLeIf1jvxvHvLN9xp79IfxwjszsCFmr_w4C6ICYFR0YEaCePj4Gwpf3tpjA0NUXS34WZN03nBp8xFqhk93zQ9lR7TySCZj3b05pYfM5A-0Haa7wqfpdtDbPzcV71sPeEI54xyBEFH5arNBz0oYYwc0hEDQbwHkJben"/>
<div class="absolute inset-0 bg-primary/10"></div>
</div>
</div>
<!-- Feature 2 -->
<div class="md:col-span-4 bg-tertiary-container p-10 rounded-2xl text-on-tertiary flex flex-col justify-between">
<div>
<span class="material-symbols-outlined text-4xl mb-6">construction</span>
<h3 class="text-2xl font-bold mb-3">Preventive Maintenance</h3>
</div>
<p class="text-on-tertiary/80 leading-relaxed mb-6">AI-driven scheduling that predicts mechanical failures before they happen, ensuring 99.9% uptime.</p>
<div class="flex items-center gap-2 text-tertiary-fixed font-semibold">
<span>Manage Schedules</span>
<span class="material-symbols-outlined text-sm">north_east</span>
</div>
</div>
<!-- Feature 3 -->
<div class="md:col-span-4 bg-surface-container-lowest p-10 rounded-2xl border border-outline-variant/10">
<div class="w-12 h-12 rounded-xl bg-secondary-container flex items-center justify-center mb-6 text-on-secondary-container">
<span class="material-symbols-outlined">bar_chart</span>
</div>
<h3 class="text-xl font-bold text-primary mb-3">Resource Analytics</h3>
<p class="text-on-surface-variant leading-relaxed text-sm">Comprehensive fuel consumption, operator efficiency, and project-cost mapping.</p>
</div>
<!-- Feature 4 -->
<div class="md:col-span-8 bg-surface-container-lowest p-10 rounded-2xl border border-outline-variant/10 flex flex-col justify-center">
<div class="grid grid-cols-2 md:grid-cols-4 gap-8">
<div class="text-center">
<div class="text-3xl font-bold text-primary mb-1">24/7</div>
<div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Monitoring</div>
</div>
<div class="text-center">
<div class="text-3xl font-bold text-primary mb-1">15+</div>
<div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Regions</div>
</div>
<div class="text-center">
<div class="text-3xl font-bold text-primary mb-1">0%</div>
<div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Data Loss</div>
</div>
<div class="text-center">
<div class="text-3xl font-bold text-primary mb-1">4.8k</div>
<div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Operators</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- How It Works Section (Asymmetric Editorial Style) -->
<section class="py-24 px-8 lg:px-24">
<div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-16 items-start">
<div class="lg:w-1/3 sticky top-32">
<h2 class="text-4xl font-bold text-primary mb-6 leading-tight">The Lifecycle of NIA Efficiency</h2>
<p class="text-on-surface-variant text-lg mb-8 leading-relaxed">How our integrated ecosystem ensures that every piece of machinery contributes to national agricultural self-sufficiency.</p>
<div class="p-6 bg-primary-container/10 rounded-2xl border-l-4 border-primary">
<p class="italic text-primary font-medium">"Our mission is to translate heavy metal into harvest security."</p>
</div>
</div>
<div class="lg:w-2/3 space-y-12">
<!-- Step 1 -->
<div class="group flex gap-8 items-start">
<div class="text-6xl font-black text-outline-variant/20 group-hover:text-primary transition-colors duration-500">01</div>
<div class="pt-4">
<h3 class="text-2xl font-bold text-primary mb-3">Asset Digitalization</h3>
<p class="text-on-surface-variant leading-relaxed">Every bulldozer, crane, and pump is assigned a unique digital twin in our system, capturing technical specs and maintenance history from day one.</p>
</div>
</div>
<!-- Step 2 -->
<div class="group flex gap-8 items-start">
<div class="text-6xl font-black text-outline-variant/20 group-hover:text-primary transition-colors duration-500">02</div>
<div class="pt-4">
<h3 class="text-2xl font-bold text-primary mb-3">Predictive Dispatch</h3>
<p class="text-on-surface-variant leading-relaxed">Advanced logistics algorithms match equipment capabilities with the specific terrain and technical requirements of regional irrigation projects.</p>
</div>
</div>
<!-- Step 3 -->
<div class="group flex gap-8 items-start">
<div class="text-6xl font-black text-outline-variant/20 group-hover:text-primary transition-colors duration-500">03</div>
<div class="pt-4">
<h3 class="text-2xl font-bold text-primary mb-3">Continuous Optimization</h3>
<p class="text-on-surface-variant leading-relaxed">Real-time data feeds allow our engineers to adjust operational parameters remotely, extending machine life and reducing ecological impact.</p>
</div>
</div>
</div>
</div>
</section>
<!-- CTA / Secondary Hero -->
<section class="mb-24 px-8 lg:px-24">
<div class="max-w-7xl mx-auto bg-primary rounded-[2rem] overflow-hidden relative p-12 lg:p-24 text-center">
<div class="absolute inset-0 opacity-10 pointer-events-none">
<img class="w-full h-full object-cover" data-alt="Blueprint overlay of engineering machinery" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB6RWhFJapBSLa2fF8aaD1Ljx0dK2hNbV6ruJ2Q-O-DaHF2JKyc14rIbkMtAgBNemVVgdY91r8ccv3c1WJeMxVeE39lNCFeB_XKML6QR8LL8oL7TqnnH7hxMhgZvotco7PC_1xQTLkC-gzFmmUijbxd2dKX5WKbDg_K9QNPun3shgSBwB9BKilyctj0-MsiDi5ZW-T7LIUhjE2_t1s072G16jO4LSCZl_Klfyikok3YmzhZwKPLv-Nxs1cMbnKoPNnreO5rsEYfeppJ"/>
</div>
<div class="relative z-10 max-w-2xl mx-auto">
<h2 class="text-3xl md:text-5xl font-bold text-on-primary mb-8 tracking-tight">Ready to Modernize National Irrigation?</h2>
<p class="text-primary-fixed/80 text-lg mb-12">Access the unified command center and lead the transition to high-precision agricultural infrastructure management.</p>
<button class="px-12 py-5 bg-secondary text-on-secondary rounded-xl font-bold text-lg hover:bg-secondary/90 transition-all shadow-xl hover:shadow-secondary/30">
                        Request System Access
                    </button>
</div>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-slate-100 dark:bg-slate-950 w-full mt-auto border-t border-slate-200 dark:border-slate-800">
<div class="flex flex-col md:flex-row justify-between items-center px-12 py-10 w-full gap-6">
<div class="flex flex-col items-center md:items-start gap-2">
<span class="text-lg font-bold text-blue-900 dark:text-blue-400 tracking-tighter">NIA Equipment</span>
<p class="text-slate-500 dark:text-slate-400 font-public-sans text-sm text-center md:text-left">
                    © <?php echo date("Y"); ?> National Irrigation Administration. <br><b>Developed by: Andrew B. Malubag.
                </p>
</div>
<div class="flex flex-wrap justify-center gap-8">
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Privacy Policy</a>
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Terms of Service</a>
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Contact Support</a>
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Fleet Guidelines</a>
</div>
</div>
</footer>
</body></html>