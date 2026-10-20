/* =========================================================
   NOTIFIKASI  —  public/js/notifikasi.js
   Belum ada backend: data di bawah adalah DUMMY dan status
   "dibaca" hanya disimpan di memori (hilang saat refresh).
   Bentuk data (per notifikasi):
   {
     id, kat:'presensi'|'poin'|'pengumuman'|'akun',
     judul, isi, waktu:'YYYY-MM-DDTHH:mm', dibaca:true|false
   }
   Titik sambung backend ditandai "TODO (backend)".
========================================================= */

(function () {
    "use strict";

    const app = document.getElementById("notifApp");
    if (!app) return;

    const $ = (id) => document.getElementById(id);

    /* =========================
       KONFIGURASI (DUMMY)
    ========================= */

    const PER_PAGE = 7;

    // Tanggal "hari ini" untuk data dummy. TODO (backend): ganti dengan waktu server.
    const REF_HARI_INI = "2026-08-31";

    const KAT = {
        semua: { label: "Semua", icon: "#i-bell" },
        presensi: {
            label: "Presensi",
            icon: "#i-clock",
            link: "history",
            linkTeks: "Lihat Riwayat Presensi",
        },
        poin: {
            label: "Poin",
            icon: "#i-star",
            link: "history",
            linkTeks: "Lihat Riwayat Presensi",
        },
        pengumuman: { label: "Pengumuman", icon: "#i-megaphone" },
        akun: {
            label: "Akun & Keamanan",
            icon: "#i-shield",
            link: "profil",
            linkTeks: "Buka Profil Saya",
        },
    };

    const URL_TUJUAN = {
        history: app.dataset.urlHistory,
        profil: app.dataset.urlProfil,
    };

    const data = [
        {
            id: 1,
            kat: "presensi",
            judul: "Presensi Berhasil",
            isi: "Kamu berhasil melakukan presensi pada 31 Agustus 2026 (Senin).",
            waktu: "2026-08-31T09:10",
            dibaca: false,
        },
        {
            id: 2,
            kat: "poin",
            judul: "Poin Bertambah",
            isi: "Kamu mendapatkan +10 poin dari presensi hari ini.",
            waktu: "2026-08-31T09:10",
            dibaca: false,
        },
        {
            id: 3,
            kat: "pengumuman",
            judul: "Pengumuman Baru",
            isi: "Template laporan mingguan sudah diperbarui. Silakan cek di menu Magang.",
            waktu: "2026-08-30T16:32",
            dibaca: false,
        },
        {
            id: 4,
            kat: "akun",
            judul: "Perubahan Password",
            isi: "Password akun kamu telah berhasil diubah pada 26 Agustus 2026 pukul 16.24 WIB.",
            waktu: "2026-08-26T16:24",
            dibaca: true,
        },
        {
            id: 5,
            kat: "presensi",
            judul: "Presensi Terlambat",
            isi: "Kamu melakukan presensi pada 28 Agustus 2026 (Jumat) pukul 12:18 WIB.",
            waktu: "2026-08-28T12:18",
            dibaca: true,
        },
        {
            id: 6,
            kat: "poin",
            judul: "Poin Bertambah",
            isi: "Kamu mendapatkan +10 poin dari tugas magang mingguan.",
            waktu: "2026-08-25T10:05",
            dibaca: true,
        },
        {
            id: 7,
            kat: "pengumuman",
            judul: "Pengumuman Jadwal Meeting",
            isi: "Jadwal meeting internal Let's Play akan dilaksanakan pada 2 September 2026.",
            waktu: "2026-08-24T13:40",
            dibaca: true,
        },
    ];

    /* =========================
       HELPER
    ========================= */

    const esc = (v) =>
        String(v ?? "").replace(
            /[&<>"']/g,
            (c) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;",
                })[c],
        );

    const icon = (id, cls) =>
        '<svg class="notif-ico ' +
        (cls || "") +
        '" aria-hidden="true"><use href="' +
        id +
        '"/></svg>';

    const fmtTgl = new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        timeZone: "UTC",
    });
    const toDate = (iso) => new Date(iso + "T00:00:00Z");

    function kemarinDari(iso) {
        const d = toDate(iso);
        d.setUTCDate(d.getUTCDate() - 1);
        return d.toISOString().slice(0, 10);
    }

    // "09:10 WIB" / "Kemarin, 16:32" / "26 Agustus 2026"
    function waktuSingkat(dt) {
        const [tgl, jam] = dt.split("T");

        if (tgl === REF_HARI_INI) return jam + " WIB";
        if (tgl === kemarinDari(REF_HARI_INI)) return "Kemarin, " + jam;
        return fmtTgl.format(toDate(tgl));
    }

    // "31 Agustus 2026, 09:10 WIB"
    function waktuLengkap(dt) {
        const [tgl, jam] = dt.split("T");
        return fmtTgl.format(toDate(tgl)) + ", " + jam + " WIB";
    }

    const belumDibaca = () => data.filter((n) => !n.dibaca).length;

    /* =========================
       STATE
    ========================= */

    const state = { filter: "semua", sort: "terbaru", query: "", page: 1 };

    const el = {
        list: $("notifList"),
        empty: $("notifEmpty"),
        emptyText: $("notifEmptyText"),
        info: $("notifInfo"),
        pager: $("notifPager"),
        search: $("notifSearch"),

        cats: $("catList"),
        filterDot: $("filterDot"),
        filterDlg: $("filterDialog"),
        detailDlg: $("detailDialog"),
    };

    function daftarTerfilter() {
        const q = state.query.trim().toLowerCase();

        const hasil = data.filter((n) => {
            if (state.filter !== "semua" && n.kat !== state.filter)
                return false;
            if (!q) return true;

            return (
                (n.judul + " " + n.isi + " " + KAT[n.kat].label)
                    .toLowerCase()
                    .indexOf(q) !== -1
            );
        });

        const terbaruDulu = (a, b) =>
            a.waktu < b.waktu ? 1 : a.waktu > b.waktu ? -1 : a.id - b.id;

        if (state.sort === "terlama") {
            hasil.sort((a, b) => -terbaruDulu(a, b));
        } else if (state.sort === "belum") {
            hasil.sort((a, b) =>
                a.dibaca === b.dibaca ? terbaruDulu(a, b) : a.dibaca ? 1 : -1,
            );
        } else {
            hasil.sort(terbaruDulu);
        }

        return hasil;
    }

    /* =========================
       BADGE (sidebar, bottom bar, header)
    ========================= */

    function syncBadge() {
        const jumlah = belumDibaca();

        document.querySelectorAll(".nav-badge, .bell-badge").forEach((b) => {
            b.textContent = jumlah;
            b.style.display = jumlah > 0 ? "" : "none";
        });
    }

    /* =========================
       TOAST
    ========================= */

    let toastTimer = null;

    function toast(pesan) {
        $("toastText").textContent = pesan;

        const t = $("toast");
        t.hidden = false;
        t.style.animation = "none";
        void t.offsetWidth;
        t.style.animation = "";

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            t.hidden = true;
        }, 3000);
    }

    /* =========================
       RENDER: KATEGORI
    ========================= */

    function renderKategori() {
        el.cats.innerHTML = Object.keys(KAT)
            .map((k) => {
                const jumlah =
                    k === "semua"
                        ? data.length
                        : data.filter((n) => n.kat === k).length;

                return (
                    '<li><button type="button" class="notif-cat notif-cat--' +
                    k +
                    (state.filter === k ? " is-active" : "") +
                    '" data-cat="' +
                    k +
                    '"' +
                    (state.filter === k
                        ? ' aria-pressed="true"'
                        : ' aria-pressed="false"') +
                    ">" +
                    '<span class="notif-icon notif-icon--' +
                    k +
                    '">' +
                    icon(KAT[k].icon) +
                    "</span>" +
                    '<span class="notif-cat__label">' +
                    esc(KAT[k].label) +
                    "</span>" +
                    '<span class="notif-cat__count">' +
                    jumlah +
                    "</span>" +
                    icon("#i-chevron-right", "notif-cat__chev") +
                    "</button></li>"
                );
            })
            .join("");

        el.filterDot.hidden = state.filter === "semua";
    }

    /* =========================
       RENDER: DAFTAR + PAGINATION
    ========================= */

    function itemHtml(n) {
        const k = KAT[n.kat];

        return (
            '<li><button type="button" class="notif-item' +
            (n.dibaca ? "" : " is-unread") +
            '" data-id="' +
            n.id +
            '">' +
            '<span class="notif-icon notif-icon--' +
            n.kat +
            '">' +
            icon(k.icon) +
            "</span>" +
            '<span class="notif-item__title">' +
            esc(n.judul) +
            (n.dibaca
                ? ""
                : '<span class="notif-dot" role="img" aria-label="Belum dibaca"></span>') +
            "</span>" +
            '<span class="notif-item__desc">' +
            esc(n.isi) +
            "</span>" +
            '<span class="notif-chip notif-chip--' +
            n.kat +
            '">' +
            esc(k.label) +
            "</span>" +
            '<span class="notif-item__time">' +
            esc(waktuSingkat(n.waktu)) +
            "</span>" +
            icon("#i-chevron-right", "notif-item__chev") +
            "</button></li>"
        );
    }

    function nomorHalaman(sekarang, total) {
        const set = new Set([1, total, sekarang - 1, sekarang, sekarang + 1]);
        const angka = Array.from(set)
            .filter((n) => n >= 1 && n <= total)
            .sort((a, b) => a - b);
        const hasil = [];

        angka.forEach((n, i) => {
            if (i && n - angka[i - 1] > 1) hasil.push("…");
            hasil.push(n);
        });

        return hasil;
    }

    function renderPager(totalHalaman) {
        if (totalHalaman < 1) {
            el.pager.innerHTML = "";
            return;
        }

        const cur = state.page;

        let html =
            '<button type="button" class="notif-page" data-page="' +
            (cur - 1) +
            '" aria-label="Halaman sebelumnya"' +
            (cur === 1 ? " disabled" : "") +
            ">" +
            icon("#i-chevron-left") +
            "</button>";

        nomorHalaman(cur, totalHalaman).forEach((n) => {
            html +=
                n === "…"
                    ? '<span class="notif-page-dots" aria-hidden="true">…</span>'
                    : '<button type="button" class="notif-page' +
                      (n === cur ? " is-current" : "") +
                      '" data-page="' +
                      n +
                      '"' +
                      (n === cur ? ' aria-current="page"' : "") +
                      ">" +
                      n +
                      "</button>";
        });

        html +=
            '<button type="button" class="notif-page" data-page="' +
            (cur + 1) +
            '" aria-label="Halaman berikutnya"' +
            (cur === totalHalaman ? " disabled" : "") +
            ">" +
            icon("#i-chevron-right") +
            "</button>";

        el.pager.innerHTML = html;
    }

    function renderDaftar() {
        const hasil = daftarTerfilter();
        const totalHalaman = Math.ceil(hasil.length / PER_PAGE);

        if (state.page > totalHalaman) state.page = Math.max(1, totalHalaman);

        const mulai = (state.page - 1) * PER_PAGE;
        const tampil = hasil.slice(mulai, mulai + PER_PAGE);

        el.list.innerHTML = tampil.map(itemHtml).join("");
        el.list.hidden = tampil.length === 0;
        el.empty.hidden = tampil.length !== 0;

        el.emptyText.textContent =
            state.query.trim() || state.filter !== "semua"
                ? "Coba ubah kata kunci atau pilih kategori lain."
                : "Belum ada notifikasi untuk ditampilkan.";

        el.info.textContent =
            "Menampilkan " +
            tampil.length +
            " dari " +
            hasil.length +
            " notifikasi";
        renderPager(totalHalaman);
    }

    function renderSemua() {
        renderKategori();
        renderDaftar();
        syncBadge();
    }

    /* =========================
       POP-UP FILTER (layar sempit)
    ========================= */

    const mqSempit = window.matchMedia("(max-width: 1100px)");

    // Isi filter hanya ada satu di DOM: dipindah ke pop-up atau panel kanan.
    function tempatkanFilter() {
        $(
            mqSempit.matches ? "slotFilterSheet" : "slotFilterDesktop",
        ).appendChild($("filterBody"));

        if (!mqSempit.matches && el.filterDlg.open) el.filterDlg.close();
    }

    mqSempit.addEventListener("change", tempatkanFilter);

    $("btnFilter").addEventListener("click", () => el.filterDlg.showModal());

    el.cats.addEventListener("click", (e) => {
        const tombol = e.target.closest("[data-cat]");
        if (!tombol) return;

        state.filter = tombol.dataset.cat;
        state.page = 1;
        renderSemua();

        if (el.filterDlg.open) el.filterDlg.close();
    });

    $("btnMarkAll").addEventListener("click", () => {
        if (belumDibaca() === 0) {
            toast("Tidak ada notifikasi baru");
        } else {
            // TODO (backend): kirim permintaan "tandai semua dibaca" ke server.
            data.forEach((n) => {
                n.dibaca = true;
            });
            renderSemua();
            toast("Semua notifikasi ditandai sudah dibaca");
        }

        if (el.filterDlg.open) el.filterDlg.close();
    });

    /* =========================
       POP-UP DETAIL
    ========================= */

    function bukaDetail(id) {
        const n = data.find((item) => String(item.id) === String(id));
        if (!n) return;

        const k = KAT[n.kat];

        $("detailIcon").className = "notif-icon notif-icon--" + n.kat;
        $("detailIcon").innerHTML = icon(k.icon);
        $("detailTitle").textContent = n.judul;
        $("detailChip").className = "notif-chip notif-chip--" + n.kat;
        $("detailChip").textContent = k.label;
        $("detailTime").textContent = waktuLengkap(n.waktu);
        $("detailText").textContent = n.isi;

        const link = $("detailLink");
        if (k.link && URL_TUJUAN[k.link]) {
            link.href = URL_TUJUAN[k.link];
            link.textContent = k.linkTeks;
            link.hidden = false;
        } else {
            link.hidden = true;
        }

        // TODO (backend): tandai notifikasi ini sudah dibaca di server.
        if (!n.dibaca) {
            n.dibaca = true;
            renderSemua();
        }

        el.detailDlg.showModal();
    }

    $("detailClose").addEventListener("click", () => el.detailDlg.close());

    [el.detailDlg, el.filterDlg].forEach((dlg) => {
        dlg.addEventListener("click", (e) => {
            if (e.target === dlg) dlg.close(); // klik area gelap
        });
    });

    el.list.addEventListener("click", (e) => {
        const tombol = e.target.closest(".notif-item");
        if (tombol) bukaDetail(tombol.dataset.id);
    });

    /* =========================
       PENCARIAN, URUTAN, HALAMAN
    ========================= */

    el.search.addEventListener("input", () => {
        state.query = el.search.value;
        state.page = 1;
        renderDaftar();
    });

    /* DROPDOWN URUTAN (pengganti <select>) */

    const sortUI = {
        wrap: $("sortWrap"),
        btn: $("sortBtn"),
        label: $("sortLabel"),
        menu: $("sortMenu"),
    };

    const itemSort = () =>
        Array.from(sortUI.menu.querySelectorAll("[data-value]"));

    function fokusItem(i) {
        const items = itemSort();
        items[(i + items.length) % items.length].focus();
    }

    function bukaSort() {
        sortUI.menu.hidden = false;
        sortUI.wrap.classList.add("is-open");
        sortUI.btn.setAttribute("aria-expanded", "true");
    }

    function tutupSort() {
        sortUI.menu.hidden = true;
        sortUI.wrap.classList.remove("is-open");
        sortUI.btn.setAttribute("aria-expanded", "false");
    }

    function pilihSort(nilai) {
        state.sort = nilai;
        state.page = 1;

        itemSort().forEach((li) => {
            const aktif = li.dataset.value === nilai;
            li.setAttribute("aria-selected", aktif ? "true" : "false");
            if (aktif)
                sortUI.label.textContent = li.querySelector("span").textContent;
        });

        renderDaftar();
    }

    sortUI.btn.addEventListener("click", () =>
        sortUI.menu.hidden ? bukaSort() : tutupSort(),
    );

    sortUI.btn.addEventListener("keydown", (e) => {
        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
            e.preventDefault();
            bukaSort();
            fokusItem(
                itemSort().findIndex(
                    (li) => li.getAttribute("aria-selected") === "true",
                ),
            );
        }
    });

    sortUI.menu.addEventListener("click", (e) => {
        const li = e.target.closest("[data-value]");
        if (!li) return;

        pilihSort(li.dataset.value);
        tutupSort();
        sortUI.btn.focus();
    });

    sortUI.menu.addEventListener("keydown", (e) => {
        const i = itemSort().indexOf(document.activeElement);

        if (e.key === "ArrowDown") {
            e.preventDefault();
            fokusItem(i + 1);
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            fokusItem(i - 1);
        } else if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            document.activeElement.click();
        } else if (e.key === "Tab") {
            tutupSort();
        }
    });

    // klik di luar / tombol Esc menutup menu
    document.addEventListener("click", (e) => {
        if (!sortUI.wrap.contains(e.target)) tutupSort();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !sortUI.menu.hidden) {
            tutupSort();
            sortUI.btn.focus();
        }
    });

    el.pager.addEventListener("click", (e) => {
        const tombol = e.target.closest("[data-page]");
        if (!tombol || tombol.disabled) return;

        state.page = Number(tombol.dataset.page);
        renderDaftar();
        app.scrollIntoView({ block: "start" });
    });

    /* =========================
       INIT
    ========================= */

    tempatkanFilter();
    renderSemua();
})();
