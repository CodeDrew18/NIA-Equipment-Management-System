<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<title>Transportation Request Form</title>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "tertiary-container": "#00554a",
              "on-tertiary-fixed": "#00201b",
              "surface-container-high": "#e6e8eb",
              "on-primary-fixed": "#001c3b",
              "outline-variant": "#c3c6d1",
              "on-tertiary-fixed-variant": "#005046",
              "on-tertiary-container": "#78caba",
              "outline": "#737781",
              "surface-container": "#eceef1",
              "surface-tint": "#335f99",
              "surface-variant": "#e0e3e6",
              "on-tertiary": "#ffffff",
              "tertiary": "#003c34",
              "secondary-fixed": "#bcefc0",
              "on-secondary-container": "#3e6d47",
              "error-container": "#ffdad6",
              "background": "#f7f9fc",
              "primary-fixed-dim": "#a6c8ff",
              "primary-container": "#1a4b84",
              "on-error": "#ffffff",
              "primary-fixed": "#d5e3ff",
              "on-primary": "#ffffff",
              "secondary-fixed-dim": "#a0d3a5",
              "on-surface-variant": "#424750",
              "on-background": "#191c1e",
              "secondary": "#3a6843",
              "on-primary-fixed-variant": "#144780",
              "surface": "#f7f9fc",
              "on-surface": "#191c1e",
              "inverse-surface": "#2d3133",
              "inverse-primary": "#a6c8ff",
              "on-secondary-fixed": "#00210a",
              "inverse-on-surface": "#eff1f4",
              "secondary-container": "#b9ecbd",
              "surface-dim": "#d8dadd",
              "on-secondary": "#ffffff",
              "error": "#ba1a1a",
              "on-secondary-fixed-variant": "#22502d",
              "primary": "#003466",
              "on-error-container": "#93000a",
              "surface-container-low": "#f2f4f7",
              "tertiary-fixed-dim": "#84d5c5",
              "surface-container-highest": "#e0e3e6",
              "surface-container-lowest": "#ffffff",
              "on-primary-container": "#93bcfc",
              "surface-bright": "#f7f9fc",
              "tertiary-fixed": "#a0f2e1"
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

        /* Remove number input arrows */
.no-spin::-webkit-outer-spin-button,
.no-spin::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.no-spin {
  -moz-appearance: textfield; /* Firefox */
  appearance: none; /* general */
}
    </style>

@include('layouts.header')


<!-- Main Content Area -->
<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-20">
<!-- Header Section -->
<header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 px-4">
<div class="max-w-2xl">
<span class="inline-block px-3 py-1 bg-secondary-container text-on-secondary-container text-[10px] font-bold uppercase tracking-widest rounded-full mb-4">Travel Request Information</span>
<h1 class="text-4xl font-extrabold text-primary tracking-tight mb-2">Transportation Request Form</h1>
<p class="text-on-surface-variant text-lg leading-relaxed">Submit a formal request for vehicle use for official business purposes. Ensure all trip and personnel details are accurate.</p>
</div>
<div class="flex items-center gap-4 text-sm font-semibold text-primary">
<div class="flex flex-col items-end">
<span class="text-xs uppercase text-slate-400 tracking-wider">Form ID</span>
<span>
    REQ-<?php echo date('Y'); ?>-<?php echo strtoupper(substr(uniqid(), -4)); ?>
</span>
</div>
<div class="w-[2px] h-10 bg-slate-200"></div>
<div class="flex flex-col items-end">
<span class="text-xs uppercase text-slate-400 tracking-wider">Request Date</span>
<span><?php echo date('M j, Y');?></span>
</div>
</div>
</header>
<form id="request-form" class="space-y-10 px-4" method="POST" action="{{route('request-form.submit')}}" enctype="multipart/form-data">
<!-- Section 1: Requestor & Trip Core Info -->
  @csrf
  @if ($errors->any())
    <div class="rounded-xl border border-error/30 bg-error-container p-4 text-on-error-container">
      <p class="font-semibold mb-2">Please correct the following:</p>
      <ul class="list-disc pl-5 space-y-1 text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('error'))
    <div class="rounded-xl border border-error/30 bg-error-container p-4 text-on-error-container text-sm font-semibold">
      {{ session('error') }}
    </div>
  @endif

  @if (session('request_form_success'))
    <div class="rounded-xl border border-secondary/30 bg-secondary-container p-4 text-on-secondary-container text-sm font-semibold flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <span>{{ session('request_form_success') }}</span>
  
    </div>
  @endif

<section>
  <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
      <span class="material-symbols-outlined">info</span>
    </div>
    <h2 class="text-xl font-bold text-primary tracking-tight">Request Details</h2>
  </div>

  <div class="grid grid-cols-3 gap-6">
    <!-- First row: 3 columns -->
    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date of Request</label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}"/>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">To Be Used By</label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Division / Personnel" type="text" name="requested_by" value="{{ old('requested_by') }}"/>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Destination</label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Site Location" type="text" name="destination" value="{{ old('destination') }}"/>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-6 mt-6">
    <!-- Second row: 2 columns that span like the first row -->
    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100 col-span-1">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date & Time Used: <b>(FROM)</b></label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="datetime-local" name="date_time_from" value="{{ old('date_time_from') }}"/>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100 col-span-1">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date & Time Used: <b>(TO)</b></label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="datetime-local" name="date_time_to" value="{{ old('date_time_to') }}"/>
    </div>
  </div>
</section>
<!-- Section 2: Purpose (Highlighted) -->
<section>
<div class="bg-primary/5 border border-primary/20 p-8 rounded-2xl">
<div class="flex items-center gap-3 mb-4">
<span class="material-symbols-outlined text-primary">description</span>
<label class="text-sm font-bold uppercase tracking-widest text-primary">Purpose of Request</label>
</div>
<textarea class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none shadow-sm text-on-surface" placeholder="Describe the specific purpose and work to be performed..." rows="3" name="purpose">{{ old('purpose') }}</textarea>
</div>
</section>
<!-- Section 3: Equipment & Project -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
<section class="lg:col-span-2 mb-5">
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
<span class="material-symbols-outlined">construction</span>
</div>
<h2 class="text-xl font-bold text-primary tracking-tight">Vehicle</h2>
</div>
<div class="bg-surface-container-low p-6 rounded-2xl h-full border border-slate-200/50">
<div class="space-y-6">
<p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Select one or more vehicle types</p>

@php
  $availableVehicleTypes = $availableVehicleTypes ?? ['coaster' => true, 'van' => true, 'pickup' => true];
  $availableVehicleCounts = $availableVehicleCounts ?? ['coaster' => 1, 'van' => 1, 'pickup' => 1];
@endphp

<div class="space-y-3">
  @if ($availableVehicleTypes['coaster'])
  @php
    $coasterMax = max(1, (int) ($availableVehicleCounts['coaster'] ?? 1));
    $coasterQty = max(1, min((int) old('vehicle_requests.0.quantity', 1), $coasterMax));
  @endphp
  <div class="vehicle-request-row flex items-center justify-between gap-3 rounded-xl border border-outline-variant/60 bg-surface-container-highest p-3" data-vehicle-row>
    <div class="flex items-center gap-2">
      <input id="vehicle-coaster" class="vehicle-request-checkbox h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="vehicle_requests[0][selected]" value="1" {{ old('vehicle_requests.0.selected') ? 'checked' : '' }} />
      <input type="hidden" name="vehicle_requests[0][type]" value="coaster" />
      <span class="material-symbols-outlined text-primary text-lg">directions_bus</span>
      <label for="vehicle-coaster" class="text-sm font-bold text-on-surface">Coaster</label>
    </div>
    <div class="flex items-center gap-2">
      <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Qty</label>
      <input class="vehicle-request-quantity w-20 bg-surface-container-lowest border border-slate-200 rounded-lg px-3 py-2 text-center text-sm focus:ring-2 focus:ring-primary" type="number" name="vehicle_requests[0][quantity]" value="{{ $coasterQty }}" min="1" max="{{ $coasterMax }}" {{ old('vehicle_requests.0.selected') ? '' : 'disabled' }} />
      <span class="text-[10px] font-semibold text-slate-500">Max {{ $coasterMax }}</span>
    </div>
  </div>
  @endif

  @if ($availableVehicleTypes['van'])
  @php
    $vanMax = max(1, (int) ($availableVehicleCounts['van'] ?? 1));
    $vanQty = max(1, min((int) old('vehicle_requests.1.quantity', 1), $vanMax));
  @endphp
  <div class="vehicle-request-row flex items-center justify-between gap-3 rounded-xl border border-outline-variant/60 bg-surface-container-highest p-3" data-vehicle-row>
    <div class="flex items-center gap-2">
      <input id="vehicle-van" class="vehicle-request-checkbox h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="vehicle_requests[1][selected]" value="1" {{ old('vehicle_requests.1.selected') ? 'checked' : '' }} />
      <input type="hidden" name="vehicle_requests[1][type]" value="van" />
      <span class="material-symbols-outlined text-primary text-lg">airport_shuttle</span>
      <label for="vehicle-van" class="text-sm font-bold text-on-surface">Van</label>
    </div>
    <div class="flex items-center gap-2">
      <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Qty</label>
      <input class="vehicle-request-quantity w-20 bg-surface-container-lowest border border-slate-200 rounded-lg px-3 py-2 text-center text-sm focus:ring-2 focus:ring-primary" type="number" name="vehicle_requests[1][quantity]" value="{{ $vanQty }}" min="1" max="{{ $vanMax }}" {{ old('vehicle_requests.1.selected') ? '' : 'disabled' }} />
      <span class="text-[10px] font-semibold text-slate-500">Max {{ $vanMax }}</span>
    </div>
  </div>
  @endif

  @if ($availableVehicleTypes['pickup'])
  @php
    $pickupMax = max(1, (int) ($availableVehicleCounts['pickup'] ?? 1));
    $pickupQty = max(1, min((int) old('vehicle_requests.2.quantity', 1), $pickupMax));
  @endphp
  <div class="vehicle-request-row flex items-center justify-between gap-3 rounded-xl border border-outline-variant/60 bg-surface-container-highest p-3" data-vehicle-row>
    <div class="flex items-center gap-2">
      <input id="vehicle-pickup" class="vehicle-request-checkbox h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="vehicle_requests[2][selected]" value="1" {{ old('vehicle_requests.2.selected') ? 'checked' : '' }} />
      <input type="hidden" name="vehicle_requests[2][type]" value="pickup" />
      <span class="material-symbols-outlined text-primary text-lg">directions_car</span>
      <label for="vehicle-pickup" class="text-sm font-bold text-on-surface">Pick-up</label>
    </div>
    <div class="flex items-center gap-2">
      <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Qty</label>
      <input class="vehicle-request-quantity w-20 bg-surface-container-lowest border border-slate-200 rounded-lg px-3 py-2 text-center text-sm focus:ring-2 focus:ring-primary" type="number" name="vehicle_requests[2][quantity]" value="{{ $pickupQty }}" min="1" max="{{ $pickupMax }}" {{ old('vehicle_requests.2.selected') ? '' : 'disabled' }} />
      <span class="text-[10px] font-semibold text-slate-500">Max {{ $pickupMax }}</span>
    </div>
  </div>
  @endif

  @if (!$availableVehicleTypes['coaster'] && !$availableVehicleTypes['van'] && !$availableVehicleTypes['pickup'])
  <div class="rounded-xl border border-error/30 bg-error-container px-4 py-3 text-xs font-semibold text-on-error-container">
    No vehicles are currently marked as Available.
  </div>
  @endif
</div>
</div>
</div>
</section>
<section class="lg:col-span-3 mb-5">
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
<span class="material-symbols-outlined">person_add</span>
</div>
<h2 class="text-xl font-bold text-primary tracking-tight">Business Passengers</h2>
</div>
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
<div class="flex items-center justify-between gap-3 pb-1">
<p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Personnel Entries <span id="personnel-count" class="ml-1 text-primary">(1)</span></p>
<button id="add-personnel-button" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all text-xs font-bold uppercase tracking-wider" type="button">
<span class="material-symbols-outlined text-lg">add</span>
    Add Personnel
  </button>
</div>
<div id="personnel-list" class="space-y-4 max-h-[24rem] overflow-y-auto pr-1">
<div class="personnel-row grid grid-cols-1 md:grid-cols-2 gap-4 items-end pb-4 border-b border-slate-100 last:border-0" data-index="0">
<div class="space-y-1">
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500">Passenger ID Number</label>
<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary font-mono no-spin" maxlength="6" pattern="\d{6}" placeholder="000000" type="text" inputmode="numeric" name="division_personnel[0][id_number]" value="{{ old('division_personnel.0.id_number') }}" data-role="personnel-id"/>
</div>
<div class="space-y-1">
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500">Name</label>
<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Personnel Name" type="text" name="division_personnel[0][name]" value="{{ old('division_personnel.0.name') }}" data-role="personnel-name" readonly/>
</div>
<div class="md:col-span-3 flex justify-end">
<button class="remove-personnel-btn hidden items-center gap-1 px-3 py-2 rounded-lg bg-error-container text-on-error-container hover:opacity-90 transition-all text-xs font-bold uppercase tracking-wider" type="button" data-role="remove-personnel">
<span class="material-symbols-outlined text-base">delete</span>
Remove
</button>
</div>
</div>
</div>
<p id="add-personnel-error" class="hidden text-xs font-semibold text-error text-right">Complete the current passenger name before adding a new row.</p>
</div>
</section>
</div>

<section class="mt-8 mb-5 pt-6">
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
<span class="material-symbols-outlined">group_work</span>
</div>
<h2 class="text-xl font-bold text-primary tracking-tight">Requesting Division</h2>
</div>
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Name</label>
<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Authorized Requestor Name" type="text" name="requesting_division_name" value="{{ old('requesting_division_name') }}"/>
</div>
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Position</label>
<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Official Designation" type="text" name="requesting_division_position" value="{{ old('requesting_division_position') }}"/>
</div>
</div>
</div>
</section>

<!-- Section 4: Dispatch Certification -->
<section class="pt-5">
  <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
      <span class="material-symbols-outlined">attach_file</span>
    </div>
    <h2 class="text-xl font-bold text-primary tracking-tight">File Attachments</h2>
  </div>

  <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
    
    <!-- File Input -->
    <label class="border-2 border-dashed border-slate-200 rounded-2xl p-8 flex flex-col items-center justify-center bg-surface-container-low/30 hover:bg-surface-container-low/50 transition-colors cursor-pointer">
      <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">cloud_upload</span>
      <p class="text-sm font-semibold text-on-surface">Click or drag files to upload</p>
      <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wider">Supported: PDF, DOC, DOCX (Max 10MB)</p>
      <input id="attachment-input" type="file" class="hidden" accept=".pdf,.doc,.docx" name="attachments[]" multiple/>
    </label>

    <!-- Attached Files List -->
    <div class="space-y-2">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Attached Files</p>
      <div id="attached-files-list" class="space-y-2">
        <p id="attached-files-empty" class="text-xs text-slate-400 italic">No files attached yet.</p>
      </div>
    </div>

  </div>
</section>
<section>
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center text-white">
<span class="material-symbols-outlined">local_shipping</span>
</div>
<h2 class="text-xl font-bold text-primary tracking-tight">Dispatch Certification</h2>
</div>
<div class="bg-white border-2 border-secondary/20 p-8 rounded-2xl shadow-sm">
<div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
<p class="text-on-surface font-medium">I hereby certify that vehicle RP/RPT:</p>
<input class="flex-1 max-w-[200px] border-b-2 border-slate-200 focus:border-secondary border-t-0 border-x-0 bg-transparent px-2 py-1 font-bold text-secondary" placeholder="" type="text" name="vehicle_id" value="{{ old('vehicle_id') }}" readonly/>
<input class="flex-1 max-w-[200px] border-b-2 border-slate-200 focus:border-secondary border-t-0 border-x-0 bg-transparent px-2 py-1 font-bold text-secondary" placeholder="" type="text" name="vehicle_id" value="{{ old('vehicle_id') }}" readonly/>
{{-- <p class="text-on-surface font-medium">and Driver:</p>
<select class="flex-1 max-w-[250px] border-b-2 border-slate-200 focus:border-secondary border-t-0 bor der-x-0 bg-transparent px-2 py-1 font-bold text-secondary" name="driver_name" readonly>
<option value="">Select Driver</option>
{{-- @foreach (($drivers ?? collect()) as $driver)
<option value="{{ $driver->name }}" @selected(old('driver_name') === $driver->name)>{{ $driver->name }}</option>
@endforeach --}}
{{-- </select> --}}
</div>
<div class="flex items-center gap-3 text-secondary">
<span class="material-symbols-outlined">verified</span>
<p class="text-sm font-semibold italic">has been officially dispatched for the requesting trip.</p>
</div>
</div>
</section>
<!-- Section 5: Authorization & Approval -->
<section class="bg-primary text-white p-10 rounded-3xl relative overflow-hidden"> 
<div class="absolute -right-20 -top-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
<div class="relative z-10">
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-end">
<div class="space-y-8">
<div>
<div class="flex items-center gap-3 mb-6">
<span class="material-symbols-outlined">verified_user</span>
<h2 class="text-xl font-bold tracking-tight">Approval Request</h2>
</div>
<div class="space-y-2">
  <p></p>
<p class="text-[16px] font-black uppercase tracking-widest text-blue-200">Division Manager A, EOD.</p>
<div class="border-b border-white/30 pb-4 mb-2">
<p class="text-xl font-bold">ENGR. EMILIO M. DOMAGAS JR.</p>
<p class="text-[12px] text-blue-300 opacity-70">Official Signatory</p>
</div>
<div class="w-full h-12 bg-white/10 rounded-lg flex items-center justify-center text-[10px] font-bold uppercase tracking-widest text-white/50 border border-white/10">
    Authorized Travel Approval
</div>
</div>
</div>
</div>
<div class="flex flex-col gap-4">
<p class="text-[11px] text-blue-200 text-right italic max-w-sm ml-auto opacity-80 leading-relaxed">
    By submitting, you certify that the vehicle and personnel will be used solely for the approved official trip and in accordance with NIA travel guidelines.
</p>
<div class="flex gap-4 w-full">
<button id="primary-download-trigger" class="flex-[1.5] py-4 px-6 rounded-xl bg-secondary hover:bg-secondary/90 text-white font-bold tracking-tight shadow-xl shadow-black/20 transition-all text-sm flex items-center justify-center gap-2" type="button">Download Travel Request <span class="material-symbols-outlined text-lg">download</span></button>
</div>
</div>
</div>
</div>
</section>
</form>

<div id="no-available-vehicles-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 px-4">
  <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
    <div class="mb-4 flex items-center gap-3 text-error">
      <span class="material-symbols-outlined">warning</span>
      <h3 class="text-lg font-bold">No Available Vehicles</h3>
    </div>
    <p class="text-sm text-on-surface-variant leading-relaxed">
      All vehicles are currently on business trip or unavailable. Please submit your request again once a vehicle becomes available.
    </p>
    <div class="mt-6 flex justify-end">
      <button id="no-available-vehicles-close" type="button" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary/90">Understood</button>
    </div>
  </div>
</div>

<div id="confirm-download-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
  <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
    <div class="mb-4 flex items-center gap-3 text-primary">
      <span class="material-symbols-outlined">help</span>
      <h3 class="text-lg font-bold">Confirm Download</h3>
    </div>
    <p class="text-sm text-on-surface-variant">Are you sure you want to download?</p>
    <div class="mt-6 flex justify-end gap-3">
      <button id="confirm-download-no" type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">No</button>
      <button id="confirm-download-yes" type="button" class="rounded-lg bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-secondary/90">Yes</button>
    </div>
  </div>
</div>

<div id="download-loading-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/45 px-4">
  <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 text-center">
    <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
    <p class="text-sm font-semibold text-on-surface">Preparing your download...</p>
  </div>
</div>
</main>

@include('layouts.footer')

<script>
  (function () {
    const requestForm = document.getElementById('request-form');
    const vehicleRequestRows = Array.from(document.querySelectorAll('[data-vehicle-row]'));
    const personnelList = document.getElementById('personnel-list');
    const personnelCount = document.getElementById('personnel-count');
    const addPersonnelButton = document.getElementById('add-personnel-button');
    const addPersonnelError = document.getElementById('add-personnel-error');
    const requestingDivisionName = requestForm ? requestForm.querySelector('input[name="requesting_division_name"]') : null;
    const requestingDivisionPosition = requestForm ? requestForm.querySelector('input[name="requesting_division_position"]') : null;
    const input = document.getElementById('attachment-input');
    const list = document.getElementById('attached-files-list');
    const emptyState = document.getElementById('attached-files-empty');
    const primaryDownloadTrigger = document.getElementById('primary-download-trigger');
    const confirmDownloadModal = document.getElementById('confirm-download-modal');
    const confirmDownloadYes = document.getElementById('confirm-download-yes');
    const confirmDownloadNo = document.getElementById('confirm-download-no');
    const noAvailableVehiclesModal = document.getElementById('no-available-vehicles-modal');
    const noAvailableVehiclesClose = document.getElementById('no-available-vehicles-close');
    const downloadLoadingModal = document.getElementById('download-loading-modal');
    const generatedDownloadUrl = @json(session('download_file') ? route('request-form.download', ['filename' => session('download_file')]) : null);
    const shouldAutoDownload = @json((bool) session('auto_download'));
    const shouldShowNoAvailableVehiclesModal = @json((bool) ($showNoAvailableVehiclesModal ?? false));

    let pendingDownloadAction = null;
    let hasConfirmedSubmit = false;

    if (!requestForm || !input || !list || !emptyState) {
      return;
    }

    const hasPersonnelSection = !!(personnelList && personnelCount && addPersonnelButton && addPersonnelError);

    function updatePersonnelCount() {
      personnelCount.textContent = '(' + getPersonnelRows().length + ')';
    }

    function showConfirmModal(action) {
      pendingDownloadAction = action;
      if (!confirmDownloadModal) {
        return;
      }

      confirmDownloadModal.classList.remove('hidden');
      confirmDownloadModal.classList.add('flex');
    }

    function hideConfirmModal() {
      pendingDownloadAction = null;
      if (!confirmDownloadModal) {
        return;
      }

      confirmDownloadModal.classList.add('hidden');
      confirmDownloadModal.classList.remove('flex');
    }

    function showLoadingModal() {
      if (!downloadLoadingModal) {
        return;
      }

      downloadLoadingModal.classList.remove('hidden');
      downloadLoadingModal.classList.add('flex');
    }

    function hideLoadingModal() {
      if (!downloadLoadingModal) {
        return;
      }

      downloadLoadingModal.classList.add('hidden');
      downloadLoadingModal.classList.remove('flex');
    }

    function showUnavailableVehiclesModal() {
      if (!noAvailableVehiclesModal) {
        return;
      }

      noAvailableVehiclesModal.classList.remove('hidden');
      noAvailableVehiclesModal.classList.add('flex');
    }

    function hideUnavailableVehiclesModal() {
      if (!noAvailableVehiclesModal) {
        return;
      }

      noAvailableVehiclesModal.classList.add('hidden');
      noAvailableVehiclesModal.classList.remove('flex');
    }

    function setPrimaryButtonBusy(isBusy) {
      if (!primaryDownloadTrigger) {
        return;
      }

      primaryDownloadTrigger.disabled = isBusy;
      primaryDownloadTrigger.classList.toggle('pointer-events-none', isBusy);
      primaryDownloadTrigger.classList.toggle('opacity-80', isBusy);
    }

    function startBackgroundDownload(downloadUrl) {
      const iframeId = 'hidden-download-frame';
      let frame = document.getElementById(iframeId);
      let isCompleted = false;

      function completeDownloadUI() {
        if (isCompleted) {
          return;
        }

        isCompleted = true;
        hideLoadingModal();
        setPrimaryButtonBusy(false);
        window.removeEventListener('focus', handleWindowFocus);
      }

      function handleWindowFocus() {
        completeDownloadUI();
      }

      if (!frame) {
        frame = document.createElement('iframe');
        frame.id = iframeId;
        frame.style.display = 'none';
        document.body.appendChild(frame);
      }

      const separator = downloadUrl.indexOf('?') === -1 ? '?' : '&';
      frame.onload = function () {
        completeDownloadUI();
      };

      window.addEventListener('focus', handleWindowFocus);
      frame.src = downloadUrl + separator + 'download_ts=' + Date.now();

      setTimeout(function () {
        completeDownloadUI();
      }, 2000);
    }

    function bindVehicleRequestRows() {
      vehicleRequestRows.forEach(function (row) {
        const checkbox = row.querySelector('.vehicle-request-checkbox');
        const quantityInput = row.querySelector('.vehicle-request-quantity');

        if (!checkbox || !quantityInput) {
          return;
        }

        function clampQuantityWithinBounds() {
          const maxAllowed = Number(quantityInput.max || 1);
          const minAllowed = Number(quantityInput.min || 1);
          const maxValue = Number.isFinite(maxAllowed) && maxAllowed > 0 ? maxAllowed : 1;
          const minValue = Number.isFinite(minAllowed) && minAllowed > 0 ? minAllowed : 1;
          const rawValue = Number(quantityInput.value);

          if (!Number.isFinite(rawValue)) {
            quantityInput.value = String(minValue);
            return;
          }

          quantityInput.value = String(Math.min(maxValue, Math.max(minValue, Math.floor(rawValue))));
        }

        function applyState() {
          const isSelected = checkbox.checked;
          quantityInput.disabled = !isSelected;

          if (isSelected && (!quantityInput.value || Number(quantityInput.value) < 1)) {
            quantityInput.value = '1';
          }

          if (isSelected) {
            clampQuantityWithinBounds();
          }

          row.classList.toggle('border-primary/50', isSelected);
          row.classList.toggle('bg-white', isSelected);
        }

        quantityInput.addEventListener('input', clampQuantityWithinBounds);
        quantityInput.addEventListener('blur', clampQuantityWithinBounds);
        checkbox.addEventListener('change', applyState);
        applyState();
      });
    }

    function hasSelectedVehicleRequests() {
      return vehicleRequestRows.some(function (row) {
        const checkbox = row.querySelector('.vehicle-request-checkbox');
        return checkbox && checkbox.checked;
      });
    }

    bindVehicleRequestRows();

    function normalizePersonnelId(rawValue) {
      return String(rawValue || '').replace(/\D/g, '').slice(0, 6);
    }

    async function fetchPersonnelName(personnelId) {
      const response = await fetch('/request-form/personnel/' + personnelId, {
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        return null;
      }

      return response.json();
    }

    function updateRemoveButtons() {
      const rows = Array.from(personnelList.querySelectorAll('.personnel-row'));
      const canRemove = rows.length > 1;

      rows.forEach(function (row) {
        const removeButton = row.querySelector('[data-role="remove-personnel"]');

        if (!removeButton) {
          return;
        }

        removeButton.classList.toggle('hidden', !canRemove);
        removeButton.classList.toggle('inline-flex', canRemove);
      });
    }

    function getPersonnelRows() {
      return Array.from(personnelList.querySelectorAll('.personnel-row'));
    }

    function reindexPersonnelRows() {
      const rows = getPersonnelRows();

      rows.forEach(function (row, index) {
        row.setAttribute('data-index', String(index));

        const idInput = row.querySelector('[data-role="personnel-id"]');
        const nameInput = row.querySelector('[data-role="personnel-name"]');

        if (idInput) {
          idInput.name = 'division_personnel[' + index + '][id_number]';
        }

        if (nameInput) {
          nameInput.name = 'division_personnel[' + index + '][name]';
        }
      });

      nextPersonnelIndex = rows.length;
    }

    function pruneEmptyPersonnelRows() {
      const rows = getPersonnelRows();

      rows.forEach(function (row) {
        const idInput = row.querySelector('[data-role="personnel-id"]');
        const nameInput = row.querySelector('[data-role="personnel-name"]');

        if (!idInput || !nameInput) {
          return;
        }

        const idValue = normalizePersonnelId(idInput.value);
        const nameValue = String(nameInput.value || '').trim();

        if (idValue === '' && nameValue === '' && getPersonnelRows().length > 1) {
          row.remove();
        }
      });

      reindexPersonnelRows();
      updateRemoveButtons();
      updatePersonnelCount();
    }

    function hasInvalidPersonnelRows() {
      const rows = getPersonnelRows();
      let hasInvalid = false;

      rows.forEach(function (row) {
        const idInput = row.querySelector('[data-role="personnel-id"]');
        const nameInput = row.querySelector('[data-role="personnel-name"]');

        if (!idInput || !nameInput) {
          return;
        }

        const idValue = normalizePersonnelId(idInput.value);
        const nameValue = String(nameInput.value || '').trim();
        const invalid = idValue === '' || nameValue === '';

        idInput.classList.toggle('ring-2', invalid && idValue === '');
        idInput.classList.toggle('ring-error/40', invalid && idValue === '');
        nameInput.classList.toggle('ring-2', invalid && nameValue === '');
        nameInput.classList.toggle('ring-error/40', invalid && nameValue === '');

        hasInvalid = hasInvalid || invalid;
      });

      return hasInvalid;
    }

    function bindPersonnelRowEvents(row) {
      const personnelIdInput = row.querySelector('[data-role="personnel-id"]');
      const personnelNameInput = row.querySelector('[data-role="personnel-name"]');
      const removeButton = row.querySelector('[data-role="remove-personnel"]');

      if (!personnelIdInput || !personnelNameInput || !removeButton) {
        return;
      }

      let lastLookupValue = '';
      const runPersonnelLookup = async function () {
        const sanitizedId = normalizePersonnelId(personnelIdInput.value);
        personnelIdInput.value = sanitizedId;

        if (sanitizedId.length !== 6) {
          if (personnelNameInput.value === '' || lastLookupValue !== sanitizedId) {
            personnelNameInput.value = '';
          }
          lastLookupValue = sanitizedId;
          return;
        }

        lastLookupValue = sanitizedId;
        const result = await fetchPersonnelName(sanitizedId);

        if (!result || lastLookupValue !== sanitizedId) {
          personnelNameInput.value = '';
          personnelNameInput.classList.add('ring-2', 'ring-error/40');
          return;
        }

        personnelNameInput.value = result.name || '';
        personnelNameInput.classList.remove('ring-2', 'ring-error/40');
        addPersonnelError.classList.add('hidden');
      };

      personnelIdInput.addEventListener('input', runPersonnelLookup);
      personnelIdInput.addEventListener('blur', runPersonnelLookup);

      if (normalizePersonnelId(personnelIdInput.value).length === 6 && !personnelNameInput.value) {
        runPersonnelLookup();
      }

      removeButton.addEventListener('click', function () {
        row.remove();
        reindexPersonnelRows();
        updateRemoveButtons();
        updatePersonnelCount();
      });
    }

    function createPersonnelRow(index) {
      const row = document.createElement('div');
      row.className = 'personnel-row grid grid-cols-1 md:grid-cols-2 gap-4 items-end pb-4 border-b border-slate-100 last:border-0';
      row.setAttribute('data-index', String(index));
      row.innerHTML = '<div class="space-y-1">'
        + '<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500">Personnel ID Number</label>'
        + '<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary font-mono no-spin" maxlength="6" pattern="\\d{6}" placeholder="000000" type="text" inputmode="numeric" name="division_personnel[' + index + '][id_number]" data-role="personnel-id"/>'
        + '</div>'
        + '<div class="space-y-1">'
        + '<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500">Name</label>'
        + '<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Personnel Name" type="text" name="division_personnel[' + index + '][name]" data-role="personnel-name" readonly/>'
        + '</div>'
        + '<div class="md:col-span-3 flex justify-end">'
        + '<button class="remove-personnel-btn items-center gap-1 px-3 py-2 rounded-lg bg-error-container text-on-error-container hover:opacity-90 transition-all text-xs font-bold uppercase tracking-wider inline-flex" type="button" data-role="remove-personnel">'
        + '<span class="material-symbols-outlined text-base">delete</span>'
        + 'Remove'
        + '</button>'
        + '</div>';

      return row;
    }

    function hasEmptyPersonnelName() {
      const rows = Array.from(personnelList.querySelectorAll('.personnel-row'));
      let hasEmpty = false;

      rows.forEach(function (row) {
        const nameInput = row.querySelector('[data-role="personnel-name"]');

        if (!nameInput) {
          return;
        }

        const isEmpty = String(nameInput.value || '').trim() === '';
        nameInput.classList.toggle('ring-2', isEmpty);
        nameInput.classList.toggle('ring-error/40', isEmpty);
        hasEmpty = hasEmpty || isEmpty;
      });

      return hasEmpty;
    }

    let nextPersonnelIndex = hasPersonnelSection ? getPersonnelRows().length : 0;

    if (hasPersonnelSection) {
      getPersonnelRows().forEach(bindPersonnelRowEvents);
      reindexPersonnelRows();
      updateRemoveButtons();
      updatePersonnelCount();

      addPersonnelButton.addEventListener('click', function () {
        if (hasEmptyPersonnelName()) {
          addPersonnelError.textContent = 'Complete the current passenger name before adding a new row.';
          addPersonnelError.classList.remove('hidden');
          return;
        }

        addPersonnelError.classList.add('hidden');
        const row = createPersonnelRow(nextPersonnelIndex);
        personnelList.appendChild(row);
        bindPersonnelRowEvents(row);
        nextPersonnelIndex += 1;
        reindexPersonnelRows();
        updateRemoveButtons();
        updatePersonnelCount();
        row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
    }

    requestForm.addEventListener('submit', function (event) {
      if (hasPersonnelSection) {
        pruneEmptyPersonnelRows();
      }

      if (!hasSelectedVehicleRequests()) {
        event.preventDefault();
        hasConfirmedSubmit = false;
        hideLoadingModal();
        if (addPersonnelError) {
          addPersonnelError.textContent = 'Select at least one vehicle type and quantity.';
          addPersonnelError.classList.remove('hidden');
        }
        return;
      }

      if (hasPersonnelSection && hasInvalidPersonnelRows()) {
        event.preventDefault();
        hasConfirmedSubmit = false;
        hideLoadingModal();
        addPersonnelError.textContent = 'Every passenger row must have a valid 6-digit ID and an auto-filled name.';
        addPersonnelError.classList.remove('hidden');
        return;
      }

      const divisionName = requestingDivisionName ? String(requestingDivisionName.value || '').trim() : '';
      const divisionPosition = requestingDivisionPosition ? String(requestingDivisionPosition.value || '').trim() : '';

      if (requestingDivisionName) {
        requestingDivisionName.classList.toggle('ring-2', divisionName === '');
        requestingDivisionName.classList.toggle('ring-error/40', divisionName === '');
      }

      if (requestingDivisionPosition) {
        requestingDivisionPosition.classList.toggle('ring-2', divisionPosition === '');
        requestingDivisionPosition.classList.toggle('ring-error/40', divisionPosition === '');
      }

      if ((requestingDivisionName && divisionName === '') || (requestingDivisionPosition && divisionPosition === '')) {
        event.preventDefault();
        hasConfirmedSubmit = false;
        hideLoadingModal();
        return;
      }

      if (!hasConfirmedSubmit) {
        event.preventDefault();
        showConfirmModal({ type: 'submit' });
        return;
      }

      hasConfirmedSubmit = false;
    });

    function formatFileSize(bytes) {
      if (bytes >= 1024 * 1024) {
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
      }

      if (bytes >= 1024) {
        return Math.round(bytes / 1024) + ' KB';
      }

      return bytes + ' B';
    }

    function removeFileByIndex(indexToRemove) {
      const currentFiles = Array.from(input.files || []);
      const dataTransfer = new DataTransfer();

      currentFiles.forEach(function (file, index) {
        if (index !== indexToRemove) {
          dataTransfer.items.add(file);
        }
      });

      input.files = dataTransfer.files;
      renderAttachedFiles();
    }

    function renderAttachedFiles() {
      list.querySelectorAll('[data-file-row="true"]').forEach(function (row) {
        row.remove();
      });

      const files = Array.from(input.files || []);
      emptyState.classList.toggle('hidden', files.length > 0);

      files.forEach(function (file, index) {
        const row = document.createElement('div');
        row.setAttribute('data-file-row', 'true');
        row.className = 'flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl shadow-sm';

        const left = document.createElement('div');
        left.className = 'flex items-center gap-3';

        const icon = document.createElement('span');
        icon.className = 'material-symbols-outlined text-primary';
        icon.textContent = 'description';

        const details = document.createElement('div');
        details.className = 'flex flex-col';

        const fileName = document.createElement('span');
        fileName.className = 'text-sm font-semibold';
        fileName.textContent = file.name;

        const fileSize = document.createElement('span');
        fileSize.className = 'text-[10px] text-slate-400';
        fileSize.textContent = formatFileSize(file.size);

        details.appendChild(fileName);
        details.appendChild(fileSize);
        left.appendChild(icon);
        left.appendChild(details);

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'p-1 text-slate-400 hover:text-error transition-colors';
        removeButton.setAttribute('aria-label', 'Remove attached file');

        const removeIcon = document.createElement('span');
        removeIcon.className = 'material-symbols-outlined text-lg';
        removeIcon.textContent = 'close';

        removeButton.appendChild(removeIcon);
        removeButton.addEventListener('click', function () {
          removeFileByIndex(index);
        });

        row.appendChild(left);
        row.appendChild(removeButton);
        list.appendChild(row);
      });
    }

    input.addEventListener('change', renderAttachedFiles);

    if (primaryDownloadTrigger) {
      primaryDownloadTrigger.addEventListener('click', function () {
        showConfirmModal({ type: 'submit' });
      });
    }

    if (confirmDownloadNo) {
      confirmDownloadNo.addEventListener('click', function () {
        hideConfirmModal();
        hideLoadingModal();
      });
    }

    if (confirmDownloadModal) {
      confirmDownloadModal.addEventListener('click', function (event) {
        if (event.target === confirmDownloadModal) {
          hideConfirmModal();
          hideLoadingModal();
        }
      });
    }

    if (confirmDownloadYes) {
      confirmDownloadYes.addEventListener('click', function () {
        const action = pendingDownloadAction;

        if (!action) {
          hideConfirmModal();
          return;
        }

        if (action.type === 'submit') {
          hideConfirmModal();
          showLoadingModal();
          hasConfirmedSubmit = true;
          requestForm.requestSubmit();
          return;
        }

        if (action.type === 'download' && action.url) {
          hideConfirmModal();
          showLoadingModal();
          setPrimaryButtonBusy(true);

          startBackgroundDownload(action.url);
        }
      });
    }

    if (noAvailableVehiclesClose) {
      noAvailableVehiclesClose.addEventListener('click', function () {
        hideUnavailableVehiclesModal();
      });
    }

    if (noAvailableVehiclesModal) {
      noAvailableVehiclesModal.addEventListener('click', function (event) {
        if (event.target === noAvailableVehiclesModal) {
          hideUnavailableVehiclesModal();
        }
      });
    }

    if (shouldShowNoAvailableVehiclesModal) {
      showUnavailableVehiclesModal();
    }

    if (shouldAutoDownload && generatedDownloadUrl) {
      showLoadingModal();
      setPrimaryButtonBusy(true);
      startBackgroundDownload(generatedDownloadUrl);
    }
  })();
</script>

</body></html>