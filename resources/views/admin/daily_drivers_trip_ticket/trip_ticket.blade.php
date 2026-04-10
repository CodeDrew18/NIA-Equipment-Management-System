<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Daily Trip Ticket Management | NIA Fleet Manager</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-container": "#b9ecbd",
                        "surface-container": "#eceef1",
                        "on-tertiary-fixed-variant": "#005046",
                        "surface-container-low": "#f2f4f7",
                        "surface-tint": "#335f99",
                        "outline": "#737781",
                        "on-error": "#ffffff",
                        "secondary-fixed": "#bcefc0",
                        "on-surface": "#191c1e",
                        "on-secondary-fixed": "#00210a",
                        "background": "#f7f9fc",
                        "primary-container": "#1a4b84",
                        "outline-variant": "#c3c6d1",
                        "on-primary-fixed-variant": "#144780",
                        "tertiary": "#003c34",
                        "tertiary-fixed-dim": "#84d5c5",
                        "surface-dim": "#d8dadd",
                        "on-secondary-fixed-variant": "#22502d",
                        "primary-fixed": "#d5e3ff",
                        "surface-container-highest": "#e0e3e6",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#001c3b",
                        "surface-bright": "#f7f9fc",
                        "secondary-fixed-dim": "#a0d3a5",
                        "tertiary-container": "#00554a",
                        "primary": "#003466",
                        "tertiary-fixed": "#a0f2e1",
                        "surface-variant": "#e0e3e6",
                        "error-container": "#ffdad6",
                        "on-primary-container": "#93bcfc",
                        "on-tertiary-container": "#78caba",
                        "secondary": "#3a6843",
                        "surface-container-high": "#e6e8eb",
                        "on-secondary-container": "#3e6d47",
                        "inverse-primary": "#a6c8ff",
                        "error": "#ba1a1a",
                        "on-surface-variant": "#424750",
                        "on-error-container": "#93000a",
                        "on-secondary": "#ffffff",
                        "surface": "#f7f9fc",
                        "on-tertiary": "#ffffff",
                        "on-background": "#191c1e",
                        "primary-fixed-dim": "#a6c8ff",
                        "inverse-surface": "#2d3133",
                        "inverse-on-surface": "#eff1f4",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed": "#00201b"
                    },
                },
            },
        }
    </script>
<style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f7f9fc; color: #191c1e; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="min-h-screen">
@include('layouts.admin_header')

<main class="w-full p-8 max-w-[1600px] mx-auto pt-24">
@if (session('admin_dtt_success'))
<div class="mb-6 rounded-xl border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container text-sm font-semibold">
{{ session('admin_dtt_success') }}
</div>
@endif

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
<div>
<h1 class="text-3xl font-extrabold text-primary tracking-tight font-headline">Daily Trip Ticket Management</h1>
<p class="text-on-surface-variant font-medium mt-1">Review and manage institutional travel logs and dispatcher records.</p>
</div>
</div>

<div class="grid grid-cols-1 gap-6 mb-4 md:grid-cols-3">
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/15 flex items-center gap-4 shadow-sm">
<div class="w-12 h-12 bg-primary-fixed text-primary rounded-full flex items-center justify-center"><span class="material-symbols-outlined">receipt_long</span></div>
<div>
<div id="metric-total" class="text-2xl font-black text-primary">{{ $totalDtts }}</div>
<div class="text-xs font-bold uppercase tracking-wider text-outline">Total DTTs</div>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/15 flex items-center gap-4 shadow-sm">
<div class="w-12 h-12 bg-primary-fixed text-primary rounded-full flex items-center justify-center"><span class="material-symbols-outlined">assignment_late</span></div>
<div>
<div id="metric-pending" class="text-2xl font-black text-primary">{{ $pendingDtts }}</div>
<div class="text-xs font-bold uppercase tracking-wider text-outline">Pending DTTs</div>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/15 flex items-center gap-4 shadow-sm">
<div class="w-12 h-12 bg-secondary-fixed text-secondary rounded-full flex items-center justify-center"><span class="material-symbols-outlined">check_circle</span></div>
<div>
<div id="metric-completed" class="text-2xl font-black text-secondary">{{ $completedDtts }}</div>
<div class="text-xs font-bold uppercase tracking-wider text-outline">Completed DTTs</div>
</div>
</div>
</div>

<div class="mb-6 flex flex-wrap items-center gap-2">
<span class="text-xs font-bold uppercase text-outline">Vehicle Type Count:</span>
<span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed-variant text-xs font-bold">Coaster: <span id="count-coaster" class="ml-1">{{ $vehicleTypeCounts['coaster'] }}</span></span>
<span class="inline-flex items-center px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold">Van: <span id="count-van" class="ml-1">{{ $vehicleTypeCounts['van'] }}</span></span>
<span class="inline-flex items-center px-3 py-1 rounded-full bg-tertiary-fixed text-on-tertiary-fixed-variant text-xs font-bold">Pickup: <span id="count-pickup" class="ml-1">{{ $vehicleTypeCounts['pickup'] }}</span></span>
</div>

<div class="bg-surface-container-low rounded-xl p-6 mb-6">
<form id="dtt-filter-form" class="flex flex-col md:flex-row items-end gap-4 w-full">
<div class="flex-grow min-w-[240px] w-full md:w-auto">
<label class="block text-xs font-bold uppercase text-outline mb-1.5 ml-1">Search ID or Name</label>
<div class="relative">
<input id="dtt-search" name="search" value="{{ $search }}" class="w-full bg-surface-container-lowest border-none rounded-lg py-2.5 pl-4 pr-10 text-sm focus:ring-2 focus:ring-primary/20 shadow-sm" placeholder="Request #, Requestor, or Vehicle..." type="text"/>
<span class="absolute right-3 top-2.5 material-symbols-outlined text-outline">search</span>
</div>
</div>
<div class="w-full md:w-48">
<label class="block text-xs font-bold uppercase text-outline mb-1.5 ml-1">Vehicle Type</label>
<select id="dtt-vehicle-type" name="vehicle_type" class="w-full bg-surface-container-lowest border-none rounded-lg py-2.5 text-sm focus:ring-2 focus:ring-primary/20 shadow-sm">
<option value="">All Types</option>
<option value="coaster" @selected($vehicleType === 'coaster')>Coaster</option>
<option value="van" @selected($vehicleType === 'van')>Van</option>
<option value="pickup" @selected($vehicleType === 'pickup')>Pickup</option>
</select>
</div>
<div class="w-full md:w-72">
<label class="block text-xs font-bold uppercase text-outline mb-1.5 ml-1">Date Range</label>
<div class="flex items-center gap-2">
<input id="dtt-from" name="from" value="{{ $fromDate }}" class="w-full bg-surface-container-lowest border-none rounded-lg py-2 text-sm focus:ring-2 focus:ring-primary/20 shadow-sm" type="date"/>
<span class="text-outline font-medium">to</span>
<input id="dtt-to" name="to" value="{{ $toDate }}" class="w-full bg-surface-container-lowest border-none rounded-lg py-2 text-sm focus:ring-2 focus:ring-primary/20 shadow-sm" type="date"/>
</div>
</div>
<div class="flex-none flex gap-2">
<button type="submit" class="h-[42px] px-4 bg-tertiary-container text-on-tertiary-container font-bold rounded-lg hover:bg-tertiary transition-all flex items-center justify-center shadow-sm">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
</button>
<a href="{{ route('admin.daily-trip-ticket') }}" class="h-[42px] px-4 bg-surface-container-high text-on-surface font-bold rounded-lg hover:bg-surface-variant transition-all flex items-center justify-center shadow-sm text-xs uppercase">Clear</a>
</div>
</form>
</div>

<div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/10">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-highest/50">
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest">Request ID</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest">Vehicle Type</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest">Requestor</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest">Date Range</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest w-[220px]">Drivers</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest w-[320px]">Attachments</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest w-[220px]">Status</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest text-right">Actions</th>
</tr>
</thead>
<tbody id="dtt-tbody" class="divide-y divide-surface-container">
@forelse ($requests as $item)
<tr class="hover:bg-surface-container-low transition-colors group cursor-pointer">
<td class="px-6 py-5"><span class="font-bold text-primary">{{ $item->form_id }}</span></td>
<td class="px-6 py-5"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline text-[18px]">airport_shuttle</span><span class="font-medium">{{ $item->vehicle_type }}</span></div></td>
<td class="px-6 py-5"><div class="flex items-center gap-3"><div class="w-7 h-7 bg-primary-container text-white text-[10px] font-bold rounded-full flex items-center justify-center uppercase">{{ strtoupper(substr($item->requestor_name, 0, 2)) }}</div><span class="font-medium">{{ $item->requestor_name }}</span></div></td>
<td class="px-6 py-5">
<div class="text-sm font-semibold text-on-surface">{{ optional($item->date_time_from)->format('d/m/Y') }} - {{ optional($item->date_time_to)->format('d/m/Y') }}</div>
<div class="text-[10px] font-bold text-outline uppercase tracking-tight">
{{ max(1, optional($item->date_time_from)->startOfDay()?->diffInDays(optional($item->date_time_to)->startOfDay() ?? optional($item->date_time_from)->startOfDay()) + 1) }} Days Total
</div>
</td>
<td class="px-6 py-5 align-top">
@php
    $driverTargets = is_array($item->driver_targets ?? null) ? $item->driver_targets : [];
@endphp
@if (count($driverTargets) > 0)
<div class="space-y-1 max-w-[240px]">
@foreach ($driverTargets as $driverTarget)
<div class="inline-flex max-w-full items-center gap-1 text-xs font-semibold text-on-surface">
<span class="material-symbols-outlined text-sm text-outline">badge</span>
<span class="truncate" title="{{ (string) ($driverTarget['name'] ?? '') }}">{{ (string) ($driverTarget['name'] ?? 'Unassigned Driver') }}</span>
</div>
@endforeach
</div>
@else
<span class="text-xs text-outline">No assigned driver</span>
@endif
</td>
<td class="px-6 py-5">
@php
    $attachmentLinks = is_array($item->attachment_links ?? null) ? $item->attachment_links : [];
@endphp
@if (count($attachmentLinks) > 0)
<div class="space-y-1 max-w-[300px]">
@foreach ($attachmentLinks as $attachment)
@php
    $attachmentName = (string) ($attachment['name'] ?? 'Attachment');
@endphp
<a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex max-w-full items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container hover:underline">
<span class="material-symbols-outlined text-sm">attach_file</span>
<span class="truncate" title="{{ $attachmentName }}">{{ $attachmentName }}</span>
</a>
@endforeach
</div>
@else
<span class="text-xs text-outline">No attachment</span>
@endif
</td>
<td class="px-6 py-5 align-top">
@php
    $status = (string) ($item->status ?? 'Signed');
    $canDispatch = (bool) ($item->can_dispatch ?? false);
@endphp
<div class="max-w-[190px]">
<label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-outline">Update Status</label>
<select data-status-url="{{ route('admin.daily-trip-ticket.status', $item) }}" data-can-dispatch="{{ $canDispatch ? '1' : '0' }}" class="dtt-status-select w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs font-bold text-on-surface shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
<option value="Signed" @selected($status === 'Signed')>Signed</option>
<option value="Dispatched" @selected($status === 'Dispatched') @disabled(!$canDispatch && $status !== 'Dispatched')>Dispatched</option>
</select>
@if (!$canDispatch && $status !== 'Dispatched')
<p class="mt-1 text-[10px] font-semibold text-outline">Print DTT first to enable Dispatched.</p>
@endif
</div>
</td>
<td class="px-6 py-5 text-right">
<div class="flex flex-col items-end gap-2">
@php
    $driverTargets = is_array($item->driver_targets ?? null) ? $item->driver_targets : [];
@endphp
@foreach ($driverTargets as $driverTarget)
<a href="{{ (string) ($driverTarget['downloadUrl'] ?? '#') }}" class="dtt-download-link inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-highest text-primary font-bold text-[10px] uppercase rounded-md hover:bg-primary hover:text-white transition-all shadow-sm max-w-[240px]" title="{{ (string) ($driverTarget['name'] ?? '') }}">
<span class="material-symbols-outlined text-[14px]">print</span>
<span class="truncate">Print {{ (string) ($driverTarget['name'] ?? 'DTT') }}</span>
</a>
@endforeach
</div>
</td>
</tr>
@empty
<tr><td colspan="8" class="px-6 py-8 text-center text-sm font-semibold text-outline">No DTT records found.</td></tr>
@endforelse
</tbody>
</table>
<div class="bg-surface-container-low px-6 py-4 flex items-center justify-between border-t border-outline-variant/10">
<div id="dtt-summary" class="text-xs font-bold text-outline">Showing {{ $requests->firstItem() }}-{{ $requests->lastItem() }} of {{ $requests->total() }} requests</div>
<div id="dtt-pagination" class="flex items-center gap-2">
@if ($requests->onFirstPage())
<span class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline opacity-60"><span class="material-symbols-outlined text-sm">chevron_left</span></span>
@else
<button type="button" data-page="{{ $requests->currentPage() - 1 }}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline hover:text-primary shadow-sm"><span class="material-symbols-outlined text-sm">chevron_left</span></button>
@endif
@foreach ($requests->getUrlRange(1, $requests->lastPage()) as $page => $url)
@if ($page == $requests->currentPage())
<span class="w-8 h-8 rounded flex items-center justify-center bg-primary text-white font-bold text-xs shadow-sm">{{ $page }}</span>
@else
<button type="button" data-page="{{ $page }}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-on-surface font-bold text-xs hover:bg-surface-container-high transition-colors shadow-sm">{{ $page }}</button>
@endif
@endforeach
@if ($requests->hasMorePages())
<button type="button" data-page="{{ $requests->currentPage() + 1 }}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline hover:text-primary shadow-sm"><span class="material-symbols-outlined text-sm">chevron_right</span></button>
@else
<span class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline opacity-60"><span class="material-symbols-outlined text-sm">chevron_right</span></span>
@endif
</div>
</div>
</div>
</main>

<div id="dtt-download-success-message" class="fixed inset-x-0 top-24 z-[65] hidden px-4">
<div class="mx-auto max-w-md border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container shadow-2xl">
<p class="text-sm font-semibold">Download started successfully.</p>
</div>
</div>

<div id="dtt-download-loading-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/45 px-4">
<div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center">
<div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
<p class="text-sm font-semibold text-on-surface">Preparing your download...</p>
</div>
</div>

<div id="dtt-confirm-download-modal" class="fixed inset-0 z-[62] hidden items-center justify-center bg-black/40 px-4">
<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
<div class="mb-4 flex items-center gap-3 text-primary">
<span class="material-symbols-outlined">help</span>
<h3 class="text-lg font-bold">Confirm Download</h3>
</div>
<p class="text-sm text-on-surface-variant">Are you sure you want to download?</p>
<div class="mt-6 flex justify-end gap-3">
<button id="dtt-confirm-download-no" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">No</button>
<button id="dtt-confirm-download-yes" type="button" class="rounded-lg bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-secondary/90">Yes</button>
</div>
</div>
</div>

<div id="dtt-warning-modal" class="fixed inset-0 z-[63] hidden items-center justify-center bg-black/45 px-4">
<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-error/20">
<div class="mb-4 flex items-center gap-3 text-error">
<span class="material-symbols-outlined">warning</span>
<h3 class="text-lg font-bold">Action Required</h3>
</div>
<p id="dtt-warning-modal-message" class="text-sm text-on-surface-variant">Please print the Daily Driver's Trip Ticket first before dispatching.</p>
<div class="mt-6 flex justify-end">
<button id="dtt-warning-modal-close" type="button" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary/90">OK</button>
</div>
</div>
</div>

@include('layouts.admin_footer')

<script>
const dttEls = {
    form: document.getElementById('dtt-filter-form'),
    search: document.getElementById('dtt-search'),
    vehicleType: document.getElementById('dtt-vehicle-type'),
    fromDate: document.getElementById('dtt-from'),
    toDate: document.getElementById('dtt-to'),
    tbody: document.getElementById('dtt-tbody'),
    summary: document.getElementById('dtt-summary'),
    pagination: document.getElementById('dtt-pagination'),
    total: document.getElementById('metric-total'),
    pending: document.getElementById('metric-pending'),
    completed: document.getElementById('metric-completed'),
    coaster: document.getElementById('count-coaster'),
    van: document.getElementById('count-van'),
    pickup: document.getElementById('count-pickup'),
    downloadSuccessMessage: document.getElementById('dtt-download-success-message'),
    downloadLoadingModal: document.getElementById('dtt-download-loading-modal'),
    confirmDownloadModal: document.getElementById('dtt-confirm-download-modal'),
    confirmDownloadYes: document.getElementById('dtt-confirm-download-yes'),
    confirmDownloadNo: document.getElementById('dtt-confirm-download-no'),
    warningModal: document.getElementById('dtt-warning-modal'),
    warningModalMessage: document.getElementById('dtt-warning-modal-message'),
    warningModalClose: document.getElementById('dtt-warning-modal-close'),
};

const dttDataUrl = "{{ route('admin.daily-trip-ticket.data') }}";
const dttCsrfToken = "{{ csrf_token() }}";
let dttCurrentPage = {{ $requests->currentPage() }};
let dttDownloadInProgress = false;
let pendingDttDownload = null;
const dttMetricAnimationFrames = new WeakMap();
const dttPrefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function esc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function dttToNumber(value) {
    const parsed = Number(String(value ?? '').replace(/[^0-9.-]/g, ''));
    return Number.isFinite(parsed) ? parsed : 0;
}

function dttFormatNumber(value, decimals = 0) {
    return Number(value).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

function animateDttMetric(element, targetValue, options = {}) {
    if (!element) {
        return;
    }

    const decimals = Number(options.decimals ?? 0);
    const suffix = String(options.suffix ?? '');
    const duration = Number(options.duration ?? 700);
    const numericTarget = Number(targetValue);
    const target = Number.isFinite(numericTarget) ? numericTarget : 0;

    const existingFrameId = dttMetricAnimationFrames.get(element);
    if (existingFrameId) {
        cancelAnimationFrame(existingFrameId);
    }

    const storedValue = Number(element.dataset.countValue);
    const start = Number.isFinite(storedValue) ? storedValue : dttToNumber(element.textContent);

    if (dttPrefersReducedMotion || duration <= 0 || Math.abs(start - target) < 0.001) {
        element.textContent = `${dttFormatNumber(target, decimals)}${suffix}`;
        element.dataset.countValue = String(target);
        return;
    }

    const startedAt = performance.now();

    function tick(now) {
        const progress = Math.min(1, (now - startedAt) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = start + ((target - start) * eased);

        element.textContent = `${dttFormatNumber(current, decimals)}${suffix}`;

        if (progress < 1) {
            const frameId = requestAnimationFrame(tick);
            dttMetricAnimationFrames.set(element, frameId);
            return;
        }

        element.textContent = `${dttFormatNumber(target, decimals)}${suffix}`;
        element.dataset.countValue = String(target);
        dttMetricAnimationFrames.delete(element);
    }

    const frameId = requestAnimationFrame(tick);
    dttMetricAnimationFrames.set(element, frameId);
}

function animateDttInitialMetrics() {
    [
        dttEls.total,
        dttEls.pending,
        dttEls.completed,
        dttEls.coaster,
        dttEls.van,
        dttEls.pickup,
    ].forEach(function (metricEl) {
        if (!metricEl) {
            return;
        }

        const target = dttToNumber(metricEl.textContent);
        metricEl.dataset.countValue = '0';
        metricEl.textContent = '0';
        animateDttMetric(metricEl, target);
    });
}

function dttStatusOptions(selectedStatus, canDispatch) {
    const options = ['Signed', 'Dispatched'];
    return options
        .map((option) => {
            const shouldDisable = option === 'Dispatched' && !canDispatch && selectedStatus !== 'Dispatched';
            return `<option value="${esc(option)}"${option === selectedStatus ? ' selected' : ''}${shouldDisable ? ' disabled' : ''}>${esc(option)}</option>`;
        })
        .join('');
}

function dttAttachmentLinks(attachments) {
    if (!Array.isArray(attachments) || attachments.length === 0) {
        return '<span class="text-xs text-outline">No attachment</span>';
    }

    return `<div class="space-y-1 max-w-[300px]">${attachments.map((attachment) => {
        const name = String(attachment?.name || 'Attachment');
        const url = String(attachment?.url || '#');

        return `<a href="${esc(url)}" target="_blank" rel="noopener" class="inline-flex max-w-full items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container hover:underline"><span class="material-symbols-outlined text-sm">attach_file</span><span class="truncate" title="${esc(name)}">${esc(name)}</span></a>`;
    }).join('')}</div>`;
}

function dttRow(item) {
    const canDispatch = Boolean(item.canDispatch);
    const showDispatchHint = !canDispatch && item.status !== 'Dispatched';
    const driverTargets = Array.isArray(item.driverTargets) ? item.driverTargets : [];
    const driverNamesHtml = driverTargets.length > 0
        ? `<div class="space-y-1 max-w-[240px]">${driverTargets.map((target) => {
            const name = String(target?.name || 'Unassigned Driver');
            return `<div class="inline-flex max-w-full items-center gap-1 text-xs font-semibold text-on-surface"><span class="material-symbols-outlined text-sm text-outline">badge</span><span class="truncate" title="${esc(name)}">${esc(name)}</span></div>`;
        }).join('')}</div>`
        : '<span class="text-xs text-outline">No assigned driver</span>';

    const driverButtonsHtml = driverTargets.length > 0
        ? driverTargets.map((target) => {
            const name = String(target?.name || 'DTT');
            const downloadUrl = String(target?.downloadUrl || item.downloadUrl || '#');
            return `<a href="${esc(downloadUrl)}" class="dtt-download-link inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-highest text-primary font-bold text-[10px] uppercase rounded-md hover:bg-primary hover:text-white transition-all shadow-sm max-w-[240px]" title="${esc(name)}"><span class="material-symbols-outlined text-[14px]">print</span><span class="truncate">Print ${esc(name)}</span></a>`;
        }).join('')
        : `<a href="${esc(item.downloadUrl)}" class="dtt-download-link inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-highest text-primary font-bold text-[10px] uppercase rounded-md hover:bg-primary hover:text-white transition-all shadow-sm"><span class="material-symbols-outlined text-[14px]">print</span>Print DTT</a>`;

    return `<tr class="hover:bg-surface-container-low transition-colors group cursor-pointer">
<td class="px-6 py-5"><span class="font-bold text-primary">${esc(item.formId)}</span></td>
<td class="px-6 py-5"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline text-[18px]">airport_shuttle</span><span class="font-medium">${esc(item.vehicleType)}</span></div></td>
<td class="px-6 py-5"><div class="flex items-center gap-3"><div class="w-7 h-7 bg-primary-container text-white text-[10px] font-bold rounded-full flex items-center justify-center uppercase">${esc(item.requestorInitials)}</div><span class="font-medium">${esc(item.requestorName)}</span></div></td>
<td class="px-6 py-5"><div class="text-sm font-semibold text-on-surface">${esc(item.dateRangeLabel)}</div><div class="text-[10px] font-bold text-outline uppercase tracking-tight">${esc(item.daysTotalLabel)}</div></td>
<td class="px-6 py-5 align-top">${driverNamesHtml}</td>
<td class="px-6 py-5">${dttAttachmentLinks(item.attachments)}</td>
<td class="px-6 py-5 align-top"><div class="max-w-[190px]"><label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-outline">Update Status</label><select data-status-url="${esc(item.updateStatusUrl)}" data-can-dispatch="${canDispatch ? '1' : '0'}" class="dtt-status-select w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs font-bold text-on-surface shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/20">${dttStatusOptions(item.status, canDispatch)}</select>${showDispatchHint ? '<p class="mt-1 text-[10px] font-semibold text-outline">Print DTT first to enable Dispatched.</p>' : ''}</div></td>
<td class="px-6 py-5 text-right"><div class="flex flex-col items-end gap-2">${driverButtonsHtml}</div></td>
</tr>`;
}

function showDttDownloadLoadingModal() {
    if (!dttEls.downloadLoadingModal) {
        return;
    }
    dttEls.downloadLoadingModal.classList.remove('hidden');
    dttEls.downloadLoadingModal.classList.add('flex');
}

function showDttConfirmDownloadModal(downloadUrl, linkEl) {
    pendingDttDownload = {
        url: downloadUrl,
        link: linkEl || null,
    };

    if (!dttEls.confirmDownloadModal) {
        return;
    }

    dttEls.confirmDownloadModal.classList.remove('hidden');
    dttEls.confirmDownloadModal.classList.add('flex');
}

function hideDttConfirmDownloadModal() {
    pendingDttDownload = null;

    if (!dttEls.confirmDownloadModal) {
        return;
    }

    dttEls.confirmDownloadModal.classList.add('hidden');
    dttEls.confirmDownloadModal.classList.remove('flex');
}

function showDttWarningModal(message) {
    if (!dttEls.warningModal) {
        return;
    }

    if (dttEls.warningModalMessage) {
        dttEls.warningModalMessage.textContent = message || 'Please print the Daily Driver\'s Trip Ticket first before dispatching.';
    }

    dttEls.warningModal.classList.remove('hidden');
    dttEls.warningModal.classList.add('flex');
}

function hideDttWarningModal() {
    if (!dttEls.warningModal) {
        return;
    }

    dttEls.warningModal.classList.add('hidden');
    dttEls.warningModal.classList.remove('flex');
}

function hideDttDownloadLoadingModal() {
    if (!dttEls.downloadLoadingModal) {
        return;
    }
    dttEls.downloadLoadingModal.classList.add('hidden');
    dttEls.downloadLoadingModal.classList.remove('flex');
}

function showDttDownloadSuccessMessage() {
    if (!dttEls.downloadSuccessMessage) {
        return;
    }

    dttEls.downloadSuccessMessage.classList.remove('hidden');

    clearTimeout(showDttDownloadSuccessMessage.timerId);
    showDttDownloadSuccessMessage.timerId = setTimeout(function () {
        dttEls.downloadSuccessMessage.classList.add('hidden');
    }, 2500);
}

function setDttDownloadLinkBusy(linkEl, isBusy) {
    if (!linkEl) {
        return;
    }

    linkEl.classList.toggle('pointer-events-none', isBusy);
    linkEl.classList.toggle('opacity-80', isBusy);
}

function startDttExcelDownload(url, linkEl) {
    if (!url) {
        return;
    }

    if (dttDownloadInProgress) {
        return;
    }

    dttDownloadInProgress = true;
    setDttDownloadLinkBusy(linkEl, true);

    const iframeId = 'hidden-download-frame';
    let frame = document.getElementById(iframeId);
    let isCompleted = false;

    function completeDownloadUI() {
        if (isCompleted) {
            return;
        }

        isCompleted = true;
        dttDownloadInProgress = false;
        setDttDownloadLinkBusy(linkEl, false);
        hideDttDownloadLoadingModal();
        showDttDownloadSuccessMessage();
        window.removeEventListener('focus', handleWindowFocus);
    }

    function handleWindowFocus() {
        completeDownloadUI();
    }

    if (!frame) {
        frame = document.createElement('iframe');
        frame.id = iframeId;
        frame.style.display = 'none';
        document.body.appendChild(frame);
    }

    const separator = url.indexOf('?') === -1 ? '?' : '&';

    frame.onload = function () {
        completeDownloadUI();
    };

    showDttDownloadLoadingModal();
    window.addEventListener('focus', handleWindowFocus);
    frame.src = url + separator + 'download_ts=' + Date.now();

    setTimeout(function () {
        completeDownloadUI();
    }, 2000);
}

function syncStatusSelectOriginalValues() {
    dttEls.tbody.querySelectorAll('.dtt-status-select').forEach((selectEl) => {
        selectEl.dataset.original = selectEl.value;
    });
}

async function updateDttStatus(selectEl) {
    const url = selectEl.getAttribute('data-status-url');
    const status = selectEl.value;
    if (!url || !status) {
        return;
    }

    const originalValue = selectEl.dataset.original || selectEl.value;
    const canDispatch = String(selectEl.dataset.canDispatch || '0') === '1';

    if (status === 'Dispatched' && !canDispatch) {
        selectEl.value = originalValue;
        showDttWarningModal('Please print the Daily Driver\'s Trip Ticket first before setting status to Dispatched.');
        return;
    }

    selectEl.disabled = true;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': dttCsrfToken,
            },
            body: JSON.stringify({ status }),
        });

        if (!response.ok) {
            let errorMessage = 'Failed to update DTT status.';

            try {
                const payload = await response.json();
                errorMessage = String(payload.message || errorMessage);
            } catch (parseError) {
                // Keep fallback message when backend response body is not JSON.
            }

            throw new Error(errorMessage);
        }

        await refreshDtt(dttCurrentPage);
    } catch (error) {
        selectEl.value = originalValue;
        console.error('DTT status update failed', error);
        showDttWarningModal(error.message);
    } finally {
        selectEl.disabled = false;
        selectEl.dataset.original = selectEl.value;
    }
}

function renderPagination(pagination) {
    const pages = pagination.pageUrls || {};
    let html = '';

    if (pagination.onFirstPage) {
        html += '<span class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline opacity-60"><span class="material-symbols-outlined text-sm">chevron_left</span></span>';
    } else {
        html += `<button type="button" data-page="${pagination.currentPage - 1}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline hover:text-primary shadow-sm"><span class="material-symbols-outlined text-sm">chevron_left</span></button>`;
    }

    Object.keys(pages).forEach((page) => {
        const pageNum = Number(page);
        if (pageNum === pagination.currentPage) {
            html += `<span class="w-8 h-8 rounded flex items-center justify-center bg-primary text-white font-bold text-xs shadow-sm">${pageNum}</span>`;
        } else {
            html += `<button type="button" data-page="${pageNum}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-on-surface font-bold text-xs hover:bg-surface-container-high transition-colors shadow-sm">${pageNum}</button>`;
        }
    });

    if (pagination.hasMorePages) {
        html += `<button type="button" data-page="${pagination.currentPage + 1}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline hover:text-primary shadow-sm"><span class="material-symbols-outlined text-sm">chevron_right</span></button>`;
    } else {
        html += '<span class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline opacity-60"><span class="material-symbols-outlined text-sm">chevron_right</span></span>';
    }

    dttEls.pagination.innerHTML = html;
}

async function refreshDtt(page = dttCurrentPage) {
    const params = new URLSearchParams();
    if (dttEls.search.value) params.set('search', dttEls.search.value);
    if (dttEls.vehicleType.value) params.set('vehicle_type', dttEls.vehicleType.value);
    if (dttEls.fromDate.value) params.set('from', dttEls.fromDate.value);
    if (dttEls.toDate.value) params.set('to', dttEls.toDate.value);
    params.set('page', page);

    try {
        const response = await fetch(`${dttDataUrl}?${params.toString()}`, {
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
        dttCurrentPage = payload.pagination.currentPage;

        animateDttMetric(dttEls.total, Number(payload.metrics.totalDtts) || 0);
        animateDttMetric(dttEls.pending, Number(payload.metrics.pendingDtts) || 0);
        animateDttMetric(dttEls.completed, Number(payload.metrics.completedDtts) || 0);
        animateDttMetric(dttEls.coaster, Number(payload.metrics.vehicleTypeCounts.coaster) || 0);
        animateDttMetric(dttEls.van, Number(payload.metrics.vehicleTypeCounts.van) || 0);
        animateDttMetric(dttEls.pickup, Number(payload.metrics.vehicleTypeCounts.pickup) || 0);

        if (payload.requests.length > 0) {
            dttEls.tbody.innerHTML = payload.requests.map(dttRow).join('');
        } else {
            dttEls.tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-sm font-semibold text-outline">No DTT records found.</td></tr>';
        }

        syncStatusSelectOriginalValues();

        dttEls.summary.textContent = payload.summaryText;
        renderPagination(payload.pagination);
    } catch (error) {
        console.error('Daily trip ticket AJAX refresh failed', error);
    }
}

dttEls.form.addEventListener('submit', function (event) {
    event.preventDefault();
    refreshDtt(1);
});

dttEls.pagination.addEventListener('click', function (event) {
    const target = event.target.closest('[data-page]');
    if (!target) {
        return;
    }
    event.preventDefault();
    const page = Number(target.getAttribute('data-page'));
    if (page > 0) {
        refreshDtt(page);
    }
});

dttEls.tbody.addEventListener('change', function (event) {
    const selectEl = event.target.closest('.dtt-status-select');
    if (!selectEl) {
        return;
    }
    updateDttStatus(selectEl);
});

dttEls.tbody.addEventListener('click', function (event) {
    const downloadLink = event.target.closest('.dtt-download-link');
    if (!downloadLink) {
        return;
    }

    event.preventDefault();
    showDttConfirmDownloadModal(downloadLink.getAttribute('href'), downloadLink);
});

if (dttEls.confirmDownloadNo) {
    dttEls.confirmDownloadNo.addEventListener('click', function () {
        hideDttConfirmDownloadModal();
        hideDttDownloadLoadingModal();
    });
}

if (dttEls.confirmDownloadModal) {
    dttEls.confirmDownloadModal.addEventListener('click', function (event) {
        if (event.target === dttEls.confirmDownloadModal) {
            hideDttConfirmDownloadModal();
            hideDttDownloadLoadingModal();
        }
    });
}

if (dttEls.confirmDownloadYes) {
    dttEls.confirmDownloadYes.addEventListener('click', function () {
        const payload = pendingDttDownload;
        hideDttConfirmDownloadModal();

        if (!payload || !payload.url) {
            return;
        }

        startDttExcelDownload(payload.url, payload.link || null);
    });
}

if (dttEls.warningModalClose) {
    dttEls.warningModalClose.addEventListener('click', function () {
        hideDttWarningModal();
    });
}

if (dttEls.warningModal) {
    dttEls.warningModal.addEventListener('click', function (event) {
        if (event.target === dttEls.warningModal) {
            hideDttWarningModal();
        }
    });
}

syncStatusSelectOriginalValues();
animateDttInitialMetrics();

if (typeof window.emsLiveRefresh === 'function') {
    window.emsLiveRefresh(function () {
        return refreshDtt(dttCurrentPage);
    }, {
        intervalMs: 4000,
        shouldPause: function () {
            return dttDownloadInProgress === true;
        },
    });
}
</script>
</body>
</html>
