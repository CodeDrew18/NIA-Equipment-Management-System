<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Vehicle Availability - Institutional Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "tertiary-fixed": "#a0f2e1",
              "on-secondary-fixed": "#00210a",
              "primary": "#003466",
              "error": "#ba1a1a",
              "on-tertiary-container": "#78caba",
              "inverse-on-surface": "#eff1f4",
              "on-secondary": "#ffffff",
              "on-error-container": "#93000a",
              "surface-container-highest": "#e0e3e6",
              "error-container": "#ffdad6",
              "on-secondary-fixed-variant": "#22502d",
              "background": "#f7f9fc",
              "tertiary": "#003c34",
              "surface-variant": "#e0e3e6",
              "surface-container-low": "#f2f4f7",
              "secondary-fixed": "#bcefc0",
              "on-tertiary": "#ffffff",
              "secondary-fixed-dim": "#a0d3a5",
              "on-surface": "#191c1e",
              "on-error": "#ffffff",
              "inverse-primary": "#a6c8ff",
              "on-primary-container": "#93bcfc",
              "on-tertiary-fixed-variant": "#005046",
              "tertiary-container": "#00554a",
              "surface-dim": "#d8dadd",
              "surface-bright": "#f7f9fc",
              "on-secondary-container": "#3e6d47",
              "surface-tint": "#335f99",
              "outline-variant": "#c3c6d1",
              "on-tertiary-fixed": "#00201b",
              "outline": "#737781",
              "on-primary-fixed": "#001c3b",
              "primary-fixed": "#d5e3ff",
              "primary-fixed-dim": "#a6c8ff",
              "surface-container-high": "#e6e8eb",
              "secondary": "#3a6843",
              "primary-container": "#1a4b84",
              "on-background": "#191c1e",
              "secondary-container": "#b9ecbd",
              "tertiary-fixed-dim": "#84d5c5",
              "surface-container-lowest": "#ffffff",
              "surface-container": "#eceef1",
              "on-primary": "#ffffff",
              "surface": "#f7f9fc",
              "inverse-surface": "#2d3133",
              "on-primary-fixed-variant": "#144780",
              "on-surface-variant": "#424750"
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
        body { font-family: 'Public Sans', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-surface flex flex-col min-h-screen antialiased">
<!-- TopNavBar -->
@include('layouts.header')

<!-- Main Canvas -->
<main class="flex-grow px-8 pb-8 pt-28 md:pt-32 max-w-7xl mx-auto w-full">
<!-- Header Section -->
<section class="mb-12 flex flex-col md:flex-row justify-between md:items-end gap-6">
<div class="max-w-2xl">
<h1 class="text-4xl font-extrabold text-primary mb-2 tracking-tight">Vehicle Availability</h1>
<p class="text-on-surface-variant leading-relaxed">National Irrigation Administration: Real-time monitoring of regional logistics and institutional transport resources.</p>
</div>
<div class="flex gap-4">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10 flex flex-col min-w-[140px]">
<span class="text-label-md font-semibold text-primary/60 uppercase tracking-widest text-xs mb-1">Total Vehicles</span>
<span class="text-3xl font-bold text-primary">06 Units</span>
</div>
<div class="bg-primary p-6 rounded-xl shadow-md flex flex-col justify-center min-w-[140px]">
<span class="text-label-md font-semibold text-on-primary/60 uppercase tracking-widest text-xs mb-1">Status</span>
<span class="text-on-primary font-bold">Live Updates</span>
</div>
</div>
</section>
<!-- Bento Grid Fleet Overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<!-- Vehicle 1: Coaster (Available) -->
<div class="group relative bg-surface-container-lowest p-6 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/10">
<div class="flex justify-between items-start mb-6">
<div class="flex flex-col">
<span class="text-xs font-bold text-primary/60 uppercase tracking-widest mb-1">Coaster</span>
<h3 class="text-xl font-bold text-on-surface">NIA-CO-001</h3>
</div>
<span class="bg-tertiary-fixed text-on-tertiary-fixed px-3 py-1 rounded-full text-xs font-bold tracking-tight">Available</span>
</div>
<div class="h-40 w-full rounded-lg mb-4 overflow-hidden bg-surface-container-low group-hover:scale-[1.02] transition-transform duration-500"><img class="w-full h-full object-cover" data-alt="A clean white modern passenger coaster bus" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA50rI7vnasHspEVvg3Pnu0AZwZwzJHHwPRZaHrSoU53IbTlq4CwLkj4FWE0kAKjm1-AXX95WdJMMFkDjkNupnZpjlWaDFcx_o_0YeHqFbTqyk012-7W1jEQYN7VEyepBWVeHzYV7udhsbXhdQhmgPQeRR7e12XciZCY5WE1uV5R23V7DkHrO0zYhj0KnzKvuXokLnm0rVWDhrS8PaL-evg6euOoxRpN0X-shH7XZGrICYhgrz66cnvdXHlwCo8mDxKGafPvqQDQKmO"/></div><div class="bg-surface-container-low/50 p-3 rounded-lg mb-4 border border-outline-variant/20"><div class="flex items-center gap-3"><span class="material-symbols-outlined text-primary text-xl" data-icon="badge">badge</span><div><p class="text-[10px] font-bold text-primary/60 uppercase tracking-wider">VEHICLE DRIVER</p><p class="text-sm font-bold text-on-surface">Eduardo Santos</p><p class="text-[10px] text-on-surface-variant font-medium">ID: NIA-DRV-105</p></div></div></div>
<div class="flex justify-between items-center text-sm">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="person">person</span>
<span>29 Seater</span>
</div>
<button class="text-primary font-bold hover:underline">View Log</button>
</div>
</div>
<!-- Vehicle 2: Coaster (Maintenance) -->
<div class="group relative bg-surface-container-lowest p-6 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/10">
<div class="flex justify-between items-start mb-6">
<div class="flex flex-col">
<span class="text-xs font-bold text-primary/60 uppercase tracking-widest mb-1">Coaster</span>
<h3 class="text-xl font-bold text-on-surface">NIA-CO-002</h3>
</div>
<span class="bg-error-container text-on-error-container px-3 py-1 rounded-full text-xs font-bold tracking-tight">Maintenance</span>
</div>
<div class="h-40 w-full rounded-lg mb-4 overflow-hidden bg-surface-container-low group-hover:scale-[1.02] transition-transform duration-500">
<img class="w-full h-full object-cover grayscale opacity-80" data-alt="A large passenger bus inside a high-tech maintenance garage" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAbn9HAwAsuRd05nsz1a3H3sILtwJfVyJOFjzWW-ktCeAHrOWM2Zylr_Mm0oQFZ7U8RFBYYz2EsoFH49yAOumCswSQta80jURjxt9Oykyl5XTeDkWlO6M4OCTcaqIppJ6M91uAvv5q2Qp08HeFqznjuOwMVOrGhVy49lgFqplO_HQ_yrW6Eo60cLf1mRbr6YTCFhd0IhELaNPGgxC71Sn6GT5WA9HWlvUOUD3MaWZWSJOssB6dkD0B8uF4TTASThM_MdiSKSP65ItdS"/>
</div><div class="bg-surface-container-low/50 p-3 rounded-lg mb-4 border border-outline-variant/20"><div class="flex items-center gap-3"><span class="material-symbols-outlined text-primary text-xl" data-icon="badge">badge</span><div><p class="text-[10px] font-bold text-primary/60 uppercase tracking-wider">VEHICLE DRIVER</p><p class="text-sm font-bold text-on-surface">Maintenance Staff</p><p class="text-[10px] text-on-surface-variant font-medium">ID: NIA-MTN-012</p></div></div></div>
<div class="flex justify-between items-center text-sm">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="build">build</span>
<span>Service Hub</span>
</div>
<button class="text-primary font-bold hover:underline">Check Status</button>
</div>
</div>
<!-- Vehicle 3: Van (On Business Trip) -->
<div class="group relative bg-surface-container-lowest p-6 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/10">
<div class="flex justify-between items-start mb-6">
<div class="flex flex-col">
<span class="text-xs font-bold text-primary/60 uppercase tracking-widest mb-1">Van</span>
<h3 class="text-xl font-bold text-on-surface">NIA-VN-001</h3>
</div>
<span class="bg-primary-container text-on-primary-container px-3 py-1 rounded-full text-xs font-bold tracking-tight">On Business Trip</span>
</div>
<div class="h-40 w-full rounded-lg mb-4 overflow-hidden bg-surface-container-low group-hover:scale-[1.02] transition-transform duration-500">
<img class="w-full h-full object-cover" data-alt="A modern silver logistics van driving on a highway" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDCHcsVr72gNAfr0uggEoR3JsKrb2RxXbbw5ZPE-ArYLSkVwUk3iWjGQB94vEc1tToUreVTqvbSXy0l3F0lC-bk5JoRYApYCSICwwy_842BphgHIx9TLEkYpX9gVtTtCB6iwc7uwqRwxXMLiSMWoPjIOElVTSGxeDi7AuG9-KRsHZcyocnAjOR9yvJdYz8_JL3VmItmnwHEmsA8VJN9USf2CFsHPN10DFKndhQH2bf8fOEWVoAqVXIkdXJdhBipoYw5FZKdUfJBlwJl"/>
</div>
<!-- Driver Info Section -->
<div class="bg-surface-container-low/50 p-3 rounded-lg mb-4 border border-outline-variant/20">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-xl" data-icon="badge">badge</span>
<div>
<p class="text-[10px] font-bold text-primary/60 uppercase tracking-wider">VEHICLE DRIVER</p>
<p class="text-sm font-bold text-on-surface">Ricardo Dela Cruz</p>
<p class="text-[10px] text-on-surface-variant font-medium">ID: NIA-DRV-204</p>
</div>
</div>
</div>
<div class="flex justify-between items-center text-sm">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="person">person</span>
<span>12 Seater</span>
</div>
<button class="text-primary font-bold hover:underline">View Log</button>
</div>
</div>
<!-- Vehicle 4: Van (Available) -->
<div class="group relative bg-surface-container-lowest p-6 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/10">
<div class="flex justify-between items-start mb-6">
<div class="flex flex-col">
<span class="text-xs font-bold text-primary/60 uppercase tracking-widest mb-1">Van</span>
<h3 class="text-xl font-bold text-on-surface">NIA-VN-002</h3>
</div>
<span class="bg-tertiary-fixed text-on-tertiary-fixed px-3 py-1 rounded-full text-xs font-bold tracking-tight">Available</span>
</div>
<div class="h-40 w-full rounded-lg mb-4 overflow-hidden bg-surface-container-low group-hover:scale-[1.02] transition-transform duration-500">
<img class="w-full h-full object-cover" data-alt="A clean white shuttle van parked" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRFnIvFb7VIasjxbM6bJZkknr_QQsfD3Se8gbU-LsDuZiY3HTlbw1d2_QDBzSuWIKSBQBmfd9izOK3ZFJ3AIT3iQqfHgTklINLqqDPkmu4emqC_tckkF9HjwkIAyXJuTDVTJFRZ07ujgNWME7N_FOiMzy-aBn9aMcaxgbwMWJZB-h2it1AjtK4N1GYgfSMJ5-PjuhMg4rV5pzFNvTVB7QyVPe6_RxWaSJKFMwcCbMbbwnnQTm7qY2Tnxphq_WD93cQkdRqqeXjNIiR"/>
</div><div class="bg-surface-container-low/50 p-3 rounded-lg mb-4 border border-outline-variant/20"><div class="flex items-center gap-3"><span class="material-symbols-outlined text-primary text-xl" data-icon="badge">badge</span><div><p class="text-[10px] font-bold text-primary/60 uppercase tracking-wider">VEHICLE DRIVER</p><p class="text-sm font-bold text-on-surface">Maria Garcia</p><p class="text-[10px] text-on-surface-variant font-medium">ID: NIA-DRV-208</p></div></div></div>
<div class="flex justify-between items-center text-sm">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="person">person</span>
<span>12 Seater</span>
</div>
<button class="text-primary font-bold hover:underline">Book Now</button>
</div>
</div>
<!-- Vehicle 5: Pickup (Reserved) -->
<div class="group relative bg-surface-container-lowest p-6 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/10">
<div class="flex justify-between items-start mb-6">
<div class="flex flex-col">
<span class="text-xs font-bold text-primary/60 uppercase tracking-widest mb-1">Pickup</span>
<h3 class="text-xl font-bold text-on-surface">NIA-PU-001</h3>
</div>
<span class="bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full text-xs font-bold tracking-tight">Reserved</span>
</div>
<div class="h-40 w-full rounded-lg mb-4 overflow-hidden bg-surface-container-low group-hover:scale-[1.02] transition-transform duration-500">
<img class="w-full h-full object-cover" data-alt="A rugged 4x4 pickup truck parked near an irrigation canal" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCrLX31G3-nbGI_nSS9itGCcZUYOUIKjFaCMAeYhZYYKAs1PjlJXLgeDNTCxe6FvqYWlT8bvM36LJQfYUQ9HYQeQURwJYakQVrvBtToiBHYJ6Hg2jRX8XXVUVeDQHA6Vltna6iGoTh3CTpqRvU6--TD2DV9zyKDnNya2BWxIkuyaObQXHaKaPC_VuArMBlVtPkzqc7WhY7wYF1YpH-paQfJZPU0QGea4tioQOSb2yLdcBFtNbkBSaz0aGKDXgsLA-Ff1vlMWvo7wYNj"/>
</div>
<!-- Driver Info Section -->
<div class="bg-surface-container-low/50 p-3 rounded-lg mb-4 border border-outline-variant/20">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-xl" data-icon="badge">badge</span>
<div>
<p class="text-[10px] font-bold text-primary/60 uppercase tracking-wider">VEHICLE DRIVER</p>
<p class="text-sm font-bold text-on-surface">Juan Bautista</p>
<p class="text-[10px] text-on-surface-variant font-medium">ID: NIA-DRV-112</p>
</div>
</div>
</div>
<div class="flex justify-between items-center text-sm">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="event_upcoming">event_upcoming</span>
<span>Reserved for 14:00</span>
</div>
<button class="text-primary font-bold hover:underline">Details</button>
</div>
</div>
<!-- Vehicle 6: Pickup (Available) -->
<div class="group relative bg-surface-container-lowest p-6 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/10">
<div class="flex justify-between items-start mb-6">
<div class="flex flex-col">
<span class="text-xs font-bold text-primary/60 uppercase tracking-widest mb-1">Pickup</span>
<h3 class="text-xl font-bold text-on-surface">NIA-PU-002</h3>
</div>
<span class="bg-tertiary-fixed text-on-tertiary-fixed px-3 py-1 rounded-full text-xs font-bold tracking-tight">Available</span>
</div>
<div class="h-40 w-full rounded-lg mb-4 overflow-hidden bg-surface-container-low group-hover:scale-[1.02] transition-transform duration-500">
<img class="w-full h-full object-cover" data-alt="Side profile of a white heavy-duty pickup truck" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBf2PxIxK2sPZfM6xFWRvrZUM66AWjrgHJwnXzoNoNxsbF6tW_ZfyOUyCc39XyH1K5YhVv6IME_drJwPvQJ94SegnOiFVUhi3W89qg4RhroAn-3meFUysa2PBtJIzj4pULddpKw-mlIOctuePIB9boMCvO7UORl8UdIeiYq4bOX7R6PciqHOCE3nCDT9HY8l0nfQCwFPw9D86ddf-GvpKxs6OORtYhatOGe3Bj4BL9sVrSBtkWrHOE2h7XOl8cwibU8b2597pDNrBsV"/>
</div><div class="bg-surface-container-low/50 p-3 rounded-lg mb-4 border border-outline-variant/20"><div class="flex items-center gap-3"><span class="material-symbols-outlined text-primary text-xl" data-icon="badge">badge</span><div><p class="text-[10px] font-bold text-primary/60 uppercase tracking-wider">VEHICLE DRIVER</p><p class="text-sm font-bold text-on-surface">Ramon Reyes</p><p class="text-[10px] text-on-surface-variant font-medium">ID: NIA-DRV-302</p></div></div></div>
<div class="flex justify-between items-center text-sm">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="terrain">terrain</span>
<span>Off-road Ready</span>
</div>
<button class="text-primary font-bold hover:underline">Deploy</button>
</div>
</div>
</div>
<!-- Asymmetric Detail Section -->
</main>
@include('layouts.footer')
</body></html>