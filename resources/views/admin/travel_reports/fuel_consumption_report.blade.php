<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Fuel Consumption Matrix | EMS</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          primary: "#003466",
          secondary: "#3a6843",
          surface: "#f7f9fc",
          "surface-container-low": "#f2f4f7",
          "surface-container-lowest": "#ffffff",
          "surface-container-high": "#e6e8eb",
          outline: "#737781",
          "outline-variant": "#c3c6d1",
          "on-surface": "#191c1e",
          "on-surface-variant": "#424750",
        },
        fontFamily: {
          body: ["Public Sans"],
          label: ["Public Sans"],
        },
      },
    },
  }
</script>
<style>
  body { font-family: 'Public Sans', sans-serif; }

  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
  }

  .fuel-table th,
  .fuel-table td {
    border: 1px solid #d4d9e2;
  }

  .fuel-table thead th {
    background: #eef2f7;
    color: #1f2937;
  }

  .fuel-input {
    width: 100%;
    border: 1px solid transparent;
    background: #ffffff;
    border-radius: 0.4rem;
    padding: 0.35rem 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #111827;
  }

  .fuel-input:focus {
    outline: none;
    border-color: #1a4b84;
    box-shadow: 0 0 0 2px rgba(26, 75, 132, 0.12);
  }

  .fuel-input[readonly] {
    background: #f4f7fb;
    color: #334155;
  }

  .fuel-input.user-cell {
    min-width: 220px;
  }

  @media print {
    .no-print {
      display: none !important;
    }

    body {
      background: #fff;
    }

    .print-sheet {
      box-shadow: none !important;
      border: none !important;
      margin: 0 !important;
    }

    .fuel-table th,
    .fuel-table td {
      border-color: #9aa3b2;
    }
  }
</style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col">
@include('layouts.admin_header')

<main class="flex-grow w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-10 xl:px-12 pt-24 pb-10">
  <section class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
      <p class="text-[11px] uppercase tracking-[0.16em] font-bold text-secondary mb-2">Reports / Fuel Monitoring</p>
      <h1 class="text-3xl font-extrabold tracking-tight text-primary">Monthly Fuel Consumption Report</h1>
      <p class="text-sm font-medium text-on-surface-variant">Monthly Annex B fuel report featuring editable entries and automatic computation of values.</p>
    </div>

    <div class="no-print flex items-center gap-3">
      <button type="button" onclick="window.print()" class="inline-flex items-center gap-1 rounded-lg border border-outline-variant px-4 py-2 text-sm font-bold text-on-surface-variant hover:bg-surface-container-high">
        <span class="material-symbols-outlined text-base">print</span>
        Print
      </button>
    </div>
  </section>

  <section class="no-print mb-6 rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4 md:p-5 shadow-[0px_10px_24px_rgba(25,28,30,0.05)]">
    <form method="GET" action="{{ route('admin.fuel-consumption-report') }}" class="grid grid-cols-1 md:grid-cols-8 gap-3">
      <div>
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Month</label>
        <input type="month" name="month" value="{{ $selectedMonth }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div class="md:col-span-2">
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Office Name</label>
        <input type="text" name="office_name" value="{{ $officeName }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div class="md:col-span-2">
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Address Line 1</label>
        <input type="text" name="address_line_1" value="{{ $addressLine1 }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div class="md:col-span-2">
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Address Line 2</label>
        <input type="text" name="address_line_2" value="{{ $addressLine2 }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div>
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Prepared By</label>
        <input type="text" name="prepared_by" value="{{ $preparedBy }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div>
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Position</label>
        <input type="text" name="prepared_position" value="{{ $preparedPosition }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div>
        <label class="block text-[10px] uppercase tracking-widest font-bold text-outline mb-1">Approved By</label>
        <input type="text" name="approved_by" value="{{ $approvedBy }}" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-3 py-2 text-sm"/>
      </div>

      <div class="md:col-span-2 flex items-end gap-2">
        <button type="submit" class="w-full bg-primary text-white rounded-lg px-4 py-2 text-sm font-bold">Load Report</button>
        <a href="{{ route('admin.fuel-consumption-report') }}" class="w-full text-center bg-surface border border-outline-variant/40 rounded-lg px-4 py-2 text-sm font-bold text-on-surface-variant hover:bg-surface-container-high">Reset</a>
      </div>
    </form>
  </section>

  <section class="print-sheet w-full bg-surface-container-lowest rounded-2xl border border-outline-variant/25 shadow-[0px_12px_30px_rgba(25,28,30,0.08)] overflow-hidden">
    <div class="px-5 md:px-6 py-5 border-b border-outline-variant/20 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
      <div>
        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-secondary mb-1">Annex B</p>
        <h2 class="text-xl md:text-2xl font-extrabold text-primary tracking-tight">Monthly Fuel Consumption Report for Service Vehicles</h2>
        <p class="text-xs font-semibold text-on-surface-variant mt-1">Month: {{ $monthLabel }}</p>
    </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-xs">
        <p><span class="font-bold text-outline">Office:</span> {{ $officeName }}</p>
        <p><span class="font-bold text-outline">Prepared By:</span> {{ $preparedBy !== '' ? $preparedBy : 'N/A' }}</p>
        <p><span class="font-bold text-outline">Address:</span> {{ $addressLine1 }}, {{ $addressLine2 }}</p>
        <p><span class="font-bold text-outline">Approved By:</span> {{ $approvedBy !== '' ? $approvedBy : 'N/A' }}</p>
      </div>
    </div>

    <div class="overflow-x-auto px-3 md:px-4 py-4">
      <table class="fuel-table w-full min-w-[1280px] border-collapse text-[11px]">
        <thead>
          <tr>
            <th rowspan="2" class="px-2 py-2 text-center uppercase font-black w-[240px]">Users</th>
            <th colspan="2" class="px-2 py-2 text-center uppercase font-black">Average Reference Consumption (Liters)</th>
            <th colspan="2" class="px-2 py-2 text-center uppercase font-black">Target Consumption w/ 10% Reduction (Liters)</th>
            <th colspan="2" class="px-2 py-2 text-center uppercase font-black">Actual Consumption This Month (Liters)</th>
            <th colspan="2" class="px-2 py-2 text-center uppercase font-black">% Variance</th>
            <th colspan="2" class="px-2 py-2 text-center uppercase font-black">Remarks</th>
          </tr>
          <tr>
            <th class="px-1 py-2 text-center uppercase font-bold">Diesel</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Gasoline</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Diesel</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Gasoline</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Diesel</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Gasoline</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Diesel</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Gasoline</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Diesel</th>
            <th class="px-1 py-2 text-center uppercase font-bold">Gasoline</th>
          </tr>
        </thead>
        <tbody id="fuel-table-body">
          @foreach ($rows as $row)
          <tr data-row-index="{{ $loop->index }}" class="hover:bg-surface-container-low/40 transition-colors">
            <td class="px-2 py-1.5 align-top">
              <input type="text" class="fuel-input user-cell" name="matrix[{{ $loop->index }}][user]" value="{{ $row['user'] ?? '' }}" placeholder="Department or user group"/>
            </td>
            <td class="px-1 py-1.5"><input type="number" min="0" step="0.01" class="fuel-input js-avg-diesel" name="matrix[{{ $loop->index }}][avg_diesel]"/></td>
            <td class="px-1 py-1.5"><input type="number" min="0" step="0.01" class="fuel-input js-avg-gasoline" name="matrix[{{ $loop->index }}][avg_gasoline]"/></td>
            <td class="px-1 py-1.5"><input type="text" readonly class="fuel-input js-target-diesel" name="matrix[{{ $loop->index }}][target_diesel]"/></td>
            <td class="px-1 py-1.5"><input type="text" readonly class="fuel-input js-target-gasoline" name="matrix[{{ $loop->index }}][target_gasoline]"/></td>
            <td class="px-1 py-1.5"><input type="number" min="0" step="0.01" class="fuel-input js-actual-diesel" name="matrix[{{ $loop->index }}][actual_diesel]"/></td>
            <td class="px-1 py-1.5"><input type="number" min="0" step="0.01" class="fuel-input js-actual-gasoline" name="matrix[{{ $loop->index }}][actual_gasoline]"/></td>
            <td class="px-1 py-1.5"><input type="text" readonly class="fuel-input js-variance-diesel" name="matrix[{{ $loop->index }}][variance_diesel]"/></td>
            <td class="px-1 py-1.5"><input type="text" readonly class="fuel-input js-variance-gasoline" name="matrix[{{ $loop->index }}][variance_gasoline]"/></td>
            <td class="px-1 py-1.5"><input type="text" class="fuel-input" name="matrix[{{ $loop->index }}][remarks_diesel]"/></td>
            <td class="px-1 py-1.5"><input type="text" class="fuel-input" name="matrix[{{ $loop->index }}][remarks_gasoline]"/></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="px-5 md:px-6 py-4 border-t border-outline-variant/20 bg-surface-container-low/40 text-[11px] leading-relaxed text-on-surface-variant space-y-1">
      <p><span class="font-black">NOTES</span></p>
      <p>1. Average reference consumption = yearly diesel or gasoline consumption divided by 12 months.</p>
      <p>2. Target consumption = average reference x 0.90. This is computed automatically.</p>
      <p>3. Percent variance = ((actual consumption this month - average reference) / average reference) x 100%.</p>
      <p>4. Fill remarks for policy reference, explanations, and exceptions.</p>
    </div>
  </section>
</main>

@include('layouts.admin_footer')

<script>
(function () {
  const tableBody = document.getElementById('fuel-table-body');

  function toNumber(value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
  }

  function formatNumber(value, decimals = 2) {
    return Number(value).toLocaleString('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  }

  function formatVariance(value) {
    const sign = value > 0 ? '+' : '';
    return `${sign}${formatNumber(value, 2)}%`;
  }

  function calculateVariance(actualValue, averageValue) {
    if (averageValue <= 0) {
      return null;
    }

    return ((actualValue - averageValue) / averageValue) * 100;
  }

  function calculateRow(row) {
    if (!row) {
      return;
    }

    const avgDieselInput = row.querySelector('.js-avg-diesel');
    const avgGasolineInput = row.querySelector('.js-avg-gasoline');
    const targetDieselInput = row.querySelector('.js-target-diesel');
    const targetGasolineInput = row.querySelector('.js-target-gasoline');
    const actualDieselInput = row.querySelector('.js-actual-diesel');
    const actualGasolineInput = row.querySelector('.js-actual-gasoline');
    const varianceDieselInput = row.querySelector('.js-variance-diesel');
    const varianceGasolineInput = row.querySelector('.js-variance-gasoline');

    const avgDiesel = toNumber(avgDieselInput?.value);
    const avgGasoline = toNumber(avgGasolineInput?.value);
    const actualDiesel = toNumber(actualDieselInput?.value);
    const actualGasoline = toNumber(actualGasolineInput?.value);

    if (targetDieselInput) {
      targetDieselInput.value = avgDiesel > 0 ? formatNumber(avgDiesel * 0.9) : '';
    }
    if (targetGasolineInput) {
      targetGasolineInput.value = avgGasoline > 0 ? formatNumber(avgGasoline * 0.9) : '';
    }

    const varianceDiesel = calculateVariance(actualDiesel, avgDiesel);
    const varianceGasoline = calculateVariance(actualGasoline, avgGasoline);

    if (varianceDieselInput) {
      varianceDieselInput.value = varianceDiesel === null ? '' : formatVariance(varianceDiesel);
    }
    if (varianceGasolineInput) {
      varianceGasolineInput.value = varianceGasoline === null ? '' : formatVariance(varianceGasoline);
    }
  }

  function calculateAllRows() {
    if (!tableBody) {
      return;
    }

    tableBody.querySelectorAll('tr[data-row-index]').forEach(function (row) {
      calculateRow(row);
    });
  }

  document.addEventListener('input', function (event) {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      return;
    }

    if (!target.matches('.js-avg-diesel, .js-avg-gasoline, .js-actual-diesel, .js-actual-gasoline')) {
      return;
    }

    const row = target.closest('tr[data-row-index]');
    calculateRow(row);
  });

  calculateAllRows();
})();
</script>
</body></html>
