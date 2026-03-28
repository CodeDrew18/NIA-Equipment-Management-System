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
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest text-center">DTT Count</th>
<th class="px-6 py-4 text-xs font-black uppercase text-on-surface tracking-widest">Status</th>
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
<td class="px-6 py-5 text-center"><span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-bold text-sm">{{ max(1, optional($item->date_time_from)->startOfDay()?->diffInDays(optional($item->date_time_to)->startOfDay() ?? optional($item->date_time_from)->startOfDay()) + 1) }}</span></td>
<td class="px-6 py-5">
@php
    $status = (string) ($item->status ?? 'Pending');
@endphp
<select data-status-url="{{ route('admin.daily-trip-ticket.status', $item) }}" class="dtt-status-select w-full border border-outline-variant rounded-md py-2 px-2 text-sm bg-white">
<option value="Pending" @selected($status === 'Pending')>Pending</option>
<option value="To be Signed" @selected($status === 'To be Signed')>To be Signed</option>
<option value="Dispatched" @selected($status === 'Dispatched')>Dispatched</option>
</select>
</td>
<td class="px-6 py-5 text-right">
<div class="flex items-center justify-end gap-2">
<a href="{{ route('admin.daily-trip-ticket.print', $item) }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-highest text-primary font-bold text-[10px] uppercase rounded-md hover:bg-primary hover:text-white transition-all shadow-sm">
<span class="material-symbols-outlined text-[14px]">print</span>
Print DTTs
</a>
</div>
</td>
</tr>
@empty
<tr><td colspan="7" class="px-6 py-8 text-center text-sm font-semibold text-outline">No DTT records found.</td></tr>
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
};

const dttDataUrl = "{{ route('admin.daily-trip-ticket.data') }}";
const dttCsrfToken = "{{ csrf_token() }}";
let dttCurrentPage = {{ $requests->currentPage() }};

function esc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function dttStatusOptions(selectedStatus) {
    const options = ['Pending', 'To be Signed', 'Dispatched'];
    return options
        .map((option) => `<option value="${esc(option)}"${option === selectedStatus ? ' selected' : ''}>${esc(option)}</option>`)
        .join('');
}

function dttRow(item) {
    return `<tr class="hover:bg-surface-container-low transition-colors group cursor-pointer">
<td class="px-6 py-5"><span class="font-bold text-primary">${esc(item.formId)}</span></td>
<td class="px-6 py-5"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline text-[18px]">airport_shuttle</span><span class="font-medium">${esc(item.vehicleType)}</span></div></td>
<td class="px-6 py-5"><div class="flex items-center gap-3"><div class="w-7 h-7 bg-primary-container text-white text-[10px] font-bold rounded-full flex items-center justify-center uppercase">${esc(item.requestorInitials)}</div><span class="font-medium">${esc(item.requestorName)}</span></div></td>
<td class="px-6 py-5"><div class="text-sm font-semibold text-on-surface">${esc(item.dateRangeLabel)}</div><div class="text-[10px] font-bold text-outline uppercase tracking-tight">${esc(item.daysTotalLabel)}</div></td>
<td class="px-6 py-5 text-center"><span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-bold text-sm">${esc(item.dttCount)}</span></td>
<td class="px-6 py-5"><select data-status-url="${esc(item.updateStatusUrl)}" class="dtt-status-select w-full border border-outline-variant rounded-md py-2 px-2 text-sm bg-white">${dttStatusOptions(item.status)}</select></td>
<td class="px-6 py-5 text-right"><div class="flex items-center justify-end gap-2"><a href="${esc(item.printUrl)}" target="_blank" rel="noopener" class="flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-highest text-primary font-bold text-[10px] uppercase rounded-md hover:bg-primary hover:text-white transition-all shadow-sm"><span class="material-symbols-outlined text-[14px]">print</span>Print DTTs</a></div></td>
</tr>`;
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
            throw new Error('Failed to update DTT status');
        }

        await refreshDtt(dttCurrentPage);
    } catch (error) {
        selectEl.value = originalValue;
        console.error('DTT status update failed', error);
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

        dttEls.total.textContent = payload.metrics.totalDtts;
        dttEls.pending.textContent = payload.metrics.pendingDtts;
        dttEls.completed.textContent = payload.metrics.completedDtts;
        dttEls.coaster.textContent = payload.metrics.vehicleTypeCounts.coaster;
        dttEls.van.textContent = payload.metrics.vehicleTypeCounts.van;
        dttEls.pickup.textContent = payload.metrics.vehicleTypeCounts.pickup;

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

syncStatusSelectOriginalValues();

setInterval(() => refreshDtt(dttCurrentPage), 10000);
</script>
</body>
</html>
