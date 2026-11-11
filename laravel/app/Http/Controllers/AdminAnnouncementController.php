<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $this->pastikanAdmin($request);

        $keyword = trim((string) $request->query('q', ''));
        $urut = $request->query('urut') === 'terlama' ? 'terlama' : 'terbaru';

        $pengumuman = Announcement::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('judul', 'like', "%{$keyword}%")
                        ->orWhere('isi', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('created_at', $urut === 'terlama' ? 'asc' : 'desc')
            ->orderBy('id', $urut === 'terlama' ? 'asc' : 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.pengumuman', [
            'pengumuman' => $pengumuman,
            'total' => Announcement::count(),
        ]);
    }

    public function store(Request $request)
    {
        $this->pastikanAdmin($request);

        $validated = $request->validate([
            'isi' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $isi = trim($validated['isi']);
            $announcement = Announcement::create([
                'id_admin' => $request->user()->getKey(),
                'judul' => Str::limit(Str::squish($isi), 80),
                'isi' => $isi,
                'status' => 'terbit',
            ]);

            User::query()
                ->where('role', 'user')
                ->where('status_akun', 'aktif')
                ->select('id')
                ->chunkById(500, function ($users) use ($announcement) {
                    $createdAt = now();
                    $notifications = $users->map(fn (User $user) => [
                        'id_user' => $user->getKey(),
                        'kategori' => 'pengumuman',
                        'judul' => $announcement->judul,
                        'isi' => $announcement->isi,
                        'url_tujuan' => null,
                        'dibaca_pada' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ])->all();

                    UserNotification::insert($notifications);
                });
        });

        return redirect()
            ->route('admin.pengumuman')
            ->with('success', 'Pengumuman berhasil diterbitkan dan dikirim ke peserta aktif.');
    }

    private function pastikanAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
