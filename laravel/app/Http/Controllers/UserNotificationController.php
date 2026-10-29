<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index()
    {
        return view('notifikasi');
    }

    public function data(Request $request)
    {
        $notifications = $request->user()
            ->notifikasiSaya()
            ->latest()
            ->get()
            ->map(fn (UserNotification $notification) => [
                'id' => $notification->getKey(),
                'kat' => $notification->kategori,
                'judul' => $notification->judul,
                'isi' => $notification->isi,
                'waktu' => $notification->created_at
                    ->timezone('Asia/Jakarta')
                    ->toIso8601String(),
                'dibaca' => $notification->dibaca_pada !== null,
                'url' => $notification->url_tujuan,
            ]);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $notifications->where('dibaca', false)->count(),
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()
                ->notifikasiSaya()
                ->whereNull('dibaca_pada')
                ->count(),
        ]);
    }

    public function markAsRead(Request $request, int $id)
    {
        $notification = $request->user()
            ->notifikasiSaya()
            ->whereKey($id)
            ->firstOrFail();

        if ($notification->dibaca_pada === null) {
            $notification->update(['dibaca_pada' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->notifikasiSaya()
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        return response()->json(['success' => true]);
    }
}
