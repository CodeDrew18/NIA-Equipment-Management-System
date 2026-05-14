<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Equipment Utilization Report | National Irrigation Administration</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary-container": "#b9ecbd",
                        "inverse-primary": "#a6c8ff",
                        "surface-tint": "#335f99",
                        "secondary": "#3a6843",
                        "on-tertiary-fixed-variant": "#005046",
                        "on-error-container": "#93000a",
                        "on-primary-fixed": "#001c3b",
                        "primary-container": "#1a4b84",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed-variant": "#144780",
                        "background": "#f7f9fc",
                        "surface-container-low": "#f2f4f7",
                        "secondary-fixed-dim": "#a0d3a5",
                        "tertiary-fixed-dim": "#84d5c5",
                        "on-primary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "primary": "#003466",
                        "outline-variant": "#c3c6d1",
                        "on-surface-variant": "#424750",
                        "on-secondary-fixed-variant": "#22502d",
                        "outline": "#737781",
                        "surface-container-high": "#e6e8eb",
                        "on-primary-container": "#93bcfc",
                        "inverse-surface": "#2d3133",
                        "tertiary-container": "#00554a",
                        "error": "#ba1a1a",
                        "surface-container-highest": "#e0e3e6",
                        "tertiary": "#003c34",
                        "surface": "#f7f9fc",
                        "on-tertiary-fixed": "#00201b",
                        "secondary-fixed": "#bcefc0",
                        "primary-fixed": "#d5e3ff",
                        "on-secondary-fixed": "#00210a",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-background": "#191c1e",
                        "surface-container": "#eceef1",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1e",
                        "on-tertiary-container": "#78caba",
                        "surface-dim": "#d8dadd",
                        "on-error": "#ffffff",
                        "tertiary-fixed": "#a0f2e1",
                        "surface-variant": "#e0e3e6",
                        "surface-bright": "#f7f9fc",
                        "on-secondary-container": "#3e6d47",
                        "inverse-on-surface": "#eff1f4",
                        "error-container": "#ffdad6"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Public Sans"],
                        "display": ["Public Sans"],
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
        body { font-family: 'Public Sans', sans-serif; }
        .glass-header { backdrop-filter: blur(20px); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .report-grid { border-collapse: separate; border-spacing: 0; }
        .report-grid th, .report-grid td { border: 1px solid rgba(195, 198, 209, 0.2); }
        @media print {
            .no-print { display: none; }
            body { background: #ffffff; }
        }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
<!-- TopNavBar -->
@include('layouts.admin_header')
<main class="mt-24 mb-12 px-8 max-w-[1440px] mx-auto w-full">
<!-- Filters -->
<div class="no-print mb-6">
<form method="GET" action="{{ route('admin.monthly-equipment-utilization-report') }}" class="flex flex-wrap items-end gap-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl px-6 py-4">
<div class="min-w-[180px]">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1 block">Month</label>
<input name="month" type="month" value="{{ $selectedMonth }}" class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold" />
</div>
<div class="min-w-[220px]">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1 block">Prepared By</label>
<select name="prepared_id" class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold">
<option value="">Use active assignatory</option>
@forelse ($assignatories as $assignatory)
<option value="{{ $assignatory->id }}" @selected($selectedPreparedId === $assignatory->id)>
{{ $assignatory->name }} - {{ $assignatory->position }}
</option>
@empty
<option value="">No assignatories found</option>
@endforelse
</select>
</div>
<div class="min-w-[220px]">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1 block">Attested By</label>
<select name="attested_id" class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold">
<option value="">Use active assignatory</option>
@forelse ($assignatories as $assignatory)
<option value="{{ $assignatory->id }}" @selected($selectedAttestedId === $assignatory->id)>
{{ $assignatory->name }} - {{ $assignatory->position }}
</option>
@empty
<option value="">No assignatories found</option>
@endforelse
</select>
</div>
<div class="min-w-[220px]">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1 block">Approved By</label>
<select name="approved_id" class="w-full bg-surface-container-low border-none rounded focus:ring-0 focus:border-primary border-b-2 border-transparent text-on-surface font-semibold">
<option value="">Use active assignatory</option>
@forelse ($assignatories as $assignatory)
<option value="{{ $assignatory->id }}" @selected($selectedApprovedId === $assignatory->id)>
{{ $assignatory->name }} - {{ $assignatory->position }}
</option>
@empty
<option value="">No assignatories found</option>
@endforelse
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="h-[40px] px-4 bg-primary text-white font-bold rounded-lg hover:bg-primary-container">Load Report</button>
<a href="{{ route('admin.monthly-equipment-utilization-report') }}" class="h-[40px] px-4 bg-surface border border-outline-variant/40 rounded-lg text-on-surface-variant font-bold flex items-center">Reset</a>
<a href="{{ route('admin.monthly-equipment-utilization-report.download', request()->query()) }}" target="_blank" class="h-[40px] px-4 bg-[#3a6843] text-white font-bold rounded-lg flex items-center hover:bg-[#22502d] transition-colors"><span class="material-symbols-outlined mr-2 text-[18px]">print</span>Print Report</a>
</div>
</form>
</div>
<!-- Report Canvas -->
<div class="bg-surface-container-lowest rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.06)] overflow-hidden">
<!-- Report Header -->
<div class="p-10 bg-white">
<div class="text-center">
    <div class="inline-block bg-primary px-8 py-4 rounded-xl mb-6">
        <h2 class="text-white font-bold tracking-tight text-3xl uppercase">
            Monthly Equipment Utilization Report
        </h2>
    </div>
</div>
<!-- Metadata Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-12 mb-10">
<div class="space-y-4">
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">For the Month of:</label>
<span class="text-lg font-bold text-primary border-b-2 border-surface-container-high pb-1">{{ $monthLabel }}</span>
</div>
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Project No:</label>
<span class="text-body font-medium text-on-surface border-b-2 border-surface-container-high pb-1">NIA-REG1-2025-042</span>
</div>
</div>
<div class="space-y-4">
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Used By:</label>
<span class="text-body font-medium text-on-surface border-b-2 border-surface-container-high pb-1">NIA Personnel</span>
</div>
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Charge To:</label>
<span class="text-body font-medium text-on-surface border-b-2 border-surface-container-high pb-1">Regional Operation Fund</span>
</div>
</div>
<div class="space-y-4">
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Date:</label>
<span class="text-body font-medium text-on-surface border-b-2 border-surface-container-high pb-1">{{ $monthRangeLabel }}</span>
</div>
<div class="flex flex-col">
<label class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Location:</label>
<span class="text-body font-medium text-on-surface border-b-2 border-surface-container-high pb-1">Urdaneta City, Pangasinan</span>
</div>
</div>
</div>
<!-- Main Data Table Container -->
<div class="overflow-x-auto no-scrollbar rounded-lg ring-1 ring-outline-variant/20 shadow-sm">
<table class="report-grid w-full text-[11px] border-2 border-primary/20">
<thead>
<tr class="bg-surface-container text-primary font-bold uppercase tracking-tighter">
<th class="p-3 text-left w-32" rowspan="2">Type/Make/Model</th>
<th class="p-3 text-center w-24" rowspan="2">Serial No.</th>
<th class="p-3 text-center w-28" rowspan="2">Prop. # / Plate #</th>
<th class="p-1 border-b border-outline-variant/30 text-center bg-surface-container-high" colspan="{{ $daysInMonth }}">Day of Month</th>
<th class="p-3 text-center w-20" rowspan="2">Total Kms/Hrs</th>
<th class="p-3 text-center w-20" rowspan="2">Rental Rate</th>
<th class="p-3 text-center w-24" rowspan="2">Total Amount</th>
</tr>
<tr class="bg-surface-container-low text-[9px] font-bold">
@for ($day = 1; $day <= $daysInMonth; $day++)
<th class="w-6 text-center border-l">{{ $day }}</th>
@endfor
</tr>
</thead>
<tbody class="text-on-surface">
@forelse ($vehicleRows as $row)
<tr class="{{ $loop->even ? 'bg-surface-container-low/30' : '' }} hover:bg-surface-container-lowest transition-colors">
<td class="p-3 font-semibold leading-tight py-4">{{ $row['typeLabel'] }}</td>
<td class="p-3 text-center font-mono py-4">{{ $row['serialLabel'] }}</td>
<td class="p-3 text-center py-4">{{ $row['propPlateLabel'] }}</td>
@if ($row['totalDistance'] > 0)
@foreach ($row['days'] as $distance)
@php
    $distanceValue = is_numeric($distance) ? (float) $distance : 0.0;
    $hasDistance = $distanceValue > 0;
@endphp
<td class="p-1 text-center border-l {{ $hasDistance ? 'bg-secondary/5 font-bold text-secondary' : 'text-outline/40' }}">
{{ $hasDistance ? number_format($distanceValue, 1) : '0' }}
</td>
@endforeach
@else
<td colspan="{{ $daysInMonth }}" class="p-3 text-center font-bold tracking-[0.3em] text-outline/50 bg-surface-container-low/30 border-l border-r uppercase">
NO OPERATION
</td>
@endif
<td class="p-3 text-center font-bold py-4 bg-surface-container/30 text-primary">{{ number_format((float) $row['totalDistance'], 1) }}</td>
<td class="p-3 text-center py-4 text-right pr-4 italic {{ $row['rentalRate'] > 0 ? 'text-primary font-bold' : 'text-on-surface-variant' }}">
    {{ $row['rentalRate'] > 0 ? '₱ ' . number_format($row['rentalRate'], 2) : 'N/A' }}
</td>
<td class="p-3 text-right pr-4 font-black py-4 {{ $row['rentalRate'] > 0 && $row['totalDistance'] > 0 ? 'text-secondary' : 'text-on-surface-variant' }}">
    {{ $row['rentalRate'] > 0 && $row['totalDistance'] > 0 ? '₱ ' . number_format($row['totalDistance'] * $row['rentalRate'], 2) : 'N/A' }}
</td>
</tr>
@empty
<tr>
<td class="p-6 text-center text-outline font-semibold" colspan="{{ $daysInMonth + 6 }}">No vehicle records found for this month.</td>
</tr>
@endforelse
</tbody>
<tfoot>
<tr class="bg-primary text-white font-black uppercase">
<td class="p-6 text-right tracking-[0.2em] text-base border-r border-white/10" colspan="{{ $daysInMonth + 3 }}">Grand Total</td>
<td class="p-6 text-center text-xl font-bold border-r border-white/10">
{{ number_format((float) $grandTotalDistance, 1) }}
</td>
<td class="p-6 border-r border-white/10"></td>
<td class="p-6 text-right pr-6 text-2xl font-black">
<div class="flex justify-between items-center">
<span class="text-sm opacity-70 font-normal">₱</span>
<span>{{ number_format((float) ($grandTotalAmount ?? 0), 2) }}</span>
</div>
</td>
</tr>
</tfoot>
</table>
</div>
<!-- Approvals Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-16 mt-16 pb-12 mt-24">
<div class="flex flex-col"><span class="text-xs font-black text-outline uppercase tracking-[0.2em] mb-12">Prepared by:</span>
<div class="border-b-4 border-primary pb-4 text-center">
<p class="text-xl font-black text-primary uppercase leading-tight">{{ $preparedBy['name'] ?? 'N/A' }}</p>
<p class="text-xs font-bold italic text-on-surface-variant mt-2">{{ $preparedBy['position'] ?? 'N/A' }}</p>
</div></div>
<div class="flex flex-col"><span class="text-xs font-black text-outline uppercase tracking-[0.2em] mb-12">Attested by:</span>
<div class="border-b-4 border-primary pb-4 text-center">
<p class="text-xl font-black text-primary uppercase leading-tight">{{ $attestedBy['name'] ?? 'N/A' }}</p>
<p class="text-xs font-bold italic text-on-surface-variant mt-2">{{ $attestedBy['position'] ?? 'N/A' }}</p>
</div></div>
<div class="flex flex-col"><span class="text-xs font-black text-outline uppercase tracking-[0.2em] mb-12">Approved by:</span>
<div class="border-b-4 border-primary pb-4 text-center">
<p class="text-xl font-black text-primary uppercase leading-tight">{{ $approvedBy['name'] ?? 'N/A' }}</p>
<p class="text-xs font-bold italic text-on-surface-variant mt-2">{{ $approvedBy['position'] ?? 'N/A' }}</p>
</div></div>
</div>
</div>
<!-- Footer Branding Section -->
<div class="relative bg-[#003466] overflow-hidden">
<div class="absolute inset-0 opacity-10 pointer-events-none">
<div class="h-full w-full bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
</div>
<!-- Wave Accents -->
<div class="h-16 w-full flex">
<div class="h-full w-1/3 bg-[#00AEEF] transform -skew-x-[45deg] -translate-x-12"></div>
<div class="h-full w-1/4 bg-[#003466] transform -skew-x-[45deg] -translate-x-16"></div>
<div class="h-full flex-grow bg-[#3a6843] transform -skew-x-[45deg] -translate-x-20"></div>
</div>
<div class="px-10 py-8 flex flex-col md:flex-row justify-between items-center gap-8 relative z-10">
<div class="text-white space-y-2">
<p class="text-sm font-bold tracking-tight">Brgy. Bayaoas, Urdaneta City, Pangasinan, 2428 Philippines</p>
<div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-white/80 font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">call</span> (075) 633-7130 local 100, 104 to 112</span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">mail</span> r1@nia.gov.ph</span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">public</span> www.nia.gov.ph</span>
<span class="flex items-center gap-1 font-bold">TIN: 000-916-415-054</span>
</div>
</div>
<div class="flex items-center gap-8">
<div class="bg-white p-2 rounded-lg">
<img alt="NIA QR Code" class="w-16 h-16" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBTXww9PwbN4H-b2eHYzuPgagzViOAfgHBJZQElLcXhGkluqJfOdy8JTDw59E2jJ0n1MFwzg_eKfTDVtXs4UwOXXgAT4VsMD_ZP74yssq7j_2Kvafo286caOO1wCpvEOyWZIPH1DUzxpITutupQFuenw7QE1T3G_NEM1PlQ8xJC9UwuOMcfTQLAjdmeV5ip3XG4M-Q6oO0TrX42HOHErjX6q-R8N6TXJiJJbRjhzJioUVHrep5zkMNAuIzx0KkinWhW87Xiy4mLI0-T"/>
</div>
<div class="flex gap-4 items-center">
<img alt="Certification Logo" class="h-12 w-auto invert brightness-0" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDuhyk1tFN1m_u1LWPaA8pwjnIe5eqjAGkB_7A5GHfr_LjM0dW4btp2VTcEgAmU0vIzUA4VZ6Lk0IxYL1YS5g1zH_p5_MIVAbf-4q3ysNGNKyQ0r2YSxHie0KG5fpqV_vhqNajSYZ-zFR6J41kNh0oFghCGde0zyTlRE06MZgNC7tmBAkW6mhrdXxcOswuvzAAiF2XbR-lDWefdtbND9KnP6KafQXZ7EIL_1ApGnWVU84-1VAzAROAQkGK-JvDTNY3q5nIzJT08Lt4g"/>
<div class="h-10 w-[1px] bg-white/20"></div>
<p class="text-[10px] text-white/60 font-bold uppercase tracking-widest leading-tight">Institutional<br/>Excellence</p>
</div>
</div>
</div>
</div>
</div>
</main>
<!-- Footer Component -->
<footer class="w-full py-8 mt-auto bg-[#f2f4f7] dark:bg-[#121416] border-t border-[#c3c6d1]/15">
@include('layouts.admin_footer')
</footer>
<!-- FAB (Suppressed based on context rules - not appropriate for report screen) -->
</body></html>