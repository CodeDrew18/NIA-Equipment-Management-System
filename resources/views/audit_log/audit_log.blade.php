<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Audit Logs | Equipment Management System</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "secondary-container": "#b9ecbd",
                    "outline": "#737781",
                    "surface-container-low": "#f2f4f7",
                    "surface-container-lowest": "#ffffff",
                    "on-primary-fixed": "#001c3b",
                    "surface": "#f7f9fc",
                    "surface-dim": "#d8dadd",
                    "surface-container-high": "#e6e8eb",
                    "inverse-surface": "#2d3133",
                    "on-tertiary-fixed-variant": "#005046",
                    "on-secondary-fixed-variant": "#22502d",
                    "on-primary-fixed-variant": "#144780",
                    "tertiary-fixed": "#a0f2e1",
                    "on-error-container": "#93000a",
                    "inverse-primary": "#a6c8ff",
                    "tertiary": "#003c34",
                    "on-secondary-fixed": "#00210a",
                    "surface-container-highest": "#e0e3e6",
                    "primary": "#003466",
                    "on-tertiary-fixed": "#00201b",
                    "surface-bright": "#f7f9fc",
                    "on-error": "#ffffff",
                    "surface-container": "#eceef1",
                    "tertiary-container": "#00554a",
                    "on-tertiary-container": "#78caba",
                    "primary-fixed": "#d5e3ff",
                    "on-secondary-container": "#3e6d47",
                    "background": "#f7f9fc",
                    "secondary-fixed-dim": "#a0d3a5",
                    "error": "#ba1a1a",
                    "on-primary": "#ffffff",
                    "error-container": "#ffdad6",
                    "secondary-fixed": "#bcefc0",
                    "tertiary-fixed-dim": "#84d5c5",
                    "on-primary-container": "#93bcfc",
                    "outline-variant": "#c3c6d1",
                    "on-surface-variant": "#424750",
                    "primary-fixed-dim": "#a6c8ff",
                    "on-tertiary": "#ffffff",
                    "surface-variant": "#e0e3e6",
                    "inverse-on-surface": "#eff1f4",
                    "surface-tint": "#335f99",
                    "on-surface": "#191c1e",
                    "on-background": "#191c1e",
                    "on-secondary": "#ffffff",
                    "secondary": "#3a6843",
                    "primary-container": "#1a4b84"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            },
            "fontFamily": {
                    "headline": ["Public Sans"],
                    "body": ["Public Sans"],
                    "label": ["Public Sans"]
            }
          },
        },
      }
    </script>
<style>
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .architectural-underline:focus {
            border-bottom: 2px solid #003466;
            outline: none;
        }
    </style>
</head>
<body class="bg-background text-on-surface">
<!-- TopAppBar Section -->
@include('layouts.header')
<main class="max-w-[1600px] mx-auto px-8 pb-12 pt-28">
<!-- Page Header -->
<section class="mb-10">
<h1 class="font-headline text-3xl font-extrabold text-primary tracking-tight mb-2">Audit Logs</h1>
<p class="text-on-surface-variant max-w-2xl leading-relaxed">
        Maintain full accountability across EMS with a tamper-aware record of authenticated user actions, process changes, route access, and source IP activity.
            </p>
</section>
<!-- Filters Section - Bento Style -->
<form method="GET" action="{{ route('audit-log') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
<div class="md:col-span-2 bg-surface-container-low p-6 rounded-xl">
<label class="font-label text-xs font-semibold uppercase tracking-wider text-outline block mb-3">Search Activity</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
<input name="search" value="{{ $search }}" class="w-full bg-surface-container-lowest border-none architectural-underline py-3 pl-12 pr-4 rounded-lg shadow-sm text-sm" placeholder="User ID, name, action, route, or IP" type="text"/>
</div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl">
<label class="font-label text-xs font-semibold uppercase tracking-wider text-outline block mb-3">Date Range</label>
<div class="flex items-center gap-2">
<input name="from" value="{{ $fromDate }}" class="w-full bg-surface-container-lowest border-none py-2 px-3 rounded-lg text-sm shadow-sm" type="date"/>
<span class="text-outline">to</span>
<input name="to" value="{{ $toDate }}" class="w-full bg-surface-container-lowest border-none py-2 px-3 rounded-lg text-sm shadow-sm" type="date"/>
</div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl flex flex-col justify-end">
<button type="submit" class="w-full bg-primary text-on-primary py-3 px-6 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-primary-container transition-all active:scale-[0.98]">
<span class="material-symbols-outlined text-sm" data-icon="filter_list">filter_list</span>
                    Apply Filters
                </button>
</div>
</form>
<!-- Data Table Container -->
<section class="bg-surface-container-lowest rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)] overflow-hidden border border-outline-variant/15">
<div class="overflow-x-auto">
<table class="w-full border-collapse text-left">
<thead class="bg-surface-container-low border-b border-outline-variant/15">
<tr>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Timestamp</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">User ID &amp; Name</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Action Category</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Activity Description</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">IP Address</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($auditLogs as $log)
@php
  $rowClass = $loop->even ? 'bg-surface-container-low/30 hover:bg-surface-container-low transition-colors' : 'hover:bg-surface-container-low transition-colors';
  $category = strtoupper((string) $log->action_category);
  $status = strtoupper((string) $log->status);

  $categoryClass = 'bg-outline-variant/30 text-on-surface-variant';
  if (str_contains($category, 'LOGIN') || str_contains($category, 'LOGOUT')) {
    $categoryClass = 'bg-tertiary-fixed text-on-tertiary-fixed-variant';
  } elseif (str_contains($category, 'PROCESS') || str_contains($category, 'EDIT') || str_contains($category, 'CREATE') || str_contains($category, 'UPDATE')) {
    $categoryClass = 'bg-primary-fixed text-on-primary-fixed';
  } elseif (str_contains($category, 'ACCESS')) {
    $categoryClass = 'bg-primary-fixed-dim text-on-primary-fixed-variant';
  }

  $statusClass = 'bg-secondary-fixed text-on-secondary-fixed';
  $dotClass = 'bg-secondary';
  if ($status === 'FAILED') {
    $statusClass = 'bg-error-container text-on-error-container';
    $dotClass = 'bg-error';
  } elseif ($status === 'WARNING') {
    $statusClass = 'bg-surface-container-highest text-on-surface-variant';
    $dotClass = 'bg-outline';
  }
@endphp
<tr class="{{ $rowClass }}">
<td class="px-6 py-5 text-sm font-medium text-on-surface">{{ optional($log->created_at)->format('M d, Y h:i A') ?? 'N/A' }}</td>
<td class="px-6 py-5">
<div class="flex flex-col">
<span class="text-sm font-bold text-primary">{{ $log->personnel_id ?: ('USR-' . $log->user_id) }}</span>
<span class="text-xs text-on-surface-variant">{{ $log->user_name ?: 'Unknown User' }}</span>
</div>
</td>
<td class="px-6 py-5">
<span class="{{ $categoryClass }} px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $category) }}</span>
</td>
<td class="px-6 py-5 text-sm text-on-surface-variant max-w-xs truncate">{{ $log->activity_description }}</td>
<td class="px-6 py-5 text-xs font-mono text-outline">{{ $log->ip_address ?: 'N/A' }}</td>
<td class="px-6 py-5">
<span class="inline-flex items-center gap-1.5 {{ $statusClass }} px-3 py-1 rounded-full text-xs font-bold">
<span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span> {{ $status }}
                </span>
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-outline">No audit records found for the selected filters.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<!-- Pagination -->
<footer class="px-6 py-4 bg-surface-container-low border-t border-outline-variant/15 flex items-center justify-between">
<span class="text-xs font-medium text-on-surface-variant">Showing {{ $auditLogs->firstItem() ?? 0 }} to {{ $auditLogs->lastItem() ?? 0 }} of {{ $auditLogs->total() }} entries</span>
<div class="flex items-center gap-2">
@if ($auditLogs->onFirstPage())
<button class="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline disabled:opacity-30" disabled="">
<span class="material-symbols-outlined">chevron_left</span>
</button>
@else
<a href="{{ $auditLogs->previousPageUrl() }}" class="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline">
<span class="material-symbols-outlined">chevron_left</span>
</a>
@endif

@foreach ($auditLogs->getUrlRange(1, $auditLogs->lastPage()) as $page => $url)
@if ($page == $auditLogs->currentPage())
<span class="h-8 w-8 rounded-lg bg-primary text-on-primary text-xs font-bold inline-flex items-center justify-center">{{ $page }}</span>
@else
<a href="{{ $url }}" class="h-8 w-8 rounded-lg hover:bg-surface-container-high text-xs font-bold text-on-surface inline-flex items-center justify-center">{{ $page }}</a>
@endif
@endforeach

@if ($auditLogs->hasMorePages())
<a href="{{ $auditLogs->nextPageUrl() }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline text-xs font-semibold">
<span>Next</span>
<span class="material-symbols-outlined">chevron_right</span>
</a>
@else
<button class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline text-xs font-semibold disabled:opacity-30" disabled="">
<span>Next</span>
<span class="material-symbols-outlined">chevron_right</span>
</button>
@endif
</div>
</footer>
</section>
<!-- Stats Grid (Optional Extra for "High-End UI") -->
<section class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="bg-primary p-6 rounded-xl text-on-primary">
<span class="font-label text-[10px] font-bold uppercase tracking-widest opacity-80">Total audit entries</span>
<div class="text-3xl font-extrabold mt-1 tracking-tight">{{ number_format($totalLogs) }}</div>
<div class="mt-4 flex items-center gap-1 text-secondary-fixed text-xs font-bold">
<span class="material-symbols-outlined text-xs">{{ $trendIcon }}</span>
          {{ ($trendPercentage >= 0 ? '+' : '') . number_format($trendPercentage, 1) }}% vs previous month
                </div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl">
<span class="font-label text-[10px] font-bold uppercase tracking-widest text-outline">Access and process alerts</span>
<div class="text-3xl font-extrabold mt-1 tracking-tight text-primary">{{ number_format($securityAlerts) }}</div>
<div class="mt-4 flex items-center gap-1 text-error text-xs font-bold">
<span class="material-symbols-outlined text-xs">warning</span>
          {{ number_format($criticalAlerts) }} failed events requiring review
                </div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl">
<span class="font-label text-[10px] font-bold uppercase tracking-widest text-outline">Users active (24h)</span>
<div class="text-3xl font-extrabold mt-1 tracking-tight text-primary">{{ str_pad((string) $activeUsers, 2, '0', STR_PAD_LEFT) }}</div>
<div class="mt-4 flex items-center gap-1 text-on-surface-variant text-xs font-bold">
<span class="material-symbols-outlined text-xs">group</span>
          With recorded authenticated activity
                </div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl overflow-hidden relative">
<span class="font-label text-[10px] font-bold uppercase tracking-widest text-outline relative z-10">Latest audit event</span>
<div class="text-3xl font-extrabold mt-1 tracking-tight text-primary relative z-10">{{ $latestEventLabel }}</div>
<div class="mt-4 flex items-center gap-1 text-secondary text-xs font-bold relative z-10">
<span class="material-symbols-outlined text-xs">check_circle</span>
          User, action, and IP captured
                </div>
<div class="absolute -right-4 -bottom-4 opacity-5">
<span class="material-symbols-outlined !text-9xl" style="font-variation-settings: 'FILL' 1;">history_edu</span>
</div>
</div>
</section>
</main>
<!-- Footer Space -->
@include('layouts.footer')
</body></html>