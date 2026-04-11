<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Vehicle Availability - Institutional Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
            tailwind.config = {
                darkMode: "class",
                theme: {
                    extend: {
                        colors: {
                            "tertiary-fixed": "#a0f2e1",
                            "on-secondary-fixed": "#00210a",
                            "primary": "#003466",
                            "error": "#ba1a1a",
                            "on-tertiary-container": "#78caba",
                            "inverse-on-surface": "#eff1f4",
                            "on-secondary": "#ffffff",
                            "on-error-container": "#93000a",
                            "surface-container-highest": "#e0e3e6",
                            "error-container": "#ffdad6",
                            "on-secondary-fixed-variant": "#22502d",
                            "background": "#f7f9fc",
                            "tertiary": "#003c34",
                            "surface-variant": "#e0e3e6",
                            "surface-container-low": "#f2f4f7",
                            "secondary-fixed": "#bcefc0",
                            "on-tertiary": "#ffffff",
                            "secondary-fixed-dim": "#a0d3a5",
                            "on-surface": "#191c1e",
                            "on-error": "#ffffff",
                            "inverse-primary": "#a6c8ff",
                            "on-primary-container": "#93bcfc",
                            "on-tertiary-fixed-variant": "#005046",
                            "tertiary-container": "#00554a",
                            "surface-dim": "#d8dadd",
                            "surface-bright": "#f7f9fc",
                            "on-secondary-container": "#3e6d47",
                            "surface-tint": "#335f99",
                            "outline-variant": "#c3c6d1",
                            "on-tertiary-fixed": "#00201b",
                            "outline": "#737781",
                            "on-primary-fixed": "#001c3b",
                            "primary-fixed": "#d5e3ff",
                            "primary-fixed-dim": "#a6c8ff",
                            "surface-container-high": "#e6e8eb",
                            "secondary": "#3a6843",
                            "primary-container": "#1a4b84",
                            "on-background": "#191c1e",
                            "secondary-container": "#b9ecbd",
                            "tertiary-fixed-dim": "#84d5c5",
                            "surface-container-lowest": "#ffffff",
                            "surface-container": "#eceef1",
                            "on-primary": "#ffffff",
                            "surface": "#f7f9fc",
                            "inverse-surface": "#2d3133",
                            "on-primary-fixed-variant": "#144780",
                            "on-surface-variant": "#424750"
                        },
                    },
                },
            }
        </script>
<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
body { font-family: 'Public Sans', sans-serif; }
</style>
</head>
<body class="bg-background text-on-surface flex flex-col min-h-screen antialiased">
@include('layouts.header')

<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pb-8 pt-28 md:pt-32">
<section class="mb-10 flex flex-col md:flex-row justify-between md:items-end gap-6">
<div class="max-w-2xl">
<h1 class="text-4xl font-extrabold text-primary mb-2 tracking-tight">Vehicle Availability</h1>
<p class="text-on-surface-variant leading-relaxed">National Irrigation Administration: Real-time monitoring of regional logistics and institutional transport resources.</p>
</div>
<div class="flex gap-4">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10 flex flex-col min-w-[170px]">
<span class="text-xs font-semibold text-primary/60 uppercase tracking-widest mb-1">Total Vehicles</span>
<span class="text-3xl font-bold text-primary"><span id="total-vehicles-count">{{ $totalVehicles }}</span> Units</span>
</div>
</div>
</section>

<div id="vehicles-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
@forelse ($vehicles as $vehicle)
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/20 shadow-sm hover:shadow-md transition-all">
<div class="flex justify-between items-start mb-4 gap-3">
<div>
<p class="text-xs font-semibold uppercase tracking-widest text-primary/60">{{ $vehicle->vehicle_type }}</p>
<h3 class="text-xl font-bold text-on-surface">{{ $vehicle->vehicle_code }}</h3>
</div>
<span @class([
    'px-3 py-1 rounded-full text-xs font-bold',
    'bg-tertiary-fixed text-on-tertiary-fixed-variant' => $vehicle->status === 'Available',
    'bg-secondary-container text-on-secondary-container' => $vehicle->status === 'Reserved',
    'bg-primary-fixed text-on-primary-fixed-variant' => $vehicle->status === 'On Business Trip',
    'bg-error-container text-on-error-container' => in_array($vehicle->status, ['Maintenance', 'Unavailable']),
])>
    {{ $vehicle->status }}
</span>
</div>

<div class="h-40 rounded-lg overflow-hidden bg-surface-container-low mb-4">
@if ($vehicle->image_url)
<img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->vehicle_code }}" class="w-full h-full object-cover"/>
@else
<div class="w-full h-full flex items-center justify-center text-outline">
<span class="material-symbols-outlined text-4xl">airport_shuttle</span>
</div>
@endif
</div>

<div class="bg-surface-container-low/50 p-3 rounded-lg border border-outline-variant/20 mb-4">
<p class="text-[10px] font-semibold uppercase tracking-wider text-primary/60">Vehicle Driver</p>
<p class="text-sm font-bold text-on-surface">{{ $vehicle->driver_name ?: 'Unassigned' }}</p>
</div>

<div class="flex justify-between items-center text-sm text-on-surface-variant">
<span>{{ $vehicle->capacity_label ?: 'No capacity set' }}</span>
<span class="font-semibold text-primary">Live</span>
</div>
</div>
@empty
<div class="col-span-full bg-surface-container-lowest border border-outline-variant/20 rounded-xl p-8 text-center text-on-surface-variant font-semibold">
No vehicle records found.
</div>
@endforelse
</div>
</main>

@include('layouts.footer')
<script>
    const vehiclesGrid = document.getElementById('vehicles-grid');
    const totalVehiclesCount = document.getElementById('total-vehicles-count');
    const vehiclesDataUrl = "{{ route('vehicle-available.data') }}";
    const vehicleCounterAnimationFrames = new WeakMap();
    const vehicleCounterReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function statusBadgeClass(status) {
        if (status === 'Available') return 'bg-tertiary-fixed text-on-tertiary-fixed-variant';
        if (status === 'Reserved') return 'bg-secondary-container text-on-secondary-container';
        if (status === 'On Business Trip') return 'bg-primary-fixed text-on-primary-fixed-variant';
        return 'bg-error-container text-on-error-container';
    }

    function toVehicleCounterNumber(value) {
        const parsed = Number(String(value ?? '').replace(/[^0-9.-]/g, ''));
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatVehicleCounterNumber(value, decimals = 0) {
        return Number(value).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    function animateVehicleCounter(element, targetValue, options = {}) {
        if (!element) {
            return;
        }

        const decimals = Number(options.decimals ?? 0);
        const duration = Number(options.duration ?? 700);
        const numericTarget = Number(targetValue);
        const target = Number.isFinite(numericTarget) ? numericTarget : 0;

        const existingFrameId = vehicleCounterAnimationFrames.get(element);
        if (existingFrameId) {
            cancelAnimationFrame(existingFrameId);
        }

        const storedValue = Number(element.dataset.countValue);
        const start = Number.isFinite(storedValue) ? storedValue : toVehicleCounterNumber(element.textContent);

        if (vehicleCounterReducedMotion || duration <= 0 || Math.abs(start - target) < 0.001) {
            element.textContent = formatVehicleCounterNumber(target, decimals);
            element.dataset.countValue = String(target);
            return;
        }

        const startedAt = performance.now();

        function tick(now) {
            const progress = Math.min(1, (now - startedAt) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = start + ((target - start) * eased);

            element.textContent = formatVehicleCounterNumber(current, decimals);

            if (progress < 1) {
                const frameId = requestAnimationFrame(tick);
                vehicleCounterAnimationFrames.set(element, frameId);
                return;
            }

            element.textContent = formatVehicleCounterNumber(target, decimals);
            element.dataset.countValue = String(target);
            vehicleCounterAnimationFrames.delete(element);
        }

        const frameId = requestAnimationFrame(tick);
        vehicleCounterAnimationFrames.set(element, frameId);
    }

    function animateVehicleInitialCount() {
        if (!totalVehiclesCount) {
            return;
        }

        const target = toVehicleCounterNumber(totalVehiclesCount.textContent);
        totalVehiclesCount.dataset.countValue = '0';
        totalVehiclesCount.textContent = '0';
        animateVehicleCounter(totalVehiclesCount, target);
    }

    function vehicleCardMarkup(vehicle) {
        const imageSection = vehicle.image_url
            ? `<img src="${escapeHtml(vehicle.image_url)}" alt="${escapeHtml(vehicle.vehicle_code)}" class="w-full h-full object-cover"/>`
            : `<div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined text-4xl">airport_shuttle</span></div>`;

        return `
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/20 shadow-sm hover:shadow-md transition-all">
    <div class="flex justify-between items-start mb-4 gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-primary/60">${escapeHtml(vehicle.vehicle_type)}</p>
            <h3 class="text-xl font-bold text-on-surface">${escapeHtml(vehicle.vehicle_code)}</h3>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold ${statusBadgeClass(vehicle.status)}">
            ${escapeHtml(vehicle.status)}
        </span>
    </div>

    <div class="h-40 rounded-lg overflow-hidden bg-surface-container-low mb-4">
        ${imageSection}
    </div>

    <div class="bg-surface-container-low/50 p-3 rounded-lg border border-outline-variant/20 mb-4">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-primary/60">Vehicle Driver</p>
        <p class="text-sm font-bold text-on-surface">${escapeHtml(vehicle.driver_name || 'Unassigned')}</p>
    </div>

    <div class="flex justify-between items-center text-sm text-on-surface-variant">
        <span>${escapeHtml(vehicle.capacity_label || 'No capacity set')}</span>
        <span class="font-semibold text-primary">Live</span>
    </div>
</div>`;
    }

    function noDataMarkup() {
        return `<div class="col-span-full bg-surface-container-lowest border border-outline-variant/20 rounded-xl p-8 text-center text-on-surface-variant font-semibold">No vehicle records found.</div>`;
    }

    async function refreshVehiclesAjax() {
        try {
            const response = await fetch(vehiclesDataUrl, {
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
            const vehicles = Array.isArray(payload.vehicles) ? payload.vehicles : [];

            animateVehicleCounter(totalVehiclesCount, Number(payload.totalVehicles) || 0);

            if (vehicles.length === 0) {
                vehiclesGrid.innerHTML = noDataMarkup();
                return;
            }

            vehiclesGrid.innerHTML = vehicles.map(vehicleCardMarkup).join('');
        } catch (error) {
            console.error('Vehicle AJAX refresh failed', error);
        }
    }

    animateVehicleInitialCount();

    if (typeof window.emsLiveRefresh === 'function') {
        window.emsLiveRefresh(function () {
            return refreshVehiclesAjax();
        }, {
            intervalMs: 4000,
        });
    }
</script>
</body>
</html>
