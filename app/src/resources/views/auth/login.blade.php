<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Pigstep</title>

    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --line: #e2e8f0;
            --line-strong: #d6deea;
            --text: #172033;
            --muted: #64748b;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --red: #dc2626;
            --red-soft: #fee2e2;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --sidebar: #0f172a;
            --shadow: 0 22px 55px rgba(15, 23, 42, 0.10);
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
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(22, 163, 74, 0.08), transparent 24%),
                var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        button,
        input {
            font: inherit;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .auth-card {
            width: min(100%, 440px);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .auth-header {
            background:
                linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0)),
                var(--sidebar);
            color: #fff;
            padding: 26px;
        }

        .auth-header h1 {
            font-size: 25px;
            letter-spacing: -0.03em;
            margin-bottom: 6px;
        }

        .auth-header p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .auth-body {
            padding: 26px;
        }

        .flash {
            margin-bottom: 16px;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            font-size: 14px;
        }

        .flash.success {
            border-color: #bbf7d0;
            background: var(--green-soft);
            color: #166534;
        }

        .flash.error {
            border-color: #fecaca;
            background: var(--red-soft);
            color: #991b1b;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        label {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
            padding: 12px 13px;
            border-radius: 12px;
            outline: none;
            transition: 0.18s ease;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 4px 0 18px;
            color: var(--muted);
            font-size: 14px;
        }

        .btn {
            width: 100%;
            border: 1px solid var(--accent);
            background: var(--accent);
            color: white;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
        }

        .auth-note {
            margin-top: 18px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px 14px;
        }

        .error-list {
            margin: 8px 0 0 18px;
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card" aria-label="Pigstep login">
            <div class="auth-header">
                <h1>Pigstep</h1>
                <p>Owner login required to access farm records.</p>
            </div>

            <div class="auth-body">
                @if (session('success'))
                    <div class="flash success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="flash error">
                        <strong>Login failed.</strong>
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <label class="remember-row">
                        <input type="checkbox" name="remember" value="1">
                        <span>Keep me signed in on this device</span>
                    </label>

                    <button type="submit" class="btn">Log in</button>
                </form>

                <div class="auth-note">
                    Public registration is disabled. Owner access is created through the server console only.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
