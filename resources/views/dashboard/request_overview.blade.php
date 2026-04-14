<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Request Overview | NIA Equipment Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
    <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-5">
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-[11px] font-bold tracking-wider uppercase mb-3">
                <span class="material-symbols-outlined text-sm">overview</span>
                Request Center
            </span>
            <h1 class="text-primary font-headline text-3xl font-extrabold tracking-tight">Request Overview</h1>
            <p class="text-on-surface-variant text-sm mt-2">View all of your transportation requests in one place and quickly find records using search.</p>
        </div>
        <a href="{{ route('request-form') }}" class="px-4 py-2.5 bg-primary text-on-primary font-semibold rounded-lg hover:opacity-90 transition-all">New Transportation Request</a>
    </div>

    <section class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm p-5">
        <form method="GET" action="{{ route('user.request-overview') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-9">
                <label for="overview-search" class="text-xs uppercase tracking-wider font-bold text-on-surface-variant">Search Requests</label>
                <div class="mt-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input
                        id="overview-search"
                        name="search"
                        value="{{ $search }}"
                        type="text"
                        placeholder="Search by Form ID, destination, purpose, or status"
                        class="w-full bg-surface-container-low border border-outline-variant/30 rounded-lg pl-11 pr-3 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>
            </div>
            <div class="md:col-span-3 flex gap-2 md:justify-end">
                <button type="submit" class="w-full md:w-auto px-4 py-2.5 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:opacity-90 transition-all">Search</button>
                @if ($search !== '')
                    <a href="{{ route('user.request-overview') }}" class="w-full md:w-auto text-center px-4 py-2.5 rounded-lg border border-outline-variant text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-all">Reset</a>
                @endif
            </div>
        </form>
    </section>

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

    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-outline-variant/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-lg font-bold text-primary">All Requests</h2>
            <p id="request-overview-total-count" class="text-sm text-on-surface-variant">{{ number_format($requests->total()) }} total request{{ $requests->total() === 1 ? '' : 's' }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container text-on-surface-variant text-[11px] uppercase tracking-wider font-bold">
                    <tr>
                        <th class="px-4 py-3 border-r border-outline-variant/20">Form ID</th>
                        <th class="px-4 py-3 border-r border-outline-variant/20">Request Date</th>
                        <th class="px-4 py-3 border-r border-outline-variant/20">Trip Schedule</th>
                        <th class="px-4 py-3 border-r border-outline-variant/20">Destination</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody id="request-overview-table-body" class="text-sm text-on-surface">
                    @forelse ($requests as $requestItem)
                        @php
                            $statusValue = (string) ($requestItem->status ?: 'Unknown');
                            $statusClass = $statusBadgeClasses[$statusValue] ?? 'bg-slate-100 text-slate-700';
                            $tripStart = optional($requestItem->date_time_from)->format('M d, Y h:i A') ?: 'N/A';
                            $tripEnd = optional($requestItem->date_time_to)->format('M d, Y h:i A') ?: 'N/A';
                        @endphp
                        <tr class="border-b border-outline-variant/10 {{ $loop->even ? 'bg-surface-container-low/35' : '' }}">
                            <td class="px-4 py-3 border-r border-outline-variant/20 font-semibold text-primary">{{ $requestItem->form_id ?: 'N/A' }}</td>
                            <td class="px-4 py-3 border-r border-outline-variant/20">{{ optional($requestItem->request_date)->format('M d, Y') ?: 'N/A' }}</td>
                            <td class="px-4 py-3 border-r border-outline-variant/20 text-xs sm:text-sm">{{ $tripStart }} to {{ $tripEnd }}</td>
                            <td class="px-4 py-3 border-r border-outline-variant/20">{{ $requestItem->destination ?: 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClass }}">{{ $statusValue }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">
                                {{ $search !== '' ? 'No requests matched your search.' : 'No requests found yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="request-overview-pagination" class="px-6 py-4 border-t border-outline-variant/20 {{ $requests->hasPages() ? '' : 'hidden' }}">
            @if ($requests->hasPages())
                {{ $requests->onEachSide(1)->links() }}
            @endif
        </div>
    </section>
</main>

@include('layouts.footer')
<script>
    (function () {
        const tableBody = document.getElementById('request-overview-table-body');
        const totalCount = document.getElementById('request-overview-total-count');
        const pagination = document.getElementById('request-overview-pagination');
        let refreshInFlight = false;

        if (!tableBody || !totalCount) {
            return;
        }

        async function refreshOverview() {
            if (refreshInFlight) {
                return;
            }

            refreshInFlight = true;

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('_live_refresh', String(Date.now()));

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextTableBody = doc.getElementById('request-overview-table-body');
                const nextTotalCount = doc.getElementById('request-overview-total-count');
                const nextPagination = doc.getElementById('request-overview-pagination');

                if (nextTableBody) {
                    tableBody.innerHTML = nextTableBody.innerHTML;
                }

                if (nextTotalCount) {
                    totalCount.textContent = nextTotalCount.textContent;
                }

                if (pagination) {
                    if (nextPagination) {
                        pagination.innerHTML = nextPagination.innerHTML;
                        pagination.classList.remove('hidden');
                    } else {
                        pagination.innerHTML = '';
                        pagination.classList.add('hidden');
                    }
                }
            } catch (error) {
                // Ignore refresh failures.
            } finally {
                refreshInFlight = false;
            }
        }

        if (typeof window.emsLiveRefresh === 'function') {
            window.emsLiveRefresh(refreshOverview, {
                intervalMs: 10000,
                runImmediately: false,
            });
        } else {
            window.setInterval(refreshOverview, 10000);
        }
    })();
</script>
</body>
</html>
