@extends('layouts.app')

@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('page_subtitle', 'Things that need attention in the farm.')

@section('top_actions')
    @if (($counts['unread'] ?? 0) > 0)
        <span class="badge red">{{ $counts['unread'] }} unread</span>
    @else
        <span class="badge green">All caught up</span>
    @endif
@endsection

@section('styles')
.notification-stack {
    display: grid;
    gap: 20px;
}

.notification-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.notification-card {
    display: grid;
    gap: 12px;
}

.notification-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
}

.notification-card-title {
    font-size: 17px;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 4px;
}

.notification-meta {
    display: grid;
    gap: 5px;
    font-size: 13px;
    color: var(--muted);
}

.notification-status-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.notification-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 2px;
}

.notification-actions form {
    margin: 0;
}

.notification-history-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.notification-history-actions form {
    margin: 0;
}

.notification-pager {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    flex-wrap: wrap;
}

.notification-pager a,
.notification-pager span {
    border: 1px solid var(--line);
    background: #fff;
    color: var(--text);
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.notification-pager span {
    color: var(--muted);
    background: var(--panel-2);
}

@media (max-width: 980px) {
    .notification-summary {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
    @php
        $activeNotifications = $activeNotifications ?? collect();
        $historyNotifications = $historyNotifications ?? collect();
        $counts = $counts ?? [
            'unread' => 0,
            'active' => 0,
            'history' => 0,
        ];

        $canClearHistory = \Illuminate\Support\Facades\Route::has('notifications.history.clear');
        $dateLabel = fn ($date) => $date ? $date->format('F j, Y') : '—';
        $dateTimeLabel = fn ($date) => $date ? $date->format('F j, Y g:i A') : '—';
    @endphp

    <div class="notification-stack">
        <div class="notification-summary">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Unread</span>
                    <span class="badge red">{{ $counts['unread'] }}</span>
                </div>
                <div class="stat-value">{{ number_format((int) $counts['unread']) }}</div>
                <div class="stat-sub">Unread items that still need attention.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Active</span>
                    <span class="badge orange">{{ $counts['active'] }}</span>
                </div>
                <div class="stat-value">{{ number_format((int) $counts['active']) }}</div>
                <div class="stat-sub">Items still active in Pigstep.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">History</span>
                    <span class="badge blue">{{ $counts['history'] }}</span>
                </div>
                <div class="stat-value">{{ number_format((int) $counts['history']) }}</div>
                <div class="stat-sub">Old items kept for reference.</div>
            </div>
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Active Notifications</h3>
                    <p>These still need checking or action.</p>
                </div>
                <span class="badge orange">{{ $counts['active'] }}</span>
            </div>

            @if ($activeNotifications->isEmpty())
                <div class="empty-state">No active notifications right now. You are caught up.</div>
            @else
                <div class="flash" style="margin-bottom: 16px;">
                    Active notifications come from real farm records. If you only delete history, the same alert can come back until the source problem is fixed or dismissed.
                </div>
                <div class="grid">
                    @foreach ($activeNotifications as $notification)
                        @php
                            $routeUrl = $notification->route_url;
                            $canRead = \Illuminate\Support\Facades\Route::has('notifications.read');
                            $canDismiss = \Illuminate\Support\Facades\Route::has('notifications.dismiss');
                        @endphp

                        <div class="panel-card notification-card">
                            <div class="notification-card-head">
                                <div>
                                    <div class="notification-card-title">{{ $notification->title }}</div>
                                    <div class="notification-meta">
                                        <div><strong>Type:</strong> {{ $notification->type_label }}</div>
                                        <div><strong>Message:</strong> {{ $notification->message }}</div>

                                        @if ($notification->due_date)
                                            <div><strong>Due Date:</strong> {{ $dateLabel($notification->due_date) }}</div>
                                        @endif

                                        <div><strong>Created:</strong> {{ $dateTimeLabel($notification->created_at) }}</div>
                                    </div>
                                </div>

                                <div class="notification-status-row">
                                    <span class="badge {{ $notification->severity_badge_class }}">{{ $notification->severity_label }}</span>

                                    @if ($notification->isUnread())
                                        <span class="badge blue">Unread</span>
                                    @else
                                        <span class="badge green">Read</span>
                                    @endif
                                </div>
                            </div>

                            <div class="notification-actions">
                                @if ($routeUrl)
                                    <a href="{{ $routeUrl }}" class="btn primary">Open Record</a>
                                @endif

                                @if ($notification->isUnread() && $canRead)
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="btn">Mark as Read</button>
                                    </form>
                                @endif

                                @if ($canDismiss)
                                    <form method="POST" action="{{ route('notifications.dismiss', $notification) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">Dismiss</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (method_exists($activeNotifications, 'hasPages') && $activeNotifications->hasPages())
                    <div class="notification-pager">
                        @if ($activeNotifications->previousPageUrl())
                            <a href="{{ $activeNotifications->previousPageUrl() }}">← Previous</a>
                        @else
                            <span>← Previous</span>
                        @endif

                        @if ($activeNotifications->nextPageUrl())
                            <a href="{{ $activeNotifications->nextPageUrl() }}">Next →</a>
                        @else
                            <span>Next →</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Notification History</h3>
                    <p>Dismissed or resolved notifications kept for reference.</p>
                </div>

                <div class="notification-history-actions">
                    <span class="badge blue">{{ $counts['history'] }}</span>

                    @if ($canClearHistory && ($counts['history'] ?? 0) > 0)
                        <form method="POST" action="{{ route('notifications.history.clear') }}" onsubmit="return confirm('Delete old dismissed/resolved history only? Active problems may come back until the source issue is fixed.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete History</button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($historyNotifications->isEmpty())
                <div class="empty-state">No dismissed or resolved notifications yet.</div>
            @else
                <div class="grid">
                    @foreach ($historyNotifications as $notification)
                        @php
                            $routeUrl = $notification->route_url;
                        @endphp

                        <div class="panel-card notification-card">
                            <div class="notification-card-head">
                                <div>
                                    <div class="notification-card-title">{{ $notification->title }}</div>
                                    <div class="notification-meta">
                                        <div><strong>Type:</strong> {{ $notification->type_label }}</div>
                                        <div><strong>Message:</strong> {{ $notification->message }}</div>

                                        @if ($notification->due_date)
                                            <div><strong>Due Date:</strong> {{ $dateLabel($notification->due_date) }}</div>
                                        @endif

                                        <div><strong>Status:</strong> {{ $notification->status_label }}</div>
                                        <div><strong>Updated:</strong> {{ $dateTimeLabel($notification->updated_at) }}</div>
                                    </div>
                                </div>

                                <div class="notification-status-row">
                                    <span class="badge {{ $notification->severity_badge_class }}">{{ $notification->severity_label }}</span>

                                    @if ($notification->resolved_at)
                                        <span class="badge green">Resolved</span>
                                    @elseif ($notification->dismissed_at)
                                        <span class="badge orange">Dismissed</span>
                                    @else
                                        <span class="badge blue">{{ $notification->status_label }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="notification-actions">
                                @if ($routeUrl)
                                    <a href="{{ $routeUrl }}" class="btn">Open Record</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (method_exists($historyNotifications, 'hasPages') && $historyNotifications->hasPages())
                    <div class="notification-pager">
                        @if ($historyNotifications->previousPageUrl())
                            <a href="{{ $historyNotifications->previousPageUrl() }}">← Previous</a>
                        @else
                            <span>← Previous</span>
                        @endif

                        @if ($historyNotifications->nextPageUrl())
                            <a href="{{ $historyNotifications->nextPageUrl() }}">Next →</a>
                        @else
                            <span>Next →</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
