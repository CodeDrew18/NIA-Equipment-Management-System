<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Monthly Official Travel Report | NIA Equipment Portal</title>
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
                        "primary-fixed-dim": "#a6c8ff",
                        "inverse-surface": "#2d3133",
                        "inverse-on-surface": "#eff1f4",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed": "#00201b",
                        "on-error-container": "#93000a",
                        "on-secondary": "#ffffff",
                        "surface": "#f7f9fc",
                        "on-tertiary": "#ffffff",
                        "on-background": "#191c1e",
                        "surface-container-high": "#e6e8eb",
                        "on-secondary-container": "#3e6d47",
                        "inverse-primary": "#a6c8ff",
                        "error": "#ba1a1a",
                        "on-surface-variant": "#424750",
                        "tertiary-container": "#00554a",
                        "primary": "#003466",
                        "tertiary-fixed": "#a0f2e1",
                        "surface-variant": "#e0e3e6",
                        "error-container": "#ffdad6",
                        "on-primary-container": "#93bcfc",
                        "on-tertiary-container": "#78caba",
                        "secondary": "#3a6843",
                        "primary-fixed": "#d5e3ff",
                        "surface-container-highest": "#e0e3e6",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#001c3b",
                        "surface-bright": "#f7f9fc",
                        "secondary-fixed-dim": "#a0d3a5",
                        "primary-container": "#1a4b84",
                        "outline-variant": "#c3c6d1",
                        "on-primary-fixed-variant": "#144780",
                        "tertiary": "#003c34",
                        "tertiary-fixed-dim": "#84d5c5",
                        "surface-dim": "#d8dadd",
                        "on-secondary-fixed-variant": "#22502d",
                        "outline": "#737781",
                        "on-error": "#ffffff",
                        "secondary-fixed": "#bcefc0",
                        "on-surface": "#191c1e",
                        "on-secondary-fixed": "#00210a",
                        "background": "#f7f9fc",
                        "secondary-container": "#b9ecbd",
                        "surface-container": "#eceef1",
                        "on-tertiary-fixed-variant": "#005046",
                        "surface-container-low": "#f2f4f7",
                        "surface-tint": "#335f99"
                    },
                    fontFamily: {
                        "headline": ["Public Sans"],
                        "body": ["Public Sans"],
                        "label": ["Public Sans"]
                    },
                    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .print-container { padding: 0; margin: 0; width: 100%; shadow: none; }
        }
    </style>
</head>
<body class="bg-surface font-body text-on-surface antialiased min-h-screen flex flex-col">
<!-- TopNavBar -->
@include('layouts.header')
<main class="mt-24 mb-16 flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12">
<!-- Page Header -->
<div class="max-w-[1920px] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-6">
<div class="asymmetric-header">
<h1 class="text-primary font-headline text-3xl font-extrabold tracking-tight mb-2 uppercase">Monthly Official Travel Report</h1>
<p class="text-secondary font-medium tracking-wide uppercase text-xs">Official Institutional Form for Each Motor Vehicle</p>
</div>
<div class="no-print flex gap-3">
{{-- <button class="px-5 py-2.5 bg-secondary-container text-on-secondary-container font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-all">
<span class="material-symbols-outlined text-lg">download</span> Export PDF
                </button> --}}
<button class="px-5 py-2.5 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-all" onclick="window.print()">
<span class="material-symbols-outlined text-lg">print</span> Print Report
                </button>
</div>
</div>
<!-- Form Metadata Bento Grid -->
<section class="max-w-[1920px] mx-auto mb-8 grid grid-cols-1 md:grid-cols-4 gap-6 xl:gap-8">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
<label class="block font-label text-[10px] font-bold text-outline uppercase tracking-widest mb-1">Month of Report</label>
<input id="monthly-report-month" class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold" type="month" value="{{ $selectedMonth }}"/>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
<label class="block font-label text-[10px] font-bold text-outline uppercase tracking-widest mb-1">Vehicle Plate No.</label>
<input class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold" type="text" value="{{ $vehiclePlate }}"/>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
<label class="block font-label text-[10px] font-bold text-outline uppercase tracking-widest mb-1">Assigned Driver</label>
<input class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold" type="text" value="{{ $assignedDriver }}"/>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
<label class="block font-label text-[10px] font-bold text-outline uppercase tracking-widest mb-1">Property Number</label>
<input class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold" type="text" value="{{ $propertyNumber }}"/>
</div>
</section>
<!-- Main Report Table -->
<div class="max-w-[1920px] mx-auto bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-outline-variant/10">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-surface-container text-on-surface-variant font-label text-[10px] uppercase tracking-wider font-bold">
<tr>
<th class="px-4 py-4 border-r border-outline-variant/20">Date</th>
<th class="px-4 py-4 border-r border-outline-variant/20">Distance (Kms/Hrs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">Diesel (Ltrs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">Gasoline (Ltrs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">E.O (Ltrs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">G.O (Ltrs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">BF (Ltrs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">Grease (Kgs)</th>
<th class="px-4 py-4 border-r border-outline-variant/20">Purchased/Issued</th>
<th class="px-4 py-4 border-r border-outline-variant/20">Passenger</th>
<th class="px-4 py-4">Destination/Place</th>
</tr>
</thead>
<tbody class="text-[13px] font-medium text-on-surface">
@if (count($reportRows) > 0)
@foreach ($reportRows as $row)
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 {{ $loop->even ? 'bg-surface-container-low/30' : '' }}">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">{{ $row['day'] }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['distance']) ? number_format((float) $row['distance'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['diesel']) ? number_format((float) $row['diesel'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['gasoline']) ? number_format((float) $row['gasoline'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['engineOil']) ? number_format((float) $row['engineOil'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['gearOil']) ? number_format((float) $row['gearOil'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['brakeFluid']) ? number_format((float) $row['brakeFluid'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ is_numeric($row['grease']) ? number_format((float) $row['grease'], 1) : '—' }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20">{{ $row['purchasedIssued'] }}</td>
<td class="px-4 py-3 border-r border-outline-variant/20 text-xs">{{ $row['passenger'] }}</td>
<td class="px-4 py-3 text-xs italic">{{ $row['destination'] }}</td>
</tr>
@endforeach
@else
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td>
<td class="px-4 py-3 text-xs italic">No trips found for your name in driver assignments this month.</td>
</tr>
@endif
{{-- Legacy sample rows retained below to preserve template structure. --}}
@if (false)
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">01</td>
<td class="px-4 py-3 border-r border-outline-variant/20">124.5</td>
<td class="px-4 py-3 border-r border-outline-variant/20">15.0</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">0.5</td>
<td class="px-4 py-3 border-r border-outline-variant/20">Issued</td>
<td class="px-4 py-3 border-r border-outline-variant/20 text-xs">Engr. Santos</td>
<td class="px-4 py-3 text-xs italic">Urdaneta Main Canal Sec. A</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">02</td>
<td class="px-4 py-3 border-r border-outline-variant/20">45.0</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20 text-xs">Admin Team</td>
<td class="px-4 py-3 text-xs italic">Regional Office - Dagupan</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">03</td>
<td class="px-4 py-3 border-r border-outline-variant/20">210.2</td>
<td class="px-4 py-3 border-r border-outline-variant/20">25.5</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">1.0</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">Purchased</td>
<td class="px-4 py-3 border-r border-outline-variant/20 text-xs">Division Mgr.</td>
<td class="px-4 py-3 text-xs italic">San Roque Dam Inspection</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">04</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">05</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">06</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">07</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">08</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">09</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">10</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">11</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">12</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">13</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">14</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">15</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">16</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">17</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">18</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">19</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">20</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">21</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">22</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">23</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">24</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">25</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">26</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">27</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">28</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">29</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10 bg-surface-container-low/30">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">30</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20">—</td><td class="px-4 py-3 border-r border-outline-variant/20 text-xs">—</td><td class="px-4 py-3 text-xs italic">—</td>
</tr>
<tr class="hover:bg-primary/5 transition-colors border-b border-outline-variant/10">
<td class="px-4 py-3 border-r border-outline-variant/20 font-bold text-primary">31</td>
<td class="px-4 py-3 border-r border-outline-variant/20">15.0</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20">—</td>
<td class="px-4 py-3 border-r border-outline-variant/20 text-xs">Site Foreman</td>
<td class="px-4 py-3 text-xs italic">Equipment Depot Maintenance</td>
</tr>
@endif
</tbody>
<tfoot class="bg-primary-container text-white font-bold">
<tr>
<td class="px-4 py-4 uppercase text-[10px] tracking-widest border-r border-white/10">Total</td>
<td class="px-4 py-4 border-r border-white/10">{{ number_format($totalDistance, 1) }}</td>
<td class="px-4 py-4 border-r border-white/10">0.0</td>
<td class="px-4 py-4 border-r border-white/10">0.0</td>
<td class="px-4 py-4 border-r border-white/10">0.0</td>
<td class="px-4 py-4 border-r border-white/10">0.0</td>
<td class="px-4 py-4 border-r border-white/10">0.0</td>
<td class="px-4 py-4 border-r border-white/10">0.0</td>
<td class="px-4 py-4" colspan="3">Consolidated Equipment Metrics</td>
</tr>
</tfoot>
</table>
</div>
</div>
<!-- Verification & Signatures -->
<section class="max-w-[1920px] mx-auto mt-12 mb-12">
<div class="bg-surface-container-low p-8 rounded-xl border border-primary/10">
<p class="text-sm font-medium text-on-surface mb-10 italic border-l-4 border-primary pl-4">
                    "I hereby certify to the correctness of the above statement and that motor vehicle was used strictly official business only."
                </p>
<div class="grid grid-cols-1 md:grid-cols-3 gap-12 mt-12">
<!-- Approved By -->
<div class="flex flex-col items-center">
<div class="w-full border-b border-on-surface mb-2 h-12 flex items-end justify-center">
<span class=" text-primary w-full mb-2 h-12 flex items-end justify-center font-bold uppercase">{{ $divisionManagerName }}</span>
</div>
<span class="text-[10px] font-bold uppercase tracking-tighter text-outline">Approved By (Division Manager, EOD)</span>
</div>
<!-- Driver Name -->
<div class="flex flex-col items-center">
<div class="w-full border-b border-on-surface mb-2 h-12 flex items-end justify-center font-bold uppercase">
                            {{ $primaryDriver }}
                        </div>
<span class="text-[10px] font-bold uppercase tracking-tighter text-outline">Name of the Driver</span>
</div>
<!-- Driver Signature -->
<div class="flex flex-col items-center">
<div class="w-full border-b border-on-surface mb-2 h-12 flex items-end justify-center">
<!-- Digital Signature Placeholder -->
</div>
<span class="text-[10px] font-bold uppercase tracking-tighter text-outline">Signature of the Driver</span>
</div>
</div>
<div class="mt-16 pt-8 border-t border-outline-variant/30 text-[11px] leading-relaxed text-on-surface-variant flex flex-col md:flex-row gap-8 items-start">
<div class="bg-secondary/5 p-4 rounded-lg border-l-2 border-secondary flex-shrink-0">
<span class="font-bold text-secondary uppercase tracking-widest block mb-2 text-[10px]">Note &amp; Instructions</span>
<p class="max-w-3xl">
                            This report should be accomplished in trip-ticket the ORIGINAL copy of which supported by the originals duly accomplished 
                            DRIVERS RECORD OF TRAVELS should be submitted thru the Administrative Officer or his equivalent to AUDITOR concerned. 
                        </p>
</div>
<div class="flex flex-col justify-end h-full">
<span class="font-bold text-primary">Equipment Management Section</span>
<span>Urdaneta City, Pangasinan</span>
</div>
</div>
</div>
</section>
</main>
<!-- Institutional Footer -->
@include('layouts.footer')
<!-- FAB (Suppressed as per rules for detailed report page, but adding subtle scroll indicator if needed) -->
<script>
    (function () {
        const monthInput = document.getElementById('monthly-report-month');

        if (!monthInput) {
            return;
        }

        monthInput.addEventListener('change', function () {
            const monthValue = String(monthInput.value || '').trim();
            const nextUrl = new URL(window.location.href);

            if (monthValue !== '') {
                nextUrl.searchParams.set('month', monthValue);
            } else {
                nextUrl.searchParams.delete('month');
            }

            window.location.assign(nextUrl.toString());
        });
    })();
</script>
</body></html>