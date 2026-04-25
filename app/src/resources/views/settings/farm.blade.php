@extends('layouts.app')

@section('title', 'Farm Settings')
@section('page_title', 'Farm Settings')
@section('page_subtitle', 'Configure global pricing and email reminder settings for Pigstep.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
@endsection

@section('content')
    <div class="grid">
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
