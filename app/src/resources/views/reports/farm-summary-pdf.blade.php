<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pigstep Farm Summary Report</title>

    <style>
        @page {
            margin: 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #172033;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        h1 {
            font-size: 24px;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        h2 {
            font-size: 15px;
            margin: 20px 0 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #d6deea;
        }

        h3 {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .muted {
            color: #64748b;
        }

        .header {
            background: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 14px;
        }

        .header-meta {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        .header-meta td {
            color: #d7e0ee;
            padding: 3px 0;
            border: 0;
            font-size: 10px;
        }

        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 0 -8px 8px;
        }

        .summary-grid td {
            width: 25%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            vertical-align: top;
        }

        .metric-label {
            display: block;
            color: #64748b;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .metric-value {
            display: block;
            font-size: 16px;
            font-weight: bold;
            color: #172033;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.data th {
            background: #f1f5f9;
            color: #475569;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 7px;
            border: 1px solid #e2e8f0;
        }

        table.data td {
            padding: 7px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .formula {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 9px 10px;
            margin-bottom: 10px;
            color: #1e3a8a;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-clear {
            background: #dcfce7;
            color: #166534;
        }

        .badge-action {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-watch {
            background: #ffedd5;
            color: #9a3412;
        }

        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #d6deea;
            color: #64748b;
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
@php
    $metrics = $summary['metrics'];
    $generatedAt = $summary['generated_at'];

    $count = fn (string $key): string => number_format((int) ($metrics[$key] ?? 0));
    $money = fn (string $key): string => 'PHP ' . number_format((float) ($metrics[$key] ?? 0), 2);

    $statusClass = function (string $status): string {
        return match ($status) {
            'Clear' => 'badge-clear',
            'Needs action', 'Needs update' => 'badge-action',
            default => 'badge-watch',
        };
    };
@endphp

    <div class="header">
        <h1>Pigstep Farm Summary Report</h1>
        <p>Manual farm operations, herd, medication program, breeding, housing, and simplified financial summary.</p>

        <table class="header-meta">
            <tr>
                <td><strong>Generated at:</strong> {{ $generatedAt->format('Y-m-d H:i:s') }}</td>
                <td><strong>Report type:</strong> {{ $summary['report_type'] }}</td>
            </tr>
            <tr>
                <td><strong>Prepared for:</strong> {{ $summary['prepared_for'] }}</td>
                <td><strong>Product by:</strong> {{ $summary['product_by'] }}</td>
            </tr>
        </table>
    </div>

    <h2>Executive Summary</h2>
    <table class="summary-grid">
        <tr>
            <td>
                <span class="metric-label">Active Pigs</span>
                <span class="metric-value">{{ $count('active_pigs') }}</span>
            </td>
            <td>
                <span class="metric-label">Mortality</span>
                <span class="metric-value">{{ $count('dead_pigs') }}</span>
            </td>
            <td>
                <span class="metric-label">Net Position</span>
                <span class="metric-value">{{ $money('net_position') }}</span>
            </td>
            <td>
                <span class="metric-label">Live Asset Value</span>
                <span class="metric-value">{{ $money('total_asset_value') }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="metric-label">Sale Revenue</span>
                <span class="metric-value">{{ $money('total_revenue') }}</span>
            </td>
            <td>
                <span class="metric-label">Mortality Loss</span>
                <span class="metric-value">{{ $money('mortality_loss') }}</span>
            </td>
            <td>
                <span class="metric-label">Breeding Cost</span>
                <span class="metric-value">{{ $money('total_breeding_cost') }}</span>
            </td>
            <td>
                <span class="metric-label">Stale Weight Pigs</span>
                <span class="metric-value">{{ $count('stale_weight_pigs') }}</span>
            </td>
        </tr>
    </table>

    <h2>Herd Inventory</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Total Pigs</th>
                <th>Active</th>
                <th>Sold</th>
                <th>Dead</th>
                <th>Archived</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $count('total_pigs') }}</td>
                <td>{{ $count('active_pigs') }}</td>
                <td>{{ $count('sold_pigs') }}</td>
                <td>{{ $count('dead_pigs') }}</td>
                <td>{{ $count('archived_pigs') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Housing / Pens</h2>
    <p class="muted" style="margin-bottom: 8px;">Total pen count: {{ $count('pen_count') }}</p>

    @if (empty($summary['pen_occupancy']))
        <p class="muted">No pens recorded.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Pen</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Active Pigs</th>
                    <th>Available Slots</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summary['pen_occupancy'] as $pen)
                    <tr>
                        <td>{{ $pen['name'] }}</td>
                        <td>{{ $pen['type'] }}</td>
                        <td>{{ $pen['capacity'] }}</td>
                        <td>{{ $pen['active_pig_count'] }}</td>
                        <td>{{ $pen['available_slots'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Breeding & Farrowing</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Active Breeding Records</th>
                <th>Farrowing Due Soon</th>
                <th>Pending Pregnancy Checks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $count('active_breeding_records') }}</td>
                <td>{{ $count('farrowing_due_soon') }}</td>
                <td>{{ $count('pending_pregnancy_checks') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Medication Program Alerts</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Stale Weight Pigs</th>
                <th>Medication Due Today</th>
                <th>Medication Overdue</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $count('stale_weight_pigs') }}</td>
                <td>{{ $count('protocol_due_today') }}</td>
                <td>{{ $count('protocol_overdue') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Overdue Medication Program Items</h3>
    @if (empty($summary['protocol_overdue_rows']))
        <p class="muted">No overdue medication program items.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Pig</th>
                    <th>Action</th>
                    <th>Type</th>
                    <th>Requirement</th>
                    <th>Scheduled Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summary['protocol_overdue_rows'] as $row)
                    <tr>
                        <td>{{ $row['pig_ear_tag'] }}</td>
                        <td>{{ $row['action'] }}</td>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['requirement'] }}</td>
                        <td>{{ $row['scheduled_date'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="page-break"></div>

    <h2>Financial Summary</h2>
    <div class="formula">
        <strong>Formula:</strong>
        Net Position = Live Asset Value + Sale Revenue - Mortality Loss - Breeding Cost
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Metric</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Live Asset Value</td><td>{{ $money('total_asset_value') }}</td></tr>
            <tr><td>Sale Revenue</td><td>{{ $money('total_revenue') }}</td></tr>
            <tr><td>Mortality Loss</td><td>{{ $money('mortality_loss') }}</td></tr>
            <tr><td>Breeding Cost</td><td>{{ $money('total_breeding_cost') }}</td></tr>
            <tr><td><strong>Net Position</strong></td><td><strong>{{ $money('net_position') }}</strong></td></tr>
        </tbody>
    </table>

    <h2>Action Checklist</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Action</th>
                <th>Count</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summary['action_checklist'] as $item)
                <tr>
                    <td>{{ $item['item'] }}</td>
                    <td>{{ $item['count'] }}</td>
                    <td>
                        <span class="badge {{ $statusClass($item['status']) }}">
                            {{ $item['status'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <strong>Generated by Pigstep.</strong><br>
        Product by Kirjane Labs. Lead Developer: Kirch Ivan A. Balite. Co-Developer: Osiris Kedigadash Palac.<br>
        Support: kirchivan123@gmail.com / 09486328353.<br>
        © {{ date('Y') }} Kirjane Labs. All rights reserved.
    </div>
</body>
</html>
