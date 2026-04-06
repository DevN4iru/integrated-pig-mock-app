<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pigstep')</title>

    <style>
        :root {
            --bg: #f5f7fb;
            --panel: #ffffff;
            --panel-2: #f9fafc;
            --line: #e6eaf2;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --orange: #ea580c;
            --orange-soft: #ffedd5;
            --red: #dc2626;
            --red-soft: #fee2e2;
            --shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            --radius: 18px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Inter, Arial, sans-serif;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        img {
            max-width: 100%;
            display: block;
        }

        .app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: #111827;
            color: #fff;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .brand h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .brand p {
            color: #cbd5e1;
            font-size: 13px;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav a {
            text-decoration: none;
            color: #d1d5db;
            padding: 12px 14px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid transparent;
            transition: 0.2s ease;
            font-size: 14px;
        }

        .nav a:hover,
        .nav a.active {
            background: rgba(255,255,255,0.08);
            color: #fff;
            border-color: rgba(255,255,255,0.08);
        }

        .nav small {
            font-size: 11px;
            color: #9ca3af;
        }

        .sidebar-note {
            margin-top: auto;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 14px;
        }

        .sidebar-note h3 {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .sidebar-note p {
            color: #cbd5e1;
            font-size: 12px;
            line-height: 1.5;
        }

        .content {
            padding: 28px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .page-title h1,
        .page-title h2 {
            font-size: 28px;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .page-title p {
            color: var(--muted);
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn,
        button.btn,
        a.btn {
            border: 1px solid var(--line);
            background: white;
            color: var(--text);
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
        }

        .btn:hover,
        button.btn:hover,
        a.btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn.primary,
        .btn-primary {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .btn-success {
            background: var(--green);
            color: white;
            border-color: var(--green);
        }

        .btn-warning {
            background: var(--orange);
            color: white;
            border-color: var(--orange);
        }

        .btn-danger {
            background: var(--red);
            color: white;
            border-color: var(--red);
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .stats,
        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
            margin-bottom: 18px;
        }

        .main-layout {
            grid-template-columns: 1.6fr 1fr;
            align-items: start;
        }

        .card,
        .panel-card,
        .stat-card,
        .page-card,
        .content-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 12px;
        }

        .label {
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }

        .badge {
            font-size: 12px;
            padding: 6px 9px;
            border-radius: 999px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .blue { background: var(--accent-soft); color: var(--accent); }
        .green { background: var(--green-soft); color: var(--green); }
        .orange { background: var(--orange-soft); color: var(--orange); }
        .red { background: var(--red-soft); color: var(--red); }

        .stat-value {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .stat-sub {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.4;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .section-title h3 {
            font-size: 18px;
        }

        .section-title p,
        .section-link {
            color: var(--muted);
            font-size: 13px;
        }

        .section-link {
            text-decoration: none;
            font-weight: 600;
        }

        .overview-panels,
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .sales-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 18px;
        }

        .pen-list,
        .alert-list,
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .row-item {
            background: var(--panel-2);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .row-item h4 {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .row-item p {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4;
        }

        .mini-stat {
            font-size: 13px;
            color: var(--text);
            font-weight: 600;
            white-space: nowrap;
        }

        .alert-item {
            border-left: 4px solid var(--orange);
            background: #fffaf5;
        }

        .alert-item.red-left {
            border-left-color: var(--red);
            background: #fff7f7;
        }

        .quick-btn {
            padding: 16px;
            border-radius: 16px;
            border: 1px dashed var(--line);
            background: white;
            cursor: pointer;
            text-align: left;
            width: 100%;
        }

        .quick-btn h4 {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .quick-btn p {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .sales-box {
            background: var(--panel-2);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
        }

        .sales-box span {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .sales-box strong {
            font-size: 22px;
        }

        .footer-note {
            margin-top: 18px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px dashed var(--line);
            border-radius: 14px;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
        }

        table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th,
        .data-table thead th {
            background: #f8fafc;
            color: var(--muted);
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        table tbody td,
        .data-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            vertical-align: top;
        }

        table tbody tr:last-child td,
        .data-table tbody tr:last-child td {
            border-bottom: 0;
        }

        table tbody tr:hover,
        .data-table tbody tr:hover {
            background: #fbfdff;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full,
        .form-grid .full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
            padding: 11px 13px;
            border-radius: 12px;
            outline: none;
            transition: 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .flash {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            box-shadow: var(--shadow);
            font-size: 14px;
        }

        .flash.success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .flash.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .empty-state {
            padding: 28px 20px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            border: 1px dashed var(--line);
            border-radius: 16px;
            background: #fff;
        }

        .text-muted {
            color: var(--muted);
        }

        .mb-0 { margin-bottom: 0 !important; }
        .mb-1 { margin-bottom: 8px !important; }
        .mb-2 { margin-bottom: 12px !important; }
        .mb-3 { margin-bottom: 16px !important; }
        .mb-4 { margin-bottom: 20px !important; }
        .mb-5 { margin-bottom: 24px !important; }

        @media (max-width: 1150px) {
            .stats,
            .stats-grid,
            .overview-panels,
            .sales-summary,
            .quick-actions,
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
            }

            .content {
                padding: 18px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats,
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .stats,
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @hasSection('styles')
        <style>
            @yield('styles')
        </style>
    @endif
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <h1>Pigstep</h1>
                <p>Pig Health & Lifecycle Tracking System</p>
            </div>

            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span>Dashboard</span>
                    <small>Overview</small>
                </a>
                <a href="{{ route('pigs.index') }}" class="{{ request()->routeIs('pigs.index') ? 'active' : '' }}">
                    <span>Pigs</span>
                    <small>Ear tags</small>
                </a>
                <a href="{{ route('pigs.create') }}" class="{{ request()->routeIs('pigs.create') ? 'active' : '' }}">
                    <span>Create Pig</span>
                    <small>New record</small>
                </a>
            </nav>

            <div class="sidebar-note">
                <h3>System status</h3>
                <p>Laravel backend is connected. This layout now applies the realUI styling system to the current pages.</p>
            </div>
        </aside>

        <main class="content">
            <header class="topbar">
                <div class="page-title">
                    <h1>@yield('page_title')</h1>
                    <p>@yield('page_subtitle')</p>
                </div>
                <div class="actions">
                    @yield('top_actions')
                </div>
            </header>

            @if (session('success'))
                <div class="flash success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="flash error">
                    <strong>Please fix the following:</strong>
                    <ul style="margin: 8px 0 0 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section>
                @yield('content')
            </section>
        </main>
    </div>

    @hasSection('scripts')
        <script>
            @yield('scripts')
        </script>
    @endif
</body>
</html>