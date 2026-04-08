<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NIA Drivers Performance Evaluation</title>
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
                        "primary": "#003466",
                        "surface-tint": "#335f99",
                        "on-primary": "#ffffff",
                        "surface-container-highest": "#e0e3e6",
                        "surface-dim": "#d8dadd",
                        "tertiary-fixed": "#a0f2e1",
                        "primary-fixed-dim": "#a6c8ff",
                        "on-primary-fixed": "#001c3b",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed-variant": "#005046",
                        "inverse-on-surface": "#eff1f4",
                        "primary-container": "#1a4b84",
                        "inverse-surface": "#2d3133",
                        "surface-container-low": "#f2f4f7",
                        "secondary-fixed-dim": "#a0d3a5",
                        "outline-variant": "#c3c6d1",
                        "on-secondary-container": "#3e6d47",
                        "tertiary-fixed-dim": "#84d5c5",
                        "on-tertiary": "#ffffff",
                        "primary-fixed": "#d5e3ff",
                        "tertiary": "#003c34",
                        "on-surface-variant": "#424750",
                        "on-secondary-fixed": "#00210a",
                        "error-container": "#ffdad6",
                        "tertiary-container": "#00554a",
                        "secondary": "#3a6843",
                        "on-primary-container": "#93bcfc",
                        "surface-container-high": "#e6e8eb",
                        "surface": "#f7f9fc",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1e",
                        "background": "#f7f9fc",
                        "on-error": "#ffffff",
                        "secondary-container": "#b9ecbd",
                        "secondary-fixed": "#bcefc0",
                        "on-tertiary-fixed": "#00201b",
                        "on-background": "#191c1e",
                        "surface-variant": "#e0e3e6",
                        "surface-container": "#eceef1",
                        "outline": "#737781",
                        "on-secondary-fixed-variant": "#22502d",
                        "surface-bright": "#f7f9fc",
                        "on-tertiary-container": "#78caba",
                        "inverse-primary": "#a6c8ff",
                        "on-primary-fixed-variant": "#144780",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a"
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
        .architectural-underline:focus {
            border-bottom-width: 2px;
            border-color: #003466;
            outline: none;
        }
    </style>
</head>
<body class="bg-background font-body text-on-surface selection:bg-primary-fixed selection:text-on-primary-fixed">
<!-- TopNavBar -->

@include('layouts.header');

@php
    $selectedFromDisplay = optional($selectedEvaluation?->date_time_from)->format('M d, Y h:i A');
    $selectedToDisplay = optional($selectedEvaluation?->date_time_to)->format('M d, Y h:i A');
    $selectedDurationDisplay = ($selectedFromDisplay && $selectedToDisplay)
        ? $selectedFromDisplay . ' to ' . $selectedToDisplay
        : '';

    $selectedEvaluationRecord = $selectedEvaluationRecord ?? null;
    $selectedEvaluationPayload = is_array($selectedEvaluationPayload ?? null)
        ? $selectedEvaluationPayload
        : [];
    $selectedCriteriaScores = is_array($selectedEvaluationPayload['criteria']['scores'] ?? null)
        ? $selectedEvaluationPayload['criteria']['scores']
        : [];
    $selectedCriteriaRemarks = is_array($selectedEvaluationPayload['criteria']['remarks'] ?? null)
        ? $selectedEvaluationPayload['criteria']['remarks']
        : [];
    $selectedImprovementComments = trim((string) ($selectedEvaluationPayload['improvement_comments'] ?? ''));
    $selectedPraiseComments = trim((string) ($selectedEvaluationPayload['praise_comments'] ?? ''));
    $canSubmitEvaluation = $selectedEvaluationRecord
        && strtolower(trim((string) ($selectedEvaluationRecord->status ?? ''))) === 'pending'
        && (string) ($selectedEvaluation?->status ?? '') === 'For Evaluation';

    $selectedDivisionPersonnelName = trim((string) ($selectedEvaluation?->requestor_name ?? ''));
    $selectedCopyNumber = (int) ($selectedEvaluationRecord?->copy_number ?? 1);
    $selectedEvaluationYear = optional($selectedEvaluation?->date_time_to)->format('Y') ?: now()->format('Y');
@endphp

<main class="max-w-5xl mx-auto px-6 pb-12 pt-28">
<!-- Header Section -->
<header class="mb-12 flex flex-col md:flex-row justify-between items-end gap-6">
<div class="max-w-2xl">
<div class="inline-flex items-center gap-2 mb-4">
<div class="h-1 w-12 bg-primary"></div>
<span class="text-label-md font-semibold text-secondary uppercase tracking-widest">Official Document</span>
</div>
<h1 class="text-4xl md:text-5xl font-extrabold text-primary tracking-tight font-headline mb-4 uppercase">Drivers Performance Evaluation</h1>
<p class="text-on-surface-variant text-lg leading-relaxed">Systematic performance assessment for the National Irrigation Administration institutional equipment fleet and personnel.</p>
</div>
<div class="bg-surface-container-highest p-6 rounded-xl border-l-4 border-primary">
<div class="text-label-sm font-bold text-primary-container uppercase tracking-tighter mb-1">Evaluation ID</div>
<div class="text-2xl font-mono font-bold text-on-surface tracking-widest">
{{ $selectedEvaluationRecord ? ('NIA-DPE-' . $selectedEvaluationYear . '-' . str_pad((string) $selectedEvaluationRecord->id, 4, '0', STR_PAD_LEFT) . '-C' . str_pad((string) $selectedCopyNumber, 2, '0', STR_PAD_LEFT)) : 'NIA-DPE-0000-0000' }}
</div>
</div>
</header>

@if ($errors->any())
<div class="mb-8 rounded-xl border border-error/30 bg-error-container p-4 text-on-error-container">
    <p class="font-bold mb-2">Please fix the following before submitting:</p>
    <ul class="list-disc pl-5 text-sm space-y-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('evaluation_submit_success'))
<div class="mb-8 rounded-xl border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container text-sm font-semibold">
    {{ session('evaluation_submit_success') }}
</div>
@endif

<!-- Pending Evaluation Trips -->
<section class="mb-12 bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/20">
<div class="p-6 border-b border-outline-variant/20">
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
<div>
<h2 class="text-2xl font-bold text-primary">Pending Trip Evaluations</h2>
<p class="text-sm text-on-surface-variant mt-1">Finished trips (Date Time To reached) assigned to your account.</p>
</div>
<div class="bg-surface-container-high px-4 py-3 rounded-xl">
<div class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Total Pending</div>
<div class="text-2xl font-black text-primary">{{ number_format($pendingEvaluationCount) }}</div>
</div>
</div>

<form method="GET" action="{{ route('evaluation-performance') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
<div class="md:col-span-6">
<label class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Search</label>
<input name="search" value="{{ $search }}" class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Control ID, Driver, Vehicle..." type="text"/>
</div>
<div class="md:col-span-2">
<label class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Date Time To (From)</label>
<input name="from" value="{{ $fromDate }}" class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="date"/>
</div>
<div class="md:col-span-2">
<label class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Date Time To (To)</label>
<input name="to" value="{{ $toDate }}" class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="date"/>
</div>
<div class="md:col-span-2 flex items-end gap-2">
<button type="submit" class="w-full px-4 py-2 bg-primary text-on-primary rounded-lg font-bold text-xs uppercase tracking-wider">Filter</button>
@if ($search !== '' || $fromDate !== '' || $toDate !== '')
<a href="{{ route('evaluation-performance') }}" class="w-full px-4 py-2 bg-surface-container-high rounded-lg text-xs font-bold uppercase tracking-wider text-center">Clear</a>
@endif
</div>
</form>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low text-primary text-[10px] uppercase tracking-widest font-bold">
<th class="px-5 py-3">Control / ID</th>
<th class="px-5 py-3">Vehicle ID</th>
<th class="px-5 py-3">Driver</th>
<th class="px-5 py-3">Date Time From</th>
<th class="px-5 py-3">Date Time To</th>
<th class="px-5 py-3 text-right">Action</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
@forelse ($pendingEvaluations as $item)
@php
    $evaluationRequest = $item->transportationRequestForm;
    $isSelected = $selectedEvaluationRecord && (int) $selectedEvaluationRecord->id === (int) $item->id;
@endphp
<tr class="{{ $isSelected ? 'bg-primary-fixed/30' : 'hover:bg-surface-container-low/40' }} transition-colors">
<td class="px-5 py-3 text-xs font-mono font-bold text-on-surface-variant">{{ $evaluationRequest?->form_id ?: 'N/A' }}</td>
<td class="px-5 py-3 text-sm">{{ $evaluationRequest?->vehicle_id ?: 'N/A' }}</td>
<td class="px-5 py-3 text-sm">{{ $item->driver_name ?: 'N/A' }}</td>
<td class="px-5 py-3 text-xs text-on-surface-variant">{{ optional($evaluationRequest?->date_time_from)->format('M d, Y h:i A') ?? 'N/A' }}</td>
<td class="px-5 py-3 text-xs text-on-surface-variant">{{ optional($evaluationRequest?->date_time_to)->format('M d, Y h:i A') ?? 'N/A' }}</td>
<td class="px-5 py-3 text-right">
<a href="{{ route('evaluation-performance', array_merge(request()->except('page'), ['evaluation_id' => $item->id])) }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold {{ $isSelected ? 'bg-primary text-white' : 'text-primary hover:bg-primary-container hover:text-white' }} transition-colors">
<span class="material-symbols-outlined text-sm">edit_note</span>
Fill Evaluation
</a>
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-5 py-10 text-center text-sm text-on-surface-variant">No finished trips are waiting for your evaluation.</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="px-5 py-4 bg-surface-container-low/60 flex items-center justify-between">
<span class="text-xs text-on-surface-variant">Showing {{ $pendingEvaluations->firstItem() ?? 0 }} to {{ $pendingEvaluations->lastItem() ?? 0 }} of {{ $pendingEvaluations->total() }}</span>
<div class="flex gap-2">
@if ($pendingEvaluations->onFirstPage())
<span class="p-2 rounded text-slate-400">
<span class="material-symbols-outlined text-sm">chevron_left</span>
</span>
@else
<a href="{{ $pendingEvaluations->previousPageUrl() }}" class="p-2 rounded hover:bg-white text-slate-500">
<span class="material-symbols-outlined text-sm">chevron_left</span>
</a>
@endif

@foreach ($pendingEvaluations->getUrlRange(1, $pendingEvaluations->lastPage()) as $page => $url)
@if ($page == $pendingEvaluations->currentPage())
<span class="w-8 h-8 rounded bg-primary text-white text-xs font-bold inline-flex items-center justify-center">{{ $page }}</span>
@else
<a href="{{ $url }}" class="w-8 h-8 rounded hover:bg-white text-xs font-medium text-slate-600 inline-flex items-center justify-center">{{ $page }}</a>
@endif
@endforeach

@if ($pendingEvaluations->hasMorePages())
<a href="{{ $pendingEvaluations->nextPageUrl() }}" class="p-2 rounded hover:bg-white text-slate-500">
<span class="material-symbols-outlined text-sm">chevron_right</span>
</a>
@else
<span class="p-2 rounded text-slate-400">
<span class="material-symbols-outlined text-sm">chevron_right</span>
</span>
@endif
</div>
</div>
</section>

@if ($selectedEvaluation)
<form id="evaluation-form" method="POST" action="{{ route('evaluation-performance.submit') }}">
@csrf
<input type="hidden" name="evaluation_id" value="{{ $selectedEvaluationRecord?->id }}"/>
@endif

<!-- Personnel & Vehicle Details Bento -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
<div class="md:col-span-2 bg-surface-container-low p-8 rounded-xl flex flex-col justify-between">
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Name of Driver</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-xl font-medium" type="text" value="{{ $selectedEvaluationRecord?->driver_name ?? '' }}" readonly/>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Date of Evaluation</label>
<input name="evaluation_date" class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-xl font-medium" type="date" value="{{ old('evaluation_date', $selectedEvaluationPayload['evaluation_date'] ?? optional($selectedEvaluationRecord?->evaluated_at)->toDateString() ?? now()->toDateString()) }}" {{ $canSubmitEvaluation ? '' : 'readonly' }}/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Type / Make of Vehicle</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-lg" type="text" value="{{ $selectedEvaluation?->vehicle_type ?? '' }}" readonly/>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold text-on-surface-variant uppercase tracking-wider block">Vehicle Plate No.</label>
<input class="w-full bg-transparent border-0 border-b border-outline-variant py-2 architectural-underline text-lg font-mono" type="text" value="{{ $selectedEvaluation?->vehicle_id ?? '' }}" readonly/>
</div>
</div>
</div>

<div class="md:col-span-1 bg-primary text-on-primary p-8 rounded-xl flex flex-col gap-6">
<div class="space-y-1">
<label class="text-label-md font-bold opacity-80 uppercase tracking-wider block">Official Destination</label>
<input class="w-full bg-transparent border-0 border-b border-on-primary/30 py-2 text-lg" type="text" value="{{ $selectedEvaluation?->destination ?? '' }}" readonly/>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold opacity-80 uppercase tracking-wider block">Purpose of Travel</label>
<textarea class="w-full bg-transparent border-0 border-b border-on-primary/30 py-2 text-sm resize-none" rows="2" readonly>{{ $selectedEvaluation?->purpose ?? '' }}</textarea>
</div>
<div class="space-y-1">
<label class="text-label-md font-bold opacity-80 uppercase tracking-wider block">Duration of Travel</label>
<input class="w-full bg-transparent border-0 border-b border-on-primary/30 py-2 text-lg" type="text" value="{{ $selectedDurationDisplay }}" readonly/>
</div>
</div>
</section>

<!-- Evaluation Methodology Header -->
<div class="flex items-center justify-between mb-8">
<h2 class="text-2xl font-bold text-primary flex items-center gap-3">
<span class="material-symbols-outlined text-secondary">fact_check</span>
                Performance Rating Criteria
            </h2>
<div class="flex items-center gap-4 bg-surface-container-high px-4 py-2 rounded-full text-xs font-bold text-on-surface-variant">
<span>1-POOR</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span>2-FAIR</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span>3-GOOD</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span>4-VERY GOOD</span>
<span class="h-3 w-px bg-outline-variant"></span>
<span class="text-primary">5-EXCELLENT</span>
</div>
</div>

@php
    $evaluationCriteria = [
        ['key' => 'r1', 'title' => 'Punctuality', 'description' => 'Arrival and departure adherence to schedule.', 'striped' => false, 'placeholder' => 'Add details...'],
        ['key' => 'r2', 'title' => 'Safe Driving', 'description' => 'No unnecessary phone calls, obedience to traffic rules, no distractions (Radio/TV), courtesy to other motorists.', 'striped' => true, 'placeholder' => 'Add details...'],
        ['key' => 'r3', 'title' => 'Courtesy', 'description' => 'Interactions with passengers and public.', 'striped' => false, 'placeholder' => 'Add details...'],
        ['key' => 'r4', 'title' => 'Personal Attitude', 'description' => 'Professionalism and willingness to assist.', 'striped' => true, 'placeholder' => 'Add details...'],
        ['key' => 'r5', 'title' => 'Knowledge of direction to destination', 'description' => 'Navigation efficiency and route familiarity.', 'striped' => false, 'placeholder' => 'Add details...'],
        ['key' => 'r6', 'title' => 'Personal Hygiene', 'description' => 'Grooming and professional appearance.', 'striped' => true, 'placeholder' => 'Add details...'],
        ['key' => 'r7', 'title' => 'Trouble shooting', 'description' => 'Action taken during mechanical issues. (Write "N/A" if no problems encountered).', 'striped' => false, 'placeholder' => 'Not applicable'],
        ['key' => 'r8', 'title' => 'Vehicle Cleanliness', 'description' => 'Interior and exterior upkeep during the trip.', 'striped' => true, 'placeholder' => 'Add details...'],
    ];

    $criteriaFallbackScores = [
        'r1' => $selectedEvaluationRecord?->timeliness_score,
        'r2' => $selectedEvaluationRecord?->safety_score,
    ];
@endphp

<!-- Evaluation Form Body -->
<div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/10">
<div class="grid grid-cols-12 bg-surface-container-high p-4 text-label-md font-bold text-primary uppercase tracking-widest">
<div class="col-span-6 md:col-span-7">Particulars</div>
<div class="col-span-4 md:col-span-2 text-center">Rate (1-5)</div>
<div class="hidden md:block md:col-span-3">Remarks</div>
</div>

@foreach ($evaluationCriteria as $criterion)
@php
    $criterionKey = (string) $criterion['key'];
    $criterionRemarkKey = 'remark_' . $criterionKey;
    $storedCriterionScore = $selectedCriteriaScores[$criterionKey] ?? $criteriaFallbackScores[$criterionKey] ?? null;
    $selectedScore = (int) old($criterionKey, $storedCriterionScore ?? 0);
    $selectedRemark = old($criterionRemarkKey, $selectedCriteriaRemarks[$criterionKey] ?? '');
    $rowClass = $criterion['striped']
        ? 'grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center bg-surface-container-low/30 hover:bg-surface-container-low transition-colors'
        : 'grid grid-cols-12 p-6 border-b border-outline-variant/10 items-center hover:bg-surface-container-low transition-colors';
@endphp
<div class="{{ $rowClass }}">
<div class="col-span-6 md:col-span-7 pr-4">
<div class="font-bold text-on-surface">{{ $criterion['title'] }}</div>
<p class="text-xs text-on-surface-variant mt-1 {{ $criterionKey === 'r2' ? 'leading-relaxed' : '' }}">{{ $criterion['description'] }}</p>
</div>
<div class="col-span-4 md:col-span-2">
<div class="flex justify-center gap-2">
@for ($score = 1; $score <= 5; $score++)
<input class="w-5 h-5 text-primary border-outline-variant focus:ring-primary" name="{{ $criterionKey }}" type="radio" value="{{ $score }}" @checked($selectedScore === $score) {{ $canSubmitEvaluation ? '' : 'disabled' }}/>
@endfor
</div>
</div>
<div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
<input class="w-full bg-surface-container-low rounded px-3 py-2 text-sm border-0 focus:ring-1 focus:ring-primary" name="{{ $criterionRemarkKey }}" placeholder="{{ $criterion['placeholder'] }}" type="text" value="{{ $selectedRemark }}" {{ $canSubmitEvaluation ? '' : 'readonly' }}/>
</div>
</div>
@endforeach
</div>

<!-- Summary Section -->
<section class="mt-12 grid grid-cols-1 md:grid-cols-12 gap-8">
<div class="md:col-span-8 space-y-8">
<div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-error">
<label class="text-label-md font-bold text-error uppercase tracking-wider block mb-3">Comments and Observations (For Improvement)</label>
<textarea class="w-full bg-surface-container-lowest border-0 rounded-lg p-4 text-sm focus:ring-1 focus:ring-error shadow-inner" name="comments_improvement" placeholder="Detailed feedback for correction or training needs..." rows="4" {{ $canSubmitEvaluation ? '' : 'readonly' }}>{{ old('comments_improvement', $selectedImprovementComments) }}</textarea>
</div>
<div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-secondary">
<label class="text-label-md font-bold text-secondary uppercase tracking-wider block mb-3">Comments and Observations (For Praise and Appreciation)</label>
<textarea class="w-full bg-surface-container-lowest border-0 rounded-lg p-4 text-sm focus:ring-1 focus:ring-secondary shadow-inner" name="comments_praise" placeholder="Commendations for exemplary performance..." rows="4" {{ $canSubmitEvaluation ? '' : 'readonly' }}>{{ old('comments_praise', $selectedPraiseComments) }}</textarea>
</div>
</div>
<div class="md:col-span-4 flex flex-col gap-6">
<div class="bg-primary-container text-white p-8 rounded-xl flex flex-col items-center justify-center text-center shadow-lg">
<span class="text-label-sm font-bold opacity-70 uppercase tracking-widest mb-2">Final Evaluation Rate</span>
<div id="evaluation-final-rate" class="text-6xl font-black mb-4">0.0</div>
<span id="evaluation-final-rate-label" class="px-4 py-1 bg-tertiary-fixed text-on-tertiary-fixed font-bold rounded-full text-xs uppercase">Not Rated</span>
</div>
<div class="bg-surface-container-high p-8 rounded-xl flex flex-col items-center justify-end h-full">
<span class="text-lg font-bold text-on-surface text-center">{{ $selectedDivisionPersonnelName !== '' ? $selectedDivisionPersonnelName : '____________________________' }}</span>
<div class="w-full border-b-2 border-primary mb-2"></div>
<span class="text-label-md font-bold text-primary uppercase text-center mt-1">Official Passenger / Team Leader</span>
<span class="text-xs text-on-surface-variant mt-1">Signature over Printed Name</span>
</div>
</div>
</section>

<!-- Actions -->
<div class="mt-16 flex flex-col md:flex-row justify-end gap-4 print:hidden">
@if ($canSubmitEvaluation)
<button id="evaluation-submit-trigger" type="button" class="px-8 py-3 bg-primary text-on-primary rounded-lg font-bold shadow-lg hover:shadow-primary/20 transition-all flex items-center gap-2">
<span class="material-symbols-outlined">print</span>
                Submit &amp; Print Evaluation
            </button>
@elseif ($selectedEvaluation)
<button type="button" disabled class="px-8 py-3 bg-surface-container-high text-on-surface-variant rounded-lg font-bold flex items-center gap-2 cursor-not-allowed">
<span class="material-symbols-outlined">task_alt</span>
                Evaluation Submitted
            </button>
@else
<button type="button" disabled class="px-8 py-3 bg-surface-container-high text-on-surface-variant rounded-lg font-bold flex items-center gap-2 cursor-not-allowed">
<span class="material-symbols-outlined">radio_button_unchecked</span>
                Select a Trip to Evaluate
            </button>
@endif
</div>

@if ($selectedEvaluation)
</form>
@endif
</main>

<div id="evaluation-submit-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
        <div class="mb-4 flex items-center gap-3 text-primary">
            <span class="material-symbols-outlined">warning</span>
            <h3 class="text-lg font-bold">Submit Evaluation</h3>
        </div>
        <p class="text-sm text-on-surface-variant leading-relaxed">Are you sure you want to submit and print this evaluation? You will no longer be able to edit it after submission.</p>
        <div class="mt-6 flex justify-end gap-3">
            <button id="evaluation-submit-confirm-no" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">No</button>
            <button id="evaluation-submit-confirm-yes" type="button" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary/90">Yes</button>
        </div>
    </div>
</div>

<div id="evaluation-submit-loading-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center">
        <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
        <p class="text-sm font-semibold text-on-surface">Submitting evaluation and preparing print...</p>
    </div>
</div>

<!-- Footer -->
@include('layouts.footer')
<script>
    (function () {
        const finalRateEl = document.getElementById('evaluation-final-rate');
        const finalRateLabelEl = document.getElementById('evaluation-final-rate-label');
        const evaluationForm = document.getElementById('evaluation-form');
        const submitTrigger = document.getElementById('evaluation-submit-trigger');
        const confirmModal = document.getElementById('evaluation-submit-confirm-modal');
        const confirmNoButton = document.getElementById('evaluation-submit-confirm-no');
        const confirmYesButton = document.getElementById('evaluation-submit-confirm-yes');
        const loadingModal = document.getElementById('evaluation-submit-loading-modal');
        const shouldAutoPrintEvaluation = @json((bool) session('auto_print_evaluation'));

        if (!finalRateEl || !finalRateLabelEl) {
            return;
        }

        const ratingNames = ['r1', 'r2', 'r3', 'r4', 'r5', 'r6', 'r7', 'r8'];
        const ratingInputs = Array.from(document.querySelectorAll('input[type="radio"][name^="r"]'));

        // Ensure each row maps left-to-right as 1..5 even if value attributes are missing.
        ratingNames.forEach(function (name) {
            const rowInputs = Array.from(document.querySelectorAll(`input[type="radio"][name="${name}"]`));

            rowInputs.forEach(function (input, index) {
                const currentValue = Number(input.value);
                if (!Number.isFinite(currentValue) || currentValue < 1 || currentValue > 5) {
                    input.value = String(index + 1);
                }
            });
        });

        function resolveLabel(score) {
            if (score <= 0) return 'Not Rated';
            if (score < 1.5) return 'Poor';
            if (score < 2.5) return 'Fair';
            if (score < 3.5) return 'Good';
            if (score < 4.5) return 'Very Good';
            return 'Excellent';
        }

        function refreshFinalRate() {
            let total = 0;
            let selectedCount = 0;

            ratingNames.forEach(function (name) {
                const checked = document.querySelector(`input[type="radio"][name="${name}"]:checked`);
                if (!checked) {
                    return;
                }

                const value = Number(checked.value);
                if (!Number.isFinite(value)) {
                    return;
                }

                total += value;
                selectedCount += 1;
            });

            const average = selectedCount > 0 ? (total / selectedCount) : 0;
            finalRateEl.textContent = average.toFixed(1);
            finalRateLabelEl.textContent = resolveLabel(average);
        }

        function showConfirmModal() {
            if (!confirmModal) {
                return;
            }

            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
        }

        function hideConfirmModal() {
            if (!confirmModal) {
                return;
            }

            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('flex');
        }

        function showLoadingModal() {
            if (!loadingModal) {
                return;
            }

            loadingModal.classList.remove('hidden');
            loadingModal.classList.add('flex');
        }

        function hideLoadingModal() {
            if (!loadingModal) {
                return;
            }

            loadingModal.classList.add('hidden');
            loadingModal.classList.remove('flex');
        }

        function hasCompleteRatings() {
            return ratingNames.every(function (name) {
                return !!document.querySelector(`input[type="radio"][name="${name}"]:checked`);
            });
        }

        ratingInputs.forEach(function (input) {
            input.addEventListener('change', refreshFinalRate);
        });

        if (submitTrigger && evaluationForm) {
            submitTrigger.addEventListener('click', function () {
                if (!hasCompleteRatings()) {
                    window.alert('Please rate all criteria before submitting the evaluation.');
                    return;
                }

                showConfirmModal();
            });
        }

        if (confirmNoButton) {
            confirmNoButton.addEventListener('click', function () {
                hideConfirmModal();
                hideLoadingModal();
            });
        }

        if (confirmModal) {
            confirmModal.addEventListener('click', function (event) {
                if (event.target === confirmModal) {
                    hideConfirmModal();
                    hideLoadingModal();
                }
            });
        }

        if (confirmYesButton && evaluationForm) {
            confirmYesButton.addEventListener('click', function () {
                hideConfirmModal();
                showLoadingModal();
                evaluationForm.requestSubmit();
            });
        }

        if (shouldAutoPrintEvaluation) {
            window.setTimeout(function () {
                window.print();
            }, 450);
        }

        refreshFinalRate();
    })();
</script>
</body></html>