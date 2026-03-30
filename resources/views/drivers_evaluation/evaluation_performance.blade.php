<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NIA Drivers Performance Evaluation</title>
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
                        "primary": "#003466",
                        "surface-tint": "#335f99",
                        "on-primary": "#ffffff",
                        "surface-container-highest": "#e0e3e6",
                        "surface-dim": "#d8dadd",
                        "tertiary-fixed": "#a0f2e1",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-primary-fixed": "#001c3b",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed-variant": "#005046",
                        "inverse-on-surface": "#eff1f4",
                        "primary-container": "#1a4b84",
                        "inverse-surface": "#2d3133",
                        "surface-container-low": "#f2f4f7",
                        "secondary-fixed-dim": "#a0d3a5",
                        "outline-variant": "#c3c6d1",
                        "on-secondary-container": "#3e6d47",
                        "tertiary-fixed-dim": "#84d5c5",
                        "on-tertiary": "#ffffff",
                        "primary-fixed": "#d5e3ff",
                        "tertiary": "#003c34",
                        "on-surface-variant": "#424750",
                        "on-secondary-fixed": "#00210a",
                        "error-container": "#ffdad6",
                        "tertiary-container": "#00554a",
                        "secondary": "#3a6843",
                        "on-primary-container": "#93bcfc",
                        "surface-container-high": "#e6e8eb",
                        "surface": "#f7f9fc",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1e",
                        "background": "#f7f9fc",
                        "on-error": "#ffffff",
                        "secondary-container": "#b9ecbd",
                        "secondary-fixed": "#bcefc0",
                        "on-tertiary-fixed": "#00201b",
                        "on-background": "#191c1e",
                        "surface-variant": "#e0e3e6",
                        "surface-container": "#eceef1",
                        "outline": "#737781",
                        "on-secondary-fixed-variant": "#22502d",
                        "surface-bright": "#f7f9fc",
                        "on-tertiary-container": "#78caba",
                        "inverse-primary": "#a6c8ff",
                        "on-primary-fixed-variant": "#144780",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a"
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
        .architectural-underline:focus {
            border-bottom-width: 2px;
            border-color: #003466;
            outline: none;
        }
    </style>
</head>
<body class="bg-background font-body text-on-surface selection:bg-primary-fixed selection:text-on-primary-fixed">
<!-- TopNavBar -->

@include('layouts.header');

<main class="max-w-5xl mx-auto px-6 pb-12 pt-28">
<!-- Header Section -->
<header class="mb-12 flex flex-col md:flex-row justify-between items-end gap-6">
<div class="max-w-2xl">
<div class="inline-flex items-center gap-2 mb-4">
<div class="h-1 w-12 bg-primary"></div>
<span class="text-label-md font-semibold text-secondary uppercase tracking-widest">Official Document</span>
</div>
<h1 class="text-4xl md:text-5xl font-extrabold text-primary tracking-tight font-headline mb-4 uppercase">Drivers Performance Evaluation</h1>
<p class="text-on-surface-variant text-lg leading-relaxed">Systematic performance assessment for the National Irrigation Administration institutional equipment fleet and personnel.</p>
</div>
<div class="bg-surface-container-highest p-6 rounded-xl border-l-4 border-primary">
<div class="text-label-sm font-bold text-primary-container uppercase tracking-tighter mb-1">Evaluation ID</div>
<div class="text-2xl font-mono font-bold text-on-surface tracking-widest">NIA-FLEET-2024-082</div>
</div>
</header>
<!-- Personnel & Vehicle Details Bento -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
<!-- Driver Primary Info -->
<div class="md:col-span-2 bg-surface-container-low p-8 rounded-xl flex flex-col justify-between">
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Name of Driver</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-xl font-medium" placeholder="Enter full name" type="text"/>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Date of Evaluation</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-xl font-medium" type="date"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Type / Make of Vehicle</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-lg" placeholder="e.g. Toyota Hilux" type="text"/>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Vehicle Plate No.</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-lg font-mono" placeholder="ABC-1234" type="text"/>
</div>
</div>
</div>
<!-- Trip Context -->
<div class="md:col-span-1 bg-primary text-on-primary p-8 rounded-xl flex flex-col gap-6">
<div class="space-y-1">
<label class="text-label-md font-bold opacity-80 uppercase tracking-wider block">Official Destination</label>
<input class="w-full bg-transparent border-0 border-b border-on-primary/30 py-2 focus:border-on-primary focus:ring-0 outline-none text-lg" type="text"/>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold opacity-80 uppercase tracking-wider block">Purpose of Travel</label>
<textarea class="w-full bg-transparent border-0 border-b border-on-primary/30 py-2 focus:border-on-primary focus:ring-0 outline-none text-sm resize-none" rows="2"></textarea>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold opacity-80 uppercase tracking-wider block">Duration of Travel</label>
<input class="w-full bg-transparent border-0 border-b border-on-primary/30 py-2 focus:border-on-primary focus:ring-0 outline-none text-lg" placeholder="e.g. 3 Days" type="text"/>
</div>
</div>
</section>
<!-- Evaluation Methodology Header -->
<div class="flex items-center justify-between mb-8">
<h2 class="text-2xl font-bold text-primary flex items-center gap-3">
<span class="material-symbols-outlined text-secondary">fact_check</span>
                Performance Rating Criteria
            </h2>
<div class="flex items-center gap-4 bg-surface-container-high px-4 py-2 rounded-full text-xs font-bold text-on-surface-variant">
<span>1-POOR</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span>2-FAIR</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span>3-GOOD</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span>4-VERY GOOD</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span class="text-primary">5-EXCELLENT</span>
</div>
</div>
<!-- Evaluation Form Body -->
<div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/10">
<div class="grid grid-cols-12 bg-surface-container-high p-4 text-label-md font-bold text-primary uppercase tracking-widest">
<div class="col-span-6 md:col-span-7">Particulars</div>
<div class="col-span-4 md:col-span-2 text-center">Rate (1-5)</div>
<div class="hidden md:block md:col-span-3">Remarks</div>
</div>
<!-- Row 1 -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Punctuality</div>
<p class="text-xs text-on-surface-variant mt-1">Arrival and departure adherence to schedule.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r1" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r1" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r1" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r1" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r1" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
<!-- Row 2 (Complex) -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center bg-surface-container-low/30 hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Safe Driving</div>
<p class="text-xs text-on-surface-variant mt-1 leading-relaxed">No unnecessary phone calls, obedience to traffic rules, no distractions (Radio/TV), courtesy to other motorists.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r2" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r2" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r2" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r2" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r2" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
<!-- Row 3 -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Courtesy</div>
<p class="text-xs text-on-surface-variant mt-1">Interactions with passengers and public.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r3" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r3" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r3" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r3" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r3" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
<!-- Row 4 -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center bg-surface-container-low/30 hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Personal Attitude</div>
<p class="text-xs text-on-surface-variant mt-1">Professionalism and willingness to assist.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r4" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r4" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r4" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r4" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r4" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
<!-- Row 5 -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Knowledge of direction to destination</div>
<p class="text-xs text-on-surface-variant mt-1">Navigation efficiency and route familiarity.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r5" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r5" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r5" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r5" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r5" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
<!-- Row 6 -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center bg-surface-container-low/30 hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Personal Hygiene</div>
<p class="text-xs text-on-surface-variant mt-1">Grooming and professional appearance.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r6" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r6" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r6" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r6" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r6" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
<!-- Row 7 -->
<div class="grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Trouble shooting</div>
<p class="text-xs text-on-surface-variant mt-1">Action taken during mechanical issues. (Write "N/A" if no problems encountered).</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r7" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r7" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r7" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r7" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r7" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Not applicable" type="text"/>
</div>
</div>
<!-- Row 8 -->
<div class="grid grid-cols-12 p-6 items-center bg-surface-container-low/30 hover:bg-surface-container-low transition-colors">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">Vehicle Cleanliness</div>
<p class="text-xs text-on-surface-variant mt-1">Interior and exterior upkeep during the trip.</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r8" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r8" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r8" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r8" type="radio"/>
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="r8" type="radio"/>
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" placeholder="Add details..." type="text"/>
</div>
</div>
</div>
<!-- Summary Section -->
<section class="mt-12 grid grid-cols-1 md:grid-cols-12 gap-8">
<div class="md:col-span-8 space-y-8">
<div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-error">
<label class="text-label-md font-bold text-error uppercase tracking-wider block mb-3">Comments and Observations (For Improvement)</label>
<textarea class="w-full bg-surface-container-lowest border-0 rounded-lg p-4 text-sm focus:ring-1 focus:ring-error shadow-inner" placeholder="Detailed feedback for correction or training needs..." rows="4"></textarea>
</div>
<div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-secondary">
<label class="text-label-md font-bold text-secondary uppercase tracking-wider block mb-3">Comments and Observations (For Praise and Appreciation)</label>
<textarea class="w-full bg-surface-container-lowest border-0 rounded-lg p-4 text-sm focus:ring-1 focus:ring-secondary shadow-inner" placeholder="Commendations for exemplary performance..." rows="4"></textarea>
</div>
</div>
<div class="md:col-span-4 flex flex-col gap-6">
<!-- Final Rate Card -->
<div class="bg-primary-container text-white p-8 rounded-xl flex flex-col items-center justify-center text-center shadow-lg">
<span class="text-label-sm font-bold opacity-70 uppercase tracking-widest mb-2">Final Evaluation Rate</span>
<div class="text-6xl font-black mb-4">4.8</div>
<span class="px-4 py-1 bg-tertiary-fixed text-on-tertiary-fixed font-bold rounded-full text-xs uppercase">Very Good</span>
</div>
<!-- Signature Block -->
<div class="bg-surface-container-high p-8 rounded-xl flex flex-col items-center justify-end h-full">
<div class="w-full border-b-2 border-primary mb-2"></div>
<span class="text-label-md font-bold text-primary uppercase text-center">Official Passenger / Team Leader</span>
<span class="text-xs text-on-surface-variant mt-1">Signature over Printed Name</span>
</div>
</div>
</section>
<!-- Actions -->
<div class="mt-16 flex flex-col md:flex-row justify-end gap-4 print:hidden">
<button class="px-8 py-3 rounded-lg text-primary font-bold hover:bg-surface-container-high transition-all flex items-center gap-2">
<span class="material-symbols-outlined">save</span>
                Save Draft
            </button>
<button class="px-8 py-3 bg-primary text-on-primary rounded-lg font-bold shadow-lg hover:shadow-primary/20 transition-all flex items-center gap-2">
<span class="material-symbols-outlined">print</span>
                Submit &amp; Print Evaluation
            </button>
</div>
</main>
<!-- Footer -->
@include('layouts.footer')
</body></html>