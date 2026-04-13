{{-- <!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Daily Equipment Utilization Report | NIA Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "on-surface": "#191c1e",
              "error-container": "#ffdad6",
              "on-error-container": "#93000a",
              "on-tertiary-container": "#78caba",
              "surface": "#f7f9fc",
              "surface-container-high": "#e6e8eb",
              "on-secondary-fixed-variant": "#22502d",
              "secondary-container": "#b9ecbd",
              "on-secondary": "#ffffff",
              "on-primary-container": "#93bcfc",
              "on-primary": "#ffffff",
              "tertiary-container": "#00554a",
              "surface-tint": "#335f99",
              "secondary": "#3a6843",
              "on-secondary-fixed": "#00210a",
              "tertiary-fixed": "#a0f2e1",
              "on-tertiary-fixed": "#00201b",
              "tertiary-fixed-dim": "#84d5c5",
              "surface-container": "#eceef1",
              "background": "#f7f9fc",
              "error": "#ba1a1a",
              "on-primary-fixed-variant": "#144780",
              "primary": "#003466",
              "surface-variant": "#e0e3e6",
              "surface-container-highest": "#e0e3e6",
              "inverse-primary": "#a6c8ff",
              "secondary-fixed": "#bcefc0",
              "on-background": "#191c1e",
              "inverse-on-surface": "#eff1f4",
              "surface-container-low": "#f2f4f7",
              "on-error": "#ffffff",
              "inverse-surface": "#2d3133",
              "surface-bright": "#f7f9fc",
              "primary-fixed": "#d5e3ff",
              "on-secondary-container": "#3e6d47",
              "tertiary": "#003c34",
              "outline-variant": "#c3c6d1",
              "outline": "#737781",
              "surface-container-lowest": "#ffffff",
              "secondary-fixed-dim": "#a0d3a5",
              "on-tertiary-fixed-variant": "#005046",
              "primary-fixed-dim": "#a6c8ff",
              "primary-container": "#1a4b84",
              "on-surface-variant": "#424750",
              "on-primary-fixed": "#001c3b",
              "surface-dim": "#d8dadd",
              "on-tertiary": "#ffffff"
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
      .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
      }
      .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
      }
      .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c3c6d1;
        border-radius: 10px;
      }
    </style>
</head>
<body class="bg-background font-body text-on-surface min-h-screen">
<!-- TopAppBar -->
@include('layouts.admin_header')
<!-- Main Content -->
<main class="pt-24 pb-24 w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12">
<div class="max-w-[1920px] mx-auto">
<!-- Header Section -->
<div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
<div>
<h1 class="text-[2.5rem] font-bold tracking-tight text-primary leading-tight font-headline">Daily Equipment Utilization Report</h1>
<p class="text-slate-500 mt-2 font-medium">Official Institutional Form | Division of Civil Works &amp; Irrigation</p>
</div>
<div class="flex gap-3">
<button class="flex items-center gap-2 bg-white border border-outline-variant px-5 py-2.5 rounded-lg text-primary font-semibold hover:bg-surface-container-low transition-all active:scale-95 shadow-sm">
<span class="material-symbols-outlined text-lg">print</span>
                        Print Form
                    </button>
<button class="px-6 py-2.5 bg-primary text-white font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-all">
<span class="material-symbols-outlined text-lg">save</span>
                        Submit Report
                    </button>
</div>
</div>
<!-- Bento Grid Content -->
<div class="grid grid-cols-12 gap-8">
<!-- Basic Information Card -->
<section class="col-span-12 bg-surface-container-lowest rounded-xl p-8 shadow-[0px_12px_32px_rgba(25,28,30,0.06)] flex flex-wrap gap-8">
<div class="flex-1 min-w-[200px]">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Date</label>
<input class="w-full bg-surface-container-highest border-none rounded-lg focus:ring-0 focus:border-b-2 focus:border-primary transition-all p-3 font-medium" type="date" value="<?php echo date('Y-m-d'); ?>"/>
</div>
<div class="flex-1 min-w-[250px]">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Type of Equipment</label>
<input class="w-full bg-surface-container-highest border-none rounded-lg p-3 font-medium" placeholder="e.g. Crawler Excavator" type="text"/>
</div>
<div class="flex-1 min-w-[250px]">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Model &amp; Serial No.</label>
<input class="w-full bg-surface-container-highest border-none rounded-lg p-3 font-medium" placeholder="CAT-320GC-0921" type="text"/>
</div>
<div class="flex-1 min-w-[200px]">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Property No.</label>
<input class="w-full bg-surface-container-highest border-none rounded-lg p-3 font-medium" placeholder="NIA-PW-2023-004" type="text"/>
</div>
<div class="flex-1 min-w-[250px]">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Location</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">location_on</span>
<input class="w-full bg-surface-container-highest border-none rounded-lg pl-10 p-3 font-medium" placeholder="Upper Pampanga River Project" type="text"/>
</div>
</div>
</section>
<!-- Section: To Be Filled By Operator -->
<div class="col-span-12">
<div class="flex items-center gap-4 mb-6">
<div class="h-px flex-1 bg-slate-200"></div>
<h2 class="text-sm font-bold text-secondary tracking-[0.2em] uppercase">To Be Filled By Operator</h2>
<div class="h-px flex-1 bg-slate-200"></div>
</div>
</div>
<!-- Fuel Consumption Bento -->
<section class="col-span-12 lg:col-span-4 bg-surface-container-low rounded-xl p-8 border border-white">
<div class="flex items-center gap-3 mb-6">
<div class="p-2 bg-secondary-container rounded-lg text-on-secondary-container">
<span class="material-symbols-outlined">local_gas_station</span>
</div>
<h3 class="font-bold text-primary">1. Fuel Consumption (Liters)</h3>
</div>
<div class="space-y-4">
<div class="flex justify-between items-center py-2 border-b border-slate-200">
<span class="text-sm font-medium text-slate-600">a. Balance in Tank</span>
<input id="deur-fuel-a" class="w-24 bg-transparent text-right font-bold text-primary border-none focus:ring-0 p-0" type="number" value="45.50" step="0.01"/>
</div>
<div class="flex justify-between items-center py-2 border-b border-slate-200">
<span class="text-sm font-medium text-slate-600">b. Issued from stock</span>
<input id="deur-fuel-b" class="w-24 bg-transparent text-right font-bold text-primary border-none focus:ring-0 p-0" type="number" value="120.00" step="0.01"/>
</div>
<div class="flex justify-between items-center py-2 border-b border-slate-200">
<span class="text-sm font-medium text-slate-600">c. Purchased during op.</span>
<input id="deur-fuel-c" class="w-24 bg-transparent text-right font-bold text-primary border-none focus:ring-0 p-0" type="number" value="0.00" step="0.01"/>
</div>
<div class="flex justify-between items-center pt-4">
<span class="text-xs font-bold text-error uppercase tracking-widest">d. Total Deduction</span>
<input id="deur-fuel-d" class="w-24 bg-transparent text-right font-bold text-error border-none focus:ring-0 p-0" type="number" value="135.20" step="0.01"/>
</div>
</div>
<div class="mt-8 pt-6 border-t border-slate-200">
<div class="p-4 bg-primary text-white rounded-lg flex justify-between items-center">
<span class="text-xs font-bold uppercase tracking-wider">Remaining Balance</span>
<span id="deur-fuel-remaining" class="text-xl font-bold">30.30 L</span>
</div>
</div>
</section>
<!-- Lubrication & Vitality -->
<section class="col-span-12 lg:col-span-5 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
<div class="flex items-center gap-3 mb-6">
<div class="p-2 bg-tertiary-fixed rounded-lg text-on-tertiary-fixed">
<span class="material-symbols-outlined">oil_barrel</span>
</div>
<h3 class="font-bold text-primary">3. Lubrication (Liters/Kgs)</h3>
</div>
<div class="grid grid-cols-2 gap-x-8 gap-y-4">
<div class="space-y-1">
<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Engine Oil 40-30</label>
<input class="w-full bg-surface-container-low border-none rounded p-2 text-sm font-bold" placeholder="0.0" type="number"/>
</div>
<div class="space-y-1">
<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hydraulic Oil #90</label>
<input class="w-full bg-surface-container-low border-none rounded p-2 text-sm font-bold" placeholder="0.0" type="number"/>
</div>
<div class="space-y-1">
<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gear Oil</label>
<input class="w-full bg-surface-container-low border-none rounded p-2 text-sm font-bold" placeholder="0.0" type="number"/>
</div>
<div class="space-y-1">
<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ATF</label>
<input class="w-full bg-surface-container-low border-none rounded p-2 text-sm font-bold" placeholder="0.0" type="number"/>
</div>
<div class="col-span-2 space-y-1">
<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grease (Kgs)</label>
<div class="flex gap-4">
<input class="flex-1 bg-surface-container-low border-none rounded p-2 text-sm font-bold" placeholder="0.0" type="number"/>
<div class="px-4 py-2 bg-tertiary-fixed-dim/20 text-tertiary font-bold text-xs rounded-full flex items-center">
<span class="material-symbols-outlined text-xs mr-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                    Adequate Level
                                </div>
</div>
</div>
</div>
</section>
<!-- Service Meter Readings -->
<section class="col-span-12 lg:col-span-3 bg-[#003466] text-white rounded-xl p-8 relative overflow-hidden group transition-all duration-300">
<div class="absolute -right-4 -bottom-4 opacity-10 transition-all duration-300 group-hover:opacity-35 group-hover:scale-105">
<span class="material-symbols-outlined text-[120px] transition-colors duration-300 group-hover:text-blue-200">timer</span>
</div>
<div class="relative z-10 h-full flex flex-col">
<h3 class="font-bold text-lg mb-6">4. Service Meter (Hrs)</h3>
<div class="space-y-6 flex-1">
<div>
<p class="text-[10px] font-bold text-blue-200/60 uppercase tracking-widest mb-3">Morning Session</p>
<div class="grid grid-cols-2 gap-2">
<div class="bg-white/10 p-2 rounded">
<p class="text-[9px] uppercase text-blue-100/50">Start</p>
<input id="deur-am-start" class="w-full bg-transparent border-none p-0 text-sm font-bold focus:ring-0" type="text" value="08:00"/>
</div>
<div class="bg-white/10 p-2 rounded">
<p class="text-[9px] uppercase text-blue-100/50">Stop</p>
<input id="deur-am-stop" class="w-full bg-transparent border-none p-0 text-sm font-bold focus:ring-0" type="text" value="12:00"/>
</div>
</div>
</div>
<div>
<p class="text-[10px] font-bold text-blue-200/60 uppercase tracking-widest mb-3">Afternoon Session</p>
<div class="grid grid-cols-2 gap-2">
<div class="bg-white/10 p-2 rounded">
<p class="text-[9px] uppercase text-blue-100/50">Start</p>
<input id="deur-pm-start" class="w-full bg-transparent border-none p-0 text-sm font-bold focus:ring-0" type="text" value="13:00"/>
</div>
<div class="bg-white/10 p-2 rounded">
<p class="text-[9px] uppercase text-blue-100/50">Stop</p>
<input id="deur-pm-stop" class="w-full bg-transparent border-none p-0 text-sm font-bold focus:ring-0" type="text" value="17:00"/>
</div>
</div>
</div>
</div>
<div class="mt-8 pt-6 border-t border-white/10">
<div class="flex justify-between items-end">
<div>
<p class="text-[10px] font-bold text-blue-200 uppercase tracking-widest">Total Utilization</p>
<p id="deur-service-total" class="text-3xl font-black">08.00</p>
</div>
<p class="text-sm font-medium text-blue-100/70">Hours</p>
</div>
</div>
</div>
</section>
<!-- Remarks & Work Details -->
<section class="col-span-12 lg:col-span-12 grid grid-cols-1 md:grid-cols-2 gap-8">
<div class="bg-surface-container-lowest rounded-xl p-8 shadow-sm">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Nature of Work</label>
<textarea class="w-full bg-surface-container-low border-none rounded-lg p-4 font-medium text-on-surface focus:ring-2 focus:ring-primary/10" placeholder="Describe the specific tasks completed today..." rows="4"></textarea>
</div>
<div class="bg-surface-container-lowest rounded-xl p-8 shadow-sm">
<label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Remarks / Observations</label>
<textarea class="w-full bg-surface-container-low border-none rounded-lg p-4 font-medium text-on-surface focus:ring-2 focus:ring-primary/10" placeholder="Note any mechanical issues, weather delays, or operational challenges..." rows="4"></textarea>
</div>
</section>
<!-- Signatures Section -->
<section class="col-span-12 bg-surface-container-high/30 rounded-xl p-10 mt-4 border border-slate-200">
<div class="flex flex-col md:flex-row gap-12 md:gap-8 md:justify-between">
<div class="flex flex-col items-center">
<input class="w-full max-w-[220px] bg-transparent border-0 border-b border-primary/40 text-center font-bold text-primary uppercase text-xs focus:ring-0 focus:border-primary" placeholder="Name" type="text"/>
<input class="w-full max-w-[220px] mt-1 bg-transparent border-0 text-center text-[10px] text-slate-500 uppercase tracking-widest focus:ring-0" placeholder="Prepared By (Operator)" type="text"/>
</div>
<div class="flex flex-col items-center">
<input class="w-full max-w-[220px] bg-transparent border-0 border-b border-slate-300 text-center font-bold text-slate-800 uppercase text-xs focus:ring-0 focus:border-slate-500" placeholder="Name" type="text"/>
<input class="w-full max-w-[220px] mt-1 bg-transparent border-0 text-center text-[10px] text-slate-500 uppercase tracking-widest focus:ring-0" placeholder="Requested By" type="text"/>
</div>
<div class="flex flex-col items-center">
<input class="w-full max-w-[220px] bg-transparent border-0 border-b border-slate-300 text-center font-bold text-slate-800 uppercase text-xs focus:ring-0 focus:border-slate-500" placeholder="Name" type="text"/>
<input class="w-full max-w-[220px] mt-1 bg-transparent border-0 text-center text-[10px] text-slate-500 uppercase tracking-widest focus:ring-0" placeholder="Approved By" type="text"/>
</div>
</div>
</section>
</div>
</div>
</main>
<!-- Footer -->
@include('layouts.admin_footer')
<script>
  (function () {
    function toNumber(value) {
      const parsed = Number(value);
      return Number.isFinite(parsed) ? parsed : 0;
    }

    const fuelA = document.getElementById('deur-fuel-a');
    const fuelB = document.getElementById('deur-fuel-b');
    const fuelC = document.getElementById('deur-fuel-c');
    const fuelD = document.getElementById('deur-fuel-d');
    const fuelRemaining = document.getElementById('deur-fuel-remaining');

    function updateFuelComputation() {
      if (!fuelA || !fuelB || !fuelC || !fuelD || !fuelRemaining) {
        return;
      }

      const remaining = (toNumber(fuelA.value) + toNumber(fuelB.value) + toNumber(fuelC.value)) - toNumber(fuelD.value);
      fuelRemaining.textContent = remaining.toFixed(2) + ' L';
    }

    const amStart = document.getElementById('deur-am-start');
    const amStop = document.getElementById('deur-am-stop');
    const pmStart = document.getElementById('deur-pm-start');
    const pmStop = document.getElementById('deur-pm-stop');
    const serviceTotal = document.getElementById('deur-service-total');

    function parseHours(value) {
      const text = String(value || '').trim();
      const match = text.match(/^(\d{1,2}):(\d{2})$/);
      if (!match) {
        return null;
      }

      const hours = Number(match[1]);
      const minutes = Number(match[2]);
      if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
        return null;
      }

      return hours + (minutes / 60);
    }

    function spanHours(startValue, stopValue) {
      const start = parseHours(startValue);
      const stop = parseHours(stopValue);
      if (start === null || stop === null || stop < start) {
        return 0;
      }

      return stop - start;
    }

    function updateServiceMeterComputation() {
      if (!amStart || !amStop || !pmStart || !pmStop || !serviceTotal) {
        return;
      }

      const total = spanHours(amStart.value, amStop.value) + spanHours(pmStart.value, pmStop.value);
      serviceTotal.textContent = total.toFixed(2);
    }

    [fuelA, fuelB, fuelC, fuelD].forEach(function (input) {
      if (input) {
        input.addEventListener('input', updateFuelComputation);
      }
    });

    [amStart, amStop, pmStart, pmStop].forEach(function (input) {
      if (input) {
        input.addEventListener('input', updateServiceMeterComputation);
      }
    });

    updateFuelComputation();
    updateServiceMeterComputation();
  })();
</script>
</body></html> --}}
