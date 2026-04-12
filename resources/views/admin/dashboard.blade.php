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
<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-12">
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
<form id="dashboard-filter-form" method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-3 bg-surface-container-lowest p-2 rounded-xl border border-outline-variant/15 shadow-sm">
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
<a id="dashboard-filter-clear" href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider text-primary hover:bg-primary/5 transition-all {{ ($fromDate || $toDate) ? '' : 'hidden' }}">Clear</a>
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
<!-- All Admin Process Summary -->
<section class="mb-10">
<div class="mb-4 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
<div>
<h2 class="text-xl font-extrabold tracking-tight text-primary">All Admin Process Summary</h2>
<p class="text-xs font-semibold uppercase tracking-wider text-outline">Live totals across every admin process page</p>
</div>
</div>
<div id="dashboard-module-summary-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
@foreach ($moduleSummaries as $summary)
<article class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-5 shadow-[0px_8px_24px_rgba(25,28,30,0.04)]">
<div class="flex items-start justify-between gap-3">
<p class="text-[11px] font-bold uppercase tracking-widest text-outline">{{ $summary['label'] }}</p>
<span class="material-symbols-outlined text-primary/70 text-[20px]" data-icon="{{ $summary['icon'] }}">{{ $summary['icon'] }}</span>
</div>
<p class="mt-4 text-3xl font-black tracking-tight text-primary">{{ number_format((int) ($summary['value'] ?? 0)) }}</p>
<p class="mt-2 text-xs font-semibold text-on-surface-variant">{{ $summary['description'] }}</p>
</article>
@endforeach
</div>
</section>
<!-- Analytics Graphs -->
<section class="mb-12 grid grid-cols-1 xl:grid-cols-12 gap-6">
<div class="xl:col-span-7 rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-6 shadow-[0px_10px_28px_rgba(25,28,30,0.04)]">
<h3 class="text-sm font-extrabold uppercase tracking-wider text-primary mb-4">Request Lifecycle Graph</h3>
<div class="h-[300px]">
<canvas id="dashboard-process-chart"></canvas>
</div>
</div>
<div class="xl:col-span-5 rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-6 shadow-[0px_10px_28px_rgba(25,28,30,0.04)]">
<h3 class="text-sm font-extrabold uppercase tracking-wider text-primary mb-4">Vehicle Status Graph</h3>
<div class="h-[300px]">
<canvas id="dashboard-vehicle-chart"></canvas>
</div>
</div>
<div class="xl:col-span-12 rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-6 shadow-[0px_10px_28px_rgba(25,28,30,0.04)]">
<h3 class="text-sm font-extrabold uppercase tracking-wider text-primary mb-4">Request Volume Graph</h3>
<div class="h-[280px]">
<canvas id="dashboard-volume-chart"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  const dashboardDataUrl = "{{ route('admin.dashboard.data') }}";
  const dashboardStatusUpdateUrlTemplate = "{{ route('admin.dashboard.requests.update-status', ['transportationRequest' => '__ID__']) }}";
  const dashboardCsrfToken = "{{ csrf_token() }}";
  const dashboardInitialModuleSummaries = @json($moduleSummaries);
  const dashboardInitialCharts = @json($charts);
  const dashboardChartPalette = ['#1a4b84', '#2563eb', '#0ea5e9', '#3a6843', '#f59e0b', '#ef4444', '#6b7280'];

  const dashboardEls = {
    totalPending: document.getElementById('dashboard-total-pending'),
    activeTripTickets: document.getElementById('dashboard-active-trip-tickets'),
    activeTripCapacityLabel: document.getElementById('dashboard-active-trip-capacity-label'),
    activeTripCapacityBar: document.getElementById('dashboard-active-trip-capacity-bar'),
    trendWrap: document.getElementById('dashboard-trend-wrap'),
    trendIcon: document.getElementById('dashboard-trend-icon'),
    trendValue: document.getElementById('dashboard-trend-value'),
    moduleSummaryGrid: document.getElementById('dashboard-module-summary-grid'),
    processChartCanvas: document.getElementById('dashboard-process-chart'),
    vehicleChartCanvas: document.getElementById('dashboard-vehicle-chart'),
    volumeChartCanvas: document.getElementById('dashboard-volume-chart'),
    filterForm: document.getElementById('dashboard-filter-form'),
    filterClear: document.getElementById('dashboard-filter-clear'),
    tbody: document.getElementById('dashboard-requests-body'),
    summary: document.getElementById('dashboard-summary-text'),
    pagination: document.getElementById('dashboard-pagination'),
  };

  const dashboardFilterFrom = document.querySelector('input[name="from"]');
  const dashboardFilterTo = document.querySelector('input[name="to"]');
  let dashboardCurrentPage = {{ $pendingRequests->currentPage() }};
  const dashboardCharts = {
    process: null,
    vehicle: null,
    volume: null,
  };
  const dashboardModuleSummaryValues = {};
  const dashboardCountAnimationFrames = new WeakMap();
  const dashboardPrefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function dashboardToNumber(value) {
    const parsed = Number(String(value ?? '').replace(/[^0-9.-]/g, ''));
    return Number.isFinite(parsed) ? parsed : 0;
  }

  function dashboardFormatNumber(value, decimals = 0) {
    return Number(value).toLocaleString('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  }

  function animateDashboardMetric(element, targetValue, options = {}) {
    if (!element) {
      return;
    }

    const decimals = Number(options.decimals ?? 0);
    const suffix = String(options.suffix ?? '');
    const duration = Number(options.duration ?? 700);
    const numericTarget = Number(targetValue);
    const target = Number.isFinite(numericTarget) ? numericTarget : 0;

    const existingFrameId = dashboardCountAnimationFrames.get(element);
    if (existingFrameId) {
      cancelAnimationFrame(existingFrameId);
    }

    const storedValue = Number(element.dataset.countValue);
    const start = Number.isFinite(storedValue) ? storedValue : dashboardToNumber(element.textContent);

    if (dashboardPrefersReducedMotion || duration <= 0 || Math.abs(start - target) < 0.001) {
      element.textContent = `${dashboardFormatNumber(target, decimals)}${suffix}`;
      element.dataset.countValue = String(target);
      return;
    }

    const startedAt = performance.now();

    function tick(now) {
      const progress = Math.min(1, (now - startedAt) / duration);
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = start + ((target - start) * eased);

      element.textContent = `${dashboardFormatNumber(current, decimals)}${suffix}`;

      if (progress < 1) {
        const frameId = requestAnimationFrame(tick);
        dashboardCountAnimationFrames.set(element, frameId);
        return;
      }

      element.textContent = `${dashboardFormatNumber(target, decimals)}${suffix}`;
      element.dataset.countValue = String(target);
      dashboardCountAnimationFrames.delete(element);
    }

    const frameId = requestAnimationFrame(tick);
    dashboardCountAnimationFrames.set(element, frameId);
  }

  function animateDashboardInitialMetrics() {
    const initialPending = dashboardToNumber(dashboardEls.totalPending?.textContent);
    const initialActiveTrips = dashboardToNumber(dashboardEls.activeTripTickets?.textContent);
    const initialTrend = dashboardToNumber(dashboardEls.trendValue?.textContent);
    const initialCapacity = dashboardToNumber(dashboardEls.activeTripCapacityLabel?.textContent);

    if (dashboardEls.totalPending) {
      dashboardEls.totalPending.dataset.countValue = '0';
      dashboardEls.totalPending.textContent = '0';
      animateDashboardMetric(dashboardEls.totalPending, initialPending);
    }

    if (dashboardEls.activeTripTickets) {
      dashboardEls.activeTripTickets.dataset.countValue = '0';
      dashboardEls.activeTripTickets.textContent = '0';
      animateDashboardMetric(dashboardEls.activeTripTickets, initialActiveTrips);
    }

    if (dashboardEls.trendValue) {
      dashboardEls.trendValue.dataset.countValue = '0';
      dashboardEls.trendValue.textContent = '0.0%';
      animateDashboardMetric(dashboardEls.trendValue, initialTrend, { decimals: 1, suffix: '%' });
    }

    if (dashboardEls.activeTripCapacityLabel) {
      dashboardEls.activeTripCapacityLabel.dataset.countValue = '0';
      dashboardEls.activeTripCapacityLabel.textContent = '0% Capacity';
      animateDashboardMetric(dashboardEls.activeTripCapacityLabel, initialCapacity, { suffix: '% Capacity' });
    }

    if (dashboardEls.activeTripCapacityBar) {
      dashboardEls.activeTripCapacityBar.style.transition = 'width 700ms ease';
      dashboardEls.activeTripCapacityBar.style.width = '0%';

      requestAnimationFrame(function () {
        const clampedCapacity = Math.max(0, Math.min(100, initialCapacity));
        dashboardEls.activeTripCapacityBar.style.width = `${clampedCapacity}%`;
      });
    }
  }

  function dashboardColorAt(index) {
    return dashboardChartPalette[index % dashboardChartPalette.length];
  }

  function renderDashboardModuleSummaries(moduleSummaries) {
    if (!dashboardEls.moduleSummaryGrid || !Array.isArray(moduleSummaries)) {
      return;
    }

    const previousValues = { ...dashboardModuleSummaryValues };

    dashboardEls.moduleSummaryGrid.innerHTML = moduleSummaries.map(function (summary, index) {
      const numericValue = Number(summary?.value);
      const safeValue = Number.isFinite(numericValue) ? numericValue : 0;
      const summaryKey = String(summary?.key || `summary_${index}`);
      const previousValueRaw = Number(previousValues[summaryKey]);
      const previousValue = Number.isFinite(previousValueRaw) ? previousValueRaw : 0;
      const label = escapeHtml(summary?.label || 'Summary');
      const icon = escapeHtml(summary?.icon || 'monitoring');
      const description = escapeHtml(summary?.description || '');

      return `<article class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-5 shadow-[0px_8px_24px_rgba(25,28,30,0.04)]">
<div class="flex items-start justify-between gap-3">
<p class="text-[11px] font-bold uppercase tracking-widest text-outline">${label}</p>
<span class="material-symbols-outlined text-primary/70 text-[20px]" data-icon="${icon}">${icon}</span>
</div>
<p class="dashboard-module-summary-value mt-4 text-3xl font-black tracking-tight text-primary" data-summary-index="${index}" data-count-value="${previousValue}">${dashboardFormatNumber(previousValue)}</p>
<p class="mt-2 text-xs font-semibold text-on-surface-variant">${description}</p>
</article>`;
    }).join('');

    moduleSummaries.forEach(function (summary, index) {
      const summaryKey = String(summary?.key || `summary_${index}`);
      const numericValue = Number(summary?.value);
      const safeValue = Number.isFinite(numericValue) ? numericValue : 0;
      dashboardModuleSummaryValues[summaryKey] = safeValue;

      const valueElement = dashboardEls.moduleSummaryGrid.querySelector(`.dashboard-module-summary-value[data-summary-index="${index}"]`);
      animateDashboardMetric(valueElement, safeValue);
    });
  }

  function normalizeDashboardSeries(series) {
    const labels = Array.isArray(series?.labels) ? series.labels : [];
    const values = Array.isArray(series?.values)
      ? series.values.map(function (value) {
          const numericValue = Number(value);
          return Number.isFinite(numericValue) ? numericValue : 0;
        })
      : [];

    return { labels, values };
  }

  function upsertDashboardProcessChart(series) {
    if (!dashboardEls.processChartCanvas || typeof Chart === 'undefined') {
      return;
    }

    const normalizedSeries = normalizeDashboardSeries(series);
    const backgroundColors = normalizedSeries.values.map(function (_, index) {
      return dashboardColorAt(index);
    });

    if (!dashboardCharts.process) {
      dashboardCharts.process = new Chart(dashboardEls.processChartCanvas, {
        type: 'bar',
        data: {
          labels: normalizedSeries.labels,
          datasets: [{
            label: 'Requests',
            data: normalizedSeries.values,
            borderRadius: 6,
            borderSkipped: false,
            backgroundColor: backgroundColors,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: dashboardPrefersReducedMotion ? 0 : 500,
          },
          plugins: {
            legend: { display: false },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0,
              },
              grid: {
                color: 'rgba(115, 119, 129, 0.18)',
              },
            },
            x: {
              grid: {
                display: false,
              },
            },
          },
        },
      });

      return;
    }

    dashboardCharts.process.data.labels = normalizedSeries.labels;
    dashboardCharts.process.data.datasets[0].data = normalizedSeries.values;
    dashboardCharts.process.data.datasets[0].backgroundColor = backgroundColors;
    dashboardCharts.process.update();
  }

  function upsertDashboardVehicleChart(series) {
    if (!dashboardEls.vehicleChartCanvas || typeof Chart === 'undefined') {
      return;
    }

    const normalizedSeries = normalizeDashboardSeries(series);
    const backgroundColors = normalizedSeries.values.map(function (_, index) {
      return dashboardColorAt(index);
    });

    if (!dashboardCharts.vehicle) {
      dashboardCharts.vehicle = new Chart(dashboardEls.vehicleChartCanvas, {
        type: 'doughnut',
        data: {
          labels: normalizedSeries.labels,
          datasets: [{
            data: normalizedSeries.values,
            backgroundColor: backgroundColors,
            borderColor: '#ffffff',
            borderWidth: 2,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: dashboardPrefersReducedMotion ? 0 : 500,
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                boxWidth: 8,
              },
            },
          },
        },
      });

      return;
    }

    dashboardCharts.vehicle.data.labels = normalizedSeries.labels;
    dashboardCharts.vehicle.data.datasets[0].data = normalizedSeries.values;
    dashboardCharts.vehicle.data.datasets[0].backgroundColor = backgroundColors;
    dashboardCharts.vehicle.update();
  }

  function upsertDashboardVolumeChart(series) {
    if (!dashboardEls.volumeChartCanvas || typeof Chart === 'undefined') {
      return;
    }

    const normalizedSeries = normalizeDashboardSeries(series);

    if (!dashboardCharts.volume) {
      dashboardCharts.volume = new Chart(dashboardEls.volumeChartCanvas, {
        type: 'line',
        data: {
          labels: normalizedSeries.labels,
          datasets: [{
            label: 'Request Volume',
            data: normalizedSeries.values,
            borderColor: '#1a4b84',
            backgroundColor: 'rgba(26, 75, 132, 0.18)',
            fill: true,
            tension: 0.32,
            pointRadius: 3,
            pointHoverRadius: 4,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: dashboardPrefersReducedMotion ? 0 : 500,
          },
          plugins: {
            legend: {
              display: false,
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0,
              },
              grid: {
                color: 'rgba(115, 119, 129, 0.18)',
              },
            },
            x: {
              grid: {
                display: false,
              },
            },
          },
        },
      });

      return;
    }

    dashboardCharts.volume.data.labels = normalizedSeries.labels;
    dashboardCharts.volume.data.datasets[0].data = normalizedSeries.values;
    dashboardCharts.volume.update();
  }

  function renderDashboardCharts(chartsPayload) {
    if (!chartsPayload) {
      return;
    }

    upsertDashboardProcessChart(chartsPayload.requestLifecycle);
    upsertDashboardVehicleChart(chartsPayload.vehicleStatus);
    upsertDashboardVolumeChart(chartsPayload.requestVolume);
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
    if (!dashboardEls.pagination) {
      return;
    }

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

  function updateDashboardFilterClearVisibility() {
    if (!dashboardEls.filterClear) {
      return;
    }

    const hasFilterValue = Boolean(dashboardFilterFrom?.value) || Boolean(dashboardFilterTo?.value);
    dashboardEls.filterClear.classList.toggle('hidden', !hasFilterValue);
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

      animateDashboardMetric(dashboardEls.totalPending, Number(payload.totalPendingRequests) || 0);
      animateDashboardMetric(dashboardEls.activeTripTickets, Number(payload.activeTripTickets) || 0);
      animateDashboardMetric(dashboardEls.activeTripCapacityLabel, Number(payload.activeTripTicketCapacity) || 0, { suffix: '% Capacity' });

      const clampedCapacity = Math.max(0, Math.min(100, Number(payload.activeTripTicketCapacity) || 0));
      dashboardEls.activeTripCapacityBar.style.transition = 'width 650ms ease';
      dashboardEls.activeTripCapacityBar.style.width = `${clampedCapacity}%`;

      dashboardEls.trendIcon.textContent = payload.trendIcon;
      dashboardEls.trendIcon.setAttribute('data-icon', payload.trendIcon);
      animateDashboardMetric(dashboardEls.trendValue, Math.abs(Number(payload.trendPercentage) || 0), { decimals: 1, suffix: '%' });
      dashboardEls.trendWrap.classList.remove('text-secondary', 'text-error');
      dashboardEls.trendWrap.classList.add(payload.trendIsPositive ? 'text-secondary' : 'text-error');

      if (Array.isArray(payload.moduleSummaries)) {
        renderDashboardModuleSummaries(payload.moduleSummaries);
      }

      renderDashboardCharts(payload.charts);

      if (Array.isArray(payload.requests) && payload.requests.length > 0) {
        if (dashboardEls.tbody) {
          dashboardEls.tbody.innerHTML = payload.requests.map(dashboardRequestRow).join('');
        }
      } else if (dashboardEls.tbody) {
        dashboardEls.tbody.innerHTML = dashboardNoRows();
      }

      if (dashboardEls.summary) {
        dashboardEls.summary.textContent = payload.summaryText;
      }
      renderDashboardPagination(payload.pagination);
    } catch (error) {
      console.error('Dashboard AJAX refresh failed', error);
    }
  }

  if (dashboardEls.pagination) {
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
  }

  if (dashboardEls.filterForm) {
    dashboardEls.filterForm.addEventListener('submit', function (event) {
      event.preventDefault();
      updateDashboardFilterClearVisibility();
      dashboardCurrentPage = 1;
      refreshDashboard(1);
    });
  }

  if (dashboardEls.filterClear) {
    dashboardEls.filterClear.addEventListener('click', function (event) {
      event.preventDefault();

      if (dashboardFilterFrom) {
        dashboardFilterFrom.value = '';
      }
      if (dashboardFilterTo) {
        dashboardFilterTo.value = '';
      }

      updateDashboardFilterClearVisibility();
      dashboardCurrentPage = 1;
      refreshDashboard(1);
    });
  }

  if (dashboardFilterFrom) {
    dashboardFilterFrom.addEventListener('change', updateDashboardFilterClearVisibility);
  }

  if (dashboardFilterTo) {
    dashboardFilterTo.addEventListener('change', updateDashboardFilterClearVisibility);
  }

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

  renderDashboardModuleSummaries(dashboardInitialModuleSummaries);
  renderDashboardCharts(dashboardInitialCharts);
  animateDashboardInitialMetrics();
  updateDashboardFilterClearVisibility();
  refreshDashboard(dashboardCurrentPage);

  const dashboardShouldPausePolling = function () {
    const modalIsOpen = Boolean(dashboardRejectModal) && !dashboardRejectModal.classList.contains('hidden');

    return document.hidden || modalIsOpen;
  };

  if (typeof window.emsLiveRefresh === 'function') {
    window.emsLiveRefresh(function () {
      return refreshDashboard(dashboardCurrentPage);
    }, {
      intervalMs: 4000,
      shouldPause: function () {
        return dashboardShouldPausePolling();
      },
    });
  } else {
    window.setInterval(function () {
      if (dashboardShouldPausePolling()) {
        return;
      }

      refreshDashboard(dashboardCurrentPage);
    }, 4000);
  }
</script>
</body></html>