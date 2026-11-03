{{--
    HALAMAN ADMIN: PENGUMUMAN
    Asumsi data dari controller (tidak mengubah backend):
      $pengumuman : paginator (->total(), ->firstItem(), ->count(), ->currentPage(), ->lastPage(), ->url($n))
      $total      : (opsional) total semua pengumuman, default ->total()
      tiap item   : judul, isi, status ('terbit'/'draft'), created_at
    Request GET: q (cari), urut (terbaru|terlama)
    Route: admin.pengumuman (daftar), admin.pengumuman.store (kirim, POST),
           admin.pengumuman.show (detail, opsional - dicek dengan Route::has)
--}}
@extends('layouts.admin')
@section('title', 'Pengumuman')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-pengumuman.css') }}">
@endpush

@section('content')
    @php
        $totalSemua = $total ?? $pengumuman->total();
        $urut       = request('urut', 'terbaru');
        $adaShow    = \Illuminate\Support\Facades\Route::has('admin.pengumuman.show');
        $adaStore   = \Illuminate\Support\Facades\Route::has('admin.pengumuman.store');
    @endphp

    {{-- ikon khusus halaman ini (hanya dipakai di sini) --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="pgm-send" viewBox="0 0 24 24"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></symbol>
    </svg>

    <x-page-hero title="Pengumuman"
                 subtitle="Kelola dan sampaikan informasi kepada peserta magang."
                 :back="route('admin.dashboard')" />

    @if (session('success'))
        <div class="pgm-alert pgm-alert--ok" role="status">{{ session('success') }}</div>
    @endif

    {{-- ===== Statistik + Cari + Urutkan ===== --}}
    <form method="GET" action="{{ route('admin.pengumuman') }}" class="pgm-toolbar" id="pgmFilter">

        <div class="pgm-stat">
            <span class="pgm-stat-ico" aria-hidden="true">
                <svg class="ico"><use href="#i-megaphone"/></svg>
            </span>
            <div>
                <span class="pgm-stat-label">Total Pengumuman</span>
                <strong class="pgm-stat-num">{{ $totalSemua }}</strong>
            </div>
        </div>

        <label class="pgm-search">
            <svg class="ico" aria-hidden="true"><use href="#i-search"/></svg>
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Cari pengumuman..." aria-label="Cari pengumuman">
        </label>

        <div class="pgm-select">
            <select name="urut" id="pgmUrut" aria-label="Urutkan pengumuman">
                <option value="terbaru" @selected($urut === 'terbaru')>Terbaru</option>
                <option value="terlama" @selected($urut === 'terlama')>Terlama</option>
            </select>
            <svg class="ico" aria-hidden="true"><use href="#i-chevron-down"/></svg>
        </div>

    </form>

    {{-- ===== Tulis Pengumuman ===== --}}
    <section class="pgm-card pgm-write" aria-labelledby="pgmTulis">
        <h2 id="pgmTulis" class="pgm-title">Tulis Pengumuman</h2>

        <form method="POST" action="{{ $adaStore ? route('admin.pengumuman.store') : '#' }}" id="pgmForm">
            @csrf
            <textarea name="isi" id="pgmIsi" rows="3" required
                      class="{{ $errors->has('isi') ? 'is-invalid' : '' }}"
                      placeholder="Masukkan isi pengumuman..."
                      aria-label="Isi pengumuman">{{ old('isi') }}</textarea>

            @error('isi')
                <p class="pgm-error">{{ $message }}</p>
            @enderror

            <div class="pgm-actions">
                <button type="submit" class="pgm-btn" id="pgmKirim">
                    <svg class="ico" aria-hidden="true"><use href="#pgm-send"/></svg>
                    Kirim Pengumuman
                </button>
            </div>
        </form>
    </section>

    {{-- ===== Daftar Pengumuman ===== --}}
    <section aria-labelledby="pgmDaftar">
        <h2 id="pgmDaftar" class="pgm-title pgm-title--list">Daftar Pengumuman</h2>

        <div class="pgm-list">
            @forelse ($pengumuman as $item)
                @php
                    $terbit  = ($item->status ?? 'terbit') === 'terbit';
                    $tanggal = optional($item->created_at)->locale('id')->translatedFormat('d F Y');
                    $judul   = $item->judul ?? \Illuminate\Support\Str::limit($item->isi, 50);
                    $href    = $adaShow ? route('admin.pengumuman.show', $item->getKey()) : null;
                    $tag     = $href ? 'a' : 'div';
                @endphp

                <{{ $tag }} @if ($href) href="{{ $href }}" @endif class="pgm-item">
                    <span class="pgm-item-ico" aria-hidden="true">
                        <svg class="ico"><use href="#i-megaphone"/></svg>
                    </span>

                    <div class="pgm-item-body">
                        <h3>{{ $judul }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($item->isi, 110) }}</p>
                    </div>

                    <span class="pgm-badge {{ $terbit ? 'pgm-badge--terbit' : 'pgm-badge--draft' }}">
                        {{ $terbit ? 'Terbit' : 'Draft' }}
                    </span>

                    <span class="pgm-date">
                        <svg class="ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                        {{ $tanggal }}
                    </span>

                    <svg class="ico pgm-go" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                </{{ $tag }}>
            @empty
                <div class="pgm-empty">
                    <svg class="ico" aria-hidden="true"><use href="#i-megaphone"/></svg>
                    <p>
                        @if (request('q'))
                            Tidak ada pengumuman yang cocok dengan "{{ request('q') }}".
                        @else
                            Belum ada pengumuman. Tulis pengumuman pertama di atas.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- ===== Footer daftar: info + pagination ===== --}}
        @if ($pengumuman->total() > 0)
            <div class="pgm-foot">
                <p class="pgm-info">
                    Menampilkan {{ $pengumuman->count() }} dari {{ $pengumuman->total() }} pengumuman
                </p>

                @if ($pengumuman->lastPage() > 1)
                    @php $qs = request()->except('page'); @endphp
                    <nav class="pgm-pager" aria-label="Halaman pengumuman">
                        @if ($pengumuman->onFirstPage())
                            <span class="pgm-page is-disabled" aria-disabled="true">
                                <svg class="ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                            </span>
                        @else
                            <a class="pgm-page" href="{{ $pengumuman->url($pengumuman->currentPage() - 1) }}" aria-label="Sebelumnya">
                                <svg class="ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                            </a>
                        @endif

                        @for ($i = 1; $i <= $pengumuman->lastPage(); $i++)
                            @if ($i === $pengumuman->currentPage())
                                <span class="pgm-page is-active" aria-current="page">{{ $i }}</span>
                            @else
                                <a class="pgm-page" href="{{ $pengumuman->appends($qs)->url($i) }}">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($pengumuman->hasMorePages())
                            <a class="pgm-page" href="{{ $pengumuman->appends($qs)->url($pengumuman->currentPage() + 1) }}" aria-label="Berikutnya">
                                <svg class="ico" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                            </a>
                        @else
                            <span class="pgm-page is-disabled" aria-disabled="true">
                                <svg class="ico" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                            </span>
                        @endif
                    </nav>
                @endif
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-pengumuman.js') }}"></script>
@endpush
