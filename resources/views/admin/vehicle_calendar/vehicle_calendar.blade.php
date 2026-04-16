<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Vehicle Schedule Calendar | NIA Fleet Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    background: '#f7f9fc',
                    surface: '#f7f9fc',
                    'surface-container': '#eceef1',
                    'surface-container-low': '#f2f4f7',
                    'surface-container-high': '#e6e8eb',
                    'surface-container-lowest': '#ffffff',
                    primary: '#003466',
                    'primary-container': '#1a4b84',
                    secondary: '#3a6843',
                    'secondary-container': '#b9ecbd',
                    tertiary: '#003c34',
                    'on-surface': '#191c1e',
                    'on-surface-variant': '#424750',
                    outline: '#737781',
                    'outline-variant': '#c3c6d1',
                    error: '#ba1a1a',
                    'error-container': '#ffdad6',
                    'on-error-container': '#93000a',
                    'primary-fixed': '#d5e3ff',
                    'on-primary-fixed-variant': '#144780',
                    'on-secondary-container': '#3e6d47',
                    'on-secondary': '#ffffff',
                    'on-primary': '#ffffff',
                    'on-tertiary': '#ffffff',
                    'tertiary-container': '#00554a',
                    'tertiary-fixed': '#a0f2e1',
                    'tertiary-fixed-dim': '#84d5c5',
                },
                fontFamily: {
                    headline: ['Public Sans'],
                    body: ['Public Sans'],
                    label: ['Public Sans'],
                },
                borderRadius: { DEFAULT: '0.125rem', lg: '0.25rem', xl: '0.5rem', full: '0.75rem' },
            },
        },
    }
</script>
<style>
    body { font-family: 'Public Sans', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
@include('layouts.admin_header')

<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-10">
    <header class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-on-primary-fixed-variant">
                <span class="material-symbols-outlined text-sm">calendar_month</span>
                Vehicle Calendar
            </span>
            <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-primary">Vehicle Schedule Calendar</h1>
            <p class="mt-2 text-sm text-on-surface-variant">Track assigned vehicles, drivers, and trip dates in one monthly view.</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:w-auto">
            <div class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Scheduled</p>
                <p class="mt-1 text-2xl font-black text-primary">{{ number_format((int) $calendarEventCount) }}</p>
            </div>
            <div class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Coaster</p>
                <p class="mt-1 text-2xl font-black text-primary">{{ number_format((int) ($typeCounts['coaster'] ?? 0)) }}</p>
            </div>
            <div class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Van</p>
                <p class="mt-1 text-2xl font-black text-primary">{{ number_format((int) ($typeCounts['van'] ?? 0)) }}</p>
            </div>
            <div class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Pickup</p>
                <p class="mt-1 text-2xl font-black text-primary">{{ number_format((int) ($typeCounts['pickup'] ?? 0)) }}</p>
            </div>
        </div>
    </header>

    <section class="mb-6 rounded-2xl border border-outline-variant/10 bg-surface-container-lowest p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.vehicle_calendar') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-4 lg:items-end">
            <div>
                <label class="mb-1 block px-1 text-[10px] font-bold uppercase tracking-widest text-outline">Month</label>
                <input type="month" name="month" value="{{ $calendarMonth->format('Y-m') }}" class="w-full rounded-lg border border-outline-variant/30 bg-surface-container-low px-3 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"/>
            </div>
            <div>
                <label class="mb-1 block px-1 text-[10px] font-bold uppercase tracking-widest text-outline">Vehicle Type</label>
                <select name="vehicle_type" class="w-full rounded-lg border border-outline-variant/30 bg-surface-container-low px-3 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                    @foreach ($vehicleTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedVehicleType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block px-1 text-[10px] font-bold uppercase tracking-widest text-outline">Status</label>
                <select name="status" class="w-full rounded-lg border border-outline-variant/30 bg-surface-container-low px-3 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-container transition-colors">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                    Apply
                </button>
                <a href="{{ $todayMonthUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant/30 bg-surface-container-low px-5 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container transition-colors">
                    Today
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="overflow-hidden rounded-2xl border border-outline-variant/10 bg-surface-container-lowest shadow-sm">
            <div class="flex flex-col gap-4 border-b border-outline-variant/15 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-primary">{{ $calendarMonth->format('F Y') }}</h2>
                    <p class="text-sm text-on-surface-variant">Weekly lanes with start-to-end continuation bars</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ $previousMonthUrl }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-outline-variant/30 bg-surface-container-low text-primary hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </a>
                    <a href="{{ $todayMonthUrl }}" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary-container transition-colors">Today</a>
                    <a href="{{ $nextMonthUrl }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-outline-variant/30 bg-surface-container-low text-primary hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-7 border-b border-outline-variant/15 bg-surface-container-low/30 text-center text-[11px] font-bold uppercase tracking-wider text-primary">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                    <div class="px-2 py-3">{{ $weekday }}</div>
                @endforeach
            </div>

            <div class="divide-y divide-outline-variant/10 bg-white">
                @foreach ($calendarWeeks as $week)
                    @php
                        $weekHeight = max(88, ((int) $week['laneCount'] * 60) + 8);
                    @endphp
                    <div class="relative">
                        <div class="grid grid-cols-7 border-b border-outline-variant/10 bg-surface-container-low/30 text-[11px] font-semibold text-on-surface-variant">
                            @foreach ($week['days'] as $day)
                                <div class="border-r border-outline-variant/10 px-3 py-2 text-left {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-surface-container-low/35 text-outline opacity-65' }} {{ $day['isToday'] ? 'bg-primary-fixed/10 text-primary font-extrabold' : '' }}">
                                    {{ $day['date']->day }}
                                </div>
                            @endforeach
                        </div>

                        <div class="relative grid grid-cols-7 gap-1 px-1 py-1" style="min-height: {{ $weekHeight }}px;">
                            @foreach ($week['segments'] as $segment)
                                @php
                                    $event = $segment['event'];
                                    $statusClass = match ($event['status']) {
                                        'On Trip' => 'bg-[#003b73] text-white border-[#003b73]',
                                        'Dispatched' => 'bg-[#0f4c81] text-white border-[#0f4c81]',
                                        'For Evaluation' => 'bg-[#7c3aed] text-white border-[#7c3aed]',
                                        'Completed' => 'bg-[#16803c] text-white border-[#16803c]',
                                        default => 'bg-[#dc7f00] text-white border-[#dc7f00]',
                                    };
                                    $statusText = strtoupper(str_replace(' ', '', (string) $event['status']));
                                    if ((string) $event['status'] === 'Completed') {
                                        $statusText = '';
                                    }
                                    $eventPayload = [
                                        'requestId' => $event['requestId'],
                                        'formId' => $event['formId'],
                                        'status' => $event['status'],
                                        'vehicleCode' => $event['vehicleCode'],
                                        'vehicleType' => $event['vehicleType'],
                                        'vehicleLabel' => $event['vehicleLabel'],
                                        'capacityLabel' => $event['capacityLabel'],
                                        'driverName' => $event['driverName'],
                                        'destination' => $event['destination'],
                                        'timeLabel' => $event['timeLabel'],
                                        'rangeStart' => $event['rangeStart'],
                                        'rangeEnd' => $event['rangeEnd'],
                                    ];
                                @endphp
                                <button
                                    type="button"
                                    class="calendar-event-bar group col-start-{{ $segment['startColumn'] }} col-span-{{ $segment['span'] }} row-start-{{ $segment['lane'] + 1 }} flex min-h-[54px] flex-col items-start justify-center rounded-sm border-l-4 px-3 py-1 text-left shadow-sm transition-all duration-200 hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-primary/25 {{ $statusClass }}"
                                    data-calendar-event='@json($eventPayload)'
                                >
                                    @if ($statusText !== '')
                                        <p class="text-[10px] font-black uppercase leading-none tracking-[0.06em]">{{ $statusText }}</p>
                                    @endif
                                    <p class="mt-0.5 truncate text-[20px] font-extrabold leading-none sm:text-[18px]">{{ $event['vehicleCode'] }} - Driver: {{ $event['driverName'] }}</p>
                                    <p class="mt-0.5 truncate text-[11px] font-semibold leading-none opacity-95">{{ $event['destination'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-outline-variant/10 bg-surface-container-lowest p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-on-surface">Selected Schedule</h3>
                    <span class="rounded-full bg-primary-fixed px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-on-primary-fixed-variant">Click a bar</span>
                </div>

                <div id="calendar-detail-empty" class="mt-4 rounded-xl border border-dashed border-outline-variant/30 bg-surface-container-low px-4 py-6 text-sm font-semibold text-outline">
                    Select a schedule bar to see request details, vehicle info, and related action links.
                </div>

                <div id="calendar-detail-panel" class="mt-4 hidden space-y-4">
                    <div class="rounded-xl border border-outline-variant/20 bg-surface-container-low px-4 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-outline">Request</p>
                        <p id="calendar-detail-form-id" class="mt-1 text-xl font-black text-primary"></p>
                        <p id="calendar-detail-status" class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 rounded-xl border border-outline-variant/20 bg-surface-container-low px-4 py-4 text-sm">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Vehicle</p>
                            <p id="calendar-detail-vehicle" class="font-semibold text-on-surface"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Driver</p>
                            <p id="calendar-detail-driver" class="font-semibold text-on-surface"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Destination</p>
                            <p id="calendar-detail-destination" class="font-semibold text-on-surface"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-outline">Trip Window</p>
                            <p id="calendar-detail-range" class="font-semibold text-on-surface"></p>
                        </div>
                    </div>

                    <!-- 'Open Related Page' action removed -->
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant/10 bg-surface-container-lowest p-5 shadow-sm">
                <h3 class="text-sm font-black uppercase tracking-wider text-on-surface">Legend</h3>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-[#0b63a8]"></span><span>On Trip</span></div>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant/10 bg-surface-container-lowest p-5 shadow-sm">
                <h3 class="text-sm font-black uppercase tracking-wider text-on-surface">Status Summary</h3>
                <div class="mt-4 space-y-3">
                    @foreach ($statusCounts as $status => $count)
                        @continue($status === 'Completed')
                        <div class="flex items-center justify-between rounded-xl border border-outline-variant/20 bg-surface-container-low px-4 py-3">
                            <span class="text-sm font-semibold text-on-surface-variant">{{ $status }}</span>
                            <span class="text-lg font-black text-primary">{{ number_format((int) $count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </section>
</main>

<script>
    (function () {
        const bars = Array.from(document.querySelectorAll('.calendar-event-bar'));
        const detailEmpty = document.getElementById('calendar-detail-empty');
        const detailPanel = document.getElementById('calendar-detail-panel');
        const detailFormId = document.getElementById('calendar-detail-form-id');
        const detailStatus = document.getElementById('calendar-detail-status');
        const detailVehicle = document.getElementById('calendar-detail-vehicle');
        const detailDriver = document.getElementById('calendar-detail-driver');
        const detailDestination = document.getElementById('calendar-detail-destination');
        const detailRange = document.getElementById('calendar-detail-range');

        if (!bars.length || !detailEmpty || !detailPanel || !detailFormId || !detailStatus || !detailVehicle || !detailDriver || !detailDestination || !detailRange) {
            return;
        }

        function statusTone(status) {
            switch (String(status || '')) {
                case 'Dispatched':
                    return 'bg-blue-100 text-blue-800';
                case 'On Trip':
                    return 'bg-indigo-100 text-indigo-800';
                case 'For Evaluation':
                    return 'bg-violet-100 text-violet-800';
                case 'Completed':
                    return 'bg-emerald-100 text-emerald-800';
                default:
                    return 'bg-amber-100 text-amber-800';
            }
        }

        // relatedActionUrl removed — 'Open Related Page' action was removed from the UI

        function showDetail(eventData) {
            detailEmpty.classList.add('hidden');
            detailPanel.classList.remove('hidden');

            detailFormId.textContent = String(eventData.formId || 'N/A');
            detailStatus.textContent = String(eventData.status || 'Unknown');
            detailStatus.className = `mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ${statusTone(eventData.status)}`;
            detailVehicle.textContent = `${String(eventData.vehicleCode || 'N/A')} • ${String(eventData.vehicleLabel || 'Vehicle')}${String(eventData.capacityLabel || '') !== '' ? ' (' + String(eventData.capacityLabel) + ')' : ''}`;
            detailDriver.textContent = String(eventData.driverName || 'N/A');
            detailDestination.textContent = String(eventData.destination || 'N/A');
            detailRange.textContent = `${String(eventData.rangeStart || '')} to ${String(eventData.rangeEnd || '')} • ${String(eventData.timeLabel || 'All day')}`;
        }

        bars.forEach(function (bar) {
            bar.addEventListener('click', function () {
                const rawEvent = String(bar.getAttribute('data-calendar-event') || '{}');
                try {
                    showDetail(JSON.parse(rawEvent));
                } catch (error) {
                    // Ignore malformed payloads.
                }
            });
        });

        if (bars[0]) {
            bars[0].click();
        }
    })();
</script>

<script>
    window.__emsHasCustomLiveRefresh = true;
</script>

@include('layouts.admin_footer')
</body>
</html>