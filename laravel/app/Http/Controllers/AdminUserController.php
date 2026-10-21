<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private function pastikanAdmin(): void
    {
        abort_unless(
            Auth::check() && Auth::user()->role === 'admin',
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );
    }

    public function index(Request $request)
    {
        $this->pastikanAdmin();

        $keyword = $request->input('search');

        $users = User::query()
            ->where('role', 'user')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('asal_instansi', 'like', "%{$keyword}%")
                        ->orWhere('bidang_magang', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.data-user', compact('users', 'keyword'));
    }

    public function create()
    {
        $this->pastikanAdmin();

        return view('admin.tambah-user');
    }

    public function store(Request $request)
    {
        $this->pastikanAdmin();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_instansi' => ['required', 'string', 'max:255'],
            'bidang_magang' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'no_wa' => ['required', 'string', 'max:25'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['role'] = 'user';
        $validated['status_akun'] = 'aktif';

        User::create($validated);

        return redirect()
            ->route('admin.data-user')
            ->with('success', 'Akun peserta berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->pastikanAdmin();

        $user = User::where('role', 'user')->findOrFail($id);

        return view('admin.edit-user', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->pastikanAdmin();

        $user = User::where('role', 'user')->findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_instansi' => ['required', 'string', 'max:255'],
            'bidang_magang' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'no_wa' => ['required', 'string', 'max:25'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.data-user')
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $this->pastikanAdmin();

        $user = User::where('role', 'user')->findOrFail($id);

        $user->status_akun = $user->status_akun === 'aktif'
            ? 'nonaktif'
            : 'aktif';

        $user->save();

        return redirect()
            ->route('admin.data-user')
            ->with(
                'success',
                'Status akun berhasil diubah menjadi ' . $user->status_akun . '.'
            );
    }
}
