<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Draft Pig')</title>

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
      --shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
      --radius: 18px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, Arial, sans-serif; }

    body { background: var(--bg); color: var(--text); }

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
    }

    .brand {
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(255,255,255,0.06);
    }

    .brand h1 { font-size: 20px; }

    .nav {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .nav a {
      text-decoration: none;
      color: #d1d5db;
      padding: 12px;
      border-radius: 12px;
    }

    .nav a:hover,
    .nav a.active {
      background: rgba(255,255,255,0.08);
      color: #fff;
    }

    .content {
      padding: 28px;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid var(--line);
      text-decoration: none;
    }

    .btn.primary {
      background: var(--accent);
      color: white;
    }

    .card {
      background: var(--panel);
      border: 1px solid var(--line);
      padding: 16px;
      border-radius: var(--radius);
      margin-bottom: 16px;
    }
  </style>
</head>

<body>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="brand">
      <h1>draft-pig</h1>
      <p style="font-size:12px;">Pig Health & Lifecycle Tracking</p>
    </div>

    <nav class="nav">
      <a href="{{ route('dashboard') }}">Dashboard</a>
      <a href="{{ route('pigs.index') }}">Pigs</a>
      <a href="#">Pens</a>
      <a href="#">Supplies</a>
      <a href="#">Sales</a>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="content">

    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h2>@yield('page_title')</h2>
        <p>@yield('page_subtitle')</p>
      </div>

      <div>
        @yield('top_actions')
      </div>
    </div>

    <!-- CONTENT -->
    @yield('content')

  </main>

</div>
</body>
</html>
