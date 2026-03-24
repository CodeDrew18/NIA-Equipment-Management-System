<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
<main class="pt-24 pb-20 px-4 md:px-8 max-w-7xl mx-auto flex-grow w-full">
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
<form class="space-y-10 px-4">
<!-- Section 1: Requestor & Trip Core Info -->
{{-- <section>
  <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
      <span class="material-symbols-outlined">info</span>
    </div>
    <h2 class="text-xl font-bold text-primary tracking-tight">Request Details</h2>
  </div>

  <div class="grid grid-cols-3 gap-6">
    <!-- First column with 3 content items -->
    <div class="space-y-6">
      <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date of Request</label>
        <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="date" value="<?php echo date('Y-m-d'); ?>"/>
      </div>

      <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">To Be Used By</label>
        <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Division / Personnel" type="text"/>
      </div>

      <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Destination</label>
        <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Site Location" type="text"/>
      </div>
    </div>

    <!-- Second column with 2 content items -->
    <div class="space-y-6">
      <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date & Time Used: (FROM)</label>
        <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="datetime-local"/>
      </div>

      <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date & Time Used: (TO)</label>
        <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="datetime-local"/>
      </div>
    </div>
  </div>
</section> --}}

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
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="date" value="<?php echo date('Y-m-d'); ?>"/>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">To Be Used By</label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Division / Personnel" type="text"/>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Destination</label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" placeholder="Site Location" type="text"/>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-6 mt-6">
    <!-- Second row: 2 columns that span like the first row -->
    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100 col-span-1">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date & Time Used: <b>(FROM)</b></label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="datetime-local"/>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-slate-100 col-span-1">
      <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Date & Time Used: <b>(TO)</b></label>
      <input class="w-full bg-surface-container-low border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary" type="datetime-local"/>
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
<textarea class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none shadow-sm text-on-surface" placeholder="Describe the specific purpose and work to be performed..." rows="3"></textarea>
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
<div class="grid gap-3 grid-cols-3">
<button class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 border-primary bg-white text-primary font-bold shadow-sm" type="button">
<span class="material-symbols-outlined text-2xl">directions_bus</span>
<span class="text-[10px] uppercase tracking-tighter">Coaster</span>
</button>
<button class="flex flex-col items-center gap-2 p-3 rounded-xl border border-outline-variant bg-surface-container-highest text-on-surface-variant hover:bg-white transition-all" type="button">
<span class="material-symbols-outlined text-2xl">airport_shuttle</span>
<span class="text-[10px] uppercase tracking-tighter">Van</span>
</button>
<button class="flex flex-col items-center gap-2 p-3 rounded-xl border border-outline-variant bg-surface-container-highest text-on-surface-variant hover:bg-white transition-all" type="button">
<span class="material-symbols-outlined text-2xl">directions_car</span>
<span class="text-[10px] uppercase tracking-tighter">Pick-up</span>
</button>
</div>
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Quantity</label>
<input class="w-20 bg-surface-container-lowest border border-slate-200 rounded-lg px-3 py-2 text-center text-sm focus:ring-2 focus:ring-primary" type="number" value="1"/>
</div>
</div>
</div>
</section>
<section class="lg:col-span-3 mb-5">
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
<span class="material-symbols-outlined">person_add</span>
</div>
<h2 class="text-xl font-bold text-primary tracking-tight">Assigned Personnel</h2>
</div>
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
<div class="space-y-4" id="personnel-list">
<!-- Personnel Row 1 -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end pb-4 border-b border-slate-100 last:border-0">
<div class="space-y-1">
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500">Personnel ID Number</label>
<input class="w-full bg-surface-container-low border-none rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary font-mono" maxlength="6" pattern="\d{6}" placeholder="000000" type="number"/>
</div>
<div class="space-y-1">
<label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500">Name  </label>
<input class="w-full bg-slate-50 border border-slate-100 rounded-lg px-4 py-2 text-sm text-slate-400 cursor-not-allowed" disabled="" placeholder="Personnel Name" type="text"/>
</div>
</div>
</div>
<div class="mt-6 flex justify-end">
<button class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all text-xs font-bold uppercase tracking-wider" type="button">
<span class="material-symbols-outlined text-lg">add</span>
        Add Personnel
    </button>
</div>
</div>
</section>
</div>

<!-- Section 4: Dispatch Certification -->
<section class="pt-5 mt-5">
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
      <input id="attachment-input" type="file" class="hidden" accept=".pdf,.doc,.docx"/>
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
<input class="flex-1 max-w-[200px] border-b-2 border-slate-200 focus:border-secondary border-t-0 border-x-0 bg-transparent px-2 py-1 font-bold text-secondary" placeholder="Vehicle ID #" type="text"/>
<p class="text-on-surface font-medium">and Driver:</p>
<input class="flex-1 max-w-[250px] border-b-2 border-slate-200 focus:border-secondary border-t-0 border-x-0 bg-transparent px-2 py-1 font-bold text-secondary" placeholder="Full Name" type="text"/>
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
<button class="flex-1 py-4 px-6 rounded-xl border-2 border-white/20 hover:bg-white/10 font-bold tracking-tight transition-all flex items-center justify-center gap-2 text-sm" type="button">Download File <span class="material-symbols-outlined text-lg">download</span></button>

<button class="flex-[1.5] py-4 px-6 rounded-xl bg-secondary hover:bg-secondary/90 text-white font-bold tracking-tight shadow-xl shadow-black/20 transition-all text-sm flex items-center justify-center gap-2" type="submit">
                                Submit Travel Request
                                <span class="material-symbols-outlined text-lg">send</span>
</button>
</div>
</div>
</div>
</div>
</section>
</form>
</main>

@include('layouts.footer')

<script>
  (function () {
    const input = document.getElementById('attachment-input');
    const list = document.getElementById('attached-files-list');
    const emptyState = document.getElementById('attached-files-empty');

    if (!input || !list || !emptyState) {
      return;
    }

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
  })();
</script>

</body></html>