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
<body class="bg-background text-on-surface min-h-screen flex flex-col">
<!-- TopAppBar Section -->
@include('layouts.admin_header')
<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pb-12 pt-28">
<!-- Page Header -->
<section class="mb-10">
<h1 class="font-headline text-3xl font-extrabold text-primary tracking-tight mb-2">Audit Logs</h1>
<p class="text-on-surface-variant max-w-2xl leading-relaxed">
  Maintain full accountability across EMS with a tamper-aware record of authenticated user actions, process changes, and route activity details.
            </p>
</section>
<!-- Filters Section - Bento Style -->
<form method="GET" action="{{ route('audit-log') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
<div class="md:col-span-2 bg-surface-container-low p-6 rounded-xl">
<label class="font-label text-xs font-semibold uppercase tracking-wider text-outline block mb-3">Search Activity</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
<input name="search" value="{{ $search }}" class="w-full bg-surface-container-lowest border-none architectural-underline py-3 pl-12 pr-4 rounded-lg shadow-sm text-sm" placeholder="Name, action, route, or details" type="text"/>
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
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Name</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Action Category</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Activity Description</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Other</th>
<th class="px-6 py-4 font-label text-xs font-semibold uppercase tracking-wider text-outline">Status</th>
</tr>
</thead>
<tbody id="audit-log-tbody" class="divide-y divide-outline-variant/10">
@forelse ($auditLogs as $log)
@php
  $rowClass = $loop->even ? 'bg-surface-container-low/30 hover:bg-surface-container-low transition-colors' : 'hover:bg-surface-container-low transition-colors';
  $category = strtoupper((string) $log->action_category);
  $status = strtoupper((string) $log->status);
  $name = trim((string) ($log->user_name ?? ''));
  $method = strtoupper(trim((string) ($log->method ?? '')));
  $routeLabel = trim((string) ($log->route_name ?? ''));
  $requestPath = trim((string) ($log->request_path ?? ''));

  if ($routeLabel !== '') {
    $otherDetails = str_replace(['.', '_'], [' ', ' '], $routeLabel);
  } elseif ($requestPath !== '') {
    $otherDetails = $requestPath;
  } else {
    $otherDetails = 'N/A';
  }

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
<td class="px-6 py-5 text-sm font-semibold text-primary">{{ $name !== '' ? $name : 'Unknown User' }}</td>
<td class="px-6 py-5">
<span class="{{ $categoryClass }} px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $category) }}</span>
</td>
<td class="px-6 py-5 text-sm text-on-surface-variant max-w-xs truncate">{{ $log->activity_description }}</td>
<td class="px-6 py-5 text-xs text-outline">
@if ($method !== '')
<span class="inline-flex items-center px-2 py-0.5 mr-1 rounded bg-surface-container-high text-[10px] font-bold tracking-wide">{{ $method }}</span>
@endif
<span>{{ $otherDetails }}</span>
</td>
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
<span id="audit-log-summary" class="text-xs font-medium text-on-surface-variant">{{ $summaryText ?? ('Showing ' . ($auditLogs->firstItem() ?? 0) . ' to ' . ($auditLogs->lastItem() ?? 0) . ' of ' . $auditLogs->total() . ' entries') }}</span>
<div id="audit-log-pagination" class="flex items-center gap-2">
@if ($auditLogs->onFirstPage())
<button class="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline disabled:opacity-30" disabled="">
<span class="material-symbols-outlined">chevron_left</span>
</button>
@else
<a href="{{ $auditLogs->previousPageUrl() }}" class="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline">
<span class="material-symbols-outlined">chevron_left</span>
</a>
@endif

@php
  $lastPage = $auditLogs->lastPage();
  $currentPage = $auditLogs->currentPage();
  $window = 2;
  $visiblePages = [];

  for ($page = max(1, $currentPage - $window); $page <= min($lastPage, $currentPage + $window); $page++) {
      $visiblePages[] = $page;
  }

  $visiblePages[] = 1;
  if ($lastPage > 1) {
      $visiblePages[] = $lastPage;
  }

  $visiblePages = array_values(array_unique($visiblePages));
  sort($visiblePages);
  $previousVisiblePage = null;
@endphp

@foreach ($visiblePages as $page)
@if ($previousVisiblePage !== null && ($page - $previousVisiblePage) > 1)
<span class="h-8 px-2 rounded-lg text-xs font-bold text-outline inline-flex items-center justify-center">...</span>
@endif

@if ($page == $currentPage)
<span class="h-8 w-8 rounded-lg bg-primary text-on-primary text-xs font-bold inline-flex items-center justify-center">{{ $page }}</span>
@else
<a href="{{ $auditLogs->url($page) }}" class="h-8 w-8 rounded-lg hover:bg-surface-container-high text-xs font-bold text-on-surface inline-flex items-center justify-center">{{ $page }}</a>
@endif

@php
  $previousVisiblePage = $page;
@endphp
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
<div id="audit-total-logs" data-count-value="{{ $totalLogs }}" class="text-3xl font-extrabold mt-1 tracking-tight">{{ number_format($totalLogs) }}</div>
<div class="mt-4 flex items-center gap-1 text-secondary-fixed text-xs font-bold">
<span class="material-symbols-outlined text-xs">{{ $trendIcon }}</span>
      <span id="audit-trend-percentage">{{ ($trendPercentage >= 0 ? '+' : '') . number_format($trendPercentage, 1) }}% vs previous month</span>
                </div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl">
<span class="font-label text-[10px] font-bold uppercase tracking-widest text-outline">Access and process alerts</span>
<div id="audit-security-alerts" data-count-value="{{ $securityAlerts }}" class="text-3xl font-extrabold mt-1 tracking-tight text-primary">{{ number_format($securityAlerts) }}</div>
<div class="mt-4 flex items-center gap-1 text-error text-xs font-bold">
<span class="material-symbols-outlined text-xs">warning</span>
      <span id="audit-critical-alerts">{{ number_format($criticalAlerts) }}</span> failed events requiring review
                </div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl">
<span class="font-label text-[10px] font-bold uppercase tracking-widest text-outline">Users active (24h)</span>
<div id="audit-active-users" data-count-value="{{ $activeUsers }}" class="text-3xl font-extrabold mt-1 tracking-tight text-primary">{{ str_pad((string) $activeUsers, 2, '0', STR_PAD_LEFT) }}</div>
<div class="mt-4 flex items-center gap-1 text-on-surface-variant text-xs font-bold">
<span class="material-symbols-outlined text-xs">group</span>
          With recorded authenticated activity
                </div>
</div>
<div class="bg-surface-container-low p-6 rounded-xl overflow-hidden relative">
<span class="font-label text-[10px] font-bold uppercase tracking-widest text-outline relative z-10">Latest audit event</span>
<div id="audit-latest-event" class="text-3xl font-extrabold mt-1 tracking-tight text-primary relative z-10">{{ $latestEventLabel }}</div>
<div class="mt-4 flex items-center gap-1 text-secondary text-xs font-bold relative z-10">
<span class="material-symbols-outlined text-xs">check_circle</span>
      User, action, and request details captured
                </div>
<div class="absolute -right-4 -bottom-4 opacity-5">
<span class="material-symbols-outlined !text-9xl" style="font-variation-settings: 'FILL' 1;">history_edu</span>
</div>
</div>
</section>
</main>
<!-- Footer Space -->
@include('layouts.footer')
<script>
(function () {
  const auditEls = {
    form: document.querySelector('form[action="{{ route('audit-log') }}"]'),
    search: document.querySelector('input[name="search"]'),
    from: document.querySelector('input[name="from"]'),
    to: document.querySelector('input[name="to"]'),
    tbody: document.getElementById('audit-log-tbody'),
    summary: document.getElementById('audit-log-summary'),
    pagination: document.getElementById('audit-log-pagination'),
    totalLogs: document.getElementById('audit-total-logs'),
    trendPercentage: document.getElementById('audit-trend-percentage'),
    securityAlerts: document.getElementById('audit-security-alerts'),
    criticalAlerts: document.getElementById('audit-critical-alerts'),
    activeUsers: document.getElementById('audit-active-users'),
    latestEvent: document.getElementById('audit-latest-event'),
  };

  const auditDataUrl = "{{ route('audit-log') }}";
  const auditPrefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const auditAnimationFrames = new WeakMap();
  let auditCurrentPage = {{ $auditLogs->currentPage() }};
  let auditFilters = {
    search: @json($search),
    from: @json($fromDate),
    to: @json($toDate),
  };

  function auditEsc(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function auditToNumber(value) {
    const parsed = Number(String(value ?? '').replace(/[^0-9.-]/g, ''));
    return Number.isFinite(parsed) ? parsed : 0;
  }

  function auditFormatNumber(value, decimals = 0) {
    return Number(value).toLocaleString('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  }

  function auditAnimateMetric(element, targetValue, options = {}) {
    if (!element) {
      return;
    }

    const decimals = Number(options.decimals ?? 0);
    const suffix = String(options.suffix ?? '');
    const duration = Number(options.duration ?? 700);
    const numericTarget = Number(targetValue);
    const target = Number.isFinite(numericTarget) ? numericTarget : 0;

    const existingFrameId = auditAnimationFrames.get(element);
    if (existingFrameId) {
      cancelAnimationFrame(existingFrameId);
    }

    const storedValue = Number(element.dataset.countValue);
    const start = Number.isFinite(storedValue) ? storedValue : auditToNumber(element.textContent);

    if (auditPrefersReducedMotion || duration <= 0 || Math.abs(start - target) < 0.001) {
      element.textContent = `${auditFormatNumber(target, decimals)}${suffix}`;
      element.dataset.countValue = String(target);
      return;
    }

    const startedAt = performance.now();

    function tick(now) {
      const progress = Math.min(1, (now - startedAt) / duration);
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = start + ((target - start) * eased);

      element.textContent = `${auditFormatNumber(current, decimals)}${suffix}`;

      if (progress < 1) {
        const frameId = requestAnimationFrame(tick);
        auditAnimationFrames.set(element, frameId);
        return;
      }

      element.textContent = `${auditFormatNumber(target, decimals)}${suffix}`;
      element.dataset.countValue = String(target);
      auditAnimationFrames.delete(element);
    }

    const frameId = requestAnimationFrame(tick);
    auditAnimationFrames.set(element, frameId);
  }

  function auditAnimateInitialMetrics() {
    [auditEls.totalLogs, auditEls.securityAlerts, auditEls.activeUsers].forEach(function (metricEl) {
      if (!metricEl) {
        return;
      }

      const target = auditToNumber(metricEl.textContent);
      metricEl.dataset.countValue = '0';
      metricEl.textContent = '0';
      auditAnimateMetric(metricEl, target);
    });
  }

  function auditCategoryClass(category) {
    const value = String(category ?? '').toUpperCase();
    if (value.includes('LOGIN') || value.includes('LOGOUT')) {
      return 'bg-tertiary-fixed text-on-tertiary-fixed-variant';
    }

    if (value.includes('PROCESS') || value.includes('EDIT') || value.includes('CREATE') || value.includes('UPDATE')) {
      return 'bg-primary-fixed text-on-primary-fixed';
    }

    if (value.includes('ACCESS')) {
      return 'bg-primary-fixed-dim text-on-primary-fixed-variant';
    }

    return 'bg-outline-variant/30 text-on-surface-variant';
  }

  function auditStatusClass(status) {
    const value = String(status ?? '').toUpperCase();
    if (value === 'FAILED') {
      return { badge: 'bg-error-container text-on-error-container', dot: 'bg-error' };
    }

    if (value === 'WARNING') {
      return { badge: 'bg-surface-container-highest text-on-surface-variant', dot: 'bg-outline' };
    }

    return { badge: 'bg-secondary-fixed text-on-secondary-fixed', dot: 'bg-secondary' };
  }

  function auditRow(item, index) {
    const rowClass = index % 2 === 1 ? 'bg-surface-container-low/30 hover:bg-surface-container-low transition-colors' : 'hover:bg-surface-container-low transition-colors';
    const category = String(item.actionCategory ?? '');
    const status = String(item.status ?? '');
    const categoryClass = auditCategoryClass(category);
    const statusClass = auditStatusClass(status);

    return `<tr class="${rowClass}">
      <td class="px-6 py-5 text-sm font-medium text-on-surface">${auditEsc(item.timestamp)}</td>
      <td class="px-6 py-5 text-sm font-semibold text-primary">${auditEsc(item.name || 'Unknown User')}</td>
      <td class="px-6 py-5"><span class="${categoryClass} px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">${auditEsc(String(category || '').replaceAll('_', ' '))}</span></td>
      <td class="px-6 py-5 text-sm text-on-surface-variant max-w-xs truncate">${auditEsc(item.activityDescription)}</td>
      <td class="px-6 py-5 text-xs text-outline"><span>${auditEsc(item.otherDetails || 'N/A')}</span></td>
      <td class="px-6 py-5"><span class="inline-flex items-center gap-1.5 ${statusClass.badge} px-3 py-1 rounded-full text-xs font-bold"><span class="w-1.5 h-1.5 rounded-full ${statusClass.dot}"></span> ${auditEsc(status.toUpperCase())}</span></td>
    </tr>`;
  }

  function auditRenderPagination(pagination) {
    if (!auditEls.pagination) {
      return;
    }

    const currentPage = Number(pagination.currentPage || 1);
    const onFirstPage = Boolean(pagination.onFirstPage);
    const hasMorePages = Boolean(pagination.hasMorePages);
    const lastPage = Number(pagination.lastPage || currentPage || 1);

    function buildVisiblePages(current, last, windowSize = 2) {
      const pages = new Set();
      pages.add(1);
      pages.add(last);

      const start = Math.max(1, current - windowSize);
      const end = Math.min(last, current + windowSize);
      for (let page = start; page <= end; page += 1) {
        pages.add(page);
      }

      return Array.from(pages)
        .filter(function (page) {
          return Number.isFinite(page) && page >= 1;
        })
        .sort(function (a, b) {
          return a - b;
        });
    }

    const prevButton = onFirstPage
      ? '<button class="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline disabled:opacity-30" disabled><span class="material-symbols-outlined">chevron_left</span></button>'
      : `<button type="button" data-audit-page="${currentPage - 1}" class="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline"><span class="material-symbols-outlined">chevron_left</span></button>`;

    const visiblePages = buildVisiblePages(currentPage, Math.max(1, lastPage));
    const pageButtons = visiblePages.map(function (page, index) {
      const previous = index > 0 ? visiblePages[index - 1] : null;
      const ellipsis = previous !== null && (page - previous) > 1
        ? '<span class="h-8 px-2 rounded-lg text-xs font-bold text-outline inline-flex items-center justify-center">...</span>'
        : '';

      if (page === currentPage) {
        return `${ellipsis}<span class="h-8 w-8 rounded-lg bg-primary text-on-primary text-xs font-bold inline-flex items-center justify-center">${page}</span>`;
      }

      return `${ellipsis}<button type="button" data-audit-page="${page}" class="h-8 w-8 rounded-lg hover:bg-surface-container-high text-xs font-bold text-on-surface inline-flex items-center justify-center">${page}</button>`;
    }).join('');

    const nextButton = hasMorePages
      ? `<button type="button" data-audit-page="${currentPage + 1}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline text-xs font-semibold"><span>Next</span><span class="material-symbols-outlined">chevron_right</span></button>`
      : '<button class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-surface-container-high transition-colors text-outline text-xs font-semibold disabled:opacity-30" disabled><span>Next</span><span class="material-symbols-outlined">chevron_right</span></button>';

    auditEls.pagination.innerHTML = `${prevButton}${pageButtons}${nextButton}`;
  }

  async function auditRefresh(page = auditCurrentPage) {
    try {
      const params = new URLSearchParams();
      if (auditFilters.search) params.set('search', auditFilters.search);
      if (auditFilters.from) params.set('from', auditFilters.from);
      if (auditFilters.to) params.set('to', auditFilters.to);
      params.set('page', String(page || 1));

      const response = await fetch(`${auditDataUrl}?${params.toString()}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        cache: 'no-store',
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      const rows = Array.isArray(payload.rows) ? payload.rows : [];
      const pagination = payload.auditLogs?.pagination || payload.pagination || {};

      auditCurrentPage = Number(pagination.currentPage || 1);

      if (auditEls.tbody) {
        auditEls.tbody.innerHTML = rows.length > 0
          ? rows.map(auditRow).join('')
          : '<tr><td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-outline">No audit records found for the selected filters.</td></tr>';
      }

      if (auditEls.summary) {
        auditEls.summary.textContent = payload.summaryText || 'Showing 0 to 0 of 0 entries';
      }

      if (auditEls.totalLogs) {
        auditAnimateMetric(auditEls.totalLogs, Number(payload.totalLogs) || 0);
      }

      if (auditEls.securityAlerts) {
        auditAnimateMetric(auditEls.securityAlerts, Number(payload.securityAlerts) || 0);
      }

      if (auditEls.activeUsers) {
        auditAnimateMetric(auditEls.activeUsers, Number(payload.activeUsers) || 0);
      }

      if (auditEls.criticalAlerts) {
        auditEls.criticalAlerts.textContent = String(Number(payload.criticalAlerts) || 0);
      }

      if (auditEls.trendPercentage) {
        const trend = Number(payload.trendPercentage) || 0;
        auditEls.trendPercentage.textContent = `${trend >= 0 ? '+' : ''}${trend.toFixed(1)}% vs previous month`;
      }

      if (auditEls.latestEvent) {
        auditEls.latestEvent.textContent = payload.latestEventLabel || 'No records yet';
      }

      auditRenderPagination(pagination);
    } catch (error) {
      console.error('Audit log AJAX refresh failed', error);
    }
  }

  if (auditEls.form) {
    auditEls.form.addEventListener('submit', function (event) {
      event.preventDefault();

      auditFilters.search = String(auditEls.search?.value ?? '').trim();
      auditFilters.from = String(auditEls.from?.value ?? '').trim();
      auditFilters.to = String(auditEls.to?.value ?? '').trim();
      auditCurrentPage = 1;

      auditRefresh(1);
    });
  }

  if (auditEls.pagination) {
    auditEls.pagination.addEventListener('click', function (event) {
      const button = event.target.closest('[data-audit-page]');
      if (!button) {
        return;
      }

      event.preventDefault();
      const page = Number(button.getAttribute('data-audit-page'));
      if (!Number.isFinite(page) || page < 1) {
        return;
      }

      auditRefresh(page);
    });
  }

  auditAnimateInitialMetrics();
  auditRefresh(auditCurrentPage);

  if (typeof window.emsLiveRefresh === 'function') {
    window.emsLiveRefresh(function () {
      return auditRefresh(auditCurrentPage);
    }, {
      intervalMs: 5000,
    });
  }
})();
</script>
</body></html>