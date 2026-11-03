/* =========================================================
   NOTIFIKASI ADMIN  —  public/js/admin-notifikasi.js
   Memuat daftar notifikasi dari server, lalu mengurus pencarian,
   urutan, filter kategori, pagination, pop-up detail, tombol
   Setujui/Tolak, dan "Tandai Semua Dibaca".

   Endpoint (lihat data-* di #notifApp):
     GET  data-url-data                  -> { data: [ {...} ] } atau [ {...} ]
     POST data-url-mark-all              -> tandai semua dibaca
     POST {data-url-base}/{id}/dibaca    -> tandai satu dibaca
     POST {data-url-base}/{id}/setujui   -> setujui permintaan
     POST {data-url-base}/{id}/tolak     -> tolak permintaan

   Bentuk satu notifikasi:
     id, kategori (keterlambatan|perizinan|leaderboard|akun),
     judul, deskripsi, waktu (ISO 8601), dibaca (bool),
     perlu_keputusan (bool), status (menunggu|disetujui|ditolak),
     detail (opsional, teks lengkap), link + link_label (opsional)

   Jika endpoint gagal dan APP_DEBUG aktif, data contoh dipakai
   supaya tampilan tetap bisa dicek.
========================================================= */

(function () {
    'use strict';

    var app = document.getElementById('notifApp');
    if (!app) return;

    var $ = function (id) { return document.getElementById(id); };

    var cfg = {
        urlData: app.dataset.urlData,
        urlMarkAll: app.dataset.urlMarkAll,
        urlBase: (app.dataset.urlBase || '').replace(/\/$/, ''),
        csrf: app.dataset.csrf,
        demo: app.dataset.demo === 'true'
    };

    var PER_PAGE = 5;
    var TZ = 'Asia/Jakarta';

    /* ---------- kategori ---------- */

    var ICON_PATHS = {
        keterlambatan: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        perizinan: '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16m-11 5 2 2 4-4"/>',
        leaderboard: '<path d="M8 4h8v5a4 4 0 0 1-8 0V4Z"/><path d="M8 6H5v1a3 3 0 0 0 3 3M16 6h3v1a3 3 0 0 1-3 3M12 13v4M8 20h8"/>',
        akun: '<path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>'
    };

    var CATS = [
        { key: 'semua', label: 'Semua' },
        { key: 'keterlambatan', label: 'Konfirmasi Keterlambatan' },
        { key: 'perizinan', label: 'Perizinan' },
        { key: 'leaderboard', label: 'Leaderboard' },
        { key: 'akun', label: 'Akun & Keamanan' }
    ];

    var CHEV = '<svg class="notif-ico notif-cat__chev" aria-hidden="true"><use href="#i-chevron-right"/></svg>';

    function catLabel(key) {
        for (var i = 0; i < CATS.length; i++) if (CATS[i].key === key) return CATS[i].label;
        return key;
    }

    function iconSvg(key) {
        if (key === 'semua') {
            return '<svg class="notif-ico" aria-hidden="true"><use href="#i-bell"/></svg>';
        }
        return '<svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true">' + (ICON_PATHS[key] || ICON_PATHS.akun) + '</svg>';
    }

    /* ---------- elemen ---------- */

    var elList = $('notifList');
    var elEmpty = $('notifEmpty');
    var elEmptyText = $('notifEmptyText');
    var elInfo = $('notifInfo');
    var elPager = $('notifPager');
    var elSearch = $('notifSearch');
    var elCats = $('catList');
    var elFilterBody = $('filterBody');
    var elFilterDot = $('filterDot');
    var elToast = $('toast');
    var elToastText = $('toastText');

    var sortWrap = $('sortWrap');
    var sortBtn = $('sortBtn');
    var sortMenu = $('sortMenu');
    var sortLabel = $('sortLabel');

    var filterDialog = $('filterDialog');
    var detailDialog = $('detailDialog');
    var elDecide = $('detailDecide');
    var elDetailLink = $('detailLink');

    /* ---------- state ---------- */

    var state = {
        items: [],
        cat: 'semua',
        sort: 'terbaru',
        query: '',
        page: 1,
        loaded: false,
        error: false,
        demo: false,
        openId: null
    };

    /* ---------- util ---------- */

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function dayKey(d) {
        return d.toLocaleDateString('en-CA', { timeZone: TZ });
    }

    function hm(d) {
        return d.toLocaleTimeString('id-ID', { timeZone: TZ, hour: '2-digit', minute: '2-digit', hour12: false })
            .replace('.', ':') + ' WIB';
    }

    function longDate(d) {
        return d.toLocaleDateString('id-ID', { timeZone: TZ, day: 'numeric', month: 'long', year: 'numeric' });
    }

    /* "10:15 WIB" / "Kemarin, 00:00 WIB" / "27 Oktober 2026" */
    function timeLabel(iso) {
        var d = new Date(iso);
        if (isNaN(d)) return '';
        var k = dayKey(d);
        if (k === dayKey(new Date())) return hm(d);
        if (k === dayKey(new Date(Date.now() - 864e5))) return 'Kemarin, ' + hm(d);
        return longDate(d);
    }

    function timeFull(iso) {
        var d = new Date(iso);
        return isNaN(d) ? '' : longDate(d) + ', ' + hm(d);
    }

    function findItem(id) {
        for (var i = 0; i < state.items.length; i++) {
            if (String(state.items[i].id) === String(id)) return state.items[i];
        }
        return null;
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfg.csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body || {})
        }).then(function (r) {
            if (!r.ok) throw new Error(String(r.status));
            return r.json().catch(function () { return {}; });
        });
    }

    var toastTimer;
    function toast(text) {
        elToastText.textContent = text;
        elToast.hidden = true;
        void elToast.offsetWidth;
        elToast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { elToast.hidden = true; }, 2600);
    }

    /* ---------- data ---------- */

    function normalize(rows) {
        return rows.map(function (r) {
            return {
                id: r.id,
                kategori: ICON_PATHS[r.kategori] ? r.kategori : 'akun',
                judul: r.judul || '',
                deskripsi: r.deskripsi || '',
                detail: r.detail || '',
                waktu: r.waktu || r.created_at || '',
                dibaca: !!r.dibaca,
                perlu_keputusan: !!r.perlu_keputusan,
                status: r.status || (r.perlu_keputusan ? 'menunggu' : ''),
                link: r.link || '',
                link_label: r.link_label || 'Lihat Detail'
            };
        });
    }

    function demoData() {
        return normalize([
            { id: 1, kategori: 'keterlambatan', judul: 'Permintaan Konfirmasi Keterlambatan', deskripsi: 'Nabila meminta perubahan status presensi tanggal 3 November 2026', waktu: '2026-11-03T10:15:00+07:00', dibaca: false, perlu_keputusan: true, status: 'menunggu' },
            { id: 2, kategori: 'perizinan', judul: 'Pengajuan Izin Intern', deskripsi: 'Aji menginformasikan izin tidak masuk pada tanggal 4 November 2026', waktu: '2026-11-03T09:30:00+07:00', dibaca: false },
            { id: 3, kategori: 'leaderboard', judul: 'Leaderboard Berhasil Diperbarui', deskripsi: 'Leaderboard periode Oktober 2026 telah diperbarui secara otomatis.', waktu: '2026-11-02T00:00:00+07:00', dibaca: false },
            { id: 4, kategori: 'akun', judul: 'Akun Intern Baru', deskripsi: 'Akun peserta magang atas nama Dinda berhasil dibuat dan terdaftar', waktu: '2026-10-27T13:20:00+07:00', dibaca: false },
            { id: 5, kategori: 'keterlambatan', judul: 'Permintaan Konfirmasi Keterlambatan', deskripsi: 'Raka meminta perubahan status presensi tanggal 31 Oktober 2026', waktu: '2026-10-31T08:40:00+07:00', dibaca: true, perlu_keputusan: true, status: 'disetujui' },
            { id: 6, kategori: 'perizinan', judul: 'Pengajuan Izin Intern', deskripsi: 'Salsa mengajukan izin sakit pada tanggal 30 Oktober 2026', waktu: '2026-10-29T16:05:00+07:00', dibaca: true },
            { id: 7, kategori: 'perizinan', judul: 'Pengajuan Izin Intern', deskripsi: 'Bagas menginformasikan izin keperluan keluarga pada tanggal 28 Oktober 2026', waktu: '2026-10-27T07:50:00+07:00', dibaca: true }
        ]);
    }

    function load() {
        return fetch(cfg.urlData, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) {
                if (!r.ok) throw new Error(String(r.status));
                return r.json();
            })
            .then(function (j) {
                state.items = normalize(Array.isArray(j) ? j : (j.data || []));
            })
            .catch(function () {
                if (cfg.demo) {
                    state.demo = true;
                    state.items = demoData();
                } else {
                    state.error = true;
                    state.items = [];
                }
            })
            .then(function () {
                state.loaded = true;
                render();
            });
    }

    /* ---------- olah daftar ---------- */

    function filtered() {
        var q = state.query.trim().toLowerCase();

        var rows = state.items.filter(function (n) {
            if (state.cat !== 'semua' && n.kategori !== state.cat) return false;
            if (state.sort === 'belum' && n.dibaca) return false;
            if (q) {
                var hay = (n.judul + ' ' + n.deskripsi + ' ' + catLabel(n.kategori)).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            return true;
        });

        rows.sort(function (a, b) {
            var ta = new Date(a.waktu).getTime() || 0;
            var tb = new Date(b.waktu).getTime() || 0;
            return state.sort === 'terlama' ? ta - tb : tb - ta;
        });

        return rows;
    }

    /* ---------- render ---------- */

    function itemHTML(n) {
        var unread = !n.dibaca;
        var hasActions = n.perlu_keputusan;
        var extra = '';

        if (hasActions && n.status === 'menunggu') {
            extra =
                '<div class="adm-notif-actions">' +
                '<button type="button" class="adm-notif-btn adm-notif-btn--ok" data-aksi="setujui" data-id="' + esc(n.id) + '">' +
                '<svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>Setujui</button>' +
                '<button type="button" class="adm-notif-btn adm-notif-btn--no" data-aksi="tolak" data-id="' + esc(n.id) + '">' +
                '<svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Tolak</button>' +
                '</div>';
        } else if (hasActions && (n.status === 'disetujui' || n.status === 'ditolak')) {
            extra = '<span class="adm-notif-status adm-notif-status--' + n.status + '">' +
                (n.status === 'disetujui' ? 'Disetujui' : 'Ditolak') + '</span>';
        }

        return '<li>' +
            '<div class="notif-item' + (unread ? ' is-unread' : '') + (extra ? ' has-actions' : '') + '" ' +
            'role="button" tabindex="0" data-id="' + esc(n.id) + '">' +
            '<span class="notif-icon notif-icon--' + n.kategori + '">' + iconSvg(n.kategori) + '</span>' +
            '<span class="notif-item__title">' + esc(n.judul) + '</span>' +
            '<span class="notif-item__time">' + (unread ? '<i class="notif-dot" aria-label="Belum dibaca"></i>' : '') + esc(timeLabel(n.waktu)) + '</span>' +
            '<span class="notif-item__desc">' + esc(n.deskripsi) + '</span>' +
            '<span class="notif-chip notif-chip--' + n.kategori + '">' + esc(catLabel(n.kategori)) + '</span>' +
            extra +
            '<svg class="notif-ico notif-item__chev" aria-hidden="true"><use href="#i-chevron-right"/></svg>' +
            '</div></li>';
    }

    function renderCats() {
        var counts = { semua: state.items.length };
        state.items.forEach(function (n) { counts[n.kategori] = (counts[n.kategori] || 0) + 1; });

        elCats.innerHTML = CATS.map(function (c) {
            return '<li><button type="button" class="notif-cat notif-cat--' + c.key + (state.cat === c.key ? ' is-active' : '') + '" ' +
                'data-cat="' + c.key + '" aria-pressed="' + (state.cat === c.key) + '">' +
                '<span class="notif-icon notif-icon--' + c.key + '">' + iconSvg(c.key) + '</span>' +
                '<span class="notif-cat__label">' + esc(c.label) + '</span>' +
                '<span class="notif-cat__count">' + (counts[c.key] || 0) + '</span>' +
                CHEV +
                '</button></li>';
        }).join('');

        elFilterDot.hidden = state.cat === 'semua';
    }

    function pageList(total, cur) {
        if (total <= 5) {
            var all = [];
            for (var i = 1; i <= total; i++) all.push(i);
            return all;
        }
        var set = { 1: 1 };
        set[total] = total;
        for (var p = cur - 1; p <= cur + 1; p++) if (p > 1 && p < total) set[p] = p;

        var nums = Object.keys(set).map(Number).sort(function (a, b) { return a - b; });
        var out = [];
        nums.forEach(function (n, idx) {
            if (idx && n - nums[idx - 1] > 1) out.push('...');
            out.push(n);
        });
        return out;
    }

    function renderPager(pages) {
        if (pages <= 1) {
            elPager.innerHTML = '';
            return;
        }

        var html = '<button type="button" class="notif-page" data-page="' + (state.page - 1) + '" aria-label="Halaman sebelumnya"' +
            (state.page === 1 ? ' disabled' : '') + '>' +
            '<svg class="notif-ico adm-notif-flip" aria-hidden="true"><use href="#i-chevron-right"/></svg></button>';

        pageList(pages, state.page).forEach(function (p) {
            if (p === '...') {
                html += '<span class="notif-page-dots" aria-hidden="true">…</span>';
            } else {
                html += '<button type="button" class="notif-page' + (p === state.page ? ' is-current' : '') + '" data-page="' + p + '"' +
                    (p === state.page ? ' aria-current="page"' : '') + ' aria-label="Halaman ' + p + '">' + p + '</button>';
            }
        });

        html += '<button type="button" class="notif-page" data-page="' + (state.page + 1) + '" aria-label="Halaman berikutnya"' +
            (state.page === pages ? ' disabled' : '') + '>' +
            '<svg class="notif-ico" aria-hidden="true"><use href="#i-chevron-right"/></svg></button>';

        elPager.innerHTML = html;
    }

    function render() {
        var rows = filtered();
        var total = rows.length;
        var pages = Math.max(1, Math.ceil(total / PER_PAGE));
        if (state.page > pages) state.page = pages;

        var start = (state.page - 1) * PER_PAGE;
        var slice = rows.slice(start, start + PER_PAGE);

        elList.innerHTML = slice.map(itemHTML).join('');
        elEmpty.hidden = total > 0;

        if (!total) {
            if (!state.loaded) elEmptyText.textContent = 'Notifikasi sedang dimuat...';
            else if (state.error) elEmptyText.textContent = 'Gagal memuat notifikasi. Coba muat ulang halaman.';
            else if (state.query.trim()) elEmptyText.textContent = 'Tidak ada notifikasi yang cocok dengan pencarianmu.';
            else if (state.sort === 'belum') elEmptyText.textContent = 'Semua notifikasi sudah dibaca.';
            else elEmptyText.textContent = 'Belum ada notifikasi untuk kategori ini.';
            elInfo.textContent = '';
        } else {
            elInfo.textContent = 'Menampilkan ' + (start + 1) + '–' + (start + slice.length) + ' dari ' + total + ' notifikasi';
        }

        renderCats();
        renderPager(pages);
    }

    /* ---------- aksi data ---------- */

    function markRead(n) {
        if (!n || n.dibaca) return;
        n.dibaca = true;
        if (!state.demo) post(cfg.urlBase + '/' + encodeURIComponent(n.id) + '/dibaca').catch(function () {});
    }

    function markAll() {
        var any = state.items.some(function (n) { return !n.dibaca; });
        state.items.forEach(function (n) { n.dibaca = true; });
        render();
        if (filterDialog.open) filterDialog.close();

        if (!any) {
            toast('Semua notifikasi sudah dibaca');
            return;
        }
        toast('Semua notifikasi ditandai sudah dibaca');
        if (!state.demo) post(cfg.urlMarkAll).catch(function () {});
    }

    function decide(id, aksi, btns) {
        var n = findItem(id);
        if (!n || n.status !== 'menunggu') return;

        btns.forEach(function (b) { b.disabled = true; });

        var done = function () {
            n.status = aksi === 'setujui' ? 'disetujui' : 'ditolak';
            n.dibaca = true;
            if (detailDialog.open) detailDialog.close();
            render();
            toast(aksi === 'setujui' ? 'Permintaan disetujui' : 'Permintaan ditolak');
        };

        if (state.demo) {
            done();
            return;
        }

        post(cfg.urlBase + '/' + encodeURIComponent(n.id) + '/' + aksi)
            .then(done)
            .catch(function () {
                btns.forEach(function (b) { b.disabled = false; });
                toast('Gagal memproses permintaan, coba lagi');
            });
    }

    /* ---------- pop-up detail ---------- */

    function openDetail(id) {
        var n = findItem(id);
        if (!n) return;

        state.openId = n.id;

        $('detailIcon').className = 'notif-icon notif-icon--' + n.kategori;
        $('detailIcon').innerHTML = iconSvg(n.kategori);
        $('detailTitle').textContent = n.judul;
        $('detailChip').className = 'notif-chip notif-chip--' + n.kategori;
        $('detailChip').textContent = catLabel(n.kategori);
        $('detailTime').textContent = timeFull(n.waktu);
        $('detailText').textContent = n.detail || n.deskripsi;

        var pending = n.perlu_keputusan && n.status === 'menunggu';
        elDecide.hidden = !pending;
        Array.prototype.forEach.call(elDecide.querySelectorAll('button'), function (b) { b.disabled = false; });

        if (n.link) {
            elDetailLink.href = n.link;
            elDetailLink.textContent = n.link_label;
            elDetailLink.hidden = false;
        } else {
            elDetailLink.hidden = true;
        }

        var wasUnread = !n.dibaca;
        markRead(n);
        if (wasUnread) render();

        detailDialog.showModal();
    }

    /* ---------- urutan ---------- */

    function setSortOpen(open) {
        sortMenu.hidden = !open;
        sortWrap.classList.toggle('is-open', open);
        sortBtn.setAttribute('aria-expanded', String(open));
    }

    function chooseSort(li) {
        state.sort = li.getAttribute('data-value');
        sortLabel.textContent = li.querySelector('span').textContent;
        Array.prototype.forEach.call(sortMenu.children, function (o) {
            o.setAttribute('aria-selected', String(o === li));
        });
        setSortOpen(false);
        sortBtn.focus();
        state.page = 1;
        render();
    }

    function focusOption(delta) {
        var opts = Array.prototype.slice.call(sortMenu.children);
        var idx = opts.indexOf(document.activeElement);
        if (idx === -1) idx = opts.findIndex(function (o) { return o.getAttribute('aria-selected') === 'true'; });
        var next = Math.max(0, Math.min(opts.length - 1, idx + delta));
        opts[next].focus();
    }

    sortBtn.addEventListener('click', function () {
        setSortOpen(sortMenu.hidden);
    });

    sortBtn.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            setSortOpen(true);
            var sel = sortMenu.querySelector('[aria-selected="true"]');
            if (sel) sel.focus();
        }
    });

    sortMenu.addEventListener('click', function (e) {
        var li = e.target.closest('li[data-value]');
        if (li) chooseSort(li);
    });

    sortMenu.addEventListener('keydown', function (e) {
        var li = e.target.closest('li[data-value]');
        if (e.key === 'ArrowDown') { e.preventDefault(); focusOption(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); focusOption(-1); }
        else if ((e.key === 'Enter' || e.key === ' ') && li) { e.preventDefault(); chooseSort(li); }
        else if (e.key === 'Escape') { setSortOpen(false); sortBtn.focus(); }
    });

    document.addEventListener('click', function (e) {
        if (!sortMenu.hidden && !sortWrap.contains(e.target)) setSortOpen(false);
    });

    /* ---------- pencarian ---------- */

    var searchTimer;
    elSearch.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            state.query = elSearch.value;
            state.page = 1;
            render();
        }, 150);
    });

    /* ---------- filter: panel kanan (desktop) / pop-up (<=1100px) ---------- */

    var mq = window.matchMedia('(max-width: 1100px)');

    function placeFilter() {
        var target = mq.matches ? $('slotFilterSheet') : $('slotFilterDesktop');
        if (elFilterBody.parentNode !== target) target.appendChild(elFilterBody);
        if (!mq.matches && filterDialog.open) filterDialog.close();
    }

    if (mq.addEventListener) mq.addEventListener('change', placeFilter);
    else mq.addListener(placeFilter);
    placeFilter();

    $('btnFilter').addEventListener('click', function () {
        filterDialog.showModal();
    });

    elCats.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-cat]');
        if (!btn) return;
        state.cat = btn.getAttribute('data-cat');
        state.page = 1;
        render();
        if (filterDialog.open) filterDialog.close();
    });

    $('btnMarkAll').addEventListener('click', markAll);

    /* ---------- daftar: buka detail, setujui/tolak ---------- */

    elList.addEventListener('click', function (e) {
        var act = e.target.closest('[data-aksi]');
        if (act) {
            e.stopPropagation();
            var wrap = act.parentNode;
            decide(act.getAttribute('data-id'), act.getAttribute('data-aksi'),
                Array.prototype.slice.call(wrap.querySelectorAll('button')));
            return;
        }
        var item = e.target.closest('.notif-item');
        if (item) openDetail(item.getAttribute('data-id'));
    });

    elList.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        if (e.target.closest('[data-aksi]')) return;
        var item = e.target.closest('.notif-item');
        if (item && e.target === item) {
            e.preventDefault();
            openDetail(item.getAttribute('data-id'));
        }
    });

    elDecide.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-aksi]');
        if (!btn) return;
        decide(state.openId, btn.getAttribute('data-aksi'),
            Array.prototype.slice.call(elDecide.querySelectorAll('button')));
    });

    /* ---------- pagination ---------- */

    elPager.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-page]');
        if (!btn || btn.disabled) return;
        state.page = Number(btn.getAttribute('data-page'));
        render();
        app.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /* ---------- pop-up: tutup ---------- */

    $('detailClose').addEventListener('click', function () { detailDialog.close(); });

    [filterDialog, detailDialog].forEach(function (dlg) {
        dlg.addEventListener('click', function (e) {
            if (e.target === dlg) dlg.close();
        });
    });

    /* ---------- mulai ---------- */

    render();
    load();
})();
