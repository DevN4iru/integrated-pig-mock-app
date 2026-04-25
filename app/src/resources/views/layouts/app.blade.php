<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pigstep')</title>

    <style>
        :root {
            --bg: #f3f6fb;
            --bg-soft: #eef3fb;
            --panel: #ffffff;
            --panel-2: #f8fbff;
            --panel-3: #f1f5f9;
            --line: #e2e8f0;
            --line-strong: #d6deea;
            --text: #172033;
            --muted: #64748b;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --orange: #ea580c;
            --orange-soft: #ffedd5;
            --red: #dc2626;
            --red-soft: #fee2e2;
            --slate: #475569;
            --slate-soft: #e2e8f0;
            --sidebar: #0f172a;
            --sidebar-2: #111c34;
            --shadow-sm: 0 8px 20px rgba(15, 23, 42, 0.05);
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --radius: 18px;
            --radius-sm: 14px;
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
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.05), transparent 28%),
                radial-gradient(circle at top right, rgba(22, 163, 74, 0.04), transparent 22%),
                var(--bg);
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

        .layout-app {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar-overlay {
            display: none;
        }

        .sidebar {
            background:
                linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0)),
                var(--sidebar);
            color: #fff;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,0.06);
        }

        .brand {
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }

        .brand h1 {
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: -0.02em;
        }

        .brand p {
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.45;
        }

        .nav-group-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            padding: 0 10px;
            margin-bottom: -6px;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav a {
            color: #d7e0ee;
            padding: 13px 14px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid transparent;
            transition: 0.18s ease;
            font-size: 14px;
            background: transparent;
        }

        .nav a span {
            font-weight: 600;
        }

        .nav a small {
            font-size: 11px;
            color: #8fa0bb;
            white-space: nowrap;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
            border-color: rgba(255,255,255,0.06);
            transform: translateY(-1px);
        }

        .nav a.active {
            background: linear-gradient(180deg, rgba(37,99,235,0.22), rgba(37,99,235,0.12));
            color: #fff;
            border-color: rgba(96,165,250,0.22);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }

        .nav a.active small {
            color: #c6d4ef;
        }

        .sidebar-logout-form {
            margin: 0;
        }

        .sidebar-logout-button {
            width: 100%;
            color: #d7e0ee;
            padding: 13px 14px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid transparent;
            transition: 0.18s ease;
            font-size: 14px;
            background: transparent;
            cursor: pointer;
            text-align: left;
        }

        .sidebar-logout-button span {
            font-weight: 600;
        }

        .sidebar-logout-button small {
            font-size: 11px;
            color: #8fa0bb;
            white-space: nowrap;
        }

        .sidebar-logout-button:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
            border-color: rgba(255,255,255,0.06);
            transform: translateY(-1px);
        }

        .sidebar-note {
            margin-top: auto;
            background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            padding: 16px;
        }

        .sidebar-note h3 {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .sidebar-note p {
            color: #cbd5e1;
            font-size: 12px;
            line-height: 1.55;
        }

        .content {
            min-width: 0;
            padding: 28px;
        }

        .page-shell {
            max-width: 1440px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
            padding: 8px 2px 0;
        }

        .page-title h1,
        .page-title h2 {
            font-size: 31px;
            margin-bottom: 6px;
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .page-title p {
            color: var(--muted);
            font-size: 14px;
            max-width: 760px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
        }

        .topbar-user {
            color: var(--text);
            font-size: 13px;
            font-weight: 800;
            line-height: 1.2;
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            padding: 2px 2px;
            max-width: 220px;
        }

        .topbar-user small {
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .logout-form {
            display: inline-flex;
            margin: 0;
        }

        .btn,
        button.btn,
        a.btn {
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.18s ease;
            box-shadow: 0 1px 0 rgba(255,255,255,0.4);
        }

        .btn:hover,
        button.btn:hover,
        a.btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
            border-color: var(--line-strong);
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
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
            box-shadow: var(--shadow-sm);
            padding: 20px;
        }

        .panel-card {
            position: relative;
            overflow: hidden;
        }

        .panel-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.18), transparent);
            pointer-events: none;
        }

        .stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 12px;
        }

        .label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .blue { background: var(--accent-soft); color: var(--accent); }
        .green { background: var(--green-soft); color: var(--green); }
        .orange { background: var(--orange-soft); color: var(--orange); }
        .red { background: var(--red-soft); color: var(--red); }

        .stat-value {
            font-size: 31px;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 8px;
            line-height: 1.05;
        }

        .stat-sub {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.45;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .section-title h3 {
            font-size: 19px;
            letter-spacing: -0.02em;
            margin-bottom: 3px;
        }

        .section-title p,
        .section-link {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .section-link {
            font-weight: 700;
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
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
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
            font-weight: 700;
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
            transition: 0.18s ease;
        }

        input[readonly],
        textarea[readonly] {
            background: var(--panel-2);
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
            box-shadow: var(--shadow-sm);
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
            padding: 30px 20px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            border: 1px dashed var(--line-strong);
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
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

        @media (max-width: 1200px) {
            .stats,
            .stats-grid,
            .overview-panels,
            .sales-summary,
            .quick-actions,
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Hamburger button (hidden on desktop) */
        .menu-toggle {
            display: none;
            font-size: 26px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text);
        }

        /* Mobile sidebar behavior */
        @media (max-width: 980px) {

            body.menu-open .menu-toggle {
                display: none;
            }
            .layout-app {
                display: block;
            }

            .content {
                padding: 20px;
            }

            .topbar {
                align-items: center;
                flex-wrap: wrap;
            }

            .menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                flex: 0 0 auto;
                border-radius: 12px;
                background: #fff;
                border: 1px solid var(--line);
                box-shadow: var(--shadow-sm);
                line-height: 1;
                z-index: 1001;
            }

            .page-title {
                flex: 1 1 calc(100% - 64px);
                min-width: 0;
            }

            .actions {
                width: 100%;
                justify-content: flex-start;
            }

            .topbar-user {
                align-items: flex-start;
                max-width: 100%;
            }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 280px;
                max-width: 85vw;
                height: 100vh;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                box-shadow: var(--shadow);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .content {
                padding: 16px;
            }

            .stats,
            .stats-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-title h1,
            .page-title h2 {
                font-size: 26px;
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
    @php
        $notificationsEnabled = \Illuminate\Support\Facades\Route::has('notifications.index')
            && class_exists(\App\Models\Notification::class)
            && \Illuminate\Support\Facades\Schema::hasTable('notifications');

        $unreadNotificationCount = $notificationsEnabled
            ? \App\Models\Notification::query()->active()->unread()->count()
            : 0;
    @endphp

    <div class="layout-app">
        <div class="sidebar-overlay" id="sidebarOverlay" hidden></div>
        <aside class="sidebar" id="sidebar" aria-label="Primary navigation">
            <div class="brand">
                <h1>Pigstep</h1>
                <p>Pig Health & Lifecycle Tracking System</p>
            </div>

            <div class="nav-group-label">Navigation</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span>Dashboard</span>
                    <small>Overview</small>
                </a>

                <a href="{{ route('pigs.index') }}" class="{{ request()->routeIs('pigs.*') && ! request()->routeIs('pigs.create') ? 'active' : '' }}">
                    <span>Pigs</span>
                    <small>Ear tags</small>
                </a>

                <a href="{{ route('reproduction-cycles.index') }}" class="{{ request()->routeIs('reproduction-cycles.*') ? 'active' : '' }}">
                    <span>Breeding Records</span>
                    <small>Reproduction</small>
                </a>

                <a href="{{ route('protocol-programs.index') }}" class="{{ request()->routeIs('protocol-programs.*') ? 'active' : '' }}">
                    <span>Protocol Programs</span>
                    <small>Shared rules</small>
                </a>

                <a href="{{ route('pens.index') }}" class="{{ request()->routeIs('pens.*') ? 'active' : '' }}">
                    <span>Pens</span>
                    <small>Housing</small>
                </a>

                <a href="{{ route('pigs.create') }}" class="{{ request()->routeIs('pigs.create') ? 'active' : '' }}">
                    <span>Add Pig</span>
                    <small>New record</small>
                </a>

                <a href="{{ route('settings.farm.edit') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span>Settings</span>
                    <small>Farm config</small>
                </a>

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                        @csrf
                        <button type="submit" class="sidebar-logout-button">
                            <span>Logout</span>
                            <small>Sign out</small>
                        </button>
                    </form>
                @endauth
            </nav>

            <div class="sidebar-note">
                <h3>System status</h3>
                <p>Laravel backend is connected. The breeding module is now designed to track sow reproduction records, upcoming farrowing dates, and breeding-related cost exposure.</p>
            </div>
        </aside>

        <main class="content">
            <div class="page-shell">
                <header class="topbar">
                    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Open navigation menu" aria-controls="sidebar" aria-expanded="false">☰</button>
                    <div class="page-title">
                        <h1>@yield('page_title')</h1>
                        <p>@yield('page_subtitle')</p>
                    </div>

                    <div class="actions">
                        @if ($notificationsEnabled)
                            <a
                                href="{{ route('notifications.index') }}"
                                class="btn {{ request()->routeIs('notifications.*') ? 'primary' : '' }}"
                            >
                                <span>Notifications</span>

                                @if ($unreadNotificationCount > 0)
                                    <span class="badge red">{{ $unreadNotificationCount }}</span>
                                @endif
                            </a>
                        @endif

                        @yield('top_actions')

                        @auth
                            <span class="topbar-user">
                                <span>{{ auth()->user()->name }}</span>
                                <small>{{ auth()->user()->email }}</small>
                            </span>
                        @endauth
                    </div>
                </header>

                @if (session('success'))
                    <div class="flash success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="flash error">
                        {{ session('error') }}
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
            </div>
        </main>
    </div>

    @hasSection('scripts')
        <script>
            @yield('scripts')
        </script>
    @endif

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openMenu() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            overlay.hidden = false;
            menuToggle.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            overlay.hidden = true;
            menuToggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-open');
        }

        function toggleMenu() {
            sidebar.classList.contains('open') ? closeMenu() : openMenu();
            document.body.classList.toggle('menu-open');
        }

        menuToggle?.addEventListener('click', toggleMenu);
        overlay?.addEventListener('click', closeMenu);

        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 980) {
                closeMenu();
            }
        });
    </script>
</body>
</html>
