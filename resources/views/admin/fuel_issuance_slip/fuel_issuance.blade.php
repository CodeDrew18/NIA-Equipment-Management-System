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
<main class="pt-24 pb-12 px-4 md:px-8 max-w-7xl mx-auto">
@php
    $ctrlNumber = $selectedRequest
        ? 'FIS-' . optional($selectedRequest->request_date)->format('Y') . '-' . str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT)
        : 'FIS-0000-0000';
    $dealerName = '____________________________';
    $divisionManagerName = 'ENGR. EMILIO M. DOMAGAS JR';
@endphp

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 no-print">
    <div>
        <h1 class="text-3xl font-extrabold text-primary tracking-tight font-headline">Fuel Issuance Slip</h1>
        <p class="text-on-surface-variant mt-1">Copy generated from dispatched transportation requests.</p>
    </div>
    <button id="fi-print-office-copy" type="button" class="flex items-center gap-2 bg-white border border-outline-variant px-5 py-2.5 rounded-lg text-primary font-semibold hover:bg-surface-container-low transition-all active:scale-95 shadow-sm">
        <span class="material-symbols-outlined text-[20px]">print</span>
        Print Copy
    </button>
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

<div id="fi-validation-message" class="mb-4 hidden rounded-lg border border-error/20 bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container no-print"></div>
<div id="fi-action-message" class="mb-4 hidden rounded-lg px-4 py-3 text-sm font-semibold no-print"></div>

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
            @endphp
            <tr data-request-row="{{ $item->id }}" class="transition-colors {{ $isSelectedRow ? 'bg-primary-fixed/40' : 'hover:bg-surface-container-low' }}">
                <td class="px-6 py-4 font-bold text-primary">{{ $item->form_id }}</td>
                <td class="px-6 py-4">{{ $item->requestor_name }}</td>
                <td class="px-6 py-4">{{ optional($item->request_date)->format('M d, Y') }}</td>
                <td class="px-6 py-4">{{ $item->vehicle_id ?: 'N/A' }}</td>
                <td class="px-6 py-4">{{ $item->driver_name ?: 'N/A' }}</td>
                <td class="px-6 py-4 text-right">
                    <div class="inline-flex items-center gap-2">
                        <button type="button" data-request-id="{{ $item->id }}" class="fi-select-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-primary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-primary-container transition-colors">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            View Copy
                        </button>
                        <button type="button" data-dispatch-url="{{ route('admin.fuel_issuance_slip.dispatch', $item) }}" data-request-id="{{ $item->id }}" class="fi-dispatch-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-secondary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-secondary/90 transition-colors">
                            <span class="material-symbols-outlined text-sm">local_shipping</span>
                            Dispatch Vehicle
                        </button>
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

<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden flex flex-col">
    <div class="bg-primary px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-white">receipt_long</span>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-primary-fixed/70 font-bold">Document Type</p>
                <p class="text-white font-bold text-lg">TRANSPORTATION COPY</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] uppercase tracking-widest text-primary-fixed/70 font-bold">Ctrl No.</p>
            <p id="fi-ctrl-no" class="text-white font-mono text-xl font-black">{{ $ctrlNumber }}</p>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-semibold text-on-surface">
            <div class="flex items-end gap-2 border-b border-outline-variant/40 pb-1">
                <span class="whitespace-nowrap">Date:</span>
                <span id="fi-office-date">{{ optional($selectedRequest?->request_date)->format('M d, Y') ?? '________________' }}</span>
            </div>
            <div class="flex items-end gap-2 border-b border-outline-variant/40 pb-1">
                <span class="whitespace-nowrap">Dealer:</span>
                <input id="fi-dealer" type="text" placeholder="Enter dealer" class="w-full bg-transparent border-none p-0 text-sm font-semibold text-on-surface focus:ring-0" value="" required/>
            </div>
            <div class="md:col-span-2 flex items-end gap-2 border-b border-outline-variant/40 pb-1">
                <span class="whitespace-nowrap">Plate No/Property No.:</span>
                <span id="fi-office-vehicle">{{ $selectedRequest->vehicle_id ?? '____________________________' }}</span>
            </div>
        </div>

        <div class="bg-surface-container-low p-6 rounded-xl border border-outline-variant/10">
            <p class="text-xs font-bold text-primary uppercase tracking-widest mb-4">Please issue the following:</p>
            <div class="space-y-4 text-sm font-semibold">
                <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                    <span class="col-span-5 text-on-surface-variant">GASOLINE (Extra/Reg)</span>
                    <input id="fi-gasoline" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" required/>
                    <span class="col-span-1 text-center text-on-surface-variant">@</span>
                    <input id="fi-gasoline-price" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" placeholder="0.00" required/>
                    <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                    <span class="col-span-1 text-right text-on-surface">ltrs</span>
                </div>
                <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                    <span class="col-span-5 text-on-surface-variant">DIESEL FUEL</span>
                    <input id="fi-diesel" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" required/>
                    <span class="col-span-1 text-center text-on-surface-variant">@</span>
                    <input id="fi-diesel-price" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" placeholder="0.00" required/>
                    <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                    <span class="col-span-1 text-right text-on-surface">ltrs</span>
                </div>
                <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                    <span class="col-span-5 text-on-surface-variant">FUEL SAVE</span>
                    <input id="fi-fuel-save" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" required/>
                    <span class="col-span-1 text-center text-on-surface-variant">@</span>
                    <input id="fi-fuel-save-price" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" placeholder="0.00" required/>
                    <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                    <span class="col-span-1 text-right text-on-surface">ltrs</span>
                </div>
                <div class="grid grid-cols-12 items-end gap-2 border-b border-outline-variant/30 pb-1">
                    <span class="col-span-5 text-on-surface-variant">V-POWER</span>
                    <input id="fi-vpower" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" required/>
                    <span class="col-span-1 text-center text-on-surface-variant">@</span>
                    <input id="fi-vpower-price" type="number" min="0" step="0.01" class="col-span-2 w-full bg-transparent border-none p-0 text-right text-on-surface font-semibold focus:ring-0" value="" placeholder="0.00" required/>
                    <span class="col-span-1 text-on-surface-variant text-xs">PHP/ltr</span>
                    <span class="col-span-1 text-right text-on-surface">kg/ltrs</span>
                </div>
                <div class="flex items-end gap-2 border-b border-outline-variant/40 pb-1 pt-2">
                    <span class="font-bold text-primary uppercase">TOTAL AMOUNT</span>
                    <span class="flex-1 border-b border-dotted border-outline-variant/70"></span>
                    <span class="text-on-surface">PHP</span>
                    <span id="fi-total-amount" class="w-24 text-right text-on-surface font-bold">0.00</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pt-6">
            <div class="text-center">
                <div class="h-12 border-b border-on-surface-variant/30 flex items-end justify-center mb-1">
                    <p id="fi-driver-name" class="text-sm font-bold">{{ $selectedRequest->driver_name ?? 'N/A' }}</p>
                </div>
                <p class="text-[11px] font-bold text-on-surface uppercase leading-tight">NAME AND SIGNITURE OF DRIVER</p>
            </div>
            <div class="text-center">

                <div class="h-12 border-b border-on-surface-variant/30 flex items-end justify-center mb-1">
                    <p id="fi-division-manager-name" class="text-sm font-bold">{{ $divisionManagerName }}</p>
                </div>
                <p class="text-[11px] font-bold text-on-surface uppercase">Division Manager</p>
            </div>
        </div>
    </div>
    <div class="bg-surface-container mt-auto px-8 py-2 text-[9px] text-on-surface-variant italic text-center uppercase tracking-widest opacity-60">
        Internal Document - Verification Required
    </div>
</div>
</main>

<div id="fi-confirm-print-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center space-y-4">
        <h3 class="text-lg font-bold">Confirm Print</h3>
        <p class="text-sm text-on-surface-variant">Are you sure you want to print?</p>
        <div class="flex justify-center gap-3 pt-2">
            <button id="fi-confirm-print-no" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">No</button>
            <button id="fi-confirm-print-yes" type="button" class="rounded-lg bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-secondary/90">Yes</button>
        </div>
    </div>
</div>

<div id="fi-print-loading-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center">
        <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
        <p class="text-sm font-semibold text-on-surface">Preparing your download...</p>
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
    ctrlNo: document.getElementById('fi-ctrl-no'),
    officeDate: document.getElementById('fi-office-date'),
    officeVehicle: document.getElementById('fi-office-vehicle'),
    dealer: document.getElementById('fi-dealer'),
    driverName: document.getElementById('fi-driver-name'),
    divisionManagerName: document.getElementById('fi-division-manager-name'),
    gasoline: document.getElementById('fi-gasoline'),
    gasolinePrice: document.getElementById('fi-gasoline-price'),
    diesel: document.getElementById('fi-diesel'),
    dieselPrice: document.getElementById('fi-diesel-price'),
    fuelSave: document.getElementById('fi-fuel-save'),
    fuelSavePrice: document.getElementById('fi-fuel-save-price'),
    vpower: document.getElementById('fi-vpower'),
    vpowerPrice: document.getElementById('fi-vpower-price'),
    totalAmount: document.getElementById('fi-total-amount'),
    printButton: document.getElementById('fi-print-office-copy'),
    validationMessage: document.getElementById('fi-validation-message'),
    confirmPrintModal: document.getElementById('fi-confirm-print-modal'),
    confirmPrintNo: document.getElementById('fi-confirm-print-no'),
    confirmPrintYes: document.getElementById('fi-confirm-print-yes'),
    printLoadingModal: document.getElementById('fi-print-loading-modal'),
};

const fiDataUrl = "{{ route('admin.fuel_issuance_slip.data') }}";
const fiPrintUrl = "{{ route('admin.fuel_issuance_slip.print') }}";
const fiDispatchUrlTemplate = "{{ route('admin.fuel_issuance_slip.dispatch', ['transportationRequest' => '__ID__']) }}";
const fiCsrfToken = "{{ csrf_token() }}";
let fiCurrentPage = {{ $dispatchedRequests->currentPage() }};
let fiSelectedRequestId = {{ $selectedRequest?->id ?? 'null' }};
const fiMetricAnimationFrames = new WeakMap();
const fiPrefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

function fiRow(item) {
    const isSelected = Number(item.id) === Number(fiSelectedRequestId);
    const rowClass = isSelected ? 'bg-primary-fixed/40' : 'hover:bg-surface-container-low';
    const dispatchUrl = item.dispatchUrl || fiDispatchUrlTemplate.replace('__ID__', item.id);

    return `<tr data-request-row="${fiEsc(item.id)}" class="transition-colors ${rowClass}">
        <td class="px-6 py-4 font-bold text-primary">${fiEsc(item.formId)}</td>
        <td class="px-6 py-4">${fiEsc(item.requestorName)}</td>
        <td class="px-6 py-4">${fiEsc(item.requestDate)}</td>
        <td class="px-6 py-4">${fiEsc(item.vehicleId)}</td>
        <td class="px-6 py-4">${fiEsc(item.driverName)}</td>
        <td class="px-6 py-4 text-right">
            <div class="inline-flex items-center gap-2">
                <button type="button" data-request-id="${fiEsc(item.id)}" class="fi-select-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-primary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-primary-container transition-colors">
                    <span class="material-symbols-outlined text-sm">visibility</span>
                    View Copy
                </button>
                <button type="button" data-dispatch-url="${fiEsc(dispatchUrl)}" data-request-id="${fiEsc(item.id)}" class="fi-dispatch-request inline-flex items-center gap-1 px-3 py-2 rounded-md bg-secondary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-secondary/90 transition-colors">
                    <span class="material-symbols-outlined text-sm">local_shipping</span>
                    Dispatch Vehicle
                </button>
            </div>
        </td>
    </tr>`;
}

function fiShowActionMessage(message, type = 'success') {
    const messageEl = document.getElementById('fi-action-message');
    if (!messageEl) {
        return;
    }

    messageEl.textContent = message;
    messageEl.classList.remove('hidden', 'border-secondary/30', 'bg-secondary/10', 'text-secondary', 'border-error/20', 'bg-error-container', 'text-on-error-container');

    if (type === 'error') {
        messageEl.classList.add('border', 'border-error/20', 'bg-error-container', 'text-on-error-container');
    } else {
        messageEl.classList.add('border', 'border-secondary/30', 'bg-secondary/10', 'text-secondary');
    }

    clearTimeout(fiShowActionMessage.timerId);
    fiShowActionMessage.timerId = setTimeout(function () {
        messageEl.classList.add('hidden');
    }, 2500);
}

async function fiDispatchRequest(dispatchUrl, requestId, triggerButton) {
    if (!dispatchUrl || !requestId) {
        return;
    }

    const shouldDispatch = window.confirm('Dispatch this vehicle to On Trip Vehicles?');
    if (!shouldDispatch) {
        return;
    }

    if (triggerButton) {
        triggerButton.disabled = true;
    }

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

        fiShowActionMessage(payload.message || 'Vehicle dispatched successfully.');
        await fiRefresh(fiCurrentPage);
    } catch (error) {
        fiShowActionMessage(error.message || 'Unable to dispatch vehicle.', 'error');
    } finally {
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

function fiUpdateOfficeCopy(selected) {
    fiEls.ctrlNo.textContent = selected.ctrlNumber || 'FIS-0000-0000';
    fiEls.officeDate.textContent = selected.requestDate || '________________';
    fiEls.officeVehicle.textContent = selected.vehicleId || '____________________________';
    fiEls.driverName.textContent = selected.driverName || 'N/A';
    fiEls.divisionManagerName.textContent = selected.divisionManagerName || 'ENGR. EMILIO M. DOMAGAS JR';
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

function fiRecalculateTotal() {
    const total = (fiToNumber(fiEls.gasoline.value) * fiToNumber(fiEls.gasolinePrice.value))
        + (fiToNumber(fiEls.diesel.value) * fiToNumber(fiEls.dieselPrice.value))
        + (fiToNumber(fiEls.fuelSave.value) * fiToNumber(fiEls.fuelSavePrice.value))
        + (fiToNumber(fiEls.vpower.value) * fiToNumber(fiEls.vpowerPrice.value));

    fiEls.totalAmount.textContent = fiFormatCurrency(total);
}

function fiValidateRequiredFields() {
    const requiredFields = [
        fiEls.dealer,
        fiEls.gasoline,
        fiEls.gasolinePrice,
        fiEls.diesel,
        fiEls.dieselPrice,
        fiEls.fuelSave,
        fiEls.fuelSavePrice,
        fiEls.vpower,
        fiEls.vpowerPrice,
    ];
    let hasError = false;

    requiredFields.forEach(function (field) {
        if (!field) {
            return;
        }

        const value = String(field.value || '').trim();
        const isEmpty = value === '';
        field.classList.toggle('ring-2', isEmpty);
        field.classList.toggle('ring-error/40', isEmpty);
        hasError = hasError || isEmpty;
    });

    if (hasError) {
        fiEls.validationMessage.textContent = 'Dealer, all fuel quantities, and all fuel prices per liter are required.';
        fiEls.validationMessage.classList.remove('hidden');
        return false;
    }

    fiEls.validationMessage.textContent = '';
    fiEls.validationMessage.classList.add('hidden');

    return true;
}

function fiShowConfirmPrintModal() {
    fiEls.confirmPrintModal.classList.remove('hidden');
    fiEls.confirmPrintModal.classList.add('flex');
}

function fiHideConfirmPrintModal() {
    fiEls.confirmPrintModal.classList.add('hidden');
    fiEls.confirmPrintModal.classList.remove('flex');
}

function fiShowPrintLoadingModal() {
    fiEls.printLoadingModal.classList.remove('hidden');
    fiEls.printLoadingModal.classList.add('flex');
}

function fiHidePrintLoadingModal() {
    fiEls.printLoadingModal.classList.add('hidden');
    fiEls.printLoadingModal.classList.remove('flex');
}

async function fiPrintOfficeCopy() {
    if (!fiSelectedRequestId) {
        return;
    }

    const payload = {
        _token: fiCsrfToken,
        request_id: String(fiSelectedRequestId),
        dealer: fiEls.dealer ? fiEls.dealer.value : '',
        gasoline: String(fiToNumber(fiEls.gasoline.value)),
        diesel: String(fiToNumber(fiEls.diesel.value)),
        fuel_save: String(fiToNumber(fiEls.fuelSave.value)),
        v_power: String(fiToNumber(fiEls.vpower.value)),
        total_amount: String(fiToNumber(fiEls.totalAmount.textContent)),
    };

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
    function completeDownloadUI() {
        if (isCompleted) {
            return;
        }

        isCompleted = true;
        fiHidePrintLoadingModal();
        fiEls.printButton.disabled = false;
        window.removeEventListener('focus', handleWindowFocus);
    }

    function handleWindowFocus() {
        completeDownloadUI();
    }

    fiEls.printButton.disabled = true;
    fiShowPrintLoadingModal();
    window.addEventListener('focus', handleWindowFocus);

    document.body.appendChild(form);
    form.submit();
    form.remove();

    setTimeout(function () {
        completeDownloadUI();
    }, 2000);
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
        fiUpdateOfficeCopy(payload.selected || {});
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
    fiDispatchRequest(
        dispatchButton.getAttribute('data-dispatch-url'),
        Number(dispatchButton.getAttribute('data-request-id')),
        dispatchButton
    );
});

fiEls.printButton.addEventListener('click', function () {
    if (!fiValidateRequiredFields()) {
        return;
    }
    fiShowConfirmPrintModal();
});

fiEls.confirmPrintNo.addEventListener('click', function () {
    fiHideConfirmPrintModal();
});

fiEls.confirmPrintYes.addEventListener('click', function () {
    fiHideConfirmPrintModal();
    fiPrintOfficeCopy();
});

fiEls.confirmPrintModal.addEventListener('click', function (event) {
    if (event.target === fiEls.confirmPrintModal) {
        fiHideConfirmPrintModal();
    }
});

['input', 'change'].forEach(function (eventName) {
    fiEls.gasoline.addEventListener(eventName, fiRecalculateTotal);
    fiEls.gasolinePrice.addEventListener(eventName, fiRecalculateTotal);
    fiEls.diesel.addEventListener(eventName, fiRecalculateTotal);
    fiEls.dieselPrice.addEventListener(eventName, fiRecalculateTotal);
    fiEls.fuelSave.addEventListener(eventName, fiRecalculateTotal);
    fiEls.fuelSavePrice.addEventListener(eventName, fiRecalculateTotal);
    fiEls.vpower.addEventListener(eventName, fiRecalculateTotal);
    fiEls.vpowerPrice.addEventListener(eventName, fiRecalculateTotal);
    fiEls.dealer.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.gasoline.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.gasolinePrice.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.diesel.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.dieselPrice.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.fuelSave.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.fuelSavePrice.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.vpower.addEventListener(eventName, fiValidateRequiredFields);
    fiEls.vpowerPrice.addEventListener(eventName, fiValidateRequiredFields);
});

fiRecalculateTotal();
fiAnimateInitialMetrics();
</script>
</body></html>