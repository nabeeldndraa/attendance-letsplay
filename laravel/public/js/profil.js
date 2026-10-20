/* =========================================================
   PROFIL  —  public/js/profil.js
   Belum ada backend: data profil berasal dari atribut
   data-profil di Blade (DUMMY) dan hanya disimpan di memori.
   Titik sambung backend ditandai "TODO (backend)".
========================================================= */

(function () {
    'use strict';

    const app = document.getElementById('profilApp');
    if (!app) return;

    const $ = (id) => document.getElementById(id);
    const pad = (n) => String(n).padStart(2, '0');

    /* =========================
       DATA
    ========================= */

    let profil = JSON.parse(app.dataset.profil);

    const state = { view: 'lihat' };
    const draft = { mulai: profil.mulai, selesai: profil.selesai };

    const form = $('formProfil');
    const formPass = $('formPassword');

    const NAMA_FIELD = ['nama', 'email', 'telepon', 'institusi', 'posisi'];


    /* =========================
       TANGGAL
    ========================= */

    const fmtTgl = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
    const fmtBulanTahun = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' });
    const fmtBulanPendek = new Intl.DateTimeFormat('id-ID', { month: 'short', timeZone: 'UTC' });

    const toDate = (iso) => new Date(iso + 'T00:00:00Z');
    const tgl = (iso) => (iso ? fmtTgl.format(toDate(iso)) : 'Pilih tanggal');
    const isoDari = (y, m, d) => y + '-' + pad(m + 1) + '-' + pad(d); // m = 0..11

    function hariIniIso() {
        const n = new Date();
        return isoDari(n.getFullYear(), n.getMonth(), n.getDate());
    }


    /* =========================
       TAMPILAN DATA
    ========================= */

    function renderRingkas() {
        app.querySelectorAll('[data-bind]').forEach((el) => {
            el.textContent = profil[el.dataset.bind] || '-';
        });

        $('periodeText').textContent = tgl(profil.mulai) + ' - ' + tgl(profil.selesai);
    }

    function renderTanggalDraft() {
        app.querySelectorAll('[data-date-text]').forEach((el) => {
            el.textContent = tgl(draft[el.dataset.dateText]);
        });
    }

    function isiForm() {
        NAMA_FIELD.forEach((k) => {
            form.elements[k].value = profil[k];
        });

        draft.mulai = profil.mulai;
        draft.selesai = profil.selesai;

        renderTanggalDraft();
        bersihkanError();
    }


    /* =========================
       TOAST
    ========================= */

    let toastTimer = null;

    function toast(pesan) {
        $('toastText').textContent = pesan;

        const el = $('toast');
        el.hidden = false;

        // restart animasi
        el.style.animation = 'none';
        void el.offsetWidth;
        el.style.animation = '';

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { el.hidden = true; }, 3200);
    }


    /* =========================
       POP-UP KONFIRMASI
    ========================= */

    const cf = {
        dlg: $('confirmDialog'),
        icon: $('confirmIcon'),
        title: $('confirmTitle'),
        text: $('confirmText'),
        yes: $('confirmYes'),
        no: $('confirmNo'),
    };

    function konfirmasi(opt) {
        return new Promise((resolve) => {
            cf.icon.className = 'prof-confirm__icon prof-confirm__icon--' + (opt.tone || 'danger');
            cf.icon.innerHTML = '<svg class="prof-ico" aria-hidden="true"><use href="' + opt.ikon + '"/></svg>';
            cf.title.textContent = opt.judul;
            cf.text.textContent = opt.teks;
            cf.yes.textContent = opt.ya;
            cf.no.textContent = opt.tidak || 'Batal';

            let hasil = false;

            const klikYa = () => { hasil = true; cf.dlg.close(); };
            const saatTutup = () => {
                cf.yes.removeEventListener('click', klikYa);
                cf.dlg.removeEventListener('close', saatTutup);
                resolve(hasil);
            };

            cf.yes.addEventListener('click', klikYa);
            cf.dlg.addEventListener('close', saatTutup);
            cf.dlg.showModal();
        });
    }

    cf.no.addEventListener('click', () => cf.dlg.close());

    cf.dlg.addEventListener('click', (e) => {
        if (e.target === cf.dlg) cf.dlg.close(); // klik area gelap
    });


    /* =========================
       PINDAH TAMPILAN (lihat / edit / password)
    ========================= */

    const HASH = { lihat: '', edit: '#edit-profil', password: '#ubah-password' };

    const hero = {
        title: app.querySelector('.page-hero__title'),
        sub: app.querySelector('.page-hero__sub'),
        back: app.querySelector('.page-hero__back'),
    };

    const TEKS_HERO = {
        profil: ['Profil Saya', 'Kelola informasi pribadi dan akun kamu di sini'],
        password: ['Akun & Pengaturan', 'Ubah password kamu dan simpan dengan baik'],
    };

    const mqMobile = window.matchMedia('(max-width: 768px)');

    function updateHero() {
        const t = mqMobile.matches && state.view === 'password' ? TEKS_HERO.password : TEKS_HERO.profil;
        hero.title.textContent = t[0];
        hero.sub.textContent = t[1];
    }

    mqMobile.addEventListener('change', updateHero);

    function viewDariHash() {
        if (location.hash === HASH.edit) return 'edit';
        if (location.hash === HASH.password) return 'password';
        return 'lihat';
    }

    function setView(view, awal) {
        state.view = view;
        app.dataset.view = view;

        const edit = view === 'edit';

        NAMA_FIELD.forEach((k) => {
            const input = form.elements[k];
            input.readOnly = !edit;
            input.tabIndex = edit ? 0 : -1;
        });

        if (view !== 'password') resetPassword();
        isiForm();
        updateHero();

        if (!awal) window.scrollTo({ top: 0 });
        if (edit && !awal) form.elements.nama.focus({ preventScroll: true });
    }

    function pindah(view) {
        if (view === state.view) return;

        history.pushState(null, '', location.pathname + location.search + HASH[view]);
        setView(view);
    }

    window.addEventListener('popstate', () => setView(viewDariHash()));


    /* =========================
       EDIT PROFIL
    ========================= */

    const CEK = {
        nama: (v) => (v.trim() ? '' : 'Nama lengkap wajib diisi.'),
        email: (v) => (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? '' : 'Format email belum benar.'),
        telepon: (v) => {
            const angka = v.replace(/\D/g, '');
            return /^[0-9+\-\s]+$/.test(v) && angka.length >= 9 && angka.length <= 15
                ? '' : 'Nomor telepon belum benar (9-15 angka).';
        },
        institusi: (v) => (v.trim() ? '' : 'Institusi wajib diisi.'),
        posisi: (v) => (v.trim() ? '' : 'Posisi wajib diisi.'),
    };

    function tampilError(nama, pesan) {
        const item = form.querySelector('[data-field="' + nama + '"]');
        if (!item) return;

        const p = item.querySelector('.prof-error');
        p.textContent = pesan;
        p.hidden = !pesan;

        item.querySelectorAll('.prof-field').forEach((f) => f.classList.toggle('is-invalid', !!pesan));
    }

    function bersihkanError() {
        form.querySelectorAll('[data-field]').forEach((item) => tampilError(item.dataset.field, ''));
    }

    function validasiProfil() {
        let pertama = null;

        NAMA_FIELD.forEach((k) => {
            const pesan = CEK[k](form.elements[k].value);
            tampilError(k, pesan);
            if (pesan && !pertama) pertama = form.elements[k];
        });

        let pesanTgl = '';
        if (!draft.mulai || !draft.selesai) pesanTgl = 'Tanggal mulai dan selesai wajib diisi.';
        else if (draft.selesai < draft.mulai) pesanTgl = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';

        tampilError('tanggal', pesanTgl);
        if (pesanTgl && !pertama) pertama = form.querySelector('[data-date="selesai"]');

        if (pertama) pertama.focus();
        return !pertama;
    }

    function dirtyProfil() {
        return (
            NAMA_FIELD.some((k) => form.elements[k].value.trim() !== profil[k]) ||
            draft.mulai !== profil.mulai ||
            draft.selesai !== profil.selesai
        );
    }

    async function batalProfil() {
        if (dirtyProfil()) {
            const ya = await konfirmasi({
                tone: 'warn',
                ikon: '#i-info',
                judul: 'Buang perubahan?',
                teks: 'Perubahan yang belum disimpan akan hilang.',
                ya: 'Ya, buang',
                tidak: 'Lanjut edit',
            });

            if (!ya) return;
        }

        pindah('lihat');
    }

    form.addEventListener('input', (e) => {
        const item = e.target.closest('[data-field]');
        if (item) tampilError(item.dataset.field, '');
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (state.view !== 'edit' || !validasiProfil()) return;

        NAMA_FIELD.forEach((k) => {
            profil[k] = form.elements[k].value.trim();
        });

        profil.mulai = draft.mulai;
        profil.selesai = draft.selesai;

        // TODO (backend): kirim `profil` ke endpoint update profil, lalu tampilkan
        // pesan error dari server jika gagal.

        renderRingkas();
        pindah('lihat');
        toast('Profil berhasil diperbarui');
    });

    $('btnEdit').addEventListener('click', () => pindah('edit'));
    $('btnBatalProfil').addEventListener('click', batalProfil);


    /* =========================
       UBAH PASSWORD
    ========================= */

    const pw = {
        baru: $('pwBaru'),
        konfirmasi: $('pwKonfirmasi'),
        errKonfirmasi: $('errKonfirmasi'),
        rules: $('passRules'),
        simpan: $('btnSimpanPassword'),
    };

    const SYARAT = {
        len: (v) => v.length >= 6,
        case: (v) => /[a-z]/.test(v) && /[A-Z]/.test(v),
        num: (v) => /[0-9]/.test(v),
        sym: (v) => /[^A-Za-z0-9\s]/.test(v),
    };

    function cekPassword() {
        const v = pw.baru.value;
        let semua = true;

        pw.rules.querySelectorAll('[data-rule]').forEach((li) => {
            const ok = SYARAT[li.dataset.rule](v);
            li.dataset.ok = ok ? 'true' : 'false';
            li.querySelector('use').setAttribute('href', ok ? '#s-ok' : '#s-x');
            if (!ok) semua = false;
        });

        // semua syarat terpenuhi -> latar jadi hijau lembut
        pw.rules.classList.toggle('is-valid', semua);

        const cocok = v !== '' && pw.konfirmasi.value === v;
        pw.errKonfirmasi.hidden = !(pw.konfirmasi.value && !cocok);
        pw.simpan.disabled = !(semua && cocok);
    }

    function resetPassword() {
        formPass.reset();

        formPass.querySelectorAll('[data-eye]').forEach((btn) => {
            btn.closest('.prof-input').querySelector('input').type = 'password';
            btn.setAttribute('aria-pressed', 'false');
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.querySelector('use').setAttribute('href', '#i-eye');
        });

        cekPassword();
    }

    async function batalPassword() {
        if (pw.baru.value || pw.konfirmasi.value) {
            const ya = await konfirmasi({
                tone: 'warn',
                ikon: '#i-info',
                judul: 'Batalkan ubah password?',
                teks: 'Password yang sudah kamu ketik tidak akan disimpan.',
                ya: 'Ya, batalkan',
                tidak: 'Lanjut',
            });

            if (!ya) return;
        }

        pindah('lihat');
    }

    pw.baru.addEventListener('input', cekPassword);
    pw.konfirmasi.addEventListener('input', cekPassword);

    formPass.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-eye]');
        if (!btn) return;

        const input = btn.closest('.prof-input').querySelector('input');
        const tampil = input.type === 'password';

        input.type = tampil ? 'text' : 'password';
        btn.setAttribute('aria-pressed', tampil ? 'true' : 'false');
        btn.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
        btn.querySelector('use').setAttribute('href', tampil ? '#i-eye-off' : '#i-eye');
    });

    formPass.addEventListener('submit', (e) => {
        e.preventDefault();
        if (pw.simpan.disabled) return;

        // TODO (backend): kirim { password, password_confirmation } ke endpoint
        // ubah password, tangani error dari server (mis. password terlalu lemah).

        pindah('lihat');
        toast('Password berhasil diubah');
    });

    $('btnBatalPassword').addEventListener('click', batalPassword);
    $('rowPassword').addEventListener('click', () => pindah('password'));


    /* =========================
       KELUAR
    ========================= */

    $('rowLogout').addEventListener('click', async () => {
        const ya = await konfirmasi({
            tone: 'danger',
            ikon: '#i-logout',
            judul: 'Keluar dari akun?',
            teks: 'Kamu perlu login kembali untuk melakukan presensi.',
            ya: 'Ya, keluar',
        });

        if (ya) $('logoutForm').submit();
    });


    /* =========================
       TOMBOL KEMBALI (mobile)
       Di dalam form, panah kembali = batal, bukan ke dashboard.
    ========================= */

    hero.back.addEventListener('click', (e) => {
        if (state.view === 'lihat') return;

        e.preventDefault();
        if (state.view === 'edit') batalProfil();
        else batalPassword();
    });


    /* =========================
       KALENDER
    ========================= */

    const cal = {
        dlg: $('dateDialog'),
        field: $('calField'),
        title: $('calTitle'),
        dow: $('calDow'),
        grid: $('calGrid'),
        mode: 'days',   // 'days' | 'months' | 'years'
        target: null,   // 'mulai' | 'selesai'
        y: 0,
        m: 0,
        min: null,
    };

    const NAMA_TARGET = { mulai: 'Mulai Magang', selesai: 'Selesai Magang' };

    function bukaKalender(target) {
        cal.target = target;
        cal.min = target === 'selesai' ? draft.mulai : null;

        const acuan = draft[target] || hariIniIso();
        cal.y = Number(acuan.slice(0, 4));
        cal.m = Number(acuan.slice(5, 7)) - 1;
        cal.mode = 'days';

        cal.field.textContent = NAMA_TARGET[target];
        renderKalender();
        cal.dlg.showModal();
    }

    function renderKalender() {
        const terpilih = draft[cal.target];
        const hariIni = hariIniIso();

        cal.dow.hidden = cal.mode !== 'days';
        cal.grid.className = 'prof-cal__grid' + (cal.mode === 'days' ? '' : ' prof-cal__grid--pick');

        if (cal.mode === 'days') {
            cal.title.textContent = fmtBulanTahun.format(new Date(Date.UTC(cal.y, cal.m, 1)));

            const awal = (new Date(Date.UTC(cal.y, cal.m, 1)).getUTCDay() + 6) % 7; // Senin = 0
            const jumlah = new Date(Date.UTC(cal.y, cal.m + 1, 0)).getUTCDate();

            let html = '<span></span>'.repeat(awal);

            for (let d = 1; d <= jumlah; d++) {
                const iso = isoDari(cal.y, cal.m, d);
                const kelas =
                    'prof-cal__cell' +
                    (iso === terpilih ? ' is-selected' : '') +
                    (iso === hariIni ? ' is-today' : '');
                const mati = cal.min && iso < cal.min ? ' disabled' : '';

                html += '<button type="button" class="' + kelas + '" data-iso="' + iso + '"' + mati + '>' + d + '</button>';
            }

            cal.grid.innerHTML = html;
        } else if (cal.mode === 'months') {
            cal.title.textContent = String(cal.y);

            let html = '';
            for (let m = 0; m < 12; m++) {
                const aktif = terpilih && Number(terpilih.slice(0, 4)) === cal.y && Number(terpilih.slice(5, 7)) - 1 === m;
                html += '<button type="button" class="prof-cal__cell' + (aktif ? ' is-selected' : '') + '" data-month="' + m + '">' +
                    fmtBulanPendek.format(new Date(Date.UTC(cal.y, m, 1))) + '</button>';
            }

            cal.grid.innerHTML = html;
        } else {
            const mulai = Math.floor(cal.y / 12) * 12;
            cal.title.textContent = mulai + ' - ' + (mulai + 11);

            let html = '';
            for (let y = mulai; y < mulai + 12; y++) {
                const aktif = terpilih && Number(terpilih.slice(0, 4)) === y;
                html += '<button type="button" class="prof-cal__cell' + (aktif ? ' is-selected' : '') + '" data-year="' + y + '">' + y + '</button>';
            }

            cal.grid.innerHTML = html;
        }
    }

    function geser(arah) {
        if (cal.mode === 'days') {
            cal.m += arah;
            if (cal.m < 0) { cal.m = 11; cal.y -= 1; }
            if (cal.m > 11) { cal.m = 0; cal.y += 1; }
        } else if (cal.mode === 'months') {
            cal.y += arah;
        } else {
            cal.y += arah * 12;
        }

        renderKalender();
    }

    function pilihTanggal(iso) {
        if (cal.min && iso < cal.min) return;

        draft[cal.target] = iso;

        // mulai digeser melewati selesai -> selesai ikut mengikuti
        if (cal.target === 'mulai' && draft.selesai && draft.selesai < iso) {
            draft.selesai = iso;
        }

        renderTanggalDraft();
        tampilError('tanggal', '');
        cal.dlg.close();
    }

    cal.grid.addEventListener('click', (e) => {
        const tombol = e.target.closest('button');
        if (!tombol) return;

        if (tombol.dataset.iso) {
            pilihTanggal(tombol.dataset.iso);
        } else if (tombol.dataset.month !== undefined) {
            cal.m = Number(tombol.dataset.month);
            cal.mode = 'days';
            renderKalender();
        } else if (tombol.dataset.year !== undefined) {
            cal.y = Number(tombol.dataset.year);
            cal.mode = 'months';
            renderKalender();
        }
    });

    cal.title.addEventListener('click', () => {
        cal.mode = cal.mode === 'days' ? 'months' : cal.mode === 'months' ? 'years' : 'days';
        renderKalender();
    });

    $('calPrev').addEventListener('click', () => geser(-1));
    $('calNext').addEventListener('click', () => geser(1));
    $('calToday').addEventListener('click', () => pilihTanggal(hariIniIso()));

    cal.dlg.addEventListener('click', (e) => {
        if (e.target === cal.dlg || e.target.closest('[data-cal-close]')) cal.dlg.close();
    });

    form.querySelectorAll('[data-date]').forEach((btn) => {
        btn.addEventListener('click', () => bukaKalender(btn.dataset.date));
    });


    /* =========================
       INIT
    ========================= */

    renderRingkas();
    setView(viewDariHash(), true);
})();
