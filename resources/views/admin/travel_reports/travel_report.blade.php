<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NIA Travel Portal | Equipment Management System</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-low": "#f2f4f7",
                        "surface-bright": "#f7f9fc",
                        "surface-dim": "#d8dadd",
                        "secondary": "#3a6843",
                        "surface-variant": "#e0e3e6",
                        "on-primary": "#ffffff",
                        "surface-container-high": "#e6e8eb",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-secondary-fixed-variant": "#22502d",
                        "error-container": "#ffdad6",
                        "error": "#ba1a1a",
                        "primary-container": "#1a4b84",
                        "on-surface": "#191c1e",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed-variant": "#144780",
                        "on-secondary-fixed": "#00210a",
                        "on-secondary-container": "#3e6d47",
                        "surface-container-lowest": "#ffffff",
                        "on-error": "#ffffff",
                        "on-secondary": "#ffffff",
                        "on-primary-container": "#93bcfc",
                        "secondary-fixed": "#bcefc0",
                        "inverse-primary": "#a6c8ff",
                        "primary": "#003466",
                        "tertiary-container": "#00554a",
                        "surface": "#f7f9fc",
                        "inverse-on-surface": "#eff1f4",
                        "primary-fixed": "#d5e3ff",
                        "on-background": "#191c1e",
                        "on-tertiary-fixed": "#00201b",
                        "background": "#f7f9fc",
                        "inverse-surface": "#2d3133",
                        "secondary-container": "#b9ecbd",
                        "tertiary-fixed-dim": "#84d5c5",
                        "surface-container-highest": "#e0e3e6",
                        "on-tertiary-fixed-variant": "#005046",
                        "tertiary": "#003c34",
                        "tertiary-fixed": "#a0f2e1",
                        "on-surface-variant": "#424750",
                        "on-primary-fixed": "#001c3b",
                        "on-tertiary-container": "#78caba",
                        "secondary-fixed-dim": "#a0d3a5",
                        "surface-tint": "#335f99",
                        "surface-container": "#eceef1",
                        "on-error-container": "#93000a",
                        "outline-variant": "#c3c6d1",
                        "outline": "#737781"
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
        .report-tab-btn-active {
            color: #003466;
            border-bottom: 2px solid #003466;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.7);
        }
        .report-tab-panel {
            display: none;
        }
        .report-tab-panel-active {
            display: block;
        }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
@include('layouts.admin_header')

@php
$validTabs = ['transportation-requests', 'trip-tickets', 'fuel-issuance', 'performance-evaluations'];
$activeTab = in_array((string) request('tab'), $validTabs, true)
    ? (string) request('tab')
    : 'transportation-requests';
@endphp

<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-10">
<div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
<div>
<p class="text-secondary font-bold tracking-widest text-xs uppercase mb-2">Fleet Management System</p>
<h1 class="text-4xl font-extrabold text-primary tracking-tighter leading-none">Travel Reports &amp; Analytics</h1>
</div>
<div class="flex flex-wrap gap-3">
<div class="flex items-center gap-2 bg-surface-container-lowest text-on-surface px-5 py-2.5 rounded-lg border border-outline-variant/30 font-semibold text-sm">
<span class="material-symbols-outlined text-lg">calendar_today</span>
{{ $dateRangeLabel }}
</div>
<a href="{{ route('admin.travel-reports.export', request()->query()) }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg shadow-primary/10 hover:opacity-90 transition-all">
<span class="material-symbols-outlined text-lg">file_download</span>
Export Full Report
</a>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-6 xl:gap-8 mb-12">
<div class="bg-surface-container-lowest p-6 rounded-xl border-l-4 border-primary">
<p class="text-outline text-xs font-bold uppercase tracking-widest mb-1">Active Requests</p>
<div class="flex items-baseline gap-2">
<span class="text-3xl font-bold text-primary">{{ number_format($activeRequests) }}</span>
<span class="text-secondary text-xs font-semibold">Filtered range</span>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border-l-4 border-secondary">
<p class="text-outline text-xs font-bold uppercase tracking-widest mb-1">Total Fuel Released</p>
<div class="flex items-baseline gap-2">
<span class="text-3xl font-bold text-primary">{{ number_format($totalFuelReleased, 1) }}L</span>
<span class="text-secondary text-xs font-semibold">Recorded issuance</span>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border-l-4 border-tertiary">
<p class="text-outline text-xs font-bold uppercase tracking-widest mb-1">Active Trip Tickets</p>
<div class="flex items-baseline gap-2">
<span class="text-3xl font-bold text-primary">{{ number_format($activeTripTickets) }}</span>
<span class="text-outline text-xs font-semibold">{{ number_format($inTransitTrips) }} In-transit</span>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border-l-4 border-surface-tint">
<p class="text-outline text-xs font-bold uppercase tracking-widest mb-1">Driver Performance</p>
<div class="flex items-baseline gap-2">
<span class="text-3xl font-bold text-primary">{{ number_format($driverPerformanceScore, 2) }}</span>
<span class="text-secondary text-xs font-semibold">{{ $driverPerformanceText }}</span>
</div>
</div>
</div>

<div class="bg-surface-container-low rounded-2xl p-1 shadow-sm overflow-hidden">
<div class="flex border-b border-outline-variant/10 px-4 bg-surface-container-low overflow-x-auto">
<button type="button" data-tab-target="transportation-requests" class="report-tab-btn px-6 py-4 text-sm font-medium transition-all whitespace-nowrap {{ $activeTab === 'transportation-requests' ? 'report-tab-btn-active' : 'text-outline hover:text-primary' }}">Transportation Requests</button>
<button type="button" data-tab-target="trip-tickets" class="report-tab-btn px-6 py-4 text-sm font-medium transition-all whitespace-nowrap {{ $activeTab === 'trip-tickets' ? 'report-tab-btn-active' : 'text-outline hover:text-primary' }}">Trip Tickets</button>
<button type="button" data-tab-target="fuel-issuance" class="report-tab-btn px-6 py-4 text-sm font-medium transition-all whitespace-nowrap {{ $activeTab === 'fuel-issuance' ? 'report-tab-btn-active' : 'text-outline hover:text-primary' }}">Fuel Issuance</button>
<button type="button" data-tab-target="performance-evaluations" class="report-tab-btn px-6 py-4 text-sm font-medium transition-all whitespace-nowrap {{ $activeTab === 'performance-evaluations' ? 'report-tab-btn-active' : 'text-outline hover:text-primary' }}">Performance Evaluations</button>
</div>

<div class="p-6 bg-surface-container-lowest space-y-8">
<form method="GET" action="{{ route('admin.travel-reports') }}" class="flex flex-wrap items-end gap-4 rounded-xl bg-surface-container-low p-4 border border-outline-variant/20">
<input id="report-tab-input" type="hidden" name="tab" value="{{ $activeTab }}">
<div>
<label class="block text-[10px] uppercase tracking-widest text-outline font-bold mb-1">From</label>
<input type="date" name="from" value="{{ $fromDate }}" class="bg-surface-container-high border-none text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary/20">
</div>
<div>
<label class="block text-[10px] uppercase tracking-widest text-outline font-bold mb-1">To</label>
<input type="date" name="to" value="{{ $toDate }}" class="bg-surface-container-high border-none text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary/20">
</div>
<div>
<label class="block text-[10px] uppercase tracking-widest text-outline font-bold mb-1">Status</label>
<select name="status" class="bg-surface-container-high border-none text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary/20 min-w-[180px]">
<option value="all" @selected(strtolower($selectedStatus) === 'all')>All Statuses</option>
@foreach ($statusOptions as $statusOption)
<option value="{{ $statusOption }}" @selected($selectedStatus === $statusOption)>{{ $statusOption }}</option>
@endforeach
</select>
</div>
<div>
<label class="block text-[10px] uppercase tracking-widest text-outline font-bold mb-1">Sort</label>
<select name="sort" class="bg-surface-container-high border-none text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary/20 min-w-[220px]">
<option value="date_desc" @selected($selectedSort === 'date_desc')>Sort by Date (Newest)</option>
<option value="date_asc" @selected($selectedSort === 'date_asc')>Sort by Date (Oldest)</option>
</select>
</div>
<button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold">Apply Filters</button>
<a href="{{ route('admin.travel-reports', ['tab' => $activeTab]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-outline hover:text-primary">Reset</a>
</form>

<div data-tab-panel="transportation-requests" class="report-tab-panel {{ $activeTab === 'transportation-requests' ? 'report-tab-panel-active' : '' }}">
<div class="flex items-center justify-between mb-4">
<p class="text-sm text-outline">Showing {{ $reportRequests->firstItem() ?? 0 }}-{{ $reportRequests->lastItem() ?? 0 }} of {{ $reportRequests->total() }} transportation requests</p>
</div>
<div class="overflow-x-auto rounded-xl border border-outline-variant/20">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Request ID</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Requester</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Destination</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Schedule</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Equipment</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Attachments</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline text-center">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($reportRequests as $item)
@php
$requestorName = (string) ($item->requestor_name ?: $item->requested_by ?: 'N/A');
$nameParts = collect(preg_split('/\s+/', trim($requestorName), -1, PREG_SPLIT_NO_EMPTY) ?: []);
$requestorInitials = $nameParts->isEmpty()
? '--'
: strtoupper(substr((string) $nameParts->first(), 0, 1) . substr((string) $nameParts->last(), 0, 1));

$status = (string) ($item->status ?: 'N/A');
$statusClass = match ($status) {
'Approved' => 'bg-secondary-container text-on-secondary-container',
'Dispatched', 'On Trip' => 'bg-primary-container text-on-primary-container',
'For Evaluation', 'Completed' => 'bg-tertiary-container text-on-tertiary-container',
'Rejected', 'Cancelled' => 'bg-error-container text-on-error-container',
default => 'bg-surface-container-high text-outline',
};
@endphp
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-5 text-sm font-bold text-primary">{{ $item->form_id ?: 'N/A' }}</td>
<td class="px-6 py-5">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-surface-container-high flex items-center justify-center text-xs font-bold text-primary">{{ $requestorInitials }}</div>
<div>
<p class="text-sm font-semibold text-on-surface">{{ $requestorName }}</p>
<p class="text-xs text-outline">{{ $item->requested_by ?: 'N/A' }}</p>
</div>
</div>
</td>
<td class="px-6 py-5 text-sm text-on-surface">{{ $item->destination ?: 'N/A' }}</td>
<td class="px-6 py-5 text-sm text-on-surface">{{ optional($item->date_time_from)->format('M d, h:i A') ?: 'N/A' }}</td>
<td class="px-6 py-5 text-sm text-on-surface">{{ $item->vehicle_id ?: $item->vehicle_type ?: 'N/A' }}</td>
<td class="px-6 py-5 align-top">
@php
    $requestAttachmentLinks = is_array($item->attachment_links ?? null) ? $item->attachment_links : [];
@endphp
@if (count($requestAttachmentLinks) > 0)
<div class="max-w-[280px] space-y-1">
@foreach ($requestAttachmentLinks as $attachment)
@php
    $attachmentName = (string) ($attachment['name'] ?? 'Attachment');
@endphp
<a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="group flex items-center gap-1.5 rounded-md border border-primary/10 bg-primary/5 px-2 py-1.5 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/10 hover:border-primary/20">
<span class="material-symbols-outlined text-sm">attach_file</span>
<span class="truncate" title="{{ $attachmentName }}">{{ $attachmentName }}</span>
<span class="material-symbols-outlined ml-auto text-[13px] text-primary/60 transition-transform duration-200 group-hover:translate-x-0.5">open_in_new</span>
</a>
@endforeach
</div>
@else
<span class="text-xs font-semibold text-outline">No attachment</span>
@endif
</td>
<td class="px-6 py-5 text-center">
<span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusClass }}">{{ $status }}</span>
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-8 text-center text-sm font-semibold text-outline">No transportation request records found for the selected filters.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<div class="flex justify-between items-center mt-6">
@if ($reportRequests->onFirstPage())
<span class="text-sm font-semibold text-outline/60 flex items-center gap-2 px-4 py-2 rounded-lg"><span class="material-symbols-outlined">chevron_left</span> Previous</span>
@else
<a href="{{ $reportRequests->previousPageUrl() }}" class="text-sm font-semibold text-outline hover:text-primary flex items-center gap-2 px-4 py-2 rounded-lg transition-all"><span class="material-symbols-outlined">chevron_left</span> Previous</a>
@endif
<div class="flex gap-2">
@foreach ($reportRequests->getUrlRange(1, $reportRequests->lastPage()) as $page => $url)
@if ($page == $reportRequests->currentPage())
<span class="h-10 w-10 rounded-lg bg-primary text-on-primary font-bold inline-flex items-center justify-center">{{ $page }}</span>
@else
<a href="{{ $url }}" class="h-10 w-10 rounded-lg hover:bg-surface-container-high text-outline font-bold inline-flex items-center justify-center">{{ $page }}</a>
@endif
@endforeach
</div>
@if ($reportRequests->hasMorePages())
<a href="{{ $reportRequests->nextPageUrl() }}" class="text-sm font-semibold text-outline hover:text-primary flex items-center gap-2 px-4 py-2 rounded-lg transition-all">Next <span class="material-symbols-outlined">chevron_right</span></a>
@else
<span class="text-sm font-semibold text-outline/60 flex items-center gap-2 px-4 py-2 rounded-lg">Next <span class="material-symbols-outlined">chevron_right</span></span>
@endif
</div>
</div>

<div data-tab-panel="trip-tickets" class="report-tab-panel {{ $activeTab === 'trip-tickets' ? 'report-tab-panel-active' : '' }}">
<p class="text-sm text-outline mb-4">Showing {{ count($tripTicketsRows) }} trip ticket records in selected range</p>
<div class="overflow-x-auto rounded-xl border border-outline-variant/20">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Request ID</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Date</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Driver</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Vehicle</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Distance (km)</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Fuel Total (L)</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Attachments</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline text-center">Request Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($tripTicketsRows as $ticket)
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4 text-sm font-bold text-primary">{{ $ticket['formId'] }}</td>
<td class="px-6 py-4 text-sm">{{ $ticket['requestDate'] }}</td>
<td class="px-6 py-4 text-sm">{{ $ticket['driverName'] }}</td>
<td class="px-6 py-4 text-sm">{{ $ticket['vehicleId'] }}</td>
<td class="px-6 py-4 text-sm">{{ $ticket['distance'] }}</td>
<td class="px-6 py-4 text-sm">{{ $ticket['fuelTotal'] }}</td>
<td class="px-6 py-4 align-top">
@php
    $ticketAttachmentLinks = is_array($ticket['attachments'] ?? null) ? $ticket['attachments'] : [];
@endphp
@if (count($ticketAttachmentLinks) > 0)
<div class="max-w-[280px] space-y-1">
@foreach ($ticketAttachmentLinks as $attachment)
@php
    $attachmentName = (string) ($attachment['name'] ?? 'Attachment');
@endphp
<a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="group flex items-center gap-1.5 rounded-md border border-primary/10 bg-primary/5 px-2 py-1.5 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/10 hover:border-primary/20">
<span class="material-symbols-outlined text-sm">attach_file</span>
<span class="truncate" title="{{ $attachmentName }}">{{ $attachmentName }}</span>
<span class="material-symbols-outlined ml-auto text-[13px] text-primary/60 transition-transform duration-200 group-hover:translate-x-0.5">open_in_new</span>
</a>
@endforeach
</div>
@else
<span class="text-xs font-semibold text-outline">No attachment</span>
@endif
</td>
<td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-surface-container-high text-outline">{{ $ticket['status'] }}</span></td>
</tr>
@empty
<tr><td colspan="8" class="px-6 py-8 text-center text-sm font-semibold text-outline">No trip tickets found for the selected filters.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

<div data-tab-panel="fuel-issuance" class="report-tab-panel {{ $activeTab === 'fuel-issuance' ? 'report-tab-panel-active' : '' }}">
<p class="text-sm text-outline mb-4">Showing {{ count($fuelIssuanceRows) }} fuel issuance records in selected range</p>
<div class="overflow-x-auto rounded-xl border border-outline-variant/20">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Ctrl No.</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Request ID</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Date</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Driver</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Dealer</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Liters</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Amount</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Attachments</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline text-center">Request Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($fuelIssuanceRows as $issuance)
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4 text-sm font-bold text-primary">{{ $issuance['ctrlNumber'] }}</td>
<td class="px-6 py-4 text-sm">{{ $issuance['formId'] }}</td>
<td class="px-6 py-4 text-sm">{{ $issuance['requestDate'] }}</td>
<td class="px-6 py-4 text-sm">{{ $issuance['driverName'] }}</td>
<td class="px-6 py-4 text-sm">{{ $issuance['dealer'] }}</td>
<td class="px-6 py-4 text-sm">{{ $issuance['liters'] }} L</td>
<td class="px-6 py-4 text-sm">PHP {{ $issuance['totalAmount'] }}</td>
<td class="px-6 py-4 align-top">
@php
    $fuelAttachmentLinks = is_array($issuance['attachments'] ?? null) ? $issuance['attachments'] : [];
@endphp
@if (count($fuelAttachmentLinks) > 0)
<div class="max-w-[280px] space-y-1">
@foreach ($fuelAttachmentLinks as $attachment)
@php
    $attachmentName = (string) ($attachment['name'] ?? 'Attachment');
@endphp
<a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="group flex items-center gap-1.5 rounded-md border border-primary/10 bg-primary/5 px-2 py-1.5 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/10 hover:border-primary/20">
<span class="material-symbols-outlined text-sm">attach_file</span>
<span class="truncate" title="{{ $attachmentName }}">{{ $attachmentName }}</span>
<span class="material-symbols-outlined ml-auto text-[13px] text-primary/60 transition-transform duration-200 group-hover:translate-x-0.5">open_in_new</span>
</a>
@endforeach
</div>
@else
<span class="text-xs font-semibold text-outline">No attachment</span>
@endif
</td>
<td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-surface-container-high text-outline">{{ $issuance['status'] }}</span></td>
</tr>
@empty
<tr><td colspan="9" class="px-6 py-8 text-center text-sm font-semibold text-outline">No fuel issuance records found for the selected filters.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

<div data-tab-panel="performance-evaluations" class="report-tab-panel {{ $activeTab === 'performance-evaluations' ? 'report-tab-panel-active' : '' }}">
<p class="text-sm text-outline mb-4">Showing {{ count($performanceEvaluationRows) }} performance evaluation records in selected range</p>
<div class="overflow-x-auto rounded-xl border border-outline-variant/20">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Request ID</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Date</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Driver</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Evaluation Status</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Rating</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Evaluated At</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline">Attachments</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-outline text-center">Request Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($performanceEvaluationRows as $evaluation)
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-6 py-4 text-sm font-bold text-primary">{{ $evaluation['formId'] }}</td>
<td class="px-6 py-4 text-sm">{{ $evaluation['requestDate'] }}</td>
<td class="px-6 py-4 text-sm">{{ $evaluation['driverName'] }}</td>
<td class="px-6 py-4 text-sm"><span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-secondary-container/30 text-on-secondary-container">{{ $evaluation['evaluationStatus'] }}</span></td>
<td class="px-6 py-4 text-sm">{{ $evaluation['overallRating'] ? $evaluation['overallRating'] . ' / 5.00' : 'Not yet rated' }}</td>
<td class="px-6 py-4 text-sm">{{ $evaluation['evaluatedAt'] }}</td>
<td class="px-6 py-4 align-top">
@php
    $performanceAttachmentLinks = is_array($evaluation['attachments'] ?? null) ? $evaluation['attachments'] : [];
@endphp
@if (count($performanceAttachmentLinks) > 0)
<div class="max-w-[280px] space-y-1">
@foreach ($performanceAttachmentLinks as $attachment)
@php
    $attachmentName = (string) ($attachment['name'] ?? 'Attachment');
@endphp
<a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="group flex items-center gap-1.5 rounded-md border border-primary/10 bg-primary/5 px-2 py-1.5 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/10 hover:border-primary/20">
<span class="material-symbols-outlined text-sm">attach_file</span>
<span class="truncate" title="{{ $attachmentName }}">{{ $attachmentName }}</span>
<span class="material-symbols-outlined ml-auto text-[13px] text-primary/60 transition-transform duration-200 group-hover:translate-x-0.5">open_in_new</span>
</a>
@endforeach
</div>
@else
<span class="text-xs font-semibold text-outline">No attachment</span>
@endif
</td>
<td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-surface-container-high text-outline">{{ $evaluation['requestStatus'] }}</span></td>
</tr>
@empty
<tr><td colspan="8" class="px-6 py-8 text-center text-sm font-semibold text-outline">No performance evaluation records found for the selected filters.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-12">
<div class="lg:col-span-1 bg-surface-container-low rounded-2xl p-6">
<h3 class="text-primary font-bold text-lg mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">stars</span>
Top Rated Drivers
</h3>
<div class="space-y-6">
@forelse ($topDrivers as $driver)
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="h-10 w-10 rounded-full border border-outline-variant bg-surface-container-high text-primary text-xs font-bold flex items-center justify-center">{{ $driver['initials'] }}</div>
<div>
<p class="text-sm font-bold text-on-surface">{{ $driver['name'] }}</p>
<p class="text-xs text-outline">Rating: {{ $driver['scoreLabel'] }}</p>
</div>
</div>
<span class="px-2 py-1 rounded bg-secondary/10 text-secondary text-[10px] font-bold">{{ $driver['badge'] }}</span>
</div>
@empty
<p class="text-sm text-outline">No rated drivers in selected range.</p>
@endforelse
</div>
</div>

<div class="lg:col-span-2 bg-surface-container-low rounded-2xl p-6 relative overflow-hidden group">
<div class="flex justify-between items-center mb-6">
<h3 class="text-primary font-bold text-lg">Fuel Consumption vs Utilization</h3>
<div class="flex gap-2">
<span class="flex items-center gap-1 text-[10px] font-bold uppercase text-primary"><span class="h-2 w-2 rounded-full bg-primary"></span> Consumption</span>
<span class="flex items-center gap-1 text-[10px] font-bold uppercase text-secondary"><span class="h-2 w-2 rounded-full bg-secondary"></span> Distance</span>
</div>
</div>
<div class="h-48 flex items-end gap-3 px-2">
@foreach ($chartBars as $bar)
<div class="flex-1 {{ $bar['type'] === 'fuel' ? 'bg-primary/20 hover:bg-primary' : 'bg-secondary/20 hover:bg-secondary' }} rounded-t-lg transition-all relative group" style="height: {{ $bar['height'] }}%">
<div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-on-surface text-on-primary-container text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">{{ $bar['tooltip'] }}</div>
</div>
@endforeach
</div>
<div class="flex justify-between mt-4 px-2 text-[10px] font-bold text-outline uppercase tracking-wider">
<span>{{ $chartLabels[0] ?? 'N/A' }}</span>
<span>{{ $chartLabels[1] ?? 'N/A' }}</span>
<span>{{ $chartLabels[2] ?? 'N/A' }}</span>
<span>{{ $chartLabels[3] ?? 'N/A' }}</span>
</div>
</div>
</div>
</main>

@include('layouts.admin_footer')

<script>
(function () {
    const tabButtons = Array.from(document.querySelectorAll('.report-tab-btn'));
    const tabPanels = Array.from(document.querySelectorAll('[data-tab-panel]'));
    const tabInput = document.getElementById('report-tab-input');

    function setActiveTab(tabId) {
        tabButtons.forEach(function (button) {
            const isActive = button.getAttribute('data-tab-target') === tabId;
            button.classList.toggle('report-tab-btn-active', isActive);
            button.classList.toggle('text-outline', !isActive);
            button.classList.toggle('hover:text-primary', !isActive);
        });

        tabPanels.forEach(function (panel) {
            const isActive = panel.getAttribute('data-tab-panel') === tabId;
            panel.classList.toggle('report-tab-panel-active', isActive);
        });

        if (tabInput) {
            tabInput.value = tabId;
        }
    }

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const target = button.getAttribute('data-tab-target');
            if (!target) {
                return;
            }

            setActiveTab(target);
        });
    });

    setActiveTab("{{ $activeTab }}");
})();
</script>
</body></html>
