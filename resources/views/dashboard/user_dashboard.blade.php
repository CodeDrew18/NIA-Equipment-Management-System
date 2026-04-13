<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>User Dashboard | NIA Equipment Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary-fixed-dim": "#a6c8ff",
                    "inverse-surface": "#2d3133",
                    "inverse-on-surface": "#eff1f4",
                    "surface-container-lowest": "#ffffff",
                    "on-tertiary-fixed": "#00201b",
                    "on-error-container": "#93000a",
                    "on-secondary": "#ffffff",
                    "surface": "#f7f9fc",
                    "on-tertiary": "#ffffff",
                    "on-background": "#191c1e",
                    "surface-container-high": "#e6e8eb",
                    "on-secondary-container": "#3e6d47",
                    "inverse-primary": "#a6c8ff",
                    "error": "#ba1a1a",
                    "on-surface-variant": "#424750",
                    "tertiary-container": "#00554a",
                    "primary": "#003466",
                    "tertiary-fixed": "#a0f2e1",
                    "surface-variant": "#e0e3e6",
                    "error-container": "#ffdad6",
                    "on-primary-container": "#93bcfc",
                    "on-tertiary-container": "#78caba",
                    "secondary": "#3a6843",
                    "primary-fixed": "#d5e3ff",
                    "surface-container-highest": "#e0e3e6",
                    "on-primary": "#ffffff",
                    "on-primary-fixed": "#001c3b",
                    "surface-bright": "#f7f9fc",
                    "secondary-fixed-dim": "#a0d3a5",
                    "primary-container": "#1a4b84",
                    "outline-variant": "#c3c6d1",
                    "on-primary-fixed-variant": "#144780",
                    "tertiary": "#003c34",
                    "tertiary-fixed-dim": "#84d5c5",
                    "surface-dim": "#d8dadd",
                    "on-secondary-fixed-variant": "#22502d",
                    "outline": "#737781",
                    "on-error": "#ffffff",
                    "secondary-fixed": "#bcefc0",
                    "on-surface": "#191c1e",
                    "on-secondary-fixed": "#00210a",
                    "background": "#f7f9fc",
                    "secondary-container": "#b9ecbd",
                    "surface-container": "#eceef1",
                    "on-tertiary-fixed-variant": "#005046",
                    "surface-container-low": "#f2f4f7",
                    "surface-tint": "#335f99"
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
    }
</style>
</head>
<body class="bg-surface font-body text-on-surface antialiased min-h-screen flex flex-col">
@include('layouts.header')

<main class="mt-24 mb-16 flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12">
    <div class="mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold tracking-wider uppercase mb-3">
                <span class="material-symbols-outlined text-sm">insights</span>
                Trip Command Center
            </span>
            <h1 class="text-primary font-headline text-3xl font-extrabold tracking-tight">Operations Status Overview</h1>
            <p class="text-on-surface-variant text-sm mt-2">Ready for the road? Track your requests, active trips, and evaluations in one dashboard built to keep momentum high.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('request-form') }}" class="px-4 py-2.5 bg-primary text-on-primary font-semibold rounded-lg hover:opacity-90 transition-all">New Transportation Request</a>
            <a href="{{ route('vehicle-available') }}" class="px-4 py-2.5 bg-surface-container-low border border-outline-variant/40 text-on-surface font-semibold rounded-lg hover:bg-surface-container transition-all">View Vehicle Availability</a>
        </div>
    </div>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 xl:gap-8 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
            <p class="text-xs uppercase tracking-widest text-outline font-bold mb-2">Requests This Month</p>
            <p id="metric-requests-this-month" data-current-value="0" class="text-3xl font-extrabold text-primary">{{ number_format((int) $requestsThisMonth) }}</p>
        </div>
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
            <p class="text-xs uppercase tracking-widest text-outline font-bold mb-2">Active Trips</p>
            <p id="metric-active-trips" data-current-value="0" class="text-3xl font-extrabold text-primary">{{ number_format((int) $activeTrips) }}</p>
        </div>
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
            <p class="text-xs uppercase tracking-widest text-outline font-bold mb-2">Completed Trips</p>
            <p id="metric-completed-trips" data-current-value="0" class="text-3xl font-extrabold text-primary">{{ number_format((int) $completedTrips) }}</p>
        </div>
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10">
            <p class="text-xs uppercase tracking-widest text-outline font-bold mb-2">Pending Evaluations</p>
            <p id="metric-pending-evaluations" data-current-value="0" class="text-3xl font-extrabold text-primary">{{ number_format((int) $pendingEvaluations) }}</p>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-6 xl:gap-8 mb-8">
        <div class="xl:col-span-7 bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm p-6">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold text-primary">Request Trend (Last 6 Months)</h2>
                <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Monthly Momentum</span>
            </div>
            <div class="h-[320px]">
                <canvas id="requestTrendChart" aria-label="Request Trend Chart"></canvas>
            </div>
        </div>

        <div class="xl:col-span-5 bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm p-6">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold text-primary">Request Status Mix</h2>
                <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Live Snapshot</span>
            </div>
            <div class="h-[320px]">
                <canvas id="requestStatusChart" aria-label="Request Status Chart"></canvas>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 xl:gap-8 mb-10">
        <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 flex items-center justify-between">
                <h2 class="text-lg font-bold text-primary">Recent Requests</h2>
                <a href="{{ route('request-form') }}" class="text-sm font-semibold text-primary hover:underline">Go to Requests</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container text-on-surface-variant text-[11px] uppercase tracking-wider font-bold">
                        <tr>
                            <th class="px-4 py-3 border-r border-outline-variant/20">Form ID</th>
                            <th class="px-4 py-3 border-r border-outline-variant/20">Request Date</th>
                            <th class="px-4 py-3 border-r border-outline-variant/20">Destination</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-requests-body" class="text-sm text-on-surface">
                        @php
                            $statusBadgeClasses = [
                                'To be Signed' => 'bg-amber-100 text-amber-800',
                                'Signed' => 'bg-sky-100 text-sky-800',
                                'Dispatched' => 'bg-blue-100 text-blue-800',
                                'On Trip' => 'bg-indigo-100 text-indigo-800',
                                'For Evaluation' => 'bg-violet-100 text-violet-800',
                                'Completed' => 'bg-emerald-100 text-emerald-800',
                                'Rejected' => 'bg-rose-100 text-rose-800',
                            ];
                        @endphp

                        @forelse ($recentRequests as $request)
                            @php
                                $statusValue = (string) ($request['status'] ?? 'Unknown');
                                $statusClass = $statusBadgeClasses[$statusValue] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <tr class="border-b border-outline-variant/10 {{ $loop->even ? 'bg-surface-container-low/40' : '' }}">
                                <td class="px-4 py-3 border-r border-outline-variant/20 font-semibold text-primary">{{ $request['form_id'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 border-r border-outline-variant/20">{{ $request['request_date_label'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 border-r border-outline-variant/20">{{ $request['destination'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClass }}">{{ $statusValue }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-on-surface-variant">No requests found yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm p-6">
            <h2 class="text-lg font-bold text-primary mb-4">Quick Access</h2>
            <div class="space-y-3">
                <a href="{{ route('request-form') }}" class="block w-full px-4 py-3 rounded-lg bg-primary text-on-primary font-semibold text-sm hover:opacity-90 transition-all">Transportation Request</a>
                <a href="{{ route('vehicle-available') }}" class="block w-full px-4 py-3 rounded-lg bg-surface-container-low border border-outline-variant/40 text-on-surface font-semibold text-sm hover:bg-surface-container transition-all">Vehicle Availability</a>
                <a href="{{ route('monthly-official-travel-report') }}" class="block w-full px-4 py-3 rounded-lg bg-surface-container-low border border-outline-variant/40 text-on-surface font-semibold text-sm hover:bg-surface-container transition-all">Monthly Travel Report</a>
                <a href="{{ route('evaluation-performance') }}" class="block w-full px-4 py-3 rounded-lg bg-surface-container-low border border-outline-variant/40 text-on-surface font-semibold text-sm hover:bg-surface-container transition-all">Evaluation Performance</a>
            </div>
        </div>
    </section>
</main>

@include('layouts.footer')
<script>
    (function () {
        if (typeof window.Chart === 'undefined') {
            return;
        }

        const dashboardDataUrl = @json(route('user.dashboard.data'));
        const trendCanvas = document.getElementById('requestTrendChart');
        const statusCanvas = document.getElementById('requestStatusChart');
        const recentRequestsBody = document.getElementById('recent-requests-body');

        const metricElements = {
            requestsThisMonth: document.getElementById('metric-requests-this-month'),
            activeTrips: document.getElementById('metric-active-trips'),
            completedTrips: document.getElementById('metric-completed-trips'),
            pendingEvaluations: document.getElementById('metric-pending-evaluations'),
        };

        const statusBadgeClasses = {
            'To be Signed': 'bg-amber-100 text-amber-800',
            'Signed': 'bg-sky-100 text-sky-800',
            'Dispatched': 'bg-blue-100 text-blue-800',
            'On Trip': 'bg-indigo-100 text-indigo-800',
            'For Evaluation': 'bg-violet-100 text-violet-800',
            'Completed': 'bg-emerald-100 text-emerald-800',
            'Rejected': 'bg-rose-100 text-rose-800',
        };

        const numberFormatter = new Intl.NumberFormat('en-US');
        let trendChart = null;
        let statusChart = null;
        let fetchInFlight = false;

        const initialPayload = {
            requestsThisMonth: @json((int) ($requestsThisMonth ?? 0)),
            activeTrips: @json((int) ($activeTrips ?? 0)),
            completedTrips: @json((int) ($completedTrips ?? 0)),
            pendingEvaluations: @json((int) ($pendingEvaluations ?? 0)),
            trendChartLabels: @json($trendChartLabels ?? []),
            trendChartValues: @json($trendChartValues ?? []),
            statusChartLabels: @json($statusChartLabels ?? []),
            statusChartValues: @json($statusChartValues ?? []),
            recentRequests: @json($recentRequests ?? []),
        };

        function toNumber(value) {
            const numeric = Number(value);

            return Number.isFinite(numeric) ? numeric : 0;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function animateMetric(element, targetValue, fromZero) {
            if (!element) {
                return;
            }

            const nextValue = Math.max(0, Math.round(toNumber(targetValue)));
            const startValue = fromZero
                ? 0
                : toNumber(element.getAttribute('data-current-value'));

            const duration = 800;
            const startTime = window.performance.now();

            if (element.__metricAnimationFrame) {
                window.cancelAnimationFrame(element.__metricAnimationFrame);
            }

            function tick(timestamp) {
                const progress = Math.min((timestamp - startTime) / duration, 1);
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                const currentValue = Math.round(startValue + ((nextValue - startValue) * easedProgress));

                element.textContent = numberFormatter.format(currentValue);
                element.setAttribute('data-current-value', String(currentValue));

                if (progress < 1) {
                    element.__metricAnimationFrame = window.requestAnimationFrame(tick);
                }
            }

            element.__metricAnimationFrame = window.requestAnimationFrame(tick);
        }

        function applyMetrics(payload, fromZero) {
            animateMetric(metricElements.requestsThisMonth, payload.requestsThisMonth, fromZero);
            animateMetric(metricElements.activeTrips, payload.activeTrips, fromZero);
            animateMetric(metricElements.completedTrips, payload.completedTrips, fromZero);
            animateMetric(metricElements.pendingEvaluations, payload.pendingEvaluations, fromZero);
        }

        function renderRecentRequests(rows) {
            if (!recentRequestsBody) {
                return;
            }

            if (!Array.isArray(rows) || rows.length === 0) {
                recentRequestsBody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-on-surface-variant">No requests found yet.</td></tr>';
                return;
            }

            recentRequestsBody.innerHTML = rows
                .map(function (row, index) {
                    const statusValue = String((row && row.status) ? row.status : 'Unknown');
                    const statusClass = statusBadgeClasses[statusValue] || 'bg-slate-100 text-slate-700';
                    const stripedClass = index % 2 === 1 ? 'bg-surface-container-low/40' : '';

                    return [
                        '<tr class="border-b border-outline-variant/10 ' + stripedClass + '">',
                        '<td class="px-4 py-3 border-r border-outline-variant/20 font-semibold text-primary">' + escapeHtml(row.form_id || 'N/A') + '</td>',
                        '<td class="px-4 py-3 border-r border-outline-variant/20">' + escapeHtml(row.request_date_label || 'N/A') + '</td>',
                        '<td class="px-4 py-3 border-r border-outline-variant/20">' + escapeHtml(row.destination || 'N/A') + '</td>',
                        '<td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' + statusClass + '">' + escapeHtml(statusValue) + '</span></td>',
                        '</tr>'
                    ].join('');
                })
                .join('');
        }

        function updateCharts(payload) {
            if (trendChart) {
                trendChart.data.labels = Array.isArray(payload.trendChartLabels) ? payload.trendChartLabels : [];
                trendChart.data.datasets[0].data = Array.isArray(payload.trendChartValues) ? payload.trendChartValues : [];
                trendChart.update();
            }

            if (statusChart) {
                statusChart.data.labels = Array.isArray(payload.statusChartLabels) ? payload.statusChartLabels : [];
                statusChart.data.datasets[0].data = Array.isArray(payload.statusChartValues) ? payload.statusChartValues : [];
                statusChart.update();
            }
        }

        if (trendCanvas) {
            const trendCtx = trendCanvas.getContext('2d');
            const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 320);
            trendGradient.addColorStop(0, 'rgba(26, 75, 132, 0.32)');
            trendGradient.addColorStop(1, 'rgba(26, 75, 132, 0.03)');

            trendChart = new window.Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: initialPayload.trendChartLabels,
                    datasets: [{
                        label: 'Requests',
                        data: initialPayload.trendChartValues,
                        borderColor: '#1a4b84',
                        backgroundColor: trendGradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#1a4b84',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
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
                                color: '#424750',
                            },
                            grid: {
                                color: 'rgba(115, 119, 129, 0.15)',
                            },
                        },
                        x: {
                            ticks: {
                                color: '#424750',
                            },
                            grid: {
                                display: false,
                            },
                        },
                    },
                },
            });
        }

        if (statusCanvas) {
            statusChart = new window.Chart(statusCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: initialPayload.statusChartLabels,
                    datasets: [{
                        data: initialPayload.statusChartValues,
                        backgroundColor: [
                            '#f59e0b',
                            '#0ea5e9',
                            '#3b82f6',
                            '#6366f1',
                            '#8b5cf6',
                            '#10b981',
                            '#ef4444',
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                color: '#424750',
                                font: {
                                    size: 11,
                                    weight: '600',
                                },
                            },
                        },
                    },
                    cutout: '65%',
                },
            });
        }

        applyMetrics(initialPayload, true);
        renderRecentRequests(initialPayload.recentRequests);

        async function fetchDashboardData() {
            if (fetchInFlight) {
                return;
            }

            fetchInFlight = true;

            try {
                const response = await fetch(dashboardDataUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();

                applyMetrics(payload, false);
                updateCharts(payload);
                renderRecentRequests(payload.recentRequests);
            } catch (error) {
                console.error('Dashboard auto-refresh failed.', error);
            } finally {
                fetchInFlight = false;
            }
        }

        if (typeof window.emsLiveRefresh === 'function') {
            window.emsLiveRefresh(fetchDashboardData, {
                intervalMs: 10000,
                runImmediately: false,
            });
        } else {
            window.setInterval(fetchDashboardData, 10000);
        }
    })();
</script>
</body></html>
