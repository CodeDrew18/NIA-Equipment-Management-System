<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>404 - Page Not Found | NIA Equipment Management</title>
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
              "on-surface-variant": "#424750",
              "on-primary": "#ffffff",
              "outline": "#737781",
              "tertiary-fixed-dim": "#84d5c5",
              "background": "#f7f9fc",
              "primary": "#003466",
              "surface-variant": "#e0e3e6",
              "inverse-on-surface": "#eff1f4",
              "on-tertiary-fixed-variant": "#005046",
              "on-tertiary": "#ffffff",
              "on-error": "#ffffff",
              "primary-fixed": "#d5e3ff",
              "tertiary": "#003c34",
              "on-tertiary-fixed": "#00201b",
              "on-error-container": "#93000a",
              "surface-tint": "#335f99",
              "on-primary-fixed-variant": "#144780",
              "error": "#ba1a1a",
              "inverse-surface": "#2d3133",
              "surface-container-high": "#e6e8eb",
              "on-secondary-container": "#3e6d47",
              "on-secondary-fixed": "#00210a",
              "surface": "#f7f9fc",
              "on-secondary": "#ffffff",
              "on-primary-container": "#93bcfc",
              "on-primary-fixed": "#001c3b",
              "error-container": "#ffdad6",
              "on-surface": "#191c1e",
              "surface-container": "#eceef1",
              "on-background": "#191c1e",
              "tertiary-container": "#00554a",
              "primary-fixed-dim": "#a6c8ff",
              "surface-container-highest": "#e0e3e6",
              "secondary-fixed": "#bcefc0",
              "secondary": "#3a6843",
              "surface-dim": "#d8dadd",
              "on-secondary-fixed-variant": "#22502d",
              "surface-container-low": "#f2f4f7",
              "primary-container": "#1a4b84",
              "inverse-primary": "#a6c8ff",
              "outline-variant": "#c3c6d1",
              "tertiary-fixed": "#a0f2e1",
              "secondary-fixed-dim": "#a0d3a5",
              "surface-container-lowest": "#ffffff",
              "secondary-container": "#b9ecbd",
              "surface-bright": "#f7f9fc",
              "on-tertiary-container": "#78caba"
            },
            fontFamily: {
              "headline": ["Public Sans"],
              "body": ["Public Sans"],
              "label": ["Public Sans"]
            },
            borderRadius: {"DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem"},
          },
        },
      }
    </script>
<style>
      .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      }
      body {
        font-family: 'Public Sans', sans-serif;
      }
      .blueprint-grid {
        background-image: radial-gradient(circle, #c3c6d1 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: 0.1;
      }
    </style>
</head>
<body class="bg-background text-on-background flex flex-col min-h-screen">
<!-- TopNavBar -->
@include('layouts.header')
<!-- Main Content Canvas -->
<main class="flex-grow flex items-center justify-center pt-24 px-6 relative overflow-hidden">
<!-- Background Architectural Motif -->
<div class="absolute inset-0 blueprint-grid pointer-events-none"></div>
<div class="absolute -right-24 bottom-0 opacity-10 pointer-events-none">
<span class="material-symbols-outlined text-[40rem] text-primary" data-icon="airport_shuttle">airport_shuttle</span>
</div>
<div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-12 gap-12 items-center relative z-10">
<!-- Asymmetric Text Column -->
<div class="md:col-span-7 flex flex-col items-start text-left">
<span class="font-label text-sm font-bold uppercase tracking-[0.3em] text-secondary mb-4 bg-secondary-container px-3 py-1 rounded">
                    Error Code: 404
                </span>
<h1 class="font-headline text-[8rem] md:text-[10rem] font-extrabold leading-none text-primary tracking-tighter mb-4">
                    404
                </h1>
<h2 class="font-headline text-3xl font-bold text-on-surface mb-6 tracking-tight">
                    Page Not Found: The requested portal link could not be located.
                </h2>
<p class="font-body text-lg text-on-surface-variant mb-10 max-w-lg leading-relaxed">
                    The equipment asset or administrative module you are looking for may have been decommissioned, relocated, or the URL address was entered incorrectly into the system console.
                </p>
{{-- <div class="flex flex-wrap gap-4">
<a class="inline-flex items-center px-8 py-4 bg-primary text-on-primary font-semibold rounded shadow-sm hover:brightness-110 active:scale-95 transition-all" href="{{ route('admin.dashboard') }}">
<span class="material-symbols-outlined mr-2" data-icon="dashboard">dashboard</span>
                        Return to Dashboard
                    </a>
<a class="inline-flex items-center px-8 py-4 bg-secondary-container text-on-secondary-container font-semibold rounded hover:bg-secondary-fixed transition-all active:scale-95" href="#">
<span class="material-symbols-outlined mr-2" data-icon="support_agent">support_agent</span>
                        Contact System Support
                    </a>
</div> --}}
</div>
<!-- Visual Column (Bento/Card Style) -->
<div class="md:col-span-5 relative">
<div class="bg-surface-container-lowest p-8 rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)] border border-outline-variant/15 flex flex-col items-center justify-center text-center gap-6">
<div class="w-32 h-32 rounded-full bg-surface-container-low flex items-center justify-center border-4 border-dashed border-outline-variant/30">
<span class="material-symbols-outlined text-6xl text-outline-variant" data-icon="construction">construction</span>
</div>
<div class="space-y-2">
<p class="font-label text-xs font-bold text-tertiary uppercase tracking-widest">Institutional Asset Tracking</p>
<div class="h-1 w-12 bg-primary mx-auto"></div>
</div>
<p class="text-sm text-on-surface-variant italic">
                        "Reliable irrigation service through professional equipment lifecycle management."
                    </p>
</div>
<!-- Floating Decorative Element -->
<div class="absolute -top-6 -right-6 w-24 h-24 bg-tertiary-fixed rounded-full opacity-40 blur-2xl"></div>
</div>
</div>
</main>
<!-- Footer -->
@include('layouts.footer')
</body></html>