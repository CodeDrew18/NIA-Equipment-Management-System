<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Profile Management | NIA Equipment Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#003466",
                    "primary-container": "#1a4b84",
                    "on-primary": "#ffffff",
                    "surface": "#f7f9fc",
                    "surface-container-low": "#f2f4f7",
                    "surface-container-lowest": "#ffffff",
                    "outline-variant": "#c3c6d1",
                    "outline": "#737781",
                    "on-surface": "#191c1e",
                    "on-surface-variant": "#424750",
                    "primary-fixed": "#d5e3ff",
                    "on-primary-fixed": "#001c3b"
                },
                fontFamily: {
                    "headline": ["Public Sans"],
                    "body": ["Public Sans"],
                },
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
<body class="bg-surface font-body text-on-surface min-h-screen flex flex-col">
@if ($isAdminArea)
@include('layouts.admin_header')
@else
@include('layouts.header')
@endif

<main class="flex-grow w-full max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-14">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary tracking-tight">Profile Management</h1>
        <p class="text-sm text-on-surface-variant mt-2">View the currently logged-in account details and role access.</p>
    </div>

    <section class="rounded-2xl border border-outline-variant/30 bg-surface-container-lowest shadow-sm overflow-hidden">
        <div class="bg-primary-fixed/50 border-b border-outline-variant/20 px-6 py-5">
            <p class="text-xs uppercase tracking-[0.18em] font-bold text-outline">Current Session</p>
            <h2 class="text-xl font-black text-primary mt-1">Logged-in User</h2>
        </div>

        <div class="px-6 py-6 grid grid-cols-1 lg:grid-cols-[auto,1fr] gap-6 items-start">
            <div>
                @if ($authUser?->resolved_profile_image_url)
                    <img src="{{ $authUser->resolved_profile_image_url }}" alt="Profile image" class="h-24 w-24 rounded-2xl object-cover border border-outline-variant/30 shadow-sm"/>
                @else
                    <div class="h-24 w-24 rounded-2xl bg-primary text-on-primary flex items-center justify-center text-3xl font-black border border-primary/30 shadow-sm">
                        {{ $authUser?->profile_initials ?? 'U' }}
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-outline-variant/25 bg-surface-container-low p-4">
                    <p class="text-[11px] uppercase tracking-wider font-bold text-outline">Name</p>
                    <p class="text-base font-bold text-on-surface mt-1">{{ $authUser?->name ?? 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-outline-variant/25 bg-surface-container-low p-4">
                    <p class="text-[11px] uppercase tracking-wider font-bold text-outline">Role</p>
                    <p class="text-base font-bold text-on-surface mt-1">{{ $authUser?->role_display ?? 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-outline-variant/25 bg-surface-container-low p-4">
                    <p class="text-[11px] uppercase tracking-wider font-bold text-outline">Personnel ID</p>
                    <p class="text-base font-bold text-on-surface mt-1">{{ $authUser?->personnel_id ?? 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-outline-variant/25 bg-surface-container-low p-4">
                    <p class="text-[11px] uppercase tracking-wider font-bold text-outline">Email</p>
                    <p class="text-base font-bold text-on-surface mt-1 break-all">{{ $authUser?->email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </section>
</main>

@if ($isAdminArea)
@include('layouts.admin_footer')
@else
@include('layouts.footer')
@endif
</body>
</html>
