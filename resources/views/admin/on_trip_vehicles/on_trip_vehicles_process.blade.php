<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>On Trip Vehicles | Equipment Management System</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
        }
        body { font-family: 'Public Sans', sans-serif; }
    </style>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-tertiary-container": "#78caba",
                    "on-secondary-fixed-variant": "#22502d",
                    "secondary-container": "#b9ecbd",
                    "on-tertiary": "#ffffff",
                    "surface-variant": "#e0e3e6",
                    "on-surface-variant": "#424750",
                    "surface-container": "#eceef1",
                    "outline-variant": "#c3c6d1",
                    "on-tertiary-fixed-variant": "#005046",
                    "primary-fixed": "#d5e3ff",
                    "outline": "#737781",
                    "tertiary-container": "#00554a",
                    "on-secondary": "#ffffff",
                    "on-primary-fixed-variant": "#144780",
                    "background": "#f7f9fc",
                    "surface-container-lowest": "#ffffff",
                    "surface": "#f7f9fc",
                    "secondary-fixed-dim": "#a0d3a5",
                    "on-tertiary-fixed": "#00201b",
                    "on-error": "#ffffff",
                    "inverse-primary": "#a6c8ff",
                    "primary-fixed-dim": "#a6c8ff",
                    "on-secondary-fixed": "#00210a",
                    "secondary": "#3a6843",
                    "inverse-surface": "#2d3133",
                    "surface-dim": "#d8dadd",
                    "on-error-container": "#93000a",
                    "error-container": "#ffdad6",
                    "primary-container": "#1a4b84",
                    "surface-container-high": "#e6e8eb",
                    "tertiary-fixed": "#a0f2e1",
                    "error": "#ba1a1a",
                    "tertiary": "#003c34",
                    "surface-tint": "#335f99",
                    "surface-bright": "#f7f9fc",
                    "on-primary": "#ffffff",
                    "surface-container-highest": "#e0e3e6",
                    "on-background": "#191c1e",
                    "inverse-on-surface": "#eff1f4",
                    "secondary-fixed": "#bcefc0",
                    "on-surface": "#191c1e",
                    "surface-container-low": "#f2f4f7",
                    "on-primary-container": "#93bcfc",
                    "primary": "#003466",
                    "on-primary-fixed": "#001c3b",
                    "tertiary-fixed-dim": "#84d5c5",
                    "on-secondary-container": "#3e6d47"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            }
          }
        }
      }
    </script>
</head>
<body class="bg-background text-on-background antialiased font-body">
<!-- TopNavBar -->
@include('layouts.admin_header')
<div class="flex pt-[72px]">
<!-- Main Content -->
<main class="w-full p-8 bg-background min-h-screen">
<div class="max-w-[1440px] mx-auto">
<header class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
<div>
<h1 class="text-3xl font-black text-primary tracking-tight mb-2">Active Trips</h1>
<p class="text-slate-500 max-w-2xl font-medium">Real-time monitoring of all vehicles currently dispatched and on active duty, providing live tracking and status updates.</p>
</div>
@if ($search !== '' || $fromDate !== '' || $toDate !== '')
<a href="{{ route('admin.on_trip_vehicles') }}" class="inline-flex items-center gap-2 rounded-lg bg-surface-container-high px-4 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-sm">filter_list_off</span>
Clear Filters
</a>
@endif
</header>

<!-- Search & Filters Bento Grid -->
<form method="GET" action="{{ route('admin.on_trip_vehicles') }}" class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
<div class="md:col-span-6 bg-surface-container-low p-6 rounded-xl flex flex-col justify-center">
<label class="text-xs font-bold uppercase tracking-wider text-primary mb-3">Quick Search</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
<input name="search" value="{{ $search }}" class="w-full bg-surface-container-lowest border-b-2 border-transparent focus:border-primary focus:ring-0 transition-all pl-10 py-3 rounded-lg text-sm" placeholder="Search by Request ID, Name, or Plate Number..." type="text"/>
</div>
</div>
<div class="md:col-span-4 bg-surface-container-low p-6 rounded-xl flex flex-col justify-center">
<label class="text-xs font-bold uppercase tracking-wider text-primary mb-3">Date Range Filter</label>
<div class="flex items-center gap-3">
<input name="from" value="{{ $fromDate }}" class="bg-surface-container-lowest border-none rounded-lg text-sm px-3 py-2 w-full focus:ring-2 focus:ring-primary" type="date"/>
<span class="text-slate-400">to</span>
<input name="to" value="{{ $toDate }}" class="bg-surface-container-lowest border-none rounded-lg text-sm px-3 py-2 w-full focus:ring-2 focus:ring-primary" type="date"/>
</div>
</div>
<button type="submit" class="md:col-span-2 bg-primary-container rounded-xl text-white flex flex-col items-center justify-center text-center hover:bg-primary transition-colors shadow-lg p-4">
<span class="material-symbols-outlined text-2xl mb-1" data-icon="filter_list">filter_list</span>
<span class="font-bold text-xs uppercase tracking-wider">Filter</span>
</button>
</form>

<!-- Stats/Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
<div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-outline-variant/10">
<div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total On Trip</div>
<div class="text-3xl font-black text-primary -tracking-tighter">{{ number_format($totalOnTrip) }}</div>
</div>
<div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-outline-variant/10">
<div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Vehicles Deployed</div>
<div class="text-3xl font-black text-secondary -tracking-tighter">{{ number_format($vehiclesDeployed) }}</div>
</div>
<div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-outline-variant/10">
<div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Drivers Assigned</div>
<div class="text-3xl font-black text-tertiary -tracking-tighter">{{ number_format($driversAssigned) }}</div>
</div>
<div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-outline-variant/10">
<div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Deployment Status</div>
<div class="text-xs font-bold text-secondary bg-secondary-container/30 px-2 py-1 rounded mt-2 inline-block">ACTIVE TRIPS</div>
</div>
</div>

<!-- Main Data Table Container -->
<div class="bg-surface-container-lowest rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)] overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Document Type</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Control / ID</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Vehicle ID</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Driver / Personnel</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Date Printed</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Date Time To</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-primary">Status</th>
<th class="px-6 py-4 text-right"></th>
</tr>
</thead>
<tbody class="divide-y divide-slate-50">
@forelse ($onTripRequests as $item)
<tr class="{{ $loop->even ? 'bg-surface-container-low/30' : '' }} hover:bg-slate-50/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="p-2 bg-primary-container/10 text-primary rounded">
<span class="material-symbols-outlined text-lg" data-icon="local_shipping">local_shipping</span>
</div>
<span class="text-sm font-semibold">On Trip Dispatch</span>
</div>
</td>
<td class="px-6 py-4 font-mono text-xs font-bold text-slate-600">{{ $item->form_id ?: 'N/A' }}</td>
<td class="px-6 py-4 text-sm">{{ $item->vehicle_id ?: 'N/A' }}</td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
@php
  $driverName = trim((string) ($item->driver_name ?? ''));
  $driverNameParts = collect(preg_split('/\s+/', $driverName, -1, PREG_SPLIT_NO_EMPTY) ?: []);
  $driverInitials = $driverNameParts->isEmpty()
    ? '--'
    : strtoupper(substr((string) $driverNameParts->first(), 0, 1) . substr((string) $driverNameParts->last(), 0, 1));
@endphp
<div class="w-6 h-6 rounded-full bg-primary-fixed-dim text-on-primary-fixed text-[10px] font-bold flex items-center justify-center">{{ $driverInitials }}</div>
<span class="text-sm">{{ $item->driver_name ?: 'N/A' }}</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500">{{ optional($item->request_date)->format('M d, Y') ?? 'N/A' }}</td>
<td class="px-6 py-4 text-sm text-slate-500">{{ optional($item->date_time_to)->format('M d, Y h:i A') ?? 'N/A' }}</td>
<td class="px-6 py-4">
<span class="bg-primary-fixed text-on-primary-fixed-variant px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter">On Trip</span>
</td>
<td class="px-6 py-4 text-right">
<a href="{{ route('admin.fuel_issuance_slip', ['request_id' => $item->id]) }}" class="text-primary hover:bg-primary-container hover:text-white px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-2">
<span class="material-symbols-outlined text-sm" data-icon="visibility">visibility</span>
View Copy
</a>
</td>
</tr>
@empty
<tr>
<td colspan="8" class="px-6 py-8 text-center text-sm font-semibold text-outline">No on-trip vehicle records found.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="px-6 py-4 bg-surface-container-low/50 flex items-center justify-between">
<span class="text-xs text-slate-500 font-medium">Showing {{ $onTripRequests->firstItem() ?? 0 }} to {{ $onTripRequests->lastItem() ?? 0 }} of {{ $onTripRequests->total() }} entries</span>
<div class="flex gap-2">
@if ($onTripRequests->onFirstPage())
<span class="p-2 rounded text-slate-400">
<span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
</span>
@else
<a href="{{ $onTripRequests->previousPageUrl() }}" class="p-2 rounded hover:bg-white text-slate-400">
<span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
</a>
@endif

@foreach ($onTripRequests->getUrlRange(1, $onTripRequests->lastPage()) as $page => $url)
@if ($page == $onTripRequests->currentPage())
<span class="w-8 h-8 rounded bg-primary text-white text-xs font-bold inline-flex items-center justify-center">{{ $page }}</span>
@else
<a href="{{ $url }}" class="w-8 h-8 rounded hover:bg-white text-xs font-medium text-slate-600 inline-flex items-center justify-center">{{ $page }}</a>
@endif
@endforeach

@if ($onTripRequests->hasMorePages())
<a href="{{ $onTripRequests->nextPageUrl() }}" class="p-2 rounded hover:bg-white text-slate-400">
<span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
</a>
@else
<span class="p-2 rounded text-slate-400">
<span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
</span>
@endif
</div>
</div>
</div>
<!-- Quick View / Detail Panel -->
<div class="mt-12 flex flex-col md:flex-row gap-8 mb-8">
<div class="flex-1 bg-surface-container p-8 rounded-2xl relative overflow-hidden group">
<div class="relative z-10">
<h3 class="text-xl font-bold text-primary mb-4">Trip Integrity Verification</h3>
<p class="text-sm text-slate-600 mb-6 leading-relaxed">All on-trip records are linked to approved transportation requests and fuel issuance records for complete traceability.</p>
<div class="flex gap-4">
<div class="flex items-center gap-2 text-secondary font-bold text-xs uppercase tracking-widest">
<span class="material-symbols-outlined text-base" data-icon="verified_user">verified_user</span>
Dispatch Trace Linked
</div>
<div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest">
<span class="material-symbols-outlined text-base" data-icon="route">route</span>
Live Trip Monitoring
</div>
</div>
</div>
<div class="absolute -right-12 -bottom-12 opacity-5 pointer-events-none group-hover:scale-110 transition-transform duration-700">
<span class="material-symbols-outlined text-[200px]" data-icon="directions_car">directions_car</span>
</div>
</div>
<div class="w-full md:w-1/3 bg-tertiary text-white p-8 rounded-2xl">
<h3 class="text-lg font-bold mb-4">Deployment Snapshot</h3>
<p class="text-xs text-tertiary-fixed-dim mb-6">Current list reflects all vehicles marked as on-trip from Fuel Issuance dispatch actions.</p>
<div class="w-full bg-secondary text-white py-3 rounded-lg font-bold text-xs uppercase tracking-widest text-center">Operational Feed</div>
</div>
</div>
</div>
</main>
</div>
@include('layouts.admin_footer')
</body></html>