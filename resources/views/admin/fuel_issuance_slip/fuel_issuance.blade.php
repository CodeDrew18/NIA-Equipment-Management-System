<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Fuel Issuance Slip - National Irrigation Administration</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container": "#eceef1",
                        "primary-fixed": "#d5e3ff",
                        "surface": "#f7f9fc",
                        "tertiary": "#003c34",
                        "surface-container-high": "#e6e8eb",
                        "on-surface": "#191c1e",
                        "primary": "#003466",
                        "on-primary": "#ffffff",
                        "surface-dim": "#d8dadd",
                        "on-tertiary": "#ffffff",
                        "secondary-container": "#b9ecbd",
                        "background": "#f7f9fc",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-secondary-container": "#3e6d47",
                        "surface-container-low": "#f2f4f7",
                        "on-secondary-fixed": "#00210a",
                        "primary-container": "#1a4b84",
                        "secondary-fixed-dim": "#a0d3a5",
                        "on-primary-fixed-variant": "#144780",
                        "on-tertiary-fixed-variant": "#005046",
                        "outline-variant": "#c3c6d1",
                        "on-secondary-fixed-variant": "#22502d",
                        "on-primary-container": "#93bcfc",
                        "on-background": "#191c1e",
                        "outline": "#737781",
                        "on-secondary": "#ffffff",
                        "error": "#ba1a1a",
                        "on-surface-variant": "#424750",
                        "secondary-fixed": "#bcefc0",
                        "on-tertiary-container": "#78caba",
                        "error-container": "#ffdad6",
                        "surface-tint": "#335f99",
                        "on-error": "#ffffff",
                        "on-tertiary-fixed": "#00201b",
                        "inverse-on-surface": "#eff1f4",
                        "inverse-primary": "#a6c8ff",
                        "surface-container-highest": "#e0e3e6",
                        "surface-bright": "#f7f9fc",
                        "on-error-container": "#93000a",
                        "on-primary-fixed": "#001c3b",
                        "inverse-surface": "#2d3133",
                        "surface-variant": "#e0e3e6",
                        "tertiary-container": "#00554a",
                        "secondary": "#3a6843",
                        "tertiary-fixed-dim": "#84d5c5",
                        "tertiary-fixed": "#a0f2e1",
                        "surface-container-lowest": "#ffffff"
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
        .receipt-dashed-border {
            background-image: linear-gradient(to right, #c3c6d1 33%, rgba(255,255,255,0) 0%);
            background-position: bottom;
            background-size: 12px 1px;
            background-repeat: repeat-x;
        }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body min-h-screen">
<!-- TopNavBar -->
@include('layouts.admin_header');
<main class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-12">
@php
    $ctrlNumber = $selectedRequest
        ? 'FIS-' . optional($selectedRequest->request_date)->format('Y') . '-' . str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT)
        : 'FIS-0000-0000';
    $dealerName = '____________________________';
    $divisionManagerName = 'ENGR. EMILIO M. DOMAGAS JR';
    $activeFuelPartnership = $selectedFuelPartnership ?? [
        'id' => null,
        'name' => 'Petron Fuel',
        'validFrom' => now()->toDateString(),
        'validUntil' => now()->addYear()->toDateString(),
        'validityLabel' => '1 year validity',
        'gasolinePricePerLiter' => 0,
        'dieselPricePerLiter' => 0,
        'fuelSavePricePerLiter' => 0,
        'vPowerPricePerLiter' => 0,
    ];

    $partnershipValidFromDisplay = !empty($activeFuelPartnership['validFrom'])
        ? \Illuminate\Support\Carbon::parse((string) $activeFuelPartnership['validFrom'])->format('M d, Y')
        : 'N/A';
    $partnershipValidUntilDisplay = !empty($activeFuelPartnership['validUntil'])
        ? \Illuminate\Support\Carbon::parse((string) $activeFuelPartnership['validUntil'])->format('M d, Y')
        : 'N/A';

    $selectedPayload = [
        'id' => $selectedRequest?->id,
        'ctrlNumber' => $selectedCtrlNumber ?? 'FIS-0000-0000',
        'requestDate' => optional($selectedRequest?->request_date)->format('M d, Y') ?: '________________',
        'vehicleId' => (string) ($selectedRequest?->vehicle_id ?: '____________________________'),
        'driverName' => (string) ($selectedRequest?->driver_name ?: 'N/A'),
        'divisionManagerName' => $divisionManagerName,
        'fuelPartnership' => $activeFuelPartnership,
        'copies' => $selectedCopies ?? [],
    ];
@endphp

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 no-print">
    <div>
        <h1 class="text-3xl font-extrabold text-primary tracking-tight font-headline">Fuel Issuance Slip</h1>
        <p class="text-on-surface-variant mt-1">Copy generated from dispatched transportation requests.</p>
    </div>
</div>

@if (session('admin_fuel_issuance_success'))
<div class="mb-4 rounded-lg border border-secondary/30 bg-secondary/10 px-4 py-3 text-sm font-semibold text-secondary no-print">
    {{ session('admin_fuel_issuance_success') }}
</div>
@endif

@if ($errors->has('fuel_issuance'))
<div class="mb-4 rounded-lg border border-error/20 bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container no-print">
    {{ $errors->first('fuel_issuance') }}
</div>
@endif

<div class="bg-surface-container-low rounded-xl p-4 mb-6 border border-outline-variant/10 no-print">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <form id="fi-filter-form" method="GET" action="{{ route('admin.fuel_issuance_slip') }}" class="flex flex-wrap items-end gap-4 w-full">
            <div class="flex flex-col">
                <label class="text-[10px] font-bold text-outline uppercase mb-1 px-1">Search Dispatched Requests</label>
                <input id="fi-search" name="search" value="{{ $search }}" class="bg-surface-container-lowest border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 min-w-[300px]" placeholder="Request ID, requestor, destination, plate, driver"/>
            </div>
            <div class="flex items-center gap-2 self-end">
                <button class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2 rounded-lg text-sm font-semibold shadow-md transition-all flex items-center gap-2" type="submit">
                    <span class="material-symbols-outlined text-sm">search</span> Search
                </button>
                <a href="{{ route('admin.fuel_issuance_slip') }}" class="bg-surface-container-highest hover:bg-surface-variant text-on-surface-variant px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">filter_list</span> Reset
                </a>
            </div>
        </form>
        <div class="self-end rounded-lg border border-outline-variant/20 bg-surface-container-lowest px-4 py-3 min-w-[190px]">
            <p class="text-[10px] font-bold uppercase tracking-wider text-outline text-right">Total Dispatched</p>
            <p id="fi-total-dispatched-top" class="text-right text-3xl font-black text-primary">{{ $dispatchedRequests->total() }}</p>
        </div>
    </div>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-outline-variant/10 mb-8 no-print">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-surface-container-high/50 text-on-surface-variant text-xs font-bold uppercase tracking-widest border-b border-outline-variant/10">
                <th class="px-6 py-4">Request ID</th>
                <th class="px-6 py-4">Requestor</th>
                <th class="px-6 py-4">Date</th>
                <th class="px-6 py-4">Vehicle</th>
                <th class="px-6 py-4">Driver</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="fi-tbody" class="text-sm divide-y divide-outline-variant/5">
            @forelse ($dispatchedRequests as $item)
            @php
                $isSelectedRow = $selectedRequest && $selectedRequest->id === $item->id;
                $canDispatchVehicle = (bool) ($item->can_dispatch_vehicle ?? false);
            @endphp
            <tr data-request-row="{{ $item->id }}" class="transition-colors {{ $isSelectedRow ? 'bg-primary-fixed/40' : 'hover:bg-surface-container-low' }}">
                <td class="px-6 py-4 font-bold text-primary">{{ $item->form_id }}</td>
                <td class="px-6 py-4">{{ $item->requestor_name }}</td>
                <td class="px-6 py-4">{{ optional($item->request_date)->format('M d, Y') }}</td>
                <td class="px-6 py-4">{{ $item->vehicle_id ?: 'N/A' }}</td>
                <td class="px-6 py-4">{{ $item->driver_name ?: 'N/A' }}</td>
                <td class="px-6 py-4 text-right">
                    <div class="flex flex-col items-end gap-1">
                        <div class="inline-flex items-center gap-2">
                        <button type="button" data-request-id="{{ $item->id }}" class="fi-select-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-primary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-primary-container transition-colors">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            View Copy
                        </button>
                        <button
                            type="button"
                            data-dispatch-url="{{ route('admin.fuel_issuance_slip.dispatch', $item) }}"
                            data-request-id="{{ $item->id }}"
                            data-can-dispatch="{{ $canDispatchVehicle ? '1' : '0' }}"
                            @disabled(!$canDispatchVehicle)
                            class="fi-dispatch-request inline-flex items-center gap-1 px-3 py-2 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $canDispatchVehicle ? 'bg-secondary text-white hover:bg-secondary/90 transition-colors' : 'bg-surface-container-high text-outline cursor-not-allowed opacity-80' }}"
                        >
                            <span class="material-symbols-outlined text-sm">local_shipping</span>
                            Dispatch Vehicle
                        </button>
                        </div>
                        @if (!$canDispatchVehicle)
                        <p class="text-[10px] font-semibold text-outline">Print all copies first.</p>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-outline">No dispatched transportation requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="bg-surface-container-high/30 px-6 py-4 flex items-center justify-between gap-4">
        <p id="fi-summary" class="text-xs font-semibold text-outline">Showing {{ $dispatchedRequests->firstItem() ?? 0 }}-{{ $dispatchedRequests->lastItem() ?? 0 }} of {{ $dispatchedRequests->total() }} dispatched requests</p>
            <div class="flex items-center gap-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Total Dispatched: <span id="fi-total-dispatched" class="text-primary">{{ $dispatchedRequests->total() }}</span></p>
                <div id="fi-pagination" class="flex items-center gap-2">
            @if ($dispatchedRequests->onFirstPage())
            <span class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline opacity-60"><span class="material-symbols-outlined text-sm">chevron_left</span></span>
            @else
            <button type="button" data-page="{{ $dispatchedRequests->currentPage() - 1 }}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline hover:text-primary shadow-sm"><span class="material-symbols-outlined text-sm">chevron_left</span></button>
            @endif
            @foreach ($dispatchedRequests->getUrlRange(1, $dispatchedRequests->lastPage()) as $page => $url)
            @if ($page == $dispatchedRequests->currentPage())
            <span class="w-8 h-8 rounded flex items-center justify-center bg-primary text-white font-bold text-xs shadow-sm">{{ $page }}</span>
            @else
            <button type="button" data-page="{{ $page }}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-on-surface font-bold text-xs hover:bg-surface-container-high transition-colors shadow-sm">{{ $page }}</button>
            @endif
            @endforeach
            @if ($dispatchedRequests->hasMorePages())
            <button type="button" data-page="{{ $dispatchedRequests->currentPage() + 1 }}" class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline hover:text-primary shadow-sm"><span class="material-symbols-outlined text-sm">chevron_right</span></button>
            @else
            <span class="w-8 h-8 rounded flex items-center justify-center bg-white border border-outline-variant text-outline opacity-60"><span class="material-symbols-outlined text-sm">chevron_right</span></span>
            @endif
            </div>
        </div>
    </div>
</div>

<div class="mb-6 rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-6 shadow-sm">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-outline">Fuel Partnership</p>
            <h2 id="fi-partnership-name" class="mt-1 text-2xl font-black tracking-tight text-primary">{{ $activeFuelPartnership['name'] ?? 'Petron Fuel' }}</h2>
            <p id="fi-partnership-validity-label" class="mt-1 text-xs font-bold uppercase tracking-wide text-secondary">{{ $activeFuelPartnership['validityLabel'] ?? '1 year validity' }}</p>
            <p id="fi-partnership-validity-range" class="text-xs font-semibold text-on-surface-variant">{{ $partnershipValidFromDisplay }} - {{ $partnershipValidUntilDisplay }}</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-secondary/30 bg-secondary/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">
            <span class="material-symbols-outlined text-sm">verified</span>
            Active Partnership
        </div>
    </div>
    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-outline-variant/25 bg-surface-container-low px-4 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Gasoline (Extra/Reg)</p>
            <p id="fi-partnership-gasoline-price" class="mt-1 text-lg font-black text-on-surface">PHP {{ number_format((float) ($activeFuelPartnership['gasolinePricePerLiter'] ?? 0), 2) }}/ltr</p>
        </div>
        <div class="rounded-lg border border-outline-variant/25 bg-surface-container-low px-4 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Diesel Fuel</p>
            <p id="fi-partnership-diesel-price" class="mt-1 text-lg font-black text-on-surface">PHP {{ number_format((float) ($activeFuelPartnership['dieselPricePerLiter'] ?? 0), 2) }}/ltr</p>
        </div>
        <div class="rounded-lg border border-outline-variant/25 bg-surface-container-low px-4 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Fuel Save</p>
            <p id="fi-partnership-fuel-save-price" class="mt-1 text-lg font-black text-on-surface">PHP {{ number_format((float) ($activeFuelPartnership['fuelSavePricePerLiter'] ?? 0), 2) }}/ltr</p>
        </div>
        <div class="rounded-lg border border-outline-variant/25 bg-surface-container-low px-4 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">V-Power</p>
            <p id="fi-partnership-v-power-price" class="mt-1 text-lg font-black text-on-surface">PHP {{ number_format((float) ($activeFuelPartnership['vPowerPricePerLiter'] ?? 0), 2) }}/ltr</p>
        </div>
    </div>
</div>

<div class="no-print mb-4 flex justify-end">
    <button id="fi-print-all-button" type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-container disabled:cursor-not-allowed disabled:opacity-60">
        <span class="material-symbols-outlined text-[20px]">print</span>
        Print
    </button>
</div>

<div id="fi-copies-container" class="space-y-6"></div>
</main>

<div id="fi-warning-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
        <div class="mb-4 flex items-center gap-3 text-error">
            <span class="material-symbols-outlined">warning</span>
            <h3 class="text-lg font-bold">Warning</h3>
        </div>
        <p id="fi-warning-modal-text" class="text-sm text-on-surface-variant leading-relaxed">
            Please review the highlighted fields and try again.
        </p>
        <div class="mt-6 flex justify-end">
            <button id="fi-warning-modal-close" type="button" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary/90">Understood</button>
        </div>
    </div>
</div>

<div id="fi-confirm-dispatch-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
        <div class="mb-4 flex items-center gap-3 text-primary">
            <span class="material-symbols-outlined">local_shipping</span>
            <h3 class="text-lg font-bold">Confirm Dispatch</h3>
        </div>
        <p class="text-sm text-on-surface-variant">Are you sure you want to dispatch this vehicle to On Trip Vehicles?</p>
        <div class="mt-6 flex justify-end gap-3">
            <button id="fi-confirm-dispatch-no" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">No</button>
            <button id="fi-confirm-dispatch-yes" type="button" class="rounded-lg bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-secondary/90">Yes</button>
        </div>
    </div>
</div>

<div id="fi-confirm-print-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center space-y-4">
        <h3 class="text-lg font-bold">Confirm Print</h3>
        <p id="fi-confirm-print-message" class="text-sm text-on-surface-variant">Are you sure you want to print?</p>
        <div class="flex justify-center gap-3 pt-2">
            <button id="fi-confirm-print-no" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">No</button>
            <button id="fi-confirm-print-yes" type="button" class="rounded-lg bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-secondary/90">Yes</button>
        </div>
    </div>
</div>

<div id="fi-loading-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center">
        <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
        <p id="fi-loading-modal-text" class="text-sm font-semibold text-on-surface">Preparing your download...</p>
    </div>
</div>

@include('layouts.admin_footer');
<script>
const fiEls = {
    form: document.getElementById('fi-filter-form'),
    search: document.getElementById('fi-search'),
    tbody: document.getElementById('fi-tbody'),
    pagination: document.getElementById('fi-pagination'),
    summary: document.getElementById('fi-summary'),
    totalDispatchedTop: document.getElementById('fi-total-dispatched-top'),
    totalDispatched: document.getElementById('fi-total-dispatched'),
    copiesContainer: document.getElementById('fi-copies-container'),
    warningModal: document.getElementById('fi-warning-modal'),
    warningModalText: document.getElementById('fi-warning-modal-text'),
    warningModalClose: document.getElementById('fi-warning-modal-close'),
    confirmDispatchModal: document.getElementById('fi-confirm-dispatch-modal'),
    confirmDispatchNo: document.getElementById('fi-confirm-dispatch-no'),
    confirmDispatchYes: document.getElementById('fi-confirm-dispatch-yes'),
    confirmPrintModal: document.getElementById('fi-confirm-print-modal'),
    confirmPrintNo: document.getElementById('fi-confirm-print-no'),
    confirmPrintYes: document.getElementById('fi-confirm-print-yes'),
    loadingModal: document.getElementById('fi-loading-modal'),
    loadingModalText: document.getElementById('fi-loading-modal-text'),
    printAllButton: document.getElementById('fi-print-all-button'),
    confirmPrintMessage: document.getElementById('fi-confirm-print-message'),
    partnershipName: document.getElementById('fi-partnership-name'),
    partnershipValidityLabel: document.getElementById('fi-partnership-validity-label'),
    partnershipValidityRange: document.getElementById('fi-partnership-validity-range'),
    partnershipGasolinePrice: document.getElementById('fi-partnership-gasoline-price'),
    partnershipDieselPrice: document.getElementById('fi-partnership-diesel-price'),
    partnershipFuelSavePrice: document.getElementById('fi-partnership-fuel-save-price'),
    partnershipVPowerPrice: document.getElementById('fi-partnership-v-power-price'),
};

const fiDataUrl = "{{ route('admin.fuel_issuance_slip.data') }}";
const fiPrintUrl = "{{ route('admin.fuel_issuance_slip.print') }}";
const fiDispatchUrlTemplate = "{{ route('admin.fuel_issuance_slip.dispatch', ['transportationRequest' => '__ID__']) }}";
const fiCsrfToken = "{{ csrf_token() }}";
const fiDefaultDivisionManager = "{{ $divisionManagerName }}";
const fiInitialFuelPartnership = @json($activeFuelPartnership);
let fiCurrentPage = {{ $dispatchedRequests->currentPage() }};
let fiSelectedRequestId = {{ $selectedRequest?->id ?? 'null' }};
let fiPendingDispatch = null;
let fiPendingPrintAction = null;
let fiCurrentCopies = [];
let fiCopyStateByKey = {};
let fiSelectedPayload = @json($selectedPayload);
let fiFuelPartnership = fiNormalizeFuelPartnership(fiSelectedPayload?.fuelPartnership || fiInitialFuelPartnership);
const fiMetricAnimationFrames = new WeakMap();
const fiPrefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const fiRequiredCopyFields = [
    'dealer',
    'gasoline',
    'gasolinePrice',
    'diesel',
    'dieselPrice',
    'fuelSave',
    'fuelSavePrice',
    'vpower',
    'vpowerPrice',
];

function fiEsc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function fiAnimateToNumber(value) {
    const parsed = Number(String(value ?? '').replace(/[^0-9.-]/g, ''));
    return Number.isFinite(parsed) ? parsed : 0;
}

function fiFormatAnimatedNumber(value, decimals = 0) {
    return Number(value).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

function fiAnimateMetric(element, targetValue, options = {}) {
    if (!element) {
        return;
    }

    const decimals = Number(options.decimals ?? 0);
    const suffix = String(options.suffix ?? '');
    const duration = Number(options.duration ?? 700);
    const numericTarget = Number(targetValue);
    const target = Number.isFinite(numericTarget) ? numericTarget : 0;

    const existingFrameId = fiMetricAnimationFrames.get(element);
    if (existingFrameId) {
        cancelAnimationFrame(existingFrameId);
    }

    const storedValue = Number(element.dataset.countValue);
    const start = Number.isFinite(storedValue) ? storedValue : fiAnimateToNumber(element.textContent);

    if (fiPrefersReducedMotion || duration <= 0 || Math.abs(start - target) < 0.001) {
        element.textContent = `${fiFormatAnimatedNumber(target, decimals)}${suffix}`;
        element.dataset.countValue = String(target);
        return;
    }

    const startedAt = performance.now();

    function tick(now) {
        const progress = Math.min(1, (now - startedAt) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = start + ((target - start) * eased);

        element.textContent = `${fiFormatAnimatedNumber(current, decimals)}${suffix}`;

        if (progress < 1) {
            const frameId = requestAnimationFrame(tick);
            fiMetricAnimationFrames.set(element, frameId);
            return;
        }

        element.textContent = `${fiFormatAnimatedNumber(target, decimals)}${suffix}`;
        element.dataset.countValue = String(target);
        fiMetricAnimationFrames.delete(element);
    }

    const frameId = requestAnimationFrame(tick);
    fiMetricAnimationFrames.set(element, frameId);
}

function fiNormalizeFuelPartnership(partnership) {
    const source = partnership || {};
    const defaultValidFrom = fiInitialFuelPartnership?.validFrom || '';
    const defaultValidUntil = fiInitialFuelPartnership?.validUntil || '';

    return {
        id: source.id ?? null,
        name: String(source.name || 'Petron Fuel'),
        validFrom: String(source.validFrom || defaultValidFrom),
        validUntil: String(source.validUntil || defaultValidUntil),
        validityLabel: String(source.validityLabel || '1 year validity'),
        gasolinePricePerLiter: fiToNumber(source.gasolinePricePerLiter),
        dieselPricePerLiter: fiToNumber(source.dieselPricePerLiter),
        fuelSavePricePerLiter: fiToNumber(source.fuelSavePricePerLiter),
        vPowerPricePerLiter: fiToNumber(source.vPowerPricePerLiter),
    };
}

function fiPriceInputValue(value) {
    const numeric = fiToNumber(value);
    return String(Math.round(numeric * 100) / 100);
}

function fiFormatDateForPartnership(dateValue) {
    const value = String(dateValue || '').trim();
    if (value === '') {
        return 'N/A';
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return parsed.toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
    });
}

function fiRenderFuelPartnershipCard(partnership) {
    const normalized = fiNormalizeFuelPartnership(partnership);

    if (fiEls.partnershipName) {
        fiEls.partnershipName.textContent = normalized.name;
    }
    if (fiEls.partnershipValidityLabel) {
        fiEls.partnershipValidityLabel.textContent = normalized.validityLabel;
    }
    if (fiEls.partnershipValidityRange) {
        fiEls.partnershipValidityRange.textContent = `${fiFormatDateForPartnership(normalized.validFrom)} - ${fiFormatDateForPartnership(normalized.validUntil)}`;
    }
    if (fiEls.partnershipGasolinePrice) {
        fiEls.partnershipGasolinePrice.textContent = `PHP ${fiFormatCurrency(normalized.gasolinePricePerLiter)}/ltr`;
    }
    if (fiEls.partnershipDieselPrice) {
        fiEls.partnershipDieselPrice.textContent = `PHP ${fiFormatCurrency(normalized.dieselPricePerLiter)}/ltr`;
    }
    if (fiEls.partnershipFuelSavePrice) {
        fiEls.partnershipFuelSavePrice.textContent = `PHP ${fiFormatCurrency(normalized.fuelSavePricePerLiter)}/ltr`;
    }
    if (fiEls.partnershipVPowerPrice) {
        fiEls.partnershipVPowerPrice.textContent = `PHP ${fiFormatCurrency(normalized.vPowerPricePerLiter)}/ltr`;
    }

    fiFuelPartnership = normalized;
}

function fiAnimateInitialMetrics() {
    const sourceCounter = fiEls.totalDispatchedTop || fiEls.totalDispatched;
    if (!sourceCounter) {
        return;
    }

    const target = fiAnimateToNumber(sourceCounter.textContent);

    [fiEls.totalDispatchedTop, fiEls.totalDispatched].forEach(function (counterEl) {
        if (!counterEl) {
            return;
        }

        counterEl.dataset.countValue = '0';
        counterEl.textContent = '0';
        fiAnimateMetric(counterEl, target);
    });
}

function fiAnimateDispatchedTotals(value) {
    [fiEls.totalDispatchedTop, fiEls.totalDispatched].forEach(function (counterEl) {
        fiAnimateMetric(counterEl, value);
    });
}

function fiDefaultCopyState() {
    return {
        dealer: String(fiFuelPartnership?.name || ''),
        gasoline: '',
        gasolinePrice: fiPriceInputValue(fiFuelPartnership?.gasolinePricePerLiter),
        diesel: '',
        dieselPrice: fiPriceInputValue(fiFuelPartnership?.dieselPricePerLiter),
        fuelSave: '',
        fuelSavePrice: fiPriceInputValue(fiFuelPartnership?.fuelSavePricePerLiter),
        vpower: '',
        vpowerPrice: fiPriceInputValue(fiFuelPartnership?.vPowerPricePerLiter),
    };
}

function fiResetCopyStates() {
    fiCopyStateByKey = {};
}

function fiGetCopyState(copyKey) {
    if (!fiCopyStateByKey[copyKey]) {
        fiCopyStateByKey[copyKey] = fiDefaultCopyState();
    }

    return fiCopyStateByKey[copyKey];
}

function fiNormalizeCopies(selected) {
    const source = selected || {};
    const copies = Array.isArray(source.copies) ? source.copies : [];

    if (copies.length > 0) {
        return copies.map(function (copy, index) {
            const fallbackKey = `copy_${index + 1}`;
            return {
                copyKey: String(copy.copyKey || fallbackKey),
                copyNumber: Number(copy.copyNumber || (index + 1)),
                ctrlNumber: String(copy.ctrlNumber || source.ctrlNumber || 'FIS-0000-0000'),
                vehicleId: String(copy.vehicleId || source.vehicleId || '____________________________'),
                driverName: String(copy.driverName || source.driverName || 'N/A'),
            };
        });
    }

    return [{
        copyKey: 'copy_1',
        copyNumber: 1,
        ctrlNumber: String(source.ctrlNumber || 'FIS-0000-0000'),
        vehicleId: String(source.vehicleId || '____________________________'),
        driverName: String(source.driverName || 'N/A'),
    }];
}

function fiCopyTotal(copyState) {
    return (fiToNumber(copyState.gasoline) * fiToNumber(copyState.gasolinePrice))
        + (fiToNumber(copyState.diesel) * fiToNumber(copyState.dieselPrice))
        + (fiToNumber(copyState.fuelSave) * fiToNumber(copyState.fuelSavePrice))
        + (fiToNumber(copyState.vpower) * fiToNumber(copyState.vpowerPrice));
}

function fiRenderCopyCard(copy, selectedMeta, isSingleCopy = false) {
    const copyKey = String(copy.copyKey);
    const copyState = fiGetCopyState(copyKey);
    const totalAmount = fiFormatCurrency(fiCopyTotal(copyState));
    const requestDate = String(selectedMeta.requestDate || '________________');
    const divisionManagerName = String(selectedMeta.divisionManagerName || fiDefaultDivisionManager);
    const printButtonLabel = isSingleCopy
        ? 'Print'
        : `Print Copy #${fiEsc(copy.copyNumber)}`;

    return `<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden flex flex-col fi-copy-card" data-copy-key="${fiEsc(copyKey)}">
        <div class="bg-primary px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-white">receipt_long</span>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-primary-fixed/70 font-bold">Document Type</p>
                    <p class="text-white font-bold text-lg">TRANSPORTATION COPY #${fiEsc(copy.copyNumber)}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-widest text-primary-fixed/70 font-bold">Ctrl No.</p>
                <p class="text-white font-mono text-xl font-black">${fiEsc(copy.ctrlNumber)}</p>
            </div>
        </div>
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-semibold text-on-surface">
                <div class="flex items-end gap-2 border-b border-outline-variant/40 pb-1">
                    <span class="whitespace-nowrap">Date:</span>
                    <span>${fiEsc(requestDate)}</span>
                </div>
                <div class="flex items-end gap-2 border-b border-outline-variant/40 pb-1">
                    <span class="whitespace-nowrap">Dealer:</span>
                    <input type="text" placeholder="Enter dealer" class="w-full bg-transparent border-none p-0 text-sm font-semibold text-on-surface focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="dealer" value="${fiEsc(copyState.dealer)}" required readonly/>
                </div>
                <div class="md:col-span-2 flex items-end gap-2 border-b border-outline-variant/40 pb-1">
                    <span class="whitespace-nowrap">Plate No/Property No.:</span>
                    <span>${fiEsc(copy.vehicleId)}</span>
                </div>
            </div>

            <div class="bg-surface-container-low p-6 rounded-xl border border-outline-variant/10">
                <p class="text-xs font-bold text-primary uppercase tracking-widest mb-4">Please issue the following:</p>
                <div class="space-y-4 text-sm font-semibold">
                    <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                        <span class="col-span-5 text-on-surface-variant">GASOLINE (Extra/Reg)</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="gasoline" value="${fiEsc(copyState.gasoline)}" required/>
                        <span class="col-span-1 text-center text-on-surface-variant">@</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="gasolinePrice" value="${fiEsc(copyState.gasolinePrice)}" placeholder="0.00" required/>
                        <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                        <span class="col-span-1 text-right text-on-surface">ltrs</span>
                    </div>
                    <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                        <span class="col-span-5 text-on-surface-variant">DIESEL FUEL</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="diesel" value="${fiEsc(copyState.diesel)}" required/>
                        <span class="col-span-1 text-center text-on-surface-variant">@</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="dieselPrice" value="${fiEsc(copyState.dieselPrice)}" placeholder="0.00" required/>
                        <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                        <span class="col-span-1 text-right text-on-surface">ltrs</span>
                    </div>
                    <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                        <span class="col-span-5 text-on-surface-variant">FUEL SAVE</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="fuelSave" value="${fiEsc(copyState.fuelSave)}" required/>
                        <span class="col-span-1 text-center text-on-surface-variant">@</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="fuelSavePrice" value="${fiEsc(copyState.fuelSavePrice)}" placeholder="0.00" required/>
                        <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                        <span class="col-span-1 text-right text-on-surface">ltrs</span>
                    </div>
                    <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                        <span class="col-span-5 text-on-surface-variant">V-POWER</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="vpower" value="${fiEsc(copyState.vpower)}" required/>
                        <span class="col-span-1 text-center text-on-surface-variant">@</span>
                        <input type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0 fi-copy-input" data-copy-key="${fiEsc(copyKey)}" data-copy-field="vpowerPrice" value="${fiEsc(copyState.vpowerPrice)}" placeholder="0.00" required/>
                        <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                        <span class="col-span-1 text-right text-on-surface">kg/ltrs</span>
                    </div>
                    <div class="flex items-end gap-2 border-b border-outline-variant/40 pb-1 pt-2">
                        <span class="font-bold text-primary uppercase">TOTAL AMOUNT</span>
                        <span class="flex-1 border-b border-dotted border-outline-variant/70"></span>
                        <span class="text-on-surface">PHP</span>
                        <span class="w-24 text-right text-on-surface font-bold fi-copy-total">${fiEsc(totalAmount)}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pt-2">
                <div class="text-center">
                    <div class="h-12 border-b border-on-surface-variant/30 flex items-end justify-center mb-1">
                        <p class="text-sm font-bold">${fiEsc(copy.driverName)}</p>
                    </div>
                    <p class="text-[11px] font-bold text-on-surface uppercase leading-tight">NAME AND SIGNITURE OF DRIVER</p>
                </div>
                <div class="text-center">
                    <div class="h-12 border-b border-on-surface-variant/30 flex items-end justify-center mb-1">
                        <p class="text-sm font-bold">${fiEsc(divisionManagerName)}</p>
                    </div>
                    <p class="text-[11px] font-bold text-on-surface uppercase">Division Manager</p>
                </div>
            </div>

            <div class="pt-2 no-print flex justify-end">
                <button type="button" class="fi-print-copy inline-flex items-center gap-2 bg-white border border-outline-variant px-5 py-2.5 rounded-lg text-primary font-semibold hover:bg-surface-container-low transition-all active:scale-95 shadow-sm" data-copy-key="${fiEsc(copyKey)}">
                    <span class="material-symbols-outlined text-[20px]">print</span>
                    ${printButtonLabel}
                </button>
            </div>
        </div>
        <div class="bg-surface-container mt-auto px-8 py-2 text-[9px] text-on-surface-variant italic text-center uppercase tracking-widest opacity-60">
            Internal Document - Verification Required
        </div>
    </div>`;
}

function fiRenderCopyCards(selected) {
    if (!fiEls.copiesContainer) {
        return;
    }

    const normalizedCopies = fiNormalizeCopies(selected);
    fiCurrentCopies = normalizedCopies;
    const isSingleCopy = normalizedCopies.length === 1;

    if (normalizedCopies.length < 1) {
        fiEls.copiesContainer.innerHTML = '<div class="rounded-xl border border-outline-variant/20 bg-surface-container-low px-6 py-10 text-sm font-semibold text-outline text-center">No transportation copies available for this request.</div>';
        fiUpdatePrintActionButton();
        return;
    }

    fiEls.copiesContainer.innerHTML = normalizedCopies.map(function (copy) {
        return fiRenderCopyCard(copy, selected || {}, isSingleCopy);
    }).join('');

    normalizedCopies.forEach(function (copy) {
        fiValidateCopyFields(copy.copyKey);
    });

    fiUpdatePrintActionButton();
}

function fiFindCopy(copyKey) {
    return fiCurrentCopies.find(function (copy) {
        return String(copy.copyKey) === String(copyKey);
    }) || null;
}

function fiRow(item) {
    const isSelected = Number(item.id) === Number(fiSelectedRequestId);
    const rowClass = isSelected ? 'bg-primary-fixed/40' : 'hover:bg-surface-container-low';
    const dispatchUrl = item.dispatchUrl || fiDispatchUrlTemplate.replace('__ID__', item.id);
    const canDispatchVehicle = Boolean(item.canDispatchVehicle);
    const dispatchButtonClasses = canDispatchVehicle
        ? 'fi-dispatch-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-secondary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-secondary/90 transition-colors'
        : 'fi-dispatch-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-surface-container-high text-outline text-[11px] font-bold uppercase tracking-wider cursor-not-allowed opacity-80';
    const dispatchDisabledAttribute = canDispatchVehicle ? '' : ' disabled aria-disabled="true"';
    const dispatchHint = canDispatchVehicle
        ? ''
        : '<p class="text-[10px] font-semibold text-outline text-right">Print all copies first.</p>';

    return `<tr data-request-row="${fiEsc(item.id)}" class="transition-colors ${rowClass}">
        <td class="px-6 py-4 font-bold text-primary">${fiEsc(item.formId)}</td>
        <td class="px-6 py-4">${fiEsc(item.requestorName)}</td>
        <td class="px-6 py-4">${fiEsc(item.requestDate)}</td>
        <td class="px-6 py-4">${fiEsc(item.vehicleId)}</td>
        <td class="px-6 py-4">${fiEsc(item.driverName)}</td>
        <td class="px-6 py-4 text-right">
            <div class="flex flex-col items-end gap-1">
                <div class="inline-flex items-center gap-2">
                    <button type="button" data-request-id="${fiEsc(item.id)}" class="fi-select-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-primary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-primary-container transition-colors">
                        <span class="material-symbols-outlined text-sm">visibility</span>
                        View Copy
                    </button>
                    <button type="button" data-dispatch-url="${fiEsc(dispatchUrl)}" data-request-id="${fiEsc(item.id)}" data-can-dispatch="${canDispatchVehicle ? '1' : '0'}" class="${dispatchButtonClasses}"${dispatchDisabledAttribute}>
                        <span class="material-symbols-outlined text-sm">local_shipping</span>
                        Dispatch Vehicle
                    </button>
                </div>
                ${dispatchHint}
            </div>
        </td>
    </tr>`;
}

function fiShowWarningModal(message) {
    if (!fiEls.warningModal || !fiEls.warningModalText) {
        return;
    }

    fiEls.warningModalText.textContent = message || 'Please review the highlighted fields and try again.';
    fiEls.warningModal.classList.remove('hidden');
    fiEls.warningModal.classList.add('flex');
}

function fiHideWarningModal() {
    if (!fiEls.warningModal) {
        return;
    }

    fiEls.warningModal.classList.add('hidden');
    fiEls.warningModal.classList.remove('flex');
}

function fiShowConfirmDispatchModal(dispatchUrl, requestId, triggerButton, canDispatchVehicle = true) {
    if (!fiEls.confirmDispatchModal || !dispatchUrl || !requestId) {
        return;
    }

    if (!canDispatchVehicle) {
        fiShowWarningModal('Please print all Fuel Issuance copies first before proceeding to dispatch vehicle.');
        return;
    }

    fiPendingDispatch = {
        dispatchUrl,
        requestId,
        triggerButton,
        canDispatchVehicle,
    };

    fiEls.confirmDispatchModal.classList.remove('hidden');
    fiEls.confirmDispatchModal.classList.add('flex');
}

function fiHideConfirmDispatchModal() {
    fiPendingDispatch = null;
    if (!fiEls.confirmDispatchModal) {
        return;
    }

    fiEls.confirmDispatchModal.classList.add('hidden');
    fiEls.confirmDispatchModal.classList.remove('flex');
}

function fiShowLoadingModal(message = 'Preparing your download...') {
    if (!fiEls.loadingModal) {
        return;
    }

    if (fiEls.loadingModalText) {
        fiEls.loadingModalText.textContent = message;
    }

    fiEls.loadingModal.classList.remove('hidden');
    fiEls.loadingModal.classList.add('flex');
}

function fiHideLoadingModal() {
    if (!fiEls.loadingModal) {
        return;
    }

    fiEls.loadingModal.classList.add('hidden');
    fiEls.loadingModal.classList.remove('flex');
}

function fiValidateCopyFields(copyKey) {
    const card = fiEls.copiesContainer
        ? fiEls.copiesContainer.querySelector(`.fi-copy-card[data-copy-key="${copyKey}"]`)
        : null;

    if (!card) {
        return false;
    }

    let hasError = false;

    fiRequiredCopyFields.forEach(function (fieldName) {
        const input = card.querySelector(`.fi-copy-input[data-copy-field="${fieldName}"]`);
        if (!input) {
            return;
        }

        const value = String(input.value || '').trim();
        const isEmpty = value === '';

        input.classList.toggle('ring-2', isEmpty);
        input.classList.toggle('ring-error/40', isEmpty);

        hasError = hasError || isEmpty;
    });

    return !hasError;
}

function fiValidateAllCopyFields(showWarning = false, warningMessage = 'Required to fill all fields before dispatch.') {
    if (!fiCurrentCopies.length) {
        if (showWarning) {
            fiShowWarningModal('No transportation copies available for this request.');
        }

        return false;
    }

    const hasInvalid = fiCurrentCopies.some(function (copy) {
        return !fiValidateCopyFields(copy.copyKey);
    });

    if (hasInvalid && showWarning) {
        fiShowWarningModal(warningMessage);
    }

    return !hasInvalid;
}

function fiValidateSingleCopyFields(copyKey, showWarning = false, warningMessage = 'Dealer, all fuel quantities, and all fuel prices per liter are required.') {
    const isValid = fiValidateCopyFields(copyKey);
    if (!isValid && showWarning) {
        fiShowWarningModal(warningMessage);
    }

    return isValid;
}

function fiHandleCopyInputChange(event) {
    const input = event.target.closest('.fi-copy-input');
    if (!input) {
        return;
    }

    const copyKey = String(input.getAttribute('data-copy-key') || '');
    const fieldName = String(input.getAttribute('data-copy-field') || '');

    if (copyKey === '' || fieldName === '') {
        return;
    }

    const copyState = fiGetCopyState(copyKey);
    copyState[fieldName] = input.value;

    const card = input.closest('.fi-copy-card');
    if (card) {
        const totalEl = card.querySelector('.fi-copy-total');
        if (totalEl) {
            totalEl.textContent = fiFormatCurrency(fiCopyTotal(copyState));
        }
    }

    fiValidateCopyFields(copyKey);
}

async function fiDispatchRequest(dispatchUrl, requestId, triggerButton, canDispatchVehicle = true) {
    if (!dispatchUrl || !requestId) {
        return;
    }

    if (!canDispatchVehicle) {
        fiShowWarningModal('Please print all Fuel Issuance copies first before proceeding to dispatch vehicle.');
        return;
    }

    if (!fiValidateAllCopyFields(true, 'Required to fill all fields before dispatch.')) {
        return;
    }

    if (triggerButton) {
        triggerButton.disabled = true;
    }

    fiShowLoadingModal('Updating vehicle status...');

    try {
        const response = await fetch(dispatchUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': fiCsrfToken,
            },
            body: JSON.stringify({}),
        });

        const payload = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            throw new Error(payload.message || 'Unable to dispatch vehicle.');
        }

        if (Number(fiSelectedRequestId) === Number(requestId)) {
            fiSelectedRequestId = null;
        }

        await fiRefresh(fiCurrentPage);
    } catch (error) {
        fiShowWarningModal(error.message || 'Unable to dispatch vehicle.');
    } finally {
        fiHideLoadingModal();
        if (triggerButton) {
            triggerButton.disabled = false;
        }
    }
}

function fiApplySelectedState() {
    const selectedId = Number(fiSelectedRequestId);
    fiEls.tbody.querySelectorAll('tr[data-request-row]').forEach((rowEl) => {
        const rowId = Number(rowEl.getAttribute('data-request-row'));
        const isSelected = Number.isFinite(selectedId) && selectedId > 0 && rowId === selectedId;

        rowEl.classList.toggle('bg-primary-fixed/40', isSelected);
        rowEl.classList.toggle('hover:bg-surface-container-low', !isSelected);
    });
}

function fiRenderPagination(pagination) {
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

    fiEls.pagination.innerHTML = html;
}

function fiToNumber(value) {
    const sanitized = String(value ?? '').replaceAll(',', '').trim();
    const parsed = Number(sanitized);
    return Number.isFinite(parsed) ? parsed : 0;
}

function fiFormatCurrency(value) {
    return value.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function fiPrintActionLabel() {
    return fiCurrentCopies.length > 1 ? 'Print All' : 'Print';
}

function fiUpdatePrintActionButton() {
    if (!fiEls.printAllButton) {
        return;
    }

    const hasCopies = fiCurrentCopies.length > 0;
    fiEls.printAllButton.disabled = !hasCopies;
    fiEls.printAllButton.innerHTML = `<span class="material-symbols-outlined text-[20px]">print</span>${fiPrintActionLabel()}`;
}

function fiShowConfirmPrintModal(message = 'Are you sure you want to print?') {
    if (fiEls.confirmPrintMessage) {
        fiEls.confirmPrintMessage.textContent = message;
    }

    fiEls.confirmPrintModal.classList.remove('hidden');
    fiEls.confirmPrintModal.classList.add('flex');
}

function fiHideConfirmPrintModal() {
    fiEls.confirmPrintModal.classList.add('hidden');
    fiEls.confirmPrintModal.classList.remove('flex');
}

async function fiPrintOfficeCopy(copyKey, options = {}) {
    if (!fiSelectedRequestId || !copyKey) {
        return;
    }

    const manageLoading = options.manageLoading !== false;
    const loadingMessage = typeof options.loadingMessage === 'string' && options.loadingMessage.trim() !== ''
        ? options.loadingMessage.trim()
        : 'Preparing your download...';

    const copy = fiFindCopy(copyKey);
    if (!copy) {
        fiShowWarningModal('The selected transportation copy is no longer available.');
        return;
    }

    if (!fiValidateSingleCopyFields(copyKey, true, 'Dealer, all fuel quantities, and all fuel prices per liter are required.')) {
        return;
    }

    const copyState = fiGetCopyState(copyKey);

    const payload = {
        _token: fiCsrfToken,
        request_id: String(fiSelectedRequestId),
        copy_key: String(copy.copyKey),
        vehicle_id: String(copy.vehicleId),
        driver_name: String(copy.driverName),
        dealer: String(copyState.dealer || ''),
        gasoline: String(fiToNumber(copyState.gasoline)),
        diesel: String(fiToNumber(copyState.diesel)),
        fuel_save: String(fiToNumber(copyState.fuelSave)),
        v_power: String(fiToNumber(copyState.vpower)),
        total_amount: String(fiCopyTotal(copyState)),
    };

    const printButton = fiEls.copiesContainer
        ? fiEls.copiesContainer.querySelector(`.fi-print-copy[data-copy-key="${copyKey}"]`)
        : null;

    const iframeId = 'fi-download-frame';
    let frame = document.getElementById(iframeId);
    if (!frame) {
        frame = document.createElement('iframe');
        frame.id = iframeId;
        frame.name = iframeId;
        frame.style.display = 'none';
        document.body.appendChild(frame);
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = fiPrintUrl;
    form.target = iframeId;
    form.style.display = 'none';

    Object.entries(payload).forEach(function ([key, value]) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });

    let isCompleted = false;
    await new Promise(function (resolve) {
        function completeDownloadUI() {
            if (isCompleted) {
                return;
            }

            isCompleted = true;
            if (manageLoading) {
                fiHideLoadingModal();
            }
            if (printButton) {
                printButton.disabled = false;
            }
            window.removeEventListener('focus', handleWindowFocus);
            resolve();
        }

        function handleWindowFocus() {
            completeDownloadUI();
        }

        if (printButton) {
            printButton.disabled = true;
        }
        if (manageLoading) {
            fiShowLoadingModal(loadingMessage);
        }
        window.addEventListener('focus', handleWindowFocus);

        document.body.appendChild(form);
        form.submit();
        form.remove();

        setTimeout(function () {
            completeDownloadUI();
        }, 2000);
    });
}

async function fiPrintAllCopies() {
    if (!fiCurrentCopies.length) {
        fiShowWarningModal('No transportation copies available for this request.');
        return;
    }

    if (!fiValidateAllCopyFields(true, 'Dealer, all fuel quantities, and all fuel prices per liter are required for every transportation copy.')) {
        return;
    }

    if (fiEls.printAllButton) {
        fiEls.printAllButton.disabled = true;
    }

    const totalCopies = fiCurrentCopies.length;
    fiShowLoadingModal(totalCopies > 1
        ? `Preparing ${totalCopies} transportation copy downloads...`
        : 'Preparing your download...');

    try {
        for (let index = 0; index < totalCopies; index += 1) {
            const copy = fiCurrentCopies[index];
            const loadingLabel = totalCopies > 1
                ? `Preparing copy ${index + 1} of ${totalCopies}...`
                : 'Preparing your download...';

            if (fiEls.loadingModalText) {
                fiEls.loadingModalText.textContent = loadingLabel;
            }

            await fiPrintOfficeCopy(copy.copyKey, {
                manageLoading: false,
            });
        }
    } finally {
        fiHideLoadingModal();
        fiUpdatePrintActionButton();
    }
}

async function fiRefresh(page = fiCurrentPage) {
    const params = new URLSearchParams();
    if (fiEls.search.value) params.set('search', fiEls.search.value);
    if (fiSelectedRequestId) params.set('request_id', fiSelectedRequestId);
    params.set('page', page);

    try {
        const response = await fetch(`${fiDataUrl}?${params.toString()}`, {
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
        fiCurrentPage = payload.pagination.currentPage;
        fiSelectedRequestId = payload.selected?.id ?? null;

        if (payload.requests.length > 0) {
            fiEls.tbody.innerHTML = payload.requests.map(fiRow).join('');
        } else {
            fiEls.tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-outline">No dispatched transportation requests found.</td></tr>';
        }

        fiApplySelectedState();

        fiEls.summary.textContent = payload.summaryText;
        fiAnimateDispatchedTotals(Number(payload.totalDispatchedRequests) || 0);
        fiRenderPagination(payload.pagination);

        const selectedPayload = payload.selected || {};
        const nextSelectedId = Number(selectedPayload.id || 0);
        const currentSelectedId = Number(fiSelectedPayload?.id || 0);
        if (nextSelectedId !== currentSelectedId) {
            fiResetCopyStates();
        }

        fiSelectedPayload = selectedPayload;
        fiFuelPartnership = fiNormalizeFuelPartnership(selectedPayload.fuelPartnership || fiInitialFuelPartnership);
        fiRenderFuelPartnershipCard(fiFuelPartnership);
        fiRenderCopyCards(fiSelectedPayload);
    } catch (error) {
        console.error('Fuel issuance AJAX refresh failed', error);
    }
}

fiEls.form.addEventListener('submit', function (event) {
    event.preventDefault();
    fiRefresh(1);
});

fiEls.pagination.addEventListener('click', function (event) {
    const target = event.target.closest('[data-page]');
    if (!target) {
        return;
    }
    event.preventDefault();
    const page = Number(target.getAttribute('data-page'));
    if (page > 0) {
        fiRefresh(page);
    }
});

fiEls.tbody.addEventListener('click', function (event) {
    const selectButton = event.target.closest('.fi-select-request');
    if (selectButton) {
        event.preventDefault();
        fiSelectedRequestId = Number(selectButton.getAttribute('data-request-id'));
        fiApplySelectedState();
        fiRefresh(fiCurrentPage);
        return;
    }

    const dispatchButton = event.target.closest('.fi-dispatch-request');
    if (!dispatchButton) {
        return;
    }

    event.preventDefault();
    const canDispatchVehicle = String(dispatchButton.getAttribute('data-can-dispatch') || '0') === '1';

    if (!canDispatchVehicle) {
        fiShowWarningModal('Please print all Fuel Issuance copies first before proceeding to dispatch vehicle.');
        return;
    }

    fiShowConfirmDispatchModal(
        dispatchButton.getAttribute('data-dispatch-url'),
        Number(dispatchButton.getAttribute('data-request-id')),
        dispatchButton,
        canDispatchVehicle
    );
});

fiEls.copiesContainer.addEventListener('input', fiHandleCopyInputChange);
fiEls.copiesContainer.addEventListener('change', fiHandleCopyInputChange);

fiEls.copiesContainer.addEventListener('click', function (event) {
    const printButton = event.target.closest('.fi-print-copy');
    if (!printButton) {
        return;
    }

    event.preventDefault();
    const copyKey = String(printButton.getAttribute('data-copy-key') || '');
    if (copyKey === '') {
        return;
    }

    if (!fiValidateSingleCopyFields(copyKey, true, 'Dealer, all fuel quantities, and all fuel prices per liter are required.')) {
        return;
    }

    fiPendingPrintAction = {
        mode: 'single',
        copyKey,
    };
    fiShowConfirmPrintModal('Are you sure you want to print this transportation copy?');
});

if (fiEls.printAllButton) {
    fiEls.printAllButton.addEventListener('click', function () {
        if (!fiCurrentCopies.length) {
            fiShowWarningModal('No transportation copies available for this request.');
            return;
        }

        const isMultipleCopies = fiCurrentCopies.length > 1;

        if (isMultipleCopies) {
            if (!fiValidateAllCopyFields(true, 'Dealer, all fuel quantities, and all fuel prices per liter are required for every transportation copy.')) {
                return;
            }

            fiPendingPrintAction = {
                mode: 'all',
            };
            fiShowConfirmPrintModal(`Are you sure you want to print all ${fiCurrentCopies.length} transportation copies?`);
            return;
        }

        const singleCopy = fiCurrentCopies[0];
        if (!singleCopy) {
            fiShowWarningModal('No transportation copy available for this request.');
            return;
        }

        if (!fiValidateSingleCopyFields(singleCopy.copyKey, true, 'Dealer, all fuel quantities, and all fuel prices per liter are required.')) {
            return;
        }

        fiPendingPrintAction = {
            mode: 'single',
            copyKey: String(singleCopy.copyKey),
        };
        fiShowConfirmPrintModal('Are you sure you want to print?');
    });
}

if (fiEls.warningModalClose) {
    fiEls.warningModalClose.addEventListener('click', function () {
        fiHideWarningModal();
    });
}

if (fiEls.warningModal) {
    fiEls.warningModal.addEventListener('click', function (event) {
        if (event.target === fiEls.warningModal) {
            fiHideWarningModal();
        }
    });
}

if (fiEls.confirmDispatchNo) {
    fiEls.confirmDispatchNo.addEventListener('click', function () {
        fiHideConfirmDispatchModal();
    });
}

if (fiEls.confirmDispatchYes) {
    fiEls.confirmDispatchYes.addEventListener('click', function () {
        const pendingDispatch = fiPendingDispatch;
        fiHideConfirmDispatchModal();

        if (!pendingDispatch) {
            return;
        }

        fiDispatchRequest(
            pendingDispatch.dispatchUrl,
            pendingDispatch.requestId,
            pendingDispatch.triggerButton,
            pendingDispatch.canDispatchVehicle
        );
    });
}

if (fiEls.confirmDispatchModal) {
    fiEls.confirmDispatchModal.addEventListener('click', function (event) {
        if (event.target === fiEls.confirmDispatchModal) {
            fiHideConfirmDispatchModal();
        }
    });
}

fiEls.confirmPrintNo.addEventListener('click', function () {
    fiHideConfirmPrintModal();
});

fiEls.confirmPrintYes.addEventListener('click', function () {
    const pendingAction = fiPendingPrintAction;
    fiPendingPrintAction = null;
    fiHideConfirmPrintModal();

    if (!pendingAction) {
        return;
    }

    if (pendingAction.mode === 'all') {
        fiPrintAllCopies();
        return;
    }

    if (pendingAction.mode === 'single' && pendingAction.copyKey) {
        fiPrintOfficeCopy(pendingAction.copyKey);
    }
});

fiEls.confirmPrintModal.addEventListener('click', function (event) {
    if (event.target === fiEls.confirmPrintModal) {
        fiPendingPrintAction = null;
        fiHideConfirmPrintModal();
    }
});
fiRenderFuelPartnershipCard(fiFuelPartnership);
fiRenderCopyCards(fiSelectedPayload || {});
fiAnimateInitialMetrics();
</script>
</body></html>