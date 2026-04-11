<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Vehicle Assignment | NIA Fleet Manager</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-container": "#b9ecbd",
                        "inverse-on-surface": "#eff1f4",
                        "on-tertiary-container": "#78caba",
                        "on-primary-container": "#93bcfc",
                        "outline-variant": "#c3c6d1",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed-dim": "#a0d3a5",
                        "on-secondary-fixed-variant": "#22502d",
                        "inverse-surface": "#2d3133",
                        "on-error-container": "#93000a",
                        "tertiary-container": "#00554a",
                        "on-background": "#191c1e",
                        "surface": "#f7f9fc",
                        "inverse-primary": "#a6c8ff",
                        "tertiary-fixed-dim": "#84d5c5",
                        "surface-variant": "#e0e3e6",
                        "on-surface-variant": "#424750",
                        "error-container": "#ffdad6",
                        "on-tertiary": "#ffffff",
                        "outline": "#737781",
                        "on-secondary": "#ffffff",
                        "on-primary-fixed-variant": "#144780",
                        "surface-container-low": "#f2f4f7",
                        "surface-container-high": "#e6e8eb",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#001c3b",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-tertiary-fixed-variant": "#005046",
                        "secondary-fixed": "#bcefc0",
                        "surface-tint": "#335f99",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "on-secondary-fixed": "#00210a",
                        "surface-container-highest": "#e0e3e6",
                        "surface-container": "#eceef1",
                        "primary-container": "#1a4b84",
                        "primary-fixed": "#d5e3ff",
                        "background": "#f7f9fc",
                        "secondary": "#3a6843",
                        "on-surface": "#191c1e",
                        "tertiary": "#003c34",
                        "surface-dim": "#d8dadd",
                        "primary": "#003466",
                        "on-tertiary-fixed": "#00201b",
                        "tertiary-fixed": "#a0f2e1",
                        "surface-bright": "#f7f9fc",
                        "on-secondary-container": "#3e6d47"
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
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col">
@include('layouts.admin_header')

<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-10">
    <header class="mb-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <span class="font-label text-xs uppercase tracking-widest font-semibold text-secondary mb-2 block">Fleet Logistics Workflow</span>
            <h1 class="font-headline text-4xl font-extrabold text-primary tracking-tight">Vehicle Assignment Queue</h1>
            <p class="text-on-surface-variant mt-2 max-w-2xl">Approved requests must be assigned to an available vehicle first. Once assigned, the request becomes ready for Daily Driver's Trip Ticket processing.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-surface-container-lowest border border-outline-variant/15 rounded-xl px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Available Fleet</p>
                <p class="text-2xl font-black text-primary mt-1">{{ $availableVehicleCounts['all'] }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/15 rounded-xl px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Coaster</p>
                <p class="text-2xl font-black text-primary mt-1">{{ $availableVehicleCounts['coaster'] }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/15 rounded-xl px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Van</p>
                <p class="text-2xl font-black text-primary mt-1">{{ $availableVehicleCounts['van'] }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/15 rounded-xl px-4 py-3 shadow-sm">
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">Pickup</p>
                <p class="text-2xl font-black text-primary mt-1">{{ $availableVehicleCounts['pickup'] }}</p>
            </div>
        </div>
    </header>

    @if (session('admin_vehicle_assignment_success'))
    <div class="mb-6 rounded-xl border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container text-sm font-semibold">
        {{ session('admin_vehicle_assignment_success') }}
    </div>
    @endif

    @if (session('admin_dtt_success'))
    <div class="mb-6 rounded-xl border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container text-sm font-semibold">
        {{ session('admin_dtt_success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 rounded-xl border border-error/30 bg-error-container p-4 text-on-error-container text-sm font-semibold">
        {{ $errors->first() }}
    </div>
    @endif

    <section class="bg-surface-container-low rounded-xl p-4 mb-6 border border-outline-variant/10">
        <form method="GET" action="{{ route('admin.vehicle_assignment') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-grow min-w-[260px]">
                <label class="text-[10px] font-bold text-outline uppercase mb-1 px-1 block">Search Approved Request</label>
                <input name="search" value="{{ $search }}" class="w-full bg-surface-container-lowest border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" placeholder="Request ID, requestor, destination, vehicle type"/>
            </div>
            <button class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2" type="submit">
                <span class="material-symbols-outlined text-sm">search</span> Search
            </button>
            <a href="{{ route('admin.vehicle_assignment') }}" class="bg-surface-container-highest hover:bg-surface-variant text-on-surface-variant px-4 py-2 rounded-lg text-sm font-semibold transition-all">Reset</a>
        </form>
    </section>

    <section class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-outline-variant/10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-high/50 text-on-surface-variant text-xs font-bold uppercase tracking-widest border-b border-outline-variant/10">
                    <th class="px-6 py-4">Request</th>
                    <th class="px-6 py-4">Requestor</th>
                    <th class="px-6 py-4">Destination</th>
                    <th class="px-6 py-4">Requested Vehicle</th>
                    <th class="px-6 py-4">Assign Available Vehicle</th>
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm font-body divide-y divide-outline-variant/10">
                @forelse ($requests as $item)
                @php
                    $requestedMix = is_array($item->requested_vehicle_mix ?? null)
                        ? $item->requested_vehicle_mix
                        : [
                            'coaster' => 0,
                            'van' => 0,
                            'pickup' => 0,
                            'other' => max(1, (int) ($item->vehicle_quantity ?? 1)),
                        ];

                    $vehicleLabels = [
                        'coaster' => 'Coaster',
                        'van' => 'Van',
                        'pickup' => 'Pick-up',
                        'other' => 'Vehicle',
                    ];

                    $vehicleIcons = [
                        'coaster' => 'directions_bus',
                        'van' => 'airport_shuttle',
                        'pickup' => 'local_shipping',
                        'other' => 'airport_shuttle',
                    ];

                    $oldRequestId = (int) old('transportation_request_id', 0);
                    $isSelected = $selectedRequestId === (int) $item->id || $oldRequestId === (int) $item->id;
                    $assignmentFormId = 'vehicle-assignment-form-' . $item->id;
                @endphp
                <tr class="hover:bg-surface-container-low transition-colors {{ $isSelected ? 'bg-primary-fixed/30' : '' }}">
                    <td class="px-6 py-5">
                        <p class="font-bold text-primary">{{ $item->form_id }}</p>
                        <p class="text-xs text-outline">{{ optional($item->request_date)->format('M d, Y') }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="font-semibold text-on-surface">{{ $item->requestor_name }}</p>
                        <p class="text-xs text-outline">{{ $item->requested_by }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="font-medium text-on-surface">{{ $item->destination }}</p>
                        <p class="text-xs text-outline line-clamp-1">{{ $item->purpose ?: 'No purpose provided' }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-wrap gap-2">
                            @foreach (['coaster', 'van', 'pickup', 'other'] as $typeKey)
                                @php
                                    $requiredCount = (int) ($requestedMix[$typeKey] ?? 0);
                                @endphp
                                @continue($requiredCount < 1)

                                <span class="inline-flex items-center bg-primary-fixed text-on-primary-fixed-variant px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-tight">
                                    <span class="material-symbols-outlined text-[14px] mr-1">{{ $vehicleIcons[$typeKey] }}</span>
                                    {{ $vehicleLabels[$typeKey] }} x{{ $requiredCount }}
                                </span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs font-semibold text-outline">Total required: {{ (int) ($item->required_vehicle_total ?? array_sum($requestedMix)) }}</p>
                    </td>
                    <td class="px-6 py-5">
                        @php
                            $missingRequiredInventory = false;
                        @endphp

                        <form id="{{ $assignmentFormId }}" method="POST" action="{{ route('admin.vehicle_assignment.assign', $item) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="transportation_request_id" value="{{ $item->id }}">
                            <input type="hidden" name="assignment_action" value="assign_vehicles">

                            @foreach (['coaster', 'van', 'pickup', 'other'] as $typeKey)
                                @php
                                    $requiredCount = (int) ($requestedMix[$typeKey] ?? 0);
                                @endphp
                                @continue($requiredCount < 1)

                                @php
                                    $vehicleOptions = $typeKey === 'other'
                                        ? $availableVehicles
                                        : ($availableVehiclesByType[$typeKey] ?? collect());

                                    $oldSelections = $oldRequestId === (int) $item->id
                                        ? (array) old('vehicle_codes.' . $typeKey, [])
                                        : [];

                                    if ($vehicleOptions->isEmpty()) {
                                        $missingRequiredInventory = true;
                                    }
                                @endphp

                                <div class="rounded-lg border border-outline-variant/20 bg-surface-container-low p-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                        {{ $vehicleLabels[$typeKey] }} Slots ({{ $requiredCount }})
                                    </p>

                                    @if ($vehicleOptions->isEmpty())
                                        <p class="text-xs font-semibold text-error">No available {{ strtolower($vehicleLabels[$typeKey]) }} units right now.</p>
                                    @else
                                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                            @for ($slotIndex = 0; $slotIndex < $requiredCount; $slotIndex++)
                                                @php
                                                    $oldSelectedCode = (string) ($oldSelections[$slotIndex] ?? '');
                                                @endphp
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-outline mb-1">
                                                        {{ $vehicleLabels[$typeKey] }} #{{ $slotIndex + 1 }}
                                                    </label>
                                                    <select name="vehicle_codes[{{ $typeKey }}][]" class="w-full rounded-lg border border-outline-variant/40 bg-white py-2 px-3 text-sm focus:ring-2 focus:ring-primary" required>
                                                        <option value="">Select available vehicle...</option>
                                                        @foreach ($vehicleOptions as $vehicle)
                                                            <option value="{{ $vehicle->vehicle_code }}" @selected($oldSelectedCode === $vehicle->vehicle_code)>
                                                                {{ $vehicle->vehicle_code }} - {{ $vehicle->vehicle_type }}
                                                                @if (!empty($vehicle->capacity_label))
                                                                    ({{ $vehicle->capacity_label }})
                                                                @endif
                                                                @if (!empty($vehicle->driver_name))
                                                                    | Driver: {{ $vehicle->driver_name }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </form>

                        @if ($missingRequiredInventory)
                        <p class="mt-2 text-xs font-semibold text-error">One or more required vehicle types are unavailable. Update Vehicle Availability first.</p>
                        @endif
                    </td>

                    <td class="px-6 py-5 align-middle text-center">
                        <button
                            type="submit"
                            form="{{ $assignmentFormId }}"
                            name="assignment_action"
                            value="assign_vehicles"
                            class="mx-auto block rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary-container transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            @disabled($missingRequiredInventory)
                        >
                            Assign Vehicles
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-sm font-semibold text-outline">
                        No approved requests are waiting for vehicle assignment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bg-surface-container-high/30 px-6 py-4 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant font-medium">
                Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} approved requests pending vehicle assignment
            </p>
            <div>
                {{ $requests->links() }}
            </div>
        </div>
    </section>
</main>

@include('layouts.admin_footer')
</body>
</html>
