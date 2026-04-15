<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Assignatories - NIA Equipment Management</title>
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
            <h1 class="text-4xl font-extrabold text-primary tracking-tight">Assignatories</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">Manage the signatory name and position used by request forms, fuel issuance slips, and travel reports.</p>
        </div>
        <form method="GET" action="{{ route('admin.assignatories') }}" class="w-full max-w-md">
            <label class="sr-only" for="assignatory-search">Search assignatories</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-outline">
                    <span class="material-symbols-outlined text-base">search</span>
                </span>
                <input
                    id="assignatory-search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search name or position..."
                    class="w-full rounded-lg border-none bg-surface-container-highest py-3 pl-10 pr-4 text-sm font-medium focus:ring-2 focus:ring-primary"
                />
            </div>
        </form>
    </section>

    @if (session('assignatory_success'))
    <div class="mb-6 rounded-lg border border-secondary/25 bg-secondary/10 px-4 py-3 text-sm font-semibold text-secondary">
        {{ session('assignatory_success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/20 bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="mb-8 rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-6 shadow-sm">
        <div class="mb-4 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">verified_user</span>
            <h2 class="text-xl font-black text-primary">Current Signatory</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-outline">Name</p>
                <p class="mt-2 text-2xl font-black text-primary">{{ $currentAssignatory['name'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-outline">Position</p>
                <p class="mt-2 text-lg font-bold text-on-surface">{{ $currentAssignatory['position'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <section class="mb-8 rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-6 shadow-sm">
        <div class="mb-5 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">add_circle</span>
            <h2 class="text-xl font-black text-primary">Add New Assignatory</h2>
        </div>

        <form method="POST" action="{{ route('admin.assignatories.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="search" value="{{ $search }}"/>
            <input type="hidden" name="page" value="{{ $assignatories->currentPage() }}"/>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Name</label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="ENGR. JUAN DELA CRUZ"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Position</label>
                    <input type="text" name="position" required value="{{ old('position') }}" class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary" placeholder="Division Manager A, EOD."/>
                </div>
            </div>

            @if ($supportsActiveFlag)
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="rounded border-outline-variant text-primary focus:ring-primary"/>
                Set as active assignatory
            </label>
            @endif

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-on-primary transition-colors hover:bg-primary-container">
                    <span class="material-symbols-outlined text-base">save</span>
                    Save Assignatory
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-surface-container-high/60 text-xs font-bold uppercase tracking-widest text-on-surface-variant">
                        <th class="px-5 py-4">Name</th>
                        <th class="px-5 py-4">Position</th>
                        <th class="px-5 py-4">Updated</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-sm">
                    @forelse ($assignatories as $assignatory)
                    <tr class="hover:bg-surface-container-low">
                        <td class="px-5 py-4">
                            <p class="font-bold text-on-surface">{{ $assignatory->name }}</p>
                        </td>
                        <td class="px-5 py-4 font-semibold text-on-surface-variant">{{ $assignatory->position }}</td>
                        <td class="px-5 py-4 text-on-surface-variant">{{ optional($assignatory->updated_at)->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-4">
                            @if ($supportsActiveFlag && $assignatory->is_active)
                            <span class="inline-flex rounded-full bg-secondary-container px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-secondary-container">Current</span>
                            @elseif ((int) $assignatory->id === (int) $currentAssignatoryId)
                            <span class="inline-flex rounded-full bg-secondary-container px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-secondary-container">Current</span>
                            @else
                            <span class="inline-flex rounded-full bg-surface-container-high px-3 py-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Saved</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($supportsActiveFlag && !$assignatory->is_active)
                                <form method="POST" action="{{ route('admin.assignatories.activate', $assignatory) }}">
                                    @csrf
                                    <input type="hidden" name="search" value="{{ $search }}"/>
                                    <input type="hidden" name="page" value="{{ $assignatories->currentPage() }}"/>
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-secondary/30 bg-secondary-container px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-secondary-container hover:opacity-90">
                                        <span class="material-symbols-outlined text-sm">check_circle</span>
                                        Activate
                                    </button>
                                </form>
                                @endif

                                <button
                                    type="button"
                                    class="open-assignatory-edit inline-flex items-center gap-1 rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-bold uppercase tracking-wider text-primary hover:bg-primary-fixed/30"
                                    data-update-url="{{ route('admin.assignatories.update', $assignatory) }}"
                                    data-name="{{ $assignatory->name }}"
                                    data-position="{{ $assignatory->position }}"
                                    data-is-active="{{ $assignatory->is_active ? '1' : '0' }}"
                                >
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('admin.assignatories.destroy', $assignatory) }}" class="inline-flex" onsubmit="return confirm('Delete this assignatory?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="search" value="{{ $search }}"/>
                                    <input type="hidden" name="page" value="{{ $assignatories->currentPage() }}"/>
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-error/25 bg-error-container px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-error-container hover:opacity-90">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-outline">No assignatories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assignatories->hasPages())
        <div class="border-t border-outline-variant/10 px-5 py-4">
            {{ $assignatories->links() }}
        </div>
        @endif
    </section>
</main>

<div id="assignatory-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
        <div class="mb-4 flex items-center gap-3 text-primary">
            <span class="material-symbols-outlined">edit</span>
            <h3 class="text-lg font-bold">Edit Assignatory</h3>
        </div>

        <form id="assignatory-edit-form" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="search" value="{{ $search }}"/>
            <input type="hidden" name="page" value="{{ $assignatories->currentPage() }}"/>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Name</label>
                    <input id="edit-assignatory-name" type="text" name="name" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-outline">Position</label>
                    <input id="edit-assignatory-position" type="text" name="position" required class="w-full rounded-lg border-outline-variant text-sm focus:border-primary focus:ring-primary"/>
                </div>
            </div>

            @if ($supportsActiveFlag)
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface">
                <input id="edit-assignatory-active" type="checkbox" name="is_active" value="1" class="rounded border-outline-variant text-primary focus:ring-primary"/>
                Set as active assignatory
            </label>
            @endif

            <div class="mt-6 flex justify-end gap-3">
                <button id="assignatory-edit-cancel" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary/90">Update</button>
            </div>
        </form>
    </div>
</div>

@include('layouts.admin_footer')

<script>
    (function () {
        const modal = document.getElementById('assignatory-edit-modal');
        const form = document.getElementById('assignatory-edit-form');
        const cancelButton = document.getElementById('assignatory-edit-cancel');
        const nameInput = document.getElementById('edit-assignatory-name');
        const positionInput = document.getElementById('edit-assignatory-position');
        const activeCheckbox = document.getElementById('edit-assignatory-active');

        if (!modal || !form || !nameInput || !positionInput) {
            return;
        }

        function openModal(button) {
            form.action = String(button.getAttribute('data-update-url') || '');
            nameInput.value = String(button.getAttribute('data-name') || '');
            positionInput.value = String(button.getAttribute('data-position') || '');

            if (activeCheckbox) {
                activeCheckbox.checked = String(button.getAttribute('data-is-active') || '') === '1';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.querySelectorAll('.open-assignatory-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button);
            });
        });

        if (cancelButton) {
            cancelButton.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    })();
</script>
</body>
</html>
