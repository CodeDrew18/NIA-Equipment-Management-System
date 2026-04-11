<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Fuel Partnerships - NIA Equipment Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                }
            },
        },
    }
</script>
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    body {
        font-family: 'Public Sans', sans-serif;
    }
</style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
@include('layouts.admin_header')
<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pb-12 pt-28">
    <section class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-4xl font-extrabold text-primary tracking-tight">Fuel Partnerships</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">Manage fuel partnership contracts, validity periods, and per-liter pricing used by Fuel Issuance.</p>
        </div>
        <form method="GET" action="{{ route('admin.fuel_partnerships') }}" class="w-full max-w-md">
            <label class="sr-only" for="fuel-partnership-search">Search partnerships</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-outline">
                    <span class="material-symbols-outlined text-base">search</span>
                </span>
                <input
                    id="fuel-partnership-search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search name or validity date..."
                    class="w-full rounded-lg border-none bg-surface-container-highest py-3 pl-10 pr-4 text-sm font-medium focus:ring-2 focus:ring-primary"
                />
            </div>
        </form>
    </section>

    @if (session('fuel_partnership_success'))
    <div class="mb-6 rounded-lg border border-secondary/25 bg-secondary/10 px-4 py-3 text-sm font-semibold text-secondary">
        {{ session('fuel_partnership_success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/20 bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-outline">Total Partnerships</p>
            <p class="mt-2 text-3xl font-black text-primary">{{ $totalPartnerships }}</p>
        </div>
        <div class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-outline">Active Partnerships</p>
            <p class="mt-2 text-3xl font-black text-secondary">{{ $activePartnerships }}</p>
        </div>
    </div>

    <section class="mb-8 rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-6 shadow-sm">
        <div class="mb-5 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">add_circle</span>
            <h2 class="text-xl font-black text-primary">Create Fuel Partnership</h2>
        </div>

        <form method="POST" action="{{ route('admin.fuel_partnerships.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="search" value="{{ $search }}"/>
            <input type="hidden" name="page" value="{{ $partnerships->currentPage() }}"/>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Partnership Name</label>
                    <input type="text" name="partnership_name" required value="{{ old('partnership_name') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="Petron Fuel"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Valid From</label>
                    <input type="date" name="valid_from" required value="{{ old('valid_from') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Valid Until</label>
                    <input type="date" name="valid_until" required value="{{ old('valid_until') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Gasoline Price / Liter</label>
                    <input type="number" min="0" step="0.01" name="gasoline_price_per_liter" required value="{{ old('gasoline_price_per_liter') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="0.00"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Diesel Price / Liter</label>
                    <input type="number" min="0" step="0.01" name="diesel_price_per_liter" required value="{{ old('diesel_price_per_liter') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="0.00"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Fuel Save Price / Liter</label>
                    <input type="number" min="0" step="0.01" name="fuel_save_price_per_liter" required value="{{ old('fuel_save_price_per_liter') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="0.00"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">V-Power Price / Liter</label>
                    <input type="number" min="0" step="0.01" name="v_power_price_per_liter" required value="{{ old('v_power_price_per_liter') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="0.00"/>
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="rounded border-outline-variant text-primary focus:ring-primary"/>
                Mark as active partnership
            </label>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-on-primary transition-colors hover:bg-primary-container">
                    <span class="material-symbols-outlined text-base">save</span>
                    Save Partnership
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-surface-container-high/60 text-xs font-bold uppercase tracking-widest text-on-surface-variant">
                        <th class="px-5 py-4">Partnership</th>
                        <th class="px-5 py-4">Validity</th>
                        <th class="px-5 py-4">Gasoline</th>
                        <th class="px-5 py-4">Diesel</th>
                        <th class="px-5 py-4">Fuel Save</th>
                        <th class="px-5 py-4">V-Power</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-sm">
                    @forelse ($partnerships as $partnership)
                    <tr class="hover:bg-surface-container-low">
                        <td class="px-5 py-4">
                            <p class="font-bold text-on-surface">{{ $partnership->partnership_name }}</p>
                            <p class="text-xs text-outline">Created {{ optional($partnership->created_at)->format('M d, Y') }}</p>
                        </td>
                        <td class="px-5 py-4 font-semibold text-on-surface-variant">
                            {{ optional($partnership->valid_from)->format('M d, Y') }} - {{ optional($partnership->valid_until)->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-4 font-semibold">PHP {{ number_format((float) $partnership->gasoline_price_per_liter, 2) }}</td>
                        <td class="px-5 py-4 font-semibold">PHP {{ number_format((float) $partnership->diesel_price_per_liter, 2) }}</td>
                        <td class="px-5 py-4 font-semibold">PHP {{ number_format((float) $partnership->fuel_save_price_per_liter, 2) }}</td>
                        <td class="px-5 py-4 font-semibold">PHP {{ number_format((float) $partnership->v_power_price_per_liter, 2) }}</td>
                        <td class="px-5 py-4">
                            @if ($partnership->is_active)
                            <span class="inline-flex rounded-full bg-secondary-container px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-secondary-container">Active</span>
                            @else
                            <span class="inline-flex rounded-full bg-surface-container-high px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @if (!$partnership->is_active)
                                <form method="POST" action="{{ route('admin.fuel_partnerships.activate', $partnership) }}">
                                    @csrf
                                    <input type="hidden" name="search" value="{{ $search }}"/>
                                    <input type="hidden" name="page" value="{{ $partnerships->currentPage() }}"/>
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-secondary/30 bg-secondary-container px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-secondary-container hover:opacity-90">
                                        <span class="material-symbols-outlined text-sm">check_circle</span>
                                        Activate
                                    </button>
                                </form>
                                @else
                                <span class="inline-flex items-center gap-1 rounded-lg border border-secondary/25 bg-secondary/10 px-3 py-2 text-xs font-bold uppercase tracking-wider text-secondary">
                                    <span class="material-symbols-outlined text-sm">verified</span>
                                    Active
                                </span>
                                @endif

                                <button
                                    type="button"
                                    class="open-edit-modal inline-flex items-center gap-1 rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-bold uppercase tracking-wider text-primary hover:bg-primary-fixed/30"
                                    data-id="{{ $partnership->id }}"
                                    data-name="{{ $partnership->partnership_name }}"
                                    data-valid-from="{{ optional($partnership->valid_from)->toDateString() }}"
                                    data-valid-until="{{ optional($partnership->valid_until)->toDateString() }}"
                                    data-gasoline="{{ number_format((float) $partnership->gasoline_price_per_liter, 2, '.', '') }}"
                                    data-diesel="{{ number_format((float) $partnership->diesel_price_per_liter, 2, '.', '') }}"
                                    data-fuel-save="{{ number_format((float) $partnership->fuel_save_price_per_liter, 2, '.', '') }}"
                                    data-v-power="{{ number_format((float) $partnership->v_power_price_per_liter, 2, '.', '') }}"
                                    data-is-active="{{ $partnership->is_active ? '1' : '0' }}"
                                >
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.fuel_partnerships.destroy', $partnership) }}" onsubmit="return confirm('Delete this fuel partnership?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="search" value="{{ $search }}"/>
                                    <input type="hidden" name="page" value="{{ $partnerships->currentPage() }}"/>
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-error/30 bg-error-container px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-error-container hover:opacity-90">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-sm font-semibold text-outline">No fuel partnerships found for the current filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-outline-variant/10 bg-surface-container-high/40 px-5 py-4 text-xs font-semibold text-outline sm:flex-row sm:items-center sm:justify-between">
            <p>Showing {{ $partnerships->firstItem() ?? 0 }}-{{ $partnerships->lastItem() ?? 0 }} of {{ $partnerships->total() }} partnerships</p>
            <div>
                {{ $partnerships->links() }}
            </div>
        </div>
    </section>
</main>

<div id="fuel-partnership-modal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-3xl rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-primary">Edit Fuel Partnership</h3>
                <p class="text-xs font-semibold text-outline">Update validity and per-liter price rates.</p>
            </div>
            <button type="button" id="fuel-partnership-modal-close" class="rounded-lg p-2 text-outline hover:bg-surface-container-high">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="fuel-partnership-edit-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="search" value="{{ $search }}"/>
            <input type="hidden" name="page" value="{{ $partnerships->currentPage() }}"/>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Partnership Name</label>
                    <input id="edit-partnership-name" type="text" name="partnership_name" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Valid From</label>
                    <input id="edit-valid-from" type="date" name="valid_from" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Valid Until</label>
                    <input id="edit-valid-until" type="date" name="valid_until" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Gasoline Price / Liter</label>
                    <input id="edit-gasoline-price" type="number" min="0" step="0.01" name="gasoline_price_per_liter" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Diesel Price / Liter</label>
                    <input id="edit-diesel-price" type="number" min="0" step="0.01" name="diesel_price_per_liter" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Fuel Save Price / Liter</label>
                    <input id="edit-fuel-save-price" type="number" min="0" step="0.01" name="fuel_save_price_per_liter" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">V-Power Price / Liter</label>
                    <input id="edit-v-power-price" type="number" min="0" step="0.01" name="v_power_price_per_liter" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface">
                <input id="edit-is-active" type="checkbox" name="is_active" value="1" class="rounded border-outline-variant text-primary focus:ring-primary"/>
                Mark as active partnership
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="fuel-partnership-modal-cancel" class="rounded-lg border border-outline-variant px-4 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-on-primary hover:bg-primary-container">
                    <span class="material-symbols-outlined text-sm">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@include('layouts.admin_footer')
<script>
(function () {
    const modal = document.getElementById('fuel-partnership-modal');
    const closeButton = document.getElementById('fuel-partnership-modal-close');
    const cancelButton = document.getElementById('fuel-partnership-modal-cancel');
    const form = document.getElementById('fuel-partnership-edit-form');
    const buttons = Array.from(document.querySelectorAll('.open-edit-modal'));
    const updateUrlTemplate = "{{ route('admin.fuel_partnerships.update', ['fuelPartnership' => '__ID__']) }}";

    if (!modal || !form || buttons.length < 1) {
        return;
    }

    const nameInput = document.getElementById('edit-partnership-name');
    const validFromInput = document.getElementById('edit-valid-from');
    const validUntilInput = document.getElementById('edit-valid-until');
    const gasolineInput = document.getElementById('edit-gasoline-price');
    const dieselInput = document.getElementById('edit-diesel-price');
    const fuelSaveInput = document.getElementById('edit-fuel-save-price');
    const vPowerInput = document.getElementById('edit-v-power-price');
    const activeInput = document.getElementById('edit-is-active');

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function showModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const id = button.getAttribute('data-id');
            form.action = updateUrlTemplate.replace('__ID__', String(id || ''));

            nameInput.value = button.getAttribute('data-name') || '';
            validFromInput.value = button.getAttribute('data-valid-from') || '';
            validUntilInput.value = button.getAttribute('data-valid-until') || '';
            gasolineInput.value = button.getAttribute('data-gasoline') || '0.00';
            dieselInput.value = button.getAttribute('data-diesel') || '0.00';
            fuelSaveInput.value = button.getAttribute('data-fuel-save') || '0.00';
            vPowerInput.value = button.getAttribute('data-v-power') || '0.00';
            activeInput.checked = button.getAttribute('data-is-active') === '1';

            showModal();
        });
    });

    if (closeButton) {
        closeButton.addEventListener('click', hideModal);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', hideModal);
    }

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            hideModal();
        }
    });
})();
</script>
</body>
</html>
