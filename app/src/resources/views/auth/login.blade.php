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
            --panel-soft: #f8fafc;
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
            --sidebar-soft: #172554;
            --shadow: 0 22px 55px rgba(15, 23, 42, 0.10);
            --shadow-soft: 0 14px 32px rgba(15, 23, 42, 0.07);
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
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 30%),
                radial-gradient(circle at top right, rgba(22, 163, 74, 0.08), transparent 26%),
                radial-gradient(circle at bottom left, rgba(15, 23, 42, 0.04), transparent 24%),
                var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        button,
        input {
            font: inherit;
        }

        a {
            color: inherit;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
        }

        .auth-wrap {
            width: min(100%, 1060px);
            display: grid;
            grid-template-columns: minmax(320px, 440px) minmax(320px, 1fr);
            gap: 22px;
            align-items: stretch;
        }

        .auth-card,
        .product-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .auth-header {
            background:
                linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0)),
                linear-gradient(180deg, var(--sidebar), #111827);
            color: #fff;
            padding: 28px;
        }

        .auth-header h1 {
            font-size: 27px;
            letter-spacing: -0.03em;
            margin-bottom: 7px;
        }

        .auth-header p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .auth-body {
            padding: 28px;
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
            background: var(--panel-soft);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px 14px;
        }

        .error-list {
            margin: 8px 0 0 18px;
        }

        .product-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--green));
        }

        .product-kicker {
            color: var(--accent);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .product-card h2 {
            font-size: 30px;
            line-height: 1.12;
            letter-spacing: -0.04em;
            color: var(--text);
            margin-bottom: 12px;
        }

        .product-summary {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
            max-width: 620px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 22px 0;
        }

        .info-card {
            background: var(--panel-soft);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
        }

        .info-card strong {
            display: block;
            color: var(--text);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-card span,
        .info-card a {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
            text-decoration: none;
        }

        .credit-box {
            background:
                linear-gradient(180deg, rgba(37, 99, 235, 0.07), rgba(37, 99, 235, 0.03)),
                #ffffff;
            border: 1px solid #cfe0ff;
            border-radius: 18px;
            padding: 16px;
            margin-top: 8px;
        }

        .credit-box h3 {
            font-size: 15px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .credit-box p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .footer-note {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .footer-note strong {
            color: var(--text);
        }

        @media (max-width: 920px) {
            .auth-shell {
                padding: 20px;
                align-items: start;
            }

            .auth-wrap {
                grid-template-columns: 1fr;
            }

            .product-card {
                order: 2;
            }

            .auth-card {
                order: 1;
            }
        }

        @media (max-width: 560px) {
            .auth-shell {
                padding: 16px;
            }

            .auth-header,
            .auth-body,
            .product-card {
                padding: 22px;
            }

            .product-card h2 {
                font-size: 25px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <div class="auth-wrap">
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

            <section class="product-card" aria-label="Pigstep product information">
                <div>
                    <div class="product-kicker">Kirjane Labs Product</div>
                    <h2>Pig health, lifecycle, breeding, and farm operations tracking.</h2>

                    <p class="product-summary">
                        Pigstep is a private farm management system tailored for client use. It helps track pig records,
                        pen assignments, breeding records, health events, medication and vaccination logs, protocol program
                        reminders, sales, mortality, transfers, notifications, and operational cost exposure in one focused
                        workflow.
                    </p>

                    <div class="info-grid">
                        <div class="info-card">
                            <strong>Prepared for</strong>
                            <span>Timothy Maglente</span>
                        </div>

                        <div class="info-card">
                            <strong>Product by</strong>
                            <span>Kirjane Labs</span>
                        </div>

                        <div class="info-card">
                            <strong>Lead Developer</strong>
                            <span>Kirch Ivan A. Balite</span>
                        </div>

                        <div class="info-card">
                            <strong>Co-Developer</strong>
                            <span>Osiris Kedigadash Palac</span>
                        </div>
                    </div>

                    <div class="credit-box">
                        <h3>Support and contact</h3>
                        <p>
                            For maintenance, technical support, deployment assistance, or system updates, contact
                            Kirch Ivan A. Balite at
                            <a href="mailto:kirchivan123@gmail.com">kirchivan123@gmail.com</a>
                            or 09486328353.
                        </p>
                    </div>
                </div>

                <div class="footer-note">
                    <strong>© {{ date('Y') }} Kirjane Labs. All rights reserved.</strong><br>
                    Pigstep is a custom software product designed for controlled client use. Unauthorized copying,
                    redistribution, resale, or modification outside approved maintenance work is prohibited.
                </div>
            </section>
        </div>
    </main>
</body>
</html>
