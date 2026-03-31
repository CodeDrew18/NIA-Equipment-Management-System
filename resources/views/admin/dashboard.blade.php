<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Institutional Admin Dashboard | NIA</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "on-secondary-container": "#3e6d47",
              "inverse-surface": "#2d3133",
              "on-tertiary": "#ffffff",
              "on-primary-fixed": "#001c3b",
              "on-error-container": "#93000a",
              "outline-variant": "#c3c6d1",
              "on-secondary": "#ffffff",
              "on-error": "#ffffff",
              "secondary": "#3a6843",
              "primary": "#003466",
              "on-primary-container": "#93bcfc",
              "error-container": "#ffdad6",
              "secondary-container": "#b9ecbd",
              "tertiary-fixed": "#a0f2e1",
              "on-background": "#191c1e",
              "error": "#ba1a1a",
              "surface-container-low": "#f2f4f7",
              "primary-container": "#1a4b84",
              "tertiary-container": "#00554a",
              "surface-variant": "#e0e3e6",
              "inverse-on-surface": "#eff1f4",
              "tertiary": "#003c34",
              "on-surface": "#191c1e",
              "inverse-primary": "#a6c8ff",
              "surface-bright": "#f7f9fc",
              "on-secondary-fixed": "#00210a",
              "surface-tint": "#335f99",
              "surface-container-lowest": "#ffffff",
              "secondary-fixed-dim": "#a0d3a5",
              "on-secondary-fixed-variant": "#22502d",
              "tertiary-fixed-dim": "#84d5c5",
              "surface-dim": "#d8dadd",
              "primary-fixed-dim": "#a6c8ff",
              "on-tertiary-fixed-variant": "#005046",
              "on-tertiary-container": "#78caba",
              "on-primary-fixed-variant": "#144780",
              "secondary-fixed": "#bcefc0",
              "on-tertiary-fixed": "#00201b",
              "on-surface-variant": "#424750",
              "surface-container-high": "#e6e8eb",
              "surface": "#f7f9fc",
              "primary-fixed": "#d5e3ff",
              "outline": "#737781",
              "surface-container-highest": "#e0e3e6",
              "on-primary": "#ffffff",
              "surface-container": "#eceef1",
              "background": "#f7f9fc"
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
            vertical-align: middle;
        }
        body { font-family: 'Public Sans', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
<!-- TopNavBar -->
@include('layouts.admin_header')
<main class="flex-grow pt-24 pb-12 px-8 max-w-[1440px] mx-auto w-full">
@if (session('admin_dashboard_success'))
<div class="mb-6 rounded-lg border border-secondary/30 bg-secondary/10 px-4 py-3 text-sm font-semibold text-secondary">
  {{ session('admin_dashboard_success') }}
</div>
@endif
@if ($errors->has('rejection_reason'))
<div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container">
  {{ $errors->first('rejection_reason') }}
</div>
@endif
<!-- Hero Header -->
<header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
<div>
<h1 class="text-3xl font-extrabold tracking-tight text-primary mb-1">Admin Dashboard</h1>
<p class="text-on-surface-variant font-medium opacity-80 uppercase tracking-widest text-xs">Operational Oversight &amp; Logistics Summary</p>
</div>
<!-- Date Range Picker -->
<form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-3 bg-surface-container-lowest p-2 rounded-xl border border-outline-variant/15 shadow-sm">
<div class="flex flex-col px-3">
<span class="text-[10px] uppercase font-bold text-outline tracking-wider">From</span>
<input class="bg-transparent border-none p-0 text-sm font-semibold focus:ring-0 text-primary" type="date" name="from" value="{{ $fromDate }}"/>
</div>
<div class="h-8 w-[1px] bg-outline-variant/30"></div>
<div class="flex flex-col px-3">
<span class="text-[10px] uppercase font-bold text-outline tracking-wider">To</span>
<input class="bg-transparent border-none p-0 text-sm font-semibold focus:ring-0 text-primary" type="date" name="to" value="{{ $toDate }}"/>
</div>
<button type="submit" class="bg-primary text-on-primary p-2.5 rounded-lg flex items-center justify-center hover:bg-primary-container transition-all" title="Apply filter">
<span class="material-symbols-outlined text-[20px]" data-icon="filter_list">filter_list</span>
</button>
@if ($fromDate || $toDate)
<a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider text-primary hover:bg-primary/5 transition-all">Clear</a>
@endif
</form>
</header>
<!-- Primary Metrics Bento Grid -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-12">
<!-- To Be Signed Requests Card -->
<div class="md:col-span-4 bg-surface-container-lowest p-8 rounded-xl border-l-4 border-primary shadow-[0px_12px_32px_rgba(25,28,30,0.04)] flex flex-col justify-between relative overflow-hidden group">
<div class="absolute -right-4 -top-4 text-primary/5 group-hover:text-primary/10 transition-colors">
<span class="material-symbols-outlined !text-[120px]" data-icon="pending_actions">pending_actions</span>
</div>
<div>
<span class="text-xs font-bold uppercase tracking-widest text-outline block mb-2">To Be Signed Transportation Requests</span>
<div class="flex items-baseline gap-2">
<span id="dashboard-total-pending" class="text-5xl font-black text-primary tracking-tighter">{{ $totalPendingRequests }}</span>
<span id="dashboard-trend-wrap" class="font-bold text-sm flex items-center gap-1 {{ $trendIsPositive ? 'text-secondary' : 'text-error' }}">
<span id="dashboard-trend-icon" class="material-symbols-outlined text-sm" data-icon="{{ $trendIcon }}">{{ $trendIcon }}</span>
<span id="dashboard-trend-value">{{ number_format(abs($trendPercentage), 1) }}%</span>
            </span>
</div>
</div>
<div class="mt-6 flex items-center gap-2 text-xs font-semibold text-on-surface-variant">
<span class="w-2 h-2 rounded-full bg-error"></span>
                    Requires Immediate Review
                </div>
</div>
<!-- Active Trip Tickets Card -->
<div class="md:col-span-5 bg-surface-container-lowest p-8 rounded-xl border-l-4 border-secondary shadow-[0px_12px_32px_rgba(25,28,30,0.04)] flex flex-col justify-between relative overflow-hidden group">
<div class="absolute -right-4 -top-4 text-secondary/5 group-hover:text-secondary/10 transition-colors">
<span class="material-symbols-outlined !text-[120px]" data-icon="confirmation_number">confirmation_number</span>
</div>
<div>
<span class="text-xs font-bold uppercase tracking-widest text-outline block mb-2">Active Trip Tickets (DTT)</span>
<div class="flex items-baseline gap-2">
<span id="dashboard-active-trip-tickets" class="text-5xl font-black text-primary tracking-tighter">{{ $activeTripTickets }}</span>
</div>
</div>
<div class="mt-6">
<div class="flex items-center justify-between mb-2">
<span class="text-[10px] font-bold uppercase text-outline tracking-wider">Filtered Duration Status</span>
<span id="dashboard-active-trip-capacity-label" class="text-[10px] font-bold text-secondary">{{ $activeTripTicketCapacity }}% Capacity</span>
</div>
<div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
<div id="dashboard-active-trip-capacity-bar" class="bg-secondary h-full" style="width: {{ $activeTripTicketCapacity }}%;"></div>
</div>
</div>
</div>
<!-- Quick Overview Feature -->
<div class="md:col-span-3 bg-primary text-on-primary p-8 rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.08)] flex flex-col justify-center items-center text-center relative overflow-hidden">
<div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
<span class="material-symbols-outlined !text-4xl mb-4" data-icon="verified_user">verified_user</span>
<h3 class="text-lg font-bold mb-1">Vehicle Compliance</h3>
<p class="text-xs text-on-primary/70 leading-relaxed">All active vehicles are currently within geo-fence protocols for the selected date range.</p>
</div>
</div>
<!-- Data List Section -->
<section class="bg-surface-container-lowest rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.04)] overflow-hidden border border-outline-variant/10">
<div class="px-8 py-6 border-b border-outline-variant/10 flex items-center justify-between bg-surface-container-low/30">
<h2 class="text-lg font-extrabold text-primary tracking-tight">Recent Transportation Requests</h2>
<div class="flex gap-2">
{{-- <button class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-primary hover:bg-primary/5 rounded-lg transition-all">Export Report</button>
<button class="px-4 py-2 text-xs font-bold uppercase tracking-widest bg-primary text-on-primary rounded-lg shadow-sm hover:shadow-md transition-all">View All</button> --}}
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low/50">
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline">Date</th>
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline">Requestor</th>
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline">Vehicle Type</th>
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline">Vehicle Total</th>
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline">Attachments</th>
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline">Status</th>
<th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-outline text-right">Actions</th>
</tr>
</thead>
<tbody id="dashboard-requests-body" class="divide-y divide-outline-variant/10">
@forelse ($pendingRequests as $transportationRequest)
<tr class="hover:bg-surface-container-low/20 transition-colors">
<td class="px-8 py-5 text-sm font-semibold text-on-surface">{{ optional($transportationRequest->request_date)->format('M d, Y') ?? 'N/A' }}</td>
<td class="px-8 py-5">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
  {{ strtoupper(substr($transportationRequest->requestor_name, 0, 2)) }}
</div>
<div>
<p class="text-sm font-bold text-on-surface">{{ $transportationRequest->requestor_name }}</p>
<p class="text-[10px] text-outline uppercase font-medium">{{ $transportationRequest->requestor_position }}</p>
</div>
</div>
</td>
<td class="px-8 py-5">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-slate-400 text-[18px]" data-icon="minor_crash">minor_crash</span>
<span class="text-sm font-medium text-on-surface-variant">{{ $transportationRequest->vehicle_type }}</span>
</div>
</td>

<td class="px-8 py-5">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-slate-400 text-[18px]" data-icon="minor_crash">minor_crash</span>
<span class="text-sm font-medium text-on-surface-variant">{{ $transportationRequest->vehicle_quantity }}</span>
</div>
</td>
<td class="px-8 py-5">
@php
  $dashboardAttachments = is_array($transportationRequest->attachments) ? $transportationRequest->attachments : [];
@endphp
@if (count($dashboardAttachments) > 0)
  <div class="space-y-1">
    @foreach ($dashboardAttachments as $attachmentIndex => $attachment)
      <a href="{{ route('admin.transportation-request.attachment.view', ['transportationRequest' => $transportationRequest->id, 'index' => $attachmentIndex]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container hover:underline">
        <span class="material-symbols-outlined text-sm">attach_file</span>
        {{ $attachment['file_name'] ?? 'Attachment' }}
      </a>
    @endforeach
  </div>
@else
  <span class="text-xs text-outline">No attachment</span>
@endif
</td>
<td class="px-8 py-5">
<span @class([
  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
  'bg-tertiary-fixed text-on-tertiary-fixed-variant' => $transportationRequest->status === 'Pending',
  'bg-secondary-container text-on-secondary-container' => $transportationRequest->status === 'Signed',
  'bg-error-container text-on-error-container' => $transportationRequest->status === 'Rejected',
  'bg-primary-fixed text-on-primary-fixed-variant' => $transportationRequest->status === 'To be Signed',
])>
  {{ $transportationRequest->status }}
</span>
</td>
<td class="px-8 py-5 text-right">
<div class="flex items-center justify-end gap-2">
  <form action="{{ route('admin.dashboard.requests.update-status', $transportationRequest) }}" method="POST" class="inline">
    @csrf
    <input type="hidden" name="status" value="Signed">
    <button type="submit" class="rounded-md bg-secondary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white hover:opacity-90">
      Approve
    </button>
  </form>
  <form action="{{ route('admin.dashboard.requests.update-status', $transportationRequest) }}" method="POST" class="inline dashboard-reject-form">
    <button
      type="button"
      class="dashboard-open-reject-modal rounded-md bg-error px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white hover:opacity-90"
      data-action="{{ route('admin.dashboard.requests.update-status', $transportationRequest) }}"
      data-form-id="{{ $transportationRequest->form_id }}"
    >
      Reject
    </button>
  </form>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="8" class="px-8 py-8 text-center text-sm font-semibold text-outline">No to be signed transportation requests found.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<div class="px-8 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-between">
<p id="dashboard-summary-text" class="text-[10px] font-bold uppercase text-outline tracking-wider">
  Showing {{ $pendingRequests->count() }} of {{ $pendingRequests->total() }} to be signed entries
</p>
<div id="dashboard-pagination" class="flex gap-1">
@if ($pendingRequests->onFirstPage())
<span class="w-8 h-8 rounded flex items-center justify-center text-outline bg-surface-container-lowest border border-outline-variant/20 opacity-60">
  <span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
</span>
@else
<a href="{{ $pendingRequests->previousPageUrl() }}" class="w-8 h-8 rounded flex items-center justify-center text-primary bg-surface-container-lowest border border-outline-variant/20 hover:bg-surface-container-high transition-colors">
  <span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
</a>
@endif

@foreach ($pendingRequests->getUrlRange(1, $pendingRequests->lastPage()) as $page => $url)
@if ($page == $pendingRequests->currentPage())
<span class="w-8 h-8 rounded flex items-center justify-center bg-primary text-on-primary font-bold text-xs">{{ $page }}</span>
@else
<a href="{{ $url }}" class="w-8 h-8 rounded flex items-center justify-center text-primary font-bold text-xs hover:bg-surface-container-high">{{ $page }}</a>
@endif
@endforeach

@if ($pendingRequests->hasMorePages())
<a href="{{ $pendingRequests->nextPageUrl() }}" class="w-8 h-8 rounded flex items-center justify-center text-primary bg-surface-container-lowest border border-outline-variant/20 hover:bg-surface-container-high transition-colors">
  <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
</a>
@else
<span class="w-8 h-8 rounded flex items-center justify-center text-outline bg-surface-container-lowest border border-outline-variant/20 opacity-60">
  <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
</span>
@endif
</div>
</div>
</section>
</main>

<div id="dashboard-reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
  <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
    <h3 class="text-lg font-bold text-on-surface">Reject Transportation Request</h3>
    <p class="mt-2 text-sm text-on-surface-variant">Are you sure you want to reject <span id="dashboard-reject-form-id" class="font-semibold"></span>?</p>

    <form id="dashboard-reject-form" method="POST" action="" class="mt-5 space-y-4">
      @csrf
      <input type="hidden" name="status" value="Rejected">
      <div>
        <label for="dashboard-rejection-reason" class="block text-xs font-bold uppercase tracking-wider text-outline mb-2">Reason for rejection</label>
        <textarea id="dashboard-rejection-reason" name="rejection_reason" rows="4" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Enter the reason for rejection..." required></textarea>
      </div>
      <div class="flex justify-end gap-3 pt-1">
        <button id="dashboard-reject-cancel" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">Cancel</button>
        <button type="submit" class="rounded-lg bg-error px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-red-700">Confirm Reject</button>
      </div>
    </form>
  </div>
</div>

@include('layouts.admin_footer')
<script>
  const dashboardDataUrl = "{{ route('admin.dashboard.data') }}";
  const dashboardStatusUpdateUrlTemplate = "{{ route('admin.dashboard.requests.update-status', ['transportationRequest' => '__ID__']) }}";
  const dashboardCsrfToken = "{{ csrf_token() }}";

  const dashboardEls = {
    totalPending: document.getElementById('dashboard-total-pending'),
    activeTripTickets: document.getElementById('dashboard-active-trip-tickets'),
    activeTripCapacityLabel: document.getElementById('dashboard-active-trip-capacity-label'),
    activeTripCapacityBar: document.getElementById('dashboard-active-trip-capacity-bar'),
    trendWrap: document.getElementById('dashboard-trend-wrap'),
    trendIcon: document.getElementById('dashboard-trend-icon'),
    trendValue: document.getElementById('dashboard-trend-value'),
    tbody: document.getElementById('dashboard-requests-body'),
    summary: document.getElementById('dashboard-summary-text'),
    pagination: document.getElementById('dashboard-pagination'),
  };

  const dashboardFilterFrom = document.querySelector('input[name="from"]');
  const dashboardFilterTo = document.querySelector('input[name="to"]');
  let dashboardCurrentPage = {{ $pendingRequests->currentPage() }};

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function requestStatusClass(status) {
    if (status === 'Pending') return 'bg-tertiary-fixed text-on-tertiary-fixed-variant';
    if (status === 'Signed') return 'bg-secondary-container text-on-secondary-container';
    if (status === 'Rejected') return 'bg-error-container text-on-error-container';
    return 'bg-primary-fixed text-on-primary-fixed-variant';
  }

  function dashboardRequestRow(item) {
    const actionUrl = dashboardStatusUpdateUrlTemplate.replace('__ID__', item.id);
    const attachments = Array.isArray(item.attachments) ? item.attachments : [];
    const attachmentsHtml = attachments.length > 0
      ? attachments.map(function (attachment) {
          return `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container hover:underline"><span class="material-symbols-outlined text-sm">attach_file</span>${escapeHtml(attachment.name)}</a>`;
        }).join('<br>')
      : '<span class="text-xs text-outline">No attachment</span>';

    return `
<tr class="hover:bg-surface-container-low/20 transition-colors">
  <td class="px-8 py-5 text-sm font-semibold text-on-surface">${escapeHtml(item.requestDate)}</td>
  <td class="px-8 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">${escapeHtml(item.requestorInitials)}</div>
      <div>
        <p class="text-sm font-bold text-on-surface">${escapeHtml(item.requestorName)}</p>
        <p class="text-[10px] text-outline uppercase font-medium">${escapeHtml(item.requestorPosition)}</p>
      </div>
    </div>
  </td>
  <td class="px-8 py-5">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-slate-400 text-[18px]" data-icon="minor_crash">minor_crash</span>
      <span class="text-sm font-medium text-on-surface-variant">${escapeHtml(item.vehicleType)}</span>
    </div>
  </td>
  <td class="px-8 py-5">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-slate-400 text-[18px]" data-icon="minor_crash">minor_crash</span>
      <span class="text-sm font-medium text-on-surface-variant">${escapeHtml(item.vehicleQuantity)}</span>
    </div>
  </td>
  <td class="px-8 py-5">${attachmentsHtml}</td>
  <td class="px-8 py-5">
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${requestStatusClass(item.status)}">${escapeHtml(item.status)}</span>
  </td>
  <td class="px-8 py-5 text-right">
    <div class="flex items-center justify-end gap-2">
      <form action="${actionUrl}" method="POST" class="inline">
        <input type="hidden" name="_token" value="${dashboardCsrfToken}">
        <input type="hidden" name="status" value="Signed">
        <button type="submit" class="rounded-md bg-secondary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white hover:opacity-90">Approve</button>
      </form>
      <form action="${actionUrl}" method="POST" class="inline dashboard-reject-form">
        <button type="button" class="dashboard-open-reject-modal rounded-md bg-error px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white hover:opacity-90" data-action="${escapeHtml(actionUrl)}" data-form-id="${escapeHtml(item.formId || ('Request #' + item.id))}">Reject</button>
      </form>
    </div>
  </td>
</tr>`;
  }

  function dashboardNoRows() {
    return '<tr><td colspan="8" class="px-8 py-8 text-center text-sm font-semibold text-outline">No to be signed transportation requests found.</td></tr>';
  }

  function renderDashboardPagination(pagination) {
    const pageUrls = pagination.pageUrls || {};
    let html = '';

    if (pagination.onFirstPage) {
      html += '<span class="w-8 h-8 rounded flex items-center justify-center text-outline bg-surface-container-lowest border border-outline-variant/20 opacity-60"><span class="material-symbols-outlined text-sm">chevron_left</span></span>';
    } else {
      html += `<button type="button" data-page="${pagination.currentPage - 1}" class="w-8 h-8 rounded flex items-center justify-center text-primary bg-surface-container-lowest border border-outline-variant/20 hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-sm">chevron_left</span></button>`;
    }

    Object.keys(pageUrls).forEach((page) => {
      const pageNumber = Number(page);
      if (pageNumber === pagination.currentPage) {
        html += `<span class="w-8 h-8 rounded flex items-center justify-center bg-primary text-on-primary font-bold text-xs">${pageNumber}</span>`;
      } else {
        html += `<button type="button" data-page="${pageNumber}" class="w-8 h-8 rounded flex items-center justify-center text-primary font-bold text-xs hover:bg-surface-container-high">${pageNumber}</button>`;
      }
    });

    if (pagination.hasMorePages) {
      html += `<button type="button" data-page="${pagination.currentPage + 1}" class="w-8 h-8 rounded flex items-center justify-center text-primary bg-surface-container-lowest border border-outline-variant/20 hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-sm">chevron_right</span></button>`;
    } else {
      html += '<span class="w-8 h-8 rounded flex items-center justify-center text-outline bg-surface-container-lowest border border-outline-variant/20 opacity-60"><span class="material-symbols-outlined text-sm">chevron_right</span></span>';
    }

    dashboardEls.pagination.innerHTML = html;
  }

  async function refreshDashboard(page = dashboardCurrentPage) {
    const params = new URLSearchParams();
    if (dashboardFilterFrom && dashboardFilterFrom.value) {
      params.set('from', dashboardFilterFrom.value);
    }
    if (dashboardFilterTo && dashboardFilterTo.value) {
      params.set('to', dashboardFilterTo.value);
    }
    params.set('page', page);

    try {
      const response = await fetch(`${dashboardDataUrl}?${params.toString()}`, {
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
      dashboardCurrentPage = payload.pagination.currentPage;

      dashboardEls.totalPending.textContent = payload.totalPendingRequests;
      dashboardEls.activeTripTickets.textContent = payload.activeTripTickets;
      dashboardEls.activeTripCapacityLabel.textContent = `${payload.activeTripTicketCapacity}% Capacity`;
      dashboardEls.activeTripCapacityBar.style.width = `${Math.max(0, Math.min(100, Number(payload.activeTripTicketCapacity) || 0))}%`;
      dashboardEls.trendIcon.textContent = payload.trendIcon;
      dashboardEls.trendIcon.setAttribute('data-icon', payload.trendIcon);
      dashboardEls.trendValue.textContent = `${Math.abs(Number(payload.trendPercentage)).toFixed(1)}%`;
      dashboardEls.trendWrap.classList.remove('text-secondary', 'text-error');
      dashboardEls.trendWrap.classList.add(payload.trendIsPositive ? 'text-secondary' : 'text-error');

      if (Array.isArray(payload.requests) && payload.requests.length > 0) {
        dashboardEls.tbody.innerHTML = payload.requests.map(dashboardRequestRow).join('');
      } else {
        dashboardEls.tbody.innerHTML = dashboardNoRows();
      }

      dashboardEls.summary.textContent = payload.summaryText;
      renderDashboardPagination(payload.pagination);
    } catch (error) {
      console.error('Dashboard AJAX refresh failed', error);
    }
  }

  dashboardEls.pagination.addEventListener('click', function (event) {
    const pageButton = event.target.closest('[data-page]');
    if (!pageButton) {
      return;
    }
    event.preventDefault();
    const page = Number(pageButton.getAttribute('data-page'));
    if (page > 0) {
      refreshDashboard(page);
    }
  });

  const dashboardRejectModal = document.getElementById('dashboard-reject-modal');
  const dashboardRejectForm = document.getElementById('dashboard-reject-form');
  const dashboardRejectFormId = document.getElementById('dashboard-reject-form-id');
  const dashboardRejectReason = document.getElementById('dashboard-rejection-reason');
  const dashboardRejectCancel = document.getElementById('dashboard-reject-cancel');

  function openDashboardRejectModal(action, formId) {
    if (!dashboardRejectModal || !dashboardRejectForm || !dashboardRejectFormId || !dashboardRejectReason) {
      return;
    }

    dashboardRejectForm.action = action;
    dashboardRejectFormId.textContent = formId || 'this request';
    dashboardRejectReason.value = '';
    dashboardRejectModal.classList.remove('hidden');
    dashboardRejectModal.classList.add('flex');

    setTimeout(function () {
      dashboardRejectReason.focus();
    }, 0);
  }

  function closeDashboardRejectModal() {
    if (!dashboardRejectModal) {
      return;
    }

    dashboardRejectModal.classList.add('hidden');
    dashboardRejectModal.classList.remove('flex');
  }

  document.addEventListener('click', function (event) {
    const trigger = event.target.closest('.dashboard-open-reject-modal');
    if (!trigger) {
      return;
    }

    openDashboardRejectModal(
      trigger.getAttribute('data-action'),
      trigger.getAttribute('data-form-id')
    );
  });

  if (dashboardRejectCancel) {
    dashboardRejectCancel.addEventListener('click', closeDashboardRejectModal);
  }

  if (dashboardRejectModal) {
    dashboardRejectModal.addEventListener('click', function (event) {
      if (event.target === dashboardRejectModal) {
        closeDashboardRejectModal();
      }
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeDashboardRejectModal();
    }
  });

  setInterval(() => refreshDashboard(dashboardCurrentPage), 1000);
</script>
</body></html>