<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Services\AdminRequestDecisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $this->pastikanAdmin($request);

        return view('admin.notifikasi');
    }

    public function data(Request $request)
    {
        $this->pastikanAdmin($request);

        $notifications = AdminNotification::query()
            ->where('id_admin', $request->user()->getKey())
            ->latest()
            ->get()
            ->map(fn (AdminNotification $notification) => [
                'id' => $notification->getKey(),
                'kategori' => $notification->kategori,
                'judul' => $notification->judul,
                'deskripsi' => $notification->deskripsi,
                'detail' => $notification->detail,
                'waktu' => $notification->created_at
                    ->timezone('Asia/Jakarta')
                    ->toIso8601String(),
                'dibaca' => $notification->dibaca_pada !== null,
                'perlu_keputusan' => $notification->status !== null,
                'status' => match ($notification->status) {
                    'Diajukan' => 'menunggu',
                    'Disetujui' => 'disetujui',
                    'Ditolak' => 'ditolak',
                    default => '',
                },
            ]);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $notifications->where('dibaca', false)->count(),
        ]);
    }

    public function markAsRead(Request $request, int $id)
    {
        $this->pastikanAdmin($request);

        $notification = AdminNotification::query()
            ->where('id_admin', $request->user()->getKey())
            ->whereKey($id)
            ->firstOrFail();

        if ($notification->dibaca_pada === null) {
            $notification->update(['dibaca_pada' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $this->pastikanAdmin($request);

        AdminNotification::query()
            ->where('id_admin', $request->user()->getKey())
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        return response()->json(['success' => true]);
    }

    public function decide(
        Request $request,
        int $id,
        string $action,
        AdminRequestDecisionService $decisionService
    ) {
        $this->pastikanAdmin($request);
        abort_unless(in_array($action, ['setujui', 'tolak'], true), 404);

        $validated = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ]);
        $decision = $action === 'setujui' ? 'Disetujui' : 'Ditolak';

        $processed = DB::transaction(function () use (
            $request,
            $id,
            $decision,
            $validated,
            $decisionService
        ) {
            $notification = AdminNotification::query()
                ->where('id_admin', $request->user()->getKey())
                ->lockForUpdate()
                ->findOrFail($id);

            abort_unless($notification->status === 'Diajukan', 409);

            $note = $validated['catatan_admin'] ?? null;
            $processed = match ($notification->sumber_tipe) {
                'presensi' => $decisionService->decideAttendance(
                    (int) $notification->sumber_id,
                    $request->user(),
                    $decision,
                    $note
                ),
                'izin' => $decisionService->decideLeave(
                    (int) $notification->sumber_id,
                    $request->user(),
                    $decision,
                    $note
                ),
                default => false,
            };

            abort_unless($processed, 409);

            $notification->status = $decision;
            $notification->dibaca_pada = now();
            $notification->save();

            return true;
        });

        return response()->json([
            'success' => true,
            'status' => $decision,
            'processed' => $processed,
        ]);
    }

    private function pastikanAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
