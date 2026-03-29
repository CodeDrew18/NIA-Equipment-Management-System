<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NIA | Transportation Requests Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-container": "#b9ecbd",
                        "inverse-on-surface": "#eff1f4",
                        "on-tertiary-container": "#78caba",
                        "on-primary-container": "#93bcfc",
                        "outline-variant": "#c3c6d1",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed-dim": "#a0d3a5",
                        "on-secondary-fixed-variant": "#22502d",
                        "inverse-surface": "#2d3133",
                        "on-error-container": "#93000a",
                        "tertiary-container": "#00554a",
                        "on-background": "#191c1e",
                        "surface": "#f7f9fc",
                        "inverse-primary": "#a6c8ff",
                        "tertiary-fixed-dim": "#84d5c5",
                        "surface-variant": "#e0e3e6",
                        "on-surface-variant": "#424750",
                        "error-container": "#ffdad6",
                        "on-tertiary": "#ffffff",
                        "outline": "#737781",
                        "on-secondary": "#ffffff",
                        "on-primary-fixed-variant": "#144780",
                        "surface-container-low": "#f2f4f7",
                        "surface-container-high": "#e6e8eb",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#001c3b",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-tertiary-fixed-variant": "#005046",
                        "secondary-fixed": "#bcefc0",
                        "surface-tint": "#335f99",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "on-secondary-fixed": "#00210a",
                        "surface-container-highest": "#e0e3e6",
                        "surface-container": "#eceef1",
                        "primary-container": "#1a4b84",
                        "primary-fixed": "#d5e3ff",
                        "background": "#f7f9fc",
                        "secondary": "#3a6843",
                        "on-surface": "#191c1e",
                        "tertiary": "#003c34",
                        "surface-dim": "#d8dadd",
                        "primary": "#003466",
                        "on-tertiary-fixed": "#00201b",
                        "tertiary-fixed": "#a0f2e1",
                        "surface-bright": "#f7f9fc",
                        "on-secondary-container": "#3e6d47"
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
            vertical-align: middle;
        }
        body { font-family: 'Public Sans', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col">
@include('layouts.admin_header')
<main class="flex-grow max-w-screen-2xl w-full mx-auto px-8 py-10 pt-24">
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-12 gap-8">
<div>
<span class="font-label text-xs uppercase tracking-widest font-semibold text-secondary mb-2 block">Central Management System</span>
<h1 class="font-headline text-4xl font-extrabold text-primary tracking-tight">Pending Transportation Requests</h1>
<p class="text-on-surface-variant mt-2 max-w-xl">Review and authorize institutional vehicle deployments for regional operations and technical field assessments.</p>
</div>
<div class="flex gap-4">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)] border border-outline-variant/15">
<span class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Queue Status</span>
<div class="flex items-baseline gap-2">
<span class="text-3xl font-extrabold text-primary">{{ $requests->total() }}</span>
<span class="text-sm font-medium text-secondary">Awaiting Approval</span>
</div>
</div>
<div class="bg-primary p-6 rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)]">
<span class="block text-xs font-bold text-on-primary-container uppercase mb-1">Pending Signature</span>
<div class="flex items-baseline gap-2 text-on-primary">
<span class="text-sm opacity-80 ">To be</span>
<span class=" text-2xl font-extrabold ">Signed</span>
</div>
</div>
</div>
</div>

@if (session('admin_transportation_request_success'))
<div class="mb-6 rounded-xl border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container text-sm font-semibold">
{{ session('admin_transportation_request_success') }}
</div>
@endif

<div class="bg-surface-container-low rounded-xl p-4 mb-6 flex flex-wrap items-end justify-between gap-4 border border-outline-variant/10">
<form method="GET" action="{{ route('admin.transportation-request') }}" class="flex flex-wrap items-end gap-4 w-full">
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase mb-1 px-1">Search Request</label>
<input name="search" value="{{ $search }}" class="bg-surface-container-lowest border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 min-w-[260px]" placeholder="Request ID, requestor, destination"/>
</div>
<div class="flex items-center gap-2 self-end">
<button class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2 rounded-lg text-sm font-semibold shadow-md transition-all flex items-center gap-2" type="submit">
<span class="material-symbols-outlined text-sm">search</span> Search
</button>
<a href="{{ route('admin.transportation-request') }}" class="bg-surface-container-highest hover:bg-surface-variant text-on-surface-variant px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2">
<span class="material-symbols-outlined text-sm">filter_list</span> Reset
</a>
</div>
</form>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)] overflow-hidden border border-outline-variant/10">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-high/50 text-on-surface-variant text-xs font-bold uppercase tracking-widest border-b border-outline-variant/10">
<th class="px-6 py-4">Request ID</th>
<th class="px-6 py-4">Requestor Name</th>
<th class="px-6 py-4">Request Date</th>
<th class="px-6 py-4">Destination</th>
<th class="px-6 py-4">Vehicle Type</th>
<th class="px-6 py-4 text-right">Action</th>
</tr>
</thead>
<tbody class="text-sm font-body divide-y divide-outline-variant/5">
@forelse ($requests as $item)
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="px-6 py-5 font-bold text-primary">{{ $item->form_id }}</td>
<td class="px-6 py-5">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-primary-fixed-dim flex items-center justify-center text-on-primary-fixed font-bold text-xs uppercase">
{{ strtoupper(substr($item->requestor_name, 0, 2)) }}
</div>
<div>
<p class="font-semibold text-on-surface">{{ $item->requestor_name }}</p>
<p class="text-xs text-outline">{{ $item->requested_by }}</p>
</div>
</div>
</td>
<td class="px-6 py-5 text-on-surface-variant">{{ optional($item->request_date)->format('M d, Y') }}</td>
<td class="px-6 py-5">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-secondary text-sm">location_on</span>
<span>{{ $item->destination }}</span>
</div>
</td>
<td class="px-6 py-5">
@php
    $vehicleTypeLabel = strtolower((string) $item->vehicle_type);
    $vehicleIcon = 'airport_shuttle';

    if (str_contains($vehicleTypeLabel, 'coaster')) {
        $vehicleIcon = 'directions_bus';
    } elseif (str_contains($vehicleTypeLabel, 'pickup') || str_contains($vehicleTypeLabel, 'pick-up')) {
        $vehicleIcon = 'local_shipping';
    } elseif (str_contains($vehicleTypeLabel, 'van')) {
        $vehicleIcon = 'airport_shuttle';
    }
@endphp
<span class="inline-flex items-center bg-primary-fixed text-on-primary-fixed-variant px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-tight">
<span class="material-symbols-outlined text-[14px] mr-1">{{ $vehicleIcon }}</span>
{{ $item->vehicle_type }}
</span>
</td>
<td class="px-6 py-5">
<form method="POST" action="{{ route('admin.transportation-request.status', $item) }}" class="flex items-center justify-end gap-2">
@csrf
<input type="hidden" name="status" value="Signed"/>
<button type="submit" class="flex items-center gap-1 px-3 py-2 rounded-md bg-green-600 text-white text-[10px] font-bold uppercase tracking-wider hover:bg-green-700 transition-colors">
    
    <!-- Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" 
         class="w-4 h-4" 
         fill="none" 
         viewBox="0 0 24 24" 
         stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z" />
    </svg>

    Approve
</button>
</form>
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-outline">No pending transportation requests found.</td>
</tr>
@endforelse
</tbody>
</table>

<div class="bg-surface-container-high/30 px-6 py-4 flex items-center justify-between">
<p class="text-xs text-on-surface-variant font-medium">
Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} pending requests
</p>
<div>
{{ $requests->links() }}
</div>
</div>
</div>
</main>
@include('layouts.admin_footer')
</body></html>
