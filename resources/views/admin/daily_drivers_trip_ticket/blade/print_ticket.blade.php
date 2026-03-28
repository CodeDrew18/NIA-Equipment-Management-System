<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print DTT {{ $request->form_id }}</title>
    <style>
        :root {
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #d1d5db;
            --accent: #0f4c81;
            --paper: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f3f4f6;
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.45;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            background: var(--paper);
            border: 1px solid var(--line);
            padding: 14mm;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 16px;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .title {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: var(--accent);
        }

        .subtitle {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .badge {
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #111827;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 16px;
            margin-bottom: 16px;
        }

        .item {
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 8px 10px;
            min-height: 56px;
        }

        .label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .value {
            font-size: 14px;
            font-weight: 700;
            word-break: break-word;
        }

        .full {
            grid-column: 1 / -1;
        }

        .section-title {
            margin: 18px 0 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 800;
            color: var(--muted);
        }

        .description {
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 10px;
            min-height: 72px;
            white-space: pre-line;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 18px;
            margin-top: 26px;
        }

        .signature-box {
            border-top: 1px solid #111827;
            padding-top: 8px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            min-height: 46px;
        }

        .actions {
            width: 210mm;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn.primary {
            background: #0f4c81;
            border-color: #0f4c81;
            color: #ffffff;
        }

        @media print {
            body { background: #ffffff; }
            .actions { display: none; }
            .sheet {
                margin: 0;
                border: none;
                width: auto;
                min-height: auto;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="btn" onclick="window.close()">Close</button>
        <button type="button" class="btn primary" onclick="window.print()">Print</button>
    </div>

    <div class="sheet">
        <header class="header">
            <div>
                <h1 class="title">Daily Trip Ticket</h1>
                <p class="subtitle">NIA Equipment Management System</p>
            </div>
            <div class="badge">Reference: {{ $request->form_id }}</div>
        </header>

        @php
            $status = (string) ($request->status ?? 'Pending');
            $passengers = [];
            if (is_array($request->business_passengers)) {
                foreach ($request->business_passengers as $row) {
                    if (is_array($row) && !empty($row['name'])) {
                        $passengers[] = $row['name'];
                    } elseif (is_string($row) && trim($row) !== '') {
                        $passengers[] = trim($row);
                    }
                }
            }
        @endphp

        <section class="grid">
            <div class="item">
                <div class="label">Request Date</div>
                <div class="value">{{ optional($request->request_date)->format('F d, Y') ?: 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="label">Status</div>
                <div class="value">{{ $status }}</div>
            </div>
            <div class="item">
                <div class="label">Requestor</div>
                <div class="value">{{ $request->requestor_name ?: 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="label">Position</div>
                <div class="value">{{ $request->requestor_position ?: 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="label">Vehicle Type</div>
                <div class="value">{{ $request->vehicle_type ?: 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="label">Vehicle Quantity</div>
                <div class="value">{{ $request->vehicle_quantity ?? 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="label">Trip Schedule</div>
                <div class="value">{{ $dateRangeLabel }}</div>
            </div>
            <div class="item">
                <div class="label">DTT Count</div>
                <div class="value">{{ $dttCount }} ({{ $daysTotalLabel }})</div>
            </div>
            <div class="item full">
                <div class="label">Destination</div>
                <div class="value">{{ $request->destination ?: 'N/A' }}</div>
            </div>
            <div class="item full">
                <div class="label">Driver</div>
                <div class="value">{{ $request->driver_name ?: 'To be assigned' }}</div>
            </div>
            <div class="item full">
                <div class="label">Business Passengers</div>
                <div class="value">{{ count($passengers) ? implode(', ', $passengers) : 'N/A' }}</div>
            </div>
        </section>

        <h2 class="section-title">Purpose</h2>
        <div class="description">{{ $request->purpose ?: 'N/A' }}</div>

        <div class="signatures">
            <div class="signature-box">Prepared By</div>
            <div class="signature-box">Checked By</div>
            <div class="signature-box">Approved By</div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
