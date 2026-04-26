@extends('layouts.app')

@section('title', 'Farm Settings')
@section('page_title', 'Farm Settings')
@section('page_subtitle', 'Configure global pricing, email reminder settings, and manual farm exports for Pigstep.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
@endsection

@section('content')
    <div class="grid">
        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Manual Reports</h3>
                    <p>Download or manually email a current farm summary export. This does not schedule weekly or month-end reports.</p>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 0;">
                <a href="{{ route('reports.farm-summary.csv') }}" class="btn primary">Download Farm Summary CSV</a>
                <a href="{{ route('reports.farm-summary.pdf') }}" class="btn">Download Farm Summary PDF</a>

                <form method="POST" action="{{ route('reports.farm-summary.email') }}" style="display: inline-flex; margin: 0;">
                    @csrf
                    <button type="submit" class="btn">Send Farm Summary to Email</button>
                </form>
            </div>

            <p class="text-muted" style="margin-top: 12px; font-size: 13px;">
                Manual email sends the current PDF and CSV report to the alert recipient email saved below.
            </p>
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Account / Session</h3>
                    <p>Current logged-in owner account. Logout is placed here to keep daily pages clean.</p>
                </div>
            </div>

            @auth
                <div class="form-grid">
                    <div class="form-group">
                        <label>Signed in as</label>
                        <input type="text" value="{{ auth()->user()->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="{{ auth()->user()->email }}" readonly>
                    </div>
                </div>

                <div class="form-actions">
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        style="display: inline-flex; margin: 0;"
                        onsubmit="return confirm('Are you sure you want to log out of Pigstep?');"
                    >
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                </div>
            @endauth
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Global Price Per Kilo</h3>
                    <p>This value is used to auto-compute pig asset value: latest weight × price per kilo.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.farm.update') }}">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label for="price_per_kg">Price per kg</label>
                        <input
                            id="price_per_kg"
                            name="price_per_kg"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('price_per_kg', $setting->price_per_kg) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="alert_recipient_email">Alert recipient email</label>
                        <input
                            id="alert_recipient_email"
                            name="alert_recipient_email"
                            type="email"
                            value="{{ old('alert_recipient_email', $setting->alert_recipient_email) }}"
                            placeholder="owner@example.com"
                        >
                    </div>

                    <div class="form-group">
                        <label for="server_close_reminder_time">Server close reminder time</label>
                        <input
                            id="server_close_reminder_time"
                            name="server_close_reminder_time"
                            type="time"
                            value="{{ old('server_close_reminder_time', $setting->server_close_reminder_time ? substr((string) $setting->server_close_reminder_time, 0, 5) : '') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="feed_reminder_time">Daily feed reminder time</label>
                        <input
                            id="feed_reminder_time"
                            name="feed_reminder_time"
                            type="time"
                            value="{{ old('feed_reminder_time', $setting->feed_reminder_time ? substr((string) $setting->feed_reminder_time, 0, 5) : '') }}"
                        >
                    </div>

                    <div class="form-group full">
                        <div class="flash" style="margin-bottom: 0;">
                            <strong>Reminder behavior</strong>
                            <ul style="margin: 8px 0 0 18px;">
                                <li>Server ready email is sent daily at 5:00 AM.</li>
                                <li>Server close reminder uses the configured close time and says Pigstep will resume at 5:00 AM.</li>
                                <li>Feed reminder uses the configured daily feed time.</li>
                                <li>Farrowing emails are sent at T-3 and on the due day.</li>
                                <li>Protocol emails are sent at T-3, on the due day/window, and once when an unresolved item becomes overdue.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
