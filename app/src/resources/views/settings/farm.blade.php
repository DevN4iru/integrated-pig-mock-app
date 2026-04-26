@extends('layouts.app')

@section('title', 'Farm Settings')
@section('page_title', 'Farm Settings')
@section('page_subtitle', 'Configure pricing, reminders, reports, and account session controls for Pigstep.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
@endsection

@section('styles')
.settings-stack {
    display: grid;
    gap: 18px;
}

.settings-utility-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
    gap: 18px;
    align-items: start;
}

.settings-card-head {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 18px;
}

.settings-card-head h3 {
    font-size: 18px;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

.settings-card-head p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.settings-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 7px 10px;
    background: #f8fbff;
    border: 1px solid #dbe4f0;
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
}

.settings-action-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.settings-action-grid .btn,
.settings-action-grid form,
.settings-action-grid form button {
    width: 100%;
}

.settings-soft-box {
    border: 1px solid #dbe4f0;
    border-radius: 16px;
    background: #f8fbff;
    padding: 14px 16px;
}

.settings-soft-box strong {
    display: block;
    margin-bottom: 8px;
}

.settings-soft-box ul {
    margin: 0 0 0 18px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.55;
}

.settings-account-grid {
    display: grid;
    gap: 12px;
}

.settings-account-line {
    display: grid;
    grid-template-columns: 90px minmax(0, 1fr);
    gap: 12px;
    align-items: center;
    border: 1px solid #dbe4f0;
    background: #f8fbff;
    border-radius: 14px;
    padding: 12px;
}

.settings-account-line span {
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.settings-account-line strong {
    min-width: 0;
    overflow-wrap: anywhere;
}

.settings-save-row {
    border-top: 1px solid #e2e8f0;
    margin-top: 18px;
    padding-top: 18px;
}

@media (max-width: 980px) {
    .settings-utility-grid {
        grid-template-columns: 1fr;
    }

    .settings-action-grid {
        grid-template-columns: 1fr;
    }

    .settings-card-head {
        display: grid;
        grid-template-columns: 1fr;
    }

    .settings-pill {
        width: fit-content;
    }
}

@media (max-width: 640px) {
    .settings-account-line {
        grid-template-columns: 1fr;
        gap: 4px;
    }

    .settings-save-row .btn,
    .settings-stack .btn {
        width: 100%;
    }
}
@endsection

@section('content')
    <div class="settings-stack">
        <div class="panel-card">
            <div class="settings-card-head">
                <div>
                    <h3>Pricing & Reminder Settings</h3>
                    <p>Main farm configuration used for pig value computation, reminder emails, and daily handling messages.</p>
                </div>

                <span class="settings-pill">Editable</span>
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
                        <div class="settings-soft-box">
                            <strong>Reminder behavior</strong>
                            <ul>
                                <li>Server ready email is sent daily at 5:00 AM.</li>
                                <li>Server close reminder uses the configured close time and says Pigstep will resume at 5:00 AM.</li>
                                <li>Feed reminder uses the configured daily feed time.</li>
                                <li>Farrowing emails are sent at T-3 and on the due day.</li>
                                <li>Protocol emails are sent at T-3, on the due day/window, and once when an unresolved item becomes overdue.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="settings-save-row">
                    <button type="submit" class="btn primary">Save Settings</button>
                </div>
            </form>
        </div>

        <div class="settings-utility-grid">
            <div class="panel-card">
                <div class="settings-card-head">
                    <div>
                        <h3>Manual Reports</h3>
                        <p>Download or manually email the current farm summary. This does not schedule weekly or month-end reports.</p>
                    </div>

                    <span class="settings-pill">Manual</span>
                </div>

                <div class="settings-action-grid">
                    <a href="{{ route('reports.farm-summary.csv') }}" class="btn primary">Download CSV</a>
                    <a href="{{ route('reports.farm-summary.pdf') }}" class="btn">Download PDF</a>

                    <form method="POST" action="{{ route('reports.farm-summary.email') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn">Send to Email</button>
                    </form>
                </div>

                <p class="text-muted" style="margin-top: 12px; font-size: 13px;">
                    Email sends the current PDF and CSV report to the alert recipient saved above.
                </p>
            </div>

            <div class="panel-card">
                <div class="settings-card-head">
                    <div>
                        <h3>Account / Session</h3>
                        <p>Current owner account. Logout is kept here so daily pages stay clean.</p>
                    </div>

                    <span class="settings-pill">Owner</span>
                </div>

                @auth
                    <div class="settings-account-grid">
                        <div class="settings-account-line">
                            <span>Name</span>
                            <strong>{{ auth()->user()->name }}</strong>
                        </div>

                        <div class="settings-account-line">
                            <span>Email</span>
                            <strong>{{ auth()->user()->email }}</strong>
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
        </div>
    </div>
@endsection
