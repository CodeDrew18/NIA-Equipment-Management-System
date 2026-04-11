<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>User Management | NIA Equipment Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "surface-container": "#eceef1",
              "on-surface": "#191c1e",
              "on-primary-fixed": "#001c3b",
              "error-container": "#ffdad6",
              "surface": "#f7f9fc",
              "on-secondary": "#ffffff",
              "on-primary-container": "#93bcfc",
              "on-secondary-fixed": "#00210a",
              "surface-container-high": "#e6e8eb",
              "on-secondary-container": "#3e6d47",
              "inverse-surface": "#2d3133",
              "surface-tint": "#335f99",
              "on-primary-fixed-variant": "#144780",
              "error": "#ba1a1a",
              "tertiary": "#003c34",
              "on-tertiary-fixed": "#00201b",
              "on-error-container": "#93000a",
              "primary-fixed": "#d5e3ff",
              "on-error": "#ffffff",
              "on-tertiary-fixed-variant": "#005046",
              "on-tertiary": "#ffffff",
              "surface-variant": "#e0e3e6",
              "inverse-on-surface": "#eff1f4",
              "primary": "#003466",
              "tertiary-fixed-dim": "#84d5c5",
              "background": "#f7f9fc",
              "outline": "#737781",
              "on-surface-variant": "#424750",
              "on-primary": "#ffffff",
              "on-tertiary-container": "#78caba",
              "surface-bright": "#f7f9fc",
              "secondary-container": "#b9ecbd",
              "surface-container-lowest": "#ffffff",
              "secondary-fixed-dim": "#a0d3a5",
              "tertiary-fixed": "#a0f2e1",
              "outline-variant": "#c3c6d1",
              "primary-container": "#1a4b84",
              "inverse-primary": "#a6c8ff",
              "surface-container-low": "#f2f4f7",
              "on-secondary-fixed-variant": "#22502d",
              "surface-dim": "#d8dadd",
              "surface-container-highest": "#e0e3e6",
              "secondary-fixed": "#bcefc0",
              "secondary": "#3a6843",
              "tertiary-container": "#00554a",
              "primary-fixed-dim": "#a6c8ff",
              "on-background": "#191c1e"
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
      body { font-family: 'Public Sans', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
<!-- TopNavBar -->
@include('layouts.admin_header');
<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pb-12 pt-28">
@php
  $roleBadgeClasses = [
    'admin' => 'bg-secondary-container text-on-secondary-container',
    'chief_of_motorpool_section' => 'bg-tertiary text-white',
    'operator' => 'bg-surface-container-highest text-on-surface-variant',
    'driver' => 'bg-primary-fixed text-primary',
    'user' => 'bg-surface-container-high text-on-surface-variant',
  ];
  $roleDescriptions = [
    'admin' => 'Full system access. Can manage users, fleet inventory, and high-level reports.',
    'chief_of_motorpool_section' => 'Oversees all maintenance cycles, equipment assignments, and operator scheduling.',
    'operator' => 'Can log equipment usage, request maintenance, and view assigned fleet status.',
    'driver' => 'Restricted access to trip logs, fuel tracking, and assignment notifications.',
    'user' => 'Read-only access to equipment availability and public reporting dashboards.',
  ];
@endphp
<!-- Page Header & Search -->
<div class="flex flex-col md:flex-row md:items-end justify-between mb-12 space-y-6 md:space-y-0">
<div class="space-y-2">
<h1 class="text-4xl font-extrabold tracking-tight text-primary font-headline">User Management</h1>
<p class="text-on-surface-variant max-w-lg">Manage system access levels, permissions, and administrative roles across the NIA equipment network.</p>
</div>
<form action="{{ route('admin.user_roles') }}" class="relative w-full md:w-96 group" method="GET">
<span class="absolute inset-y-0 left-0 pl-4 flex items-center text-outline">
<span class="material-symbols-outlined">search</span>
</span>
<input class="w-full pl-12 pr-4 py-3 bg-surface-container-highest border-none rounded-lg focus:ring-0 focus:bg-surface-container-lowest focus:border-b-2 focus:border-primary transition-all text-sm" name="search" placeholder="Search by name or ID number..." type="text" value="{{ $search }}"/>
</form>
</div>
@if (session('user_role_success'))
<div class="mb-6 rounded-lg bg-secondary-container px-4 py-3 text-sm font-medium text-on-secondary-container">
{{ session('user_role_success') }}
</div>
@endif
@if ($errors->any())
<div class="mb-6 rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">
{{ $errors->first() }}
</div>
@endif
<!-- Bento Layout for Content -->
<div class="grid grid-cols-12 gap-6">
<!-- Main User Table Card -->
<div class="col-span-12 bg-surface-container-low rounded-xl overflow-hidden shadow-sm">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-high">
<th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-on-surface-variant font-label">Name</th>
<th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-on-surface-variant font-label">ID Number</th>
<th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-on-surface-variant font-label">Access Level</th>
<th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-on-surface-variant font-label">Status</th>
<th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-on-surface-variant font-label text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($users as $user)
<tr class="{{ $loop->even ? 'bg-surface-container-highest/30' : '' }} hover:bg-surface-container-lowest transition-colors group">
<td class="px-6 py-5">
<div class="flex items-center space-x-3">
<div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center text-primary font-bold">
{{ collect(explode(' ', trim($user->name)))->filter()->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('') }}
</div>
<div>
<div class="font-bold text-on-surface">{{ $user->name }}</div>
<div class="text-xs text-on-surface-variant">{{ $user->email }}</div>
</div>
</div>
</td>
<td class="px-6 py-5 text-sm font-medium text-on-surface-variant">{{ $user->personnel_id ?? 'N/A' }}</td>
<td class="px-6 py-5">
@php
  $assignedRoles = collect(explode(',', (string) $user->role))
    ->map(fn($role) => trim($role))
    ->filter()
    ->values();
@endphp
<div class="flex flex-wrap gap-2">
@forelse ($assignedRoles as $assignedRole)
<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $roleBadgeClasses[$assignedRole] ?? 'bg-surface-container-highest text-on-surface-variant' }}">{{ $roleOptions[$assignedRole] ?? ucfirst(str_replace('_', ' ', $assignedRole)) }}</span>
@empty
<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-surface-container-highest text-on-surface-variant">No Role</span>
@endforelse
</div>
</td>
<td class="px-6 py-5">
@if ($user->email_verified_at)
<div class="flex items-center space-x-2">
<span class="w-2 h-2 rounded-full bg-secondary"></span>
<span class="text-xs font-semibold text-secondary">Active</span>
</div>
@else
<div class="flex items-center space-x-2">
<span class="w-2 h-2 rounded-full bg-error"></span>
<span class="text-xs font-semibold text-error">Inactive</span>
</div>
@endif
</td>
<td class="px-6 py-5 text-right">
<button class="open-role-modal text-primary hover:bg-primary-fixed px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center space-x-2 ml-auto" data-current-role="{{ $user->role }}" data-update-url="{{ route('admin.user_roles.update-role', $user) }}" data-user-name="{{ $user->name }}" type="button">
<span class="material-symbols-outlined text-sm">edit</span>
<span>Edit Access</span>
</button>
</td>
</tr>
@empty
<tr>
<td class="px-6 py-10 text-center text-sm text-on-surface-variant" colspan="5">No users found for the current search.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<!-- Pagination / Footer of table -->
<div class="px-6 py-4 bg-surface-container-high/50 flex justify-between items-center">
<span class="text-xs font-medium text-on-surface-variant">Showing {{ $users->count() }} of {{ $users->total() }} system users</span>
<div class="flex items-center space-x-2">
@if ($users->onFirstPage())
<span class="p-2 rounded-lg text-outline"><span class="material-symbols-outlined text-sm">chevron_left</span></span>
@else
<a class="p-2 hover:bg-white rounded-lg transition-colors" href="{{ $users->previousPageUrl() }}"><span class="material-symbols-outlined text-sm">chevron_left</span></a>
@endif
@php
  $startPage = max(1, $users->currentPage() - 2);
  $endPage = min($users->lastPage(), $users->currentPage() + 2);
@endphp
@for ($page = $startPage; $page <= $endPage; $page++)
<a class="min-w-8 h-8 px-2 inline-flex items-center justify-center rounded-lg text-xs font-semibold transition-colors {{ $page === $users->currentPage() ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-white' }}" href="{{ $users->url($page) }}">{{ $page }}</a>
@endfor
@if ($users->hasMorePages())
<a class="p-2 hover:bg-white rounded-lg transition-colors" href="{{ $users->nextPageUrl() }}"><span class="material-symbols-outlined text-sm">chevron_right</span></a>
@else
<span class="p-2 rounded-lg text-outline"><span class="material-symbols-outlined text-sm">chevron_right</span></span>
@endif
</div>
</div>
</div>
</div>
</main>
<!-- Modal Overlay -->
<div class="fixed inset-0 z-[100] bg-on-surface/40 backdrop-blur-sm items-center justify-center p-4 hidden" id="roleModalOverlay">
<div class="bg-surface-container-lowest w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[921px]">
<!-- Modal Header -->
<div class="px-8 py-6 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low">
<div>
<h2 class="text-xl font-bold text-primary font-headline">Manage User Access</h2>
<p class="text-xs text-on-surface-variant mt-1">Update permissions for <span class="text-on-surface font-semibold" id="roleModalUserName">Selected User</span></p>
</div>
<button class="text-on-surface-variant hover:text-primary transition-colors" id="roleModalCloseButton" type="button">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<form id="roleModalForm" method="POST">
@csrf
<input name="search" type="hidden" value="{{ $search }}"/>
<input name="page" type="hidden" value="{{ $users->currentPage() }}"/>
<!-- Modal Content -->
<div class="px-8 py-8 overflow-y-auto space-y-6">
<div class="grid grid-cols-1 gap-4">
@foreach ($roleOptions as $value => $label)
<label class="relative flex items-start p-4 bg-surface-container-low rounded-lg border-2 border-transparent hover:border-primary-fixed cursor-pointer transition-all group">
<input class="mt-1 rounded text-primary focus:ring-primary h-4 w-4" name="role[]" type="checkbox" value="{{ $value }}"/>
<div class="ml-4">
<span class="block text-sm font-bold text-on-surface">{{ $label }}</span>
<span class="block text-xs text-on-surface-variant mt-1 leading-relaxed">{{ $roleDescriptions[$value] ?? '' }}</span>
</div>
</label>
@endforeach
</div>
</div>
<!-- Modal Footer -->
<div class="px-8 py-6 bg-surface-container-low flex justify-end space-x-4">
<button class="px-6 py-2.5 text-sm font-bold text-on-surface-variant hover:text-primary transition-colors" id="roleModalCancelButton" type="button">Cancel</button>
<button class="px-8 py-2.5 bg-primary text-white text-sm font-bold rounded-lg shadow-lg shadow-primary/20 hover:bg-primary-container transition-all" type="submit">Save Changes</button>
</div>
</form>
</div>
</div>
<!-- Footer -->
@include('layouts.admin_footer');
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalOverlay = document.getElementById('roleModalOverlay');
  const modalForm = document.getElementById('roleModalForm');
  const modalUserName = document.getElementById('roleModalUserName');
  const closeButton = document.getElementById('roleModalCloseButton');
  const cancelButton = document.getElementById('roleModalCancelButton');
  const openButtons = document.querySelectorAll('.open-role-modal');

  const closeModal = function () {
    modalOverlay.classList.add('hidden');
    modalOverlay.classList.remove('flex');
  };

  const openModal = function () {
    modalOverlay.classList.remove('hidden');
    modalOverlay.classList.add('flex');
  };

  openButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const updateUrl = button.getAttribute('data-update-url');
      const currentRole = button.getAttribute('data-current-role');
      const userName = button.getAttribute('data-user-name');

      const selectedRoles = (currentRole || '')
        .split(',')
        .map(function (role) {
          return role.trim();
        })
        .filter(function (role) {
          return role !== '';
        });

      modalForm.setAttribute('action', updateUrl);
      modalUserName.textContent = userName || 'Selected User';

      const roleInputs = modalForm.querySelectorAll('input[name="role[]"]');
      roleInputs.forEach(function (input) {
        input.checked = selectedRoles.includes(input.value);
      });

      openModal();
    });
  });

  closeButton.addEventListener('click', closeModal);
  cancelButton.addEventListener('click', closeModal);

  modalOverlay.addEventListener('click', function (event) {
    if (event.target === modalOverlay) {
      closeModal();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !modalOverlay.classList.contains('hidden')) {
      closeModal();
    }
  });
});
</script>
</body></html>