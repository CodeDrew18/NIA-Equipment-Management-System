<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Vehicle Availability - Institutional Management</title>
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
@include('layouts.admin_header')

<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pb-8 pt-28 md:pt-32">
@if (session('admin_vehicle_success'))
<div class="mb-6 rounded-lg border border-secondary/30 bg-secondary/10 px-4 py-3 text-sm font-semibold text-secondary">
    {{ session('admin_vehicle_success') }}
</div>
@endif

<section class="mb-10 flex flex-col md:flex-row justify-between md:items-end gap-6">
<div class="max-w-2xl">
<h1 class="text-4xl font-extrabold text-primary mb-2 tracking-tight">Admin Vehicle Availability</h1>
<p class="text-on-surface-variant leading-relaxed">Update each vehicle's assigned driver and operational status.</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/10 flex flex-col min-w-[170px]">
<span class="text-xs font-semibold text-primary/60 uppercase tracking-widest mb-1">Total Vehicles</span>
<span class="text-3xl font-bold text-primary"><span id="ava-total-vehicles">{{ $totalVehicles }}</span> Units</span>
</div>
</section>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
@forelse ($vehicles as $vehicle)
<form method="POST" action="{{ route('admin.vehicle-availability.update', $vehicle) }}" enctype="multipart/form-data" class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/20 shadow-sm hover:shadow-md transition-all">
@csrf
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

<label for="vehicle_image_{{ $vehicle->id }}" class="group relative block h-32 rounded-lg overflow-hidden bg-surface-container-low mb-4 cursor-pointer border border-outline-variant/30 hover:border-primary/60 transition-colors" title="Click to upload vehicle image">
@if ($vehicle->image_url)
<img id="vehicle_image_preview_{{ $vehicle->id }}" src="{{ $vehicle->image_url }}" alt="{{ $vehicle->vehicle_code }}" class="w-full h-full object-cover"/>
@else
<img id="vehicle_image_preview_{{ $vehicle->id }}" src="" alt="{{ $vehicle->vehicle_code }}" class="hidden w-full h-full object-cover"/>
<div id="vehicle_image_placeholder_{{ $vehicle->id }}" class="w-full h-full flex items-center justify-center text-outline">
<span class="material-symbols-outlined text-4xl">airport_shuttle</span>
</div>
@endif
<div class="absolute inset-0 bg-black/35 text-white text-xs font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
Click to Upload
</div>
</label>
<input
id="vehicle_image_{{ $vehicle->id }}"
name="vehicle_image"
type="file"
accept="image/png,image/jpeg,image/webp"
onchange="previewVehicleImage(event, {{ $vehicle->id }})"
class="hidden"
/>

<div class="space-y-3">
<div>
<label class="text-xs font-semibold uppercase tracking-wider text-primary/60" for="driver_name_{{ $vehicle->id }}">Driver Name</label>
@php
    $selectedDriver = old('driver_name', $vehicle->driver_name);
@endphp
<select
id="driver_name_{{ $vehicle->id }}"
name="driver_name"
class="mt-1 block w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"
>
<option value="">No assigned driver</option>
@foreach (($drivers ?? collect()) as $driverName)
<option value="{{ $driverName }}" @selected($selectedDriver === $driverName)>{{ $driverName }}</option>
@endforeach
@if (!empty($selectedDriver) && !collect($drivers ?? [])->contains($selectedDriver))
<option value="{{ $selectedDriver }}" selected>{{ $selectedDriver }}</option>
@endif
</select>
</div>

<div>
<label class="text-xs font-semibold uppercase tracking-wider text-primary/60" for="status_{{ $vehicle->id }}">Status</label>
<select
id="status_{{ $vehicle->id }}"
name="status"
class="mt-1 block w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"
>
@foreach (['Available', 'On Business Trip', 'Reserved', 'Maintenance', 'Unavailable'] as $status)
<option value="{{ $status }}" @selected(old('status', $vehicle->status) === $status)>{{ $status }}</option>
@endforeach
</select>
</div>

{{-- <p class="-mt-2 text-[10px] text-on-surface-variant">Accepted: JPG, PNG, WEBP (max 4MB). Click the image area to select a file.</p> --}}

<div class="text-xs text-on-surface-variant">{{ $vehicle->capacity_label ?: 'No capacity set' }}</div>

<button type="submit" class="w-full rounded-lg bg-primary text-on-primary py-2 text-sm font-semibold hover:bg-primary-container transition-colors">
Save Changes
</button>
</div>
</form>
@empty
<div class="col-span-full bg-surface-container-lowest border border-outline-variant/20 rounded-xl p-8 text-center text-on-surface-variant font-semibold">
No vehicle records found. Add vehicle records to the table first.
</div>
@endforelse
</div>
</main>

@include('layouts.admin_footer')
<script>
function previewVehicleImage(event, vehicleId) {
    const input = event.target;
    const file = input.files && input.files[0] ? input.files[0] : null;
    if (!file) {
        return;
    }

    const preview = document.getElementById(`vehicle_image_preview_${vehicleId}`);
    const placeholder = document.getElementById(`vehicle_image_placeholder_${vehicleId}`);

    if (!preview) {
        return;
    }

    const objectUrl = URL.createObjectURL(file);
    preview.src = objectUrl;
    preview.classList.remove('hidden');

    if (placeholder) {
        placeholder.classList.add('hidden');
    }
}

(function () {
    const totalVehiclesEl = document.getElementById('ava-total-vehicles');
    if (!totalVehiclesEl) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const targetValue = Number(String(totalVehiclesEl.textContent || '').replace(/[^0-9.-]/g, '')) || 0;

    if (prefersReducedMotion || targetValue <= 0) {
        totalVehiclesEl.textContent = targetValue.toLocaleString('en-US');
        return;
    }

    totalVehiclesEl.textContent = '0';
    const startedAt = performance.now();
    const duration = 700;

    function tick(now) {
        const progress = Math.min(1, (now - startedAt) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(targetValue * eased);

        totalVehiclesEl.textContent = current.toLocaleString('en-US');

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    }

    requestAnimationFrame(tick);
})();
</script>
</body>
</html>
