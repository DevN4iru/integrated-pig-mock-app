<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $activeNotifications = Notification::query()
            ->active()
            ->orderedForList()
            ->paginate(20, ['*'], 'active_page');

        $historyNotifications = Notification::query()
            ->history()
            ->orderedForList()
            ->paginate(20, ['*'], 'history_page');

        $counts = [
            'unread' => Notification::query()->active()->unread()->count(),
            'active' => Notification::query()->active()->count(),
            'history' => Notification::query()->history()->count(),
        ];

        return view('notifications.index', [
            'activeNotifications' => $activeNotifications,
            'historyNotifications' => $historyNotifications,
            'counts' => $counts,
        ]);
    }

    public function read(Notification $notification): RedirectResponse
    {
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function dismiss(Notification $notification): RedirectResponse
    {
        $notification->dismiss();

        return redirect()->back()->with('success', 'Notification dismissed.');
    }
}
