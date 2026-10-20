/* =========================================================
   HISTORY  —  public/js/history.js
   Belum ada backend: data di bawah adalah DUMMY.
   Saat backend siap, ganti fungsi buatDummy() dengan fetch()
   ke endpoint riwayat. Bentuk data (per log):
   {
     id, tanggal:'YYYY-MM-DD', jam:'09.10'|null,
     status:'tepat'|'terlambat'|'alpha'|'sakit'|'izin',
     mode:'WFO'|'WFH'|null, lokasi, poin, progress,
     alasan, keterangan,          // khusus sakit/izin
     lat, lng                     // boleh null
   }
========================================================= */

(function () {
    "use strict";

    const app = document.getElementById("historyApp");
    if (!app) return;

    const $ = (id) => document.getElementById(id);

    /* =========================
       KONFIGURASI (DUMMY)
    ========================= */

    const HARI_INTERNSHIP = 60;
    const PER_PAGE = 7;
    const JUMLAH_DUMMY = 48;

    const STATUS = {
        tepat: {
            label: "Tepat Waktu",
            cls: "ok",
            rowIcon: "#i-clock",
            badgeIcon: "#s-ok",
            title: "Hadir",
            poin: "+10 poin",
            presensi: true,
        },
        terlambat: {
            label: "Terlambat",
            cls: "late",
            rowIcon: "#i-clock",
            badgeIcon: "#s-alert",
            title: "Hadir",
            poin: "+5 poin",
            presensi: true,
        },
        alpha: {
            label: "Alpha",
            cls: "alpha",
            rowIcon: "#i-clock",
            badgeIcon: "#s-x",
            title: "Tidak Hadir",
            poin: "-10 poin",
            presensi: true,
        },
        sakit: {
            label: "Sakit",
            cls: "sick",
            rowIcon: "#s-sick",
            badgeIcon: "#s-sick",
            title: "Tidak Hadir",
            presensi: false,
        },
        izin: {
            label: "Izin",
            cls: "permit",
            rowIcon: "#s-permit",
            badgeIcon: "#s-permit",
            title: "Tidak Hadir",
            presensi: false,
        },
    };

    /* =========================
       DATA DUMMY
    ========================= */

    const OVERRIDE = {
        "2026-08-31": {
            jam: "09.10",
            progress: "Melanjutkan development modul history",
        },
        "2026-08-28": {
            status: "terlambat",
            jam: "12.18",
            progress: "Selesai slicing template admin",
        },
        "2026-08-27": {
            jam: "09.05",
            progress: "Daily standup & perbaikan bug layout",
        },
        "2026-08-26": {
            status: "alpha",
            jam: "17.15",
            progress: "Sakit dengan surat keterangan dokter",
        },
        "2026-08-25": {
            jam: "10.10",
            progress: "Research component design system",
        },
        "2026-08-24": {
            status: "terlambat",
            jam: "14.45",
            progress: "Koordinasi timeline project",
        },
        "2026-08-21": {
            jam: "09.50",
            progress: "Kickoff meeting internal Letsplay",
        },
        "2026-08-19": {
            status: "izin",
            alasan: "Izin",
            keterangan: "Ada keperluan keluarga di luar kota.",
        },
        "2026-08-13": {
            status: "sakit",
            alasan: "Sakit",
            keterangan: "Demam dan flu, istirahat sesuai anjuran dokter.",
        },
        "2026-08-06": {
            mode: "WFH",
            jam: "08.47",
            lokasi: "Kolektif",
            progress: "Melanjutkan development modul history",
            lat: -7.948586156707261,
            lng: 112.64341358036212,
        },
        "2026-07-16": { status: "terlambat" },
        "2026-07-02": { status: "terlambat" },
    };

    const PROGRESS_DUMMY = [
        "Membuat halaman dashboard user",
        "Revisi UI halaman presensi",
        "Slicing desain mobile",
        "Meeting mingguan bersama tim",
        "Memperbaiki bug responsive",
        "Menyusun dokumentasi komponen",
        "Review desain bersama mentor",
        "Integrasi komponen sidebar",
    ];

    const pad = (n) => String(n).padStart(2, "0");

    function buatDummy() {
        const out = [];
        const d = new Date(Date.UTC(2026, 7, 31));

        while (out.length < JUMLAH_DUMMY) {
            const hari = d.getUTCDay();

            if (hari !== 0 && hari !== 6) {
                const i = out.length;
                const iso = d.toISOString().slice(0, 10);
                const o = OVERRIDE[iso] || {};
                const status = o.status || "tepat";
                const presensi = STATUS[status].presensi;

                let jam = null;
                if (presensi) {
                    if (o.jam) {
                        jam = o.jam;
                    } else {
                        const menit =
                            status === "tepat"
                                ? 510 + ((i * 13) % 90) // 08.30 - 09.59
                                : status === "terlambat"
                                  ? 720 + ((i * 17) % 180) // 12.00 - 14.59
                                  : 905 + ((i * 11) % 120); // setelah 15.00
                        jam =
                            pad(Math.floor(menit / 60)) + "." + pad(menit % 60);
                    }
                }

                const mode = presensi
                    ? o.mode || (i % 5 === 3 ? "WFH" : "WFO")
                    : null;

                out.push({
                    id: i + 1,
                    tanggal: iso,
                    jam: jam,
                    status: status,
                    mode: mode,
                    lokasi: presensi
                        ? o.lokasi ||
                          (mode === "WFH" ? "Kolektif" : "MCC Office")
                        : null,
                    poin: presensi ? STATUS[status].poin : null,
                    progress: presensi
                        ? o.progress ||
                          PROGRESS_DUMMY[i % PROGRESS_DUMMY.length]
                        : null,
                    alasan: o.alasan || null,
                    keterangan: o.keterangan || null,
                    lat: presensi
                        ? (o.lat ?? -7.948586 + ((i % 5) - 2) * 0.00002)
                        : null,
                    lng: presensi
                        ? (o.lng ?? 112.643413 + ((i % 5) - 2) * 0.00002)
                        : null,
                });
            }

            d.setUTCDate(d.getUTCDate() - 1);
        }

        return out;
    }

    const logs = buatDummy();

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

    // Ikon garis (#i-*) ikut warna teks; ikon berwarna (#s-*) tidak
    const icon = (id, cls = "") =>
        '<svg class="' +
        (id.indexOf("#i-") === 0 ? "hist-ico" : "hist-cico") +
        " " +
        cls +
        '" aria-hidden="true"><use href="' +
        id +
        '"/></svg>';

    const fmt = (opts) =>
        new Intl.DateTimeFormat(
            "id-ID",
            Object.assign({ timeZone: "UTC" }, opts),
        );
    const toDate = (iso) => new Date(iso + "T00:00:00Z");

    const fmtTgl = fmt({ day: "numeric", month: "long", year: "numeric" });
    const fmtBulanTahun = fmt({ month: "long", year: "numeric" });
    const fmtBulan = fmt({ month: "long" });
    const fmtHari = fmt({ weekday: "long" });

    const tglList = (iso) => fmtTgl.format(toDate(iso));
    const hariNama = (iso) => fmtHari.format(toDate(iso));
    const tglModal = (iso) =>
        pad(toDate(iso).getUTCDate()) +
        " " +
        fmtBulanTahun.format(toDate(iso)) +
        " (" +
        hariNama(iso) +
        ")";

    function labelPeriode(key) {
        const [y, m] = key.split("-").map(Number);
        const akhir = new Date(Date.UTC(y, m, 0)).getUTCDate();
        return (
            "1 - " +
            akhir +
            " " +
            fmtBulan.format(new Date(Date.UTC(y, m - 1, 1))) +
            " " +
            y
        );
    }

    /* =========================
       STATE
    ========================= */

    const periodeList = Array.from(
        new Set(logs.map((l) => l.tanggal.slice(0, 7))),
    )
        .sort()
        .reverse();

    const state = {
        periode: periodeList[0],
        query: "",
        page: 1,
    };

    const el = {
        search: $("historySearch"),
        rows: $("historyRows"),
        empty: $("historyEmpty"),
        info: $("historyInfo"),
        pager: $("historyPager"),
        dialog: $("detailDialog"),
        body: $("detailBody"),
    };

    function daftarTerfilter() {
        const q = state.query.trim().toLowerCase();

        return logs.filter((l) => {
            if (l.tanggal.slice(0, 7) !== state.periode) return false;
            if (!q) return true;

            const teks = [
                tglList(l.tanggal),
                hariNama(l.tanggal),
                l.progress,
                l.keterangan,
            ]
                .join(" ")
                .toLowerCase();

            return teks.indexOf(q) !== -1;
        });
    }

    /* =========================
       RINGKASAN
    ========================= */

    function renderRingkasan() {
        const total = logs.length;
        const tepat = logs.filter((l) => l.status === "tepat").length;
        const persen = Math.min(
            100,
            Math.round((total / HARI_INTERNSHIP) * 100),
        );
        const rasioTepat = total ? tepat / total : 0;

        $("sumPct").textContent = persen + "%";
        $("sumPctM").textContent = persen + "%";
        $("sumTotal").textContent = total;
        $("sumHari").textContent = HARI_INTERNSHIP;
        $("sumTepat").textContent = tepat;
        $("sumPredikat").textContent =
            rasioTepat >= 0.85
                ? "Predikat disiplin tinggi"
                : rasioTepat >= 0.7
                  ? "Predikat disiplin baik"
                  : "Perlu ditingkatkan";

        // diberi jeda supaya animasi donut terlihat
        requestAnimationFrame(() => {
            $("donutBar").style.strokeDasharray = persen + " 100";
        });
    }

    /* =========================
       DAFTAR + PAGINATION
    ========================= */

    function barisHtml(l) {
        const s = STATUS[l.status];

        return (
            '<li><button type="button" class="hist-row" data-id="' +
            l.id +
            '">' +
            '<span class="hist-row__icon hist-row__icon--' +
            s.cls +
            '">' +
            icon(s.rowIcon) +
            "</span>" +
            '<span class="hist-row__date"><strong>' +
            esc(tglList(l.tanggal)) +
            "</strong> <em>(" +
            esc(hariNama(l.tanggal)) +
            ")</em></span>" +
            '<span class="hist-row__time">' +
            (l.jam ? esc(l.jam) + " WIB" : "-") +
            "</span>" +
            '<span class="hist-badge hist-badge--' +
            s.cls +
            '">' +
            icon(s.badgeIcon) +
            s.label +
            "</span>" +
            '<span class="hist-row__progress">' +
            esc(l.progress || l.keterangan || "-") +
            "</span>" +
            icon("#i-chevron-right", "hist-row__chev") +
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
            '<button type="button" class="hist-page hist-page--nav" data-page="' +
            (cur - 1) +
            '" aria-label="Halaman sebelumnya"' +
            (cur === 1 ? " disabled" : "") +
            ">" +
            '<span class="hist-only-desktop">Previous</span>' +
            icon("#i-chevron-left", "hist-only-mobile") +
            "</button>";

        nomorHalaman(cur, totalHalaman).forEach((n) => {
            html +=
                n === "…"
                    ? '<span class="hist-page-dots" aria-hidden="true">…</span>'
                    : '<button type="button" class="hist-page' +
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
            '<button type="button" class="hist-page hist-page--nav" data-page="' +
            (cur + 1) +
            '" aria-label="Halaman berikutnya"' +
            (cur === totalHalaman ? " disabled" : "") +
            ">" +
            '<span class="hist-only-desktop">Next</span>' +
            icon("#i-chevron-right", "hist-only-mobile") +
            "</button>";

        el.pager.innerHTML = html;
    }

    function renderDaftar() {
        const data = daftarTerfilter();
        const totalHalaman = Math.ceil(data.length / PER_PAGE);

        if (state.page > totalHalaman) state.page = Math.max(1, totalHalaman);

        const mulai = (state.page - 1) * PER_PAGE;
        const tampil = data.slice(mulai, mulai + PER_PAGE);

        el.rows.innerHTML = tampil.map(barisHtml).join("");
        el.rows.hidden = tampil.length === 0;
        el.empty.hidden = tampil.length !== 0;

        el.info.innerHTML =
            "Menampilkan " +
            tampil.length +
            " dari " +
            data.length +
            ' <span class="hist-inline-d">log riwayat presensi</span>' +
            '<span class="hist-inline-m">data</span>';

        renderPager(totalHalaman);
    }

    /* =========================
       POP-UP DETAIL
    ========================= */

    function infoItem(iconId, label, nilaiHtml, extra) {
        return (
            '<div class="hist-info__item ' +
            (extra || "") +
            '">' +
            icon(iconId, "hist-info__ico") +
            '<div><span class="hist-info__label">' +
            label +
            "</span>" +
            '<strong class="hist-info__value">' +
            nilaiHtml +
            "</strong></div>" +
            "</div>"
        );
    }

    function srcPeta(lat, lng) {
        const dLng = 0.004;
        const dLat = 0.002;

        return (
            "https://www.openstreetmap.org/export/embed.html?bbox=" +
            (lng - dLng) +
            "%2C" +
            (lat - dLat) +
            "%2C" +
            (lng + dLng) +
            "%2C" +
            (lat + dLat) +
            "&layer=mapnik&marker=" +
            lat +
            "%2C" +
            lng
        );
    }

    function detailHtml(l) {
        const s = STATUS[l.status];

        // ---------- Bagian atas (ikon, judul, tanggal) ----------
        const ikonBesar = s.presensi
            ? '<span class="hist-d-hero__icon hist-d-hero__icon--' +
              s.cls +
              '">' +
              icon("#i-clock") +
              "</span>"
            : '<span class="hist-d-hero__icon">' + icon(s.rowIcon) + "</span>";

        const badge = s.presensi
            ? '<span class="hist-badge hist-badge--' +
              s.cls +
              '">' +
              s.label +
              "</span>"
            : "";

        const atas =
            '<div class="hist-d-hero">' +
            ikonBesar +
            "<div>" +
            '<div class="hist-d-hero__title"><h3>' +
            s.title +
            "</h3>" +
            badge +
            "</div>" +
            "<p>" +
            esc(tglModal(l.tanggal)) +
            "</p>" +
            "</div>" +
            "</div>" +
            '<div class="hist-d-divider"></div>';

        // ---------- Izin / Sakit ----------
        if (!s.presensi) {
            return (
                atas +
                '<div class="hist-info hist-info--stack">' +
                infoItem("#i-info", "Alasan", esc(l.alasan || s.label)) +
                infoItem(
                    "#i-note",
                    "Keterangan",
                    '<span class="hist-note-box">' +
                        esc(l.keterangan || "-") +
                        "</span>",
                ) +
                "</div>"
            );
        }

        // ---------- Hadir / Alpha ----------
        const modeIcon = l.mode === "WFH" ? "#i-home" : "#i-building";

        const info =
            '<div class="hist-info">' +
            infoItem("#i-clock", "Time In", esc(l.jam)) +
            infoItem(modeIcon, "Mode", esc(l.mode || "-")) +
            infoItem(
                "#i-star",
                "Point",
                esc(l.poin || "-"),
                "hist-info__item--wide-m",
            ) +
            infoItem(
                "#i-note",
                "Progress",
                esc(l.progress || "-"),
                "hist-info__item--wide-m",
            ) +
            infoItem(
                "#i-pin",
                "Lokasi",
                esc(l.lokasi || "-"),
                "hist-info__item--wide-m",
            ) +
            "</div>";

        // Koordinat bisa kosong (mis. WFH tanpa GPS) -> blok Accuracy disembunyikan
        const adaKoordinat =
            typeof l.lat === "number" && typeof l.lng === "number";

        const akurasi = adaKoordinat
            ? '<section class="hist-acc">' +
              "<h4>Accuracy</h4>" +
              '<div class="hist-coords">' +
              "<div><span>Latitude</span><strong>" +
              l.lat +
              "</strong></div>" +
              "<div><span>Longitude</span><strong>" +
              l.lng +
              "</strong></div>" +
              "</div>" +
              '<iframe class="hist-map" title="Peta lokasi presensi" loading="lazy" ' +
              'referrerpolicy="no-referrer" src="' +
              srcPeta(l.lat, l.lng) +
              '"></iframe>' +
              "</section>"
            : "";

        return atas + info + akurasi;
    }

    function bukaDetail(id) {
        const l = logs.find((item) => String(item.id) === String(id));
        if (!l) return;

        el.body.innerHTML = detailHtml(l);
        el.dialog.showModal();
    }

    el.dialog.addEventListener("close", () => {
        el.body.innerHTML = ""; // lepas iframe peta
    });

    el.dialog.addEventListener("click", (e) => {
        // klik area gelap di luar panel / tombol tutup
        if (e.target === el.dialog || e.target.closest("[data-close]")) {
            el.dialog.close();
        }
    });

    /* =========================
       PERIODE & PENCARIAN
    ========================= */

    /* ---- Periode: pemilih bulan & tahun ---- */

    const fmtBulanPendek = fmt({ month: "short" });
    const bulanPendek = Array.from({ length: 12 }, (_, m) =>
        fmtBulanPendek.format(new Date(Date.UTC(2026, m, 1))),
    );

    const tersedia = new Set(periodeList);
    const daftarTahun = periodeList.map((k) => Number(k.slice(0, 4)));
    const tahunMin = Math.min.apply(null, daftarTahun);
    const tahunMax = Math.max.apply(null, daftarTahun);

    const mp = {
        wrap: $("periodControl"),
        btn: $("periodBtn"),
        label: $("periodLabel"),
        pop: $("periodPop"),
        year: $("mpYear"),
        grid: $("mpGrid"),
        prev: $("mpPrev"),
        next: $("mpNext"),
        tahun: 0,
    };

    function renderMp() {
        mp.year.textContent = mp.tahun;
        mp.prev.disabled = mp.tahun <= tahunMin;
        mp.next.disabled = mp.tahun >= tahunMax;

        let html = "";

        for (let m = 0; m < 12; m++) {
            const key = mp.tahun + "-" + pad(m + 1);

            html +=
                '<button type="button" class="hist-mp__cell' +
                (key === state.periode ? " is-selected" : "") +
                '"' +
                ' data-key="' +
                key +
                '"' +
                (tersedia.has(key) ? "" : " disabled") +
                ">" +
                esc(bulanPendek[m]) +
                "</button>";
        }

        mp.grid.innerHTML = html;
    }

    function bukaMp() {
        mp.tahun = Number(state.periode.slice(0, 4));
        renderMp();
        mp.pop.hidden = false;
        mp.wrap.classList.add("is-open");
        mp.btn.setAttribute("aria-expanded", "true");
    }

    function tutupMp() {
        mp.pop.hidden = true;
        mp.wrap.classList.remove("is-open");
        mp.btn.setAttribute("aria-expanded", "false");
    }

    function setPeriode(key) {
        state.periode = key;
        state.page = 1;
        mp.label.textContent = labelPeriode(key);
        renderDaftar();
    }

    mp.label.textContent = labelPeriode(state.periode);

    mp.btn.addEventListener("click", () =>
        mp.pop.hidden ? bukaMp() : tutupMp(),
    );

    mp.prev.addEventListener("click", () => {
        mp.tahun -= 1;
        renderMp();
    });
    mp.next.addEventListener("click", () => {
        mp.tahun += 1;
        renderMp();
    });

    mp.grid.addEventListener("click", (e) => {
        const tombol = e.target.closest("[data-key]");
        if (!tombol || tombol.disabled) return;

        setPeriode(tombol.dataset.key);
        tutupMp();
        mp.btn.focus();
    });

    // klik di luar / tombol Esc menutup pemilih
    document.addEventListener("click", (e) => {
        if (!mp.wrap.contains(e.target)) tutupMp();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !mp.pop.hidden) {
            tutupMp();
            mp.btn.focus();
        }
    });

    el.search.addEventListener("input", () => {
        state.query = el.search.value;
        state.page = 1;
        renderDaftar();
    });

    el.rows.addEventListener("click", (e) => {
        const tombol = e.target.closest(".hist-row");
        if (tombol) bukaDetail(tombol.dataset.id);
    });

    el.pager.addEventListener("click", (e) => {
        const tombol = e.target.closest("[data-page]");
        if (!tombol || tombol.disabled) return;

        state.page = Number(tombol.dataset.page);
        renderDaftar();
    });

    /* =========================
       PINDAH KONTROL (desktop <-> mobile)
       Periode & pencarian hanya ada satu di DOM, posisinya
       dipindah sesuai lebar layar.
    ========================= */

    const mqMobile = window.matchMedia("(max-width: 768px)");

    function tempatkanKontrol() {
        const mobile = mqMobile.matches;

        $(mobile ? "slotPeriodMobile" : "slotPeriodDesktop").appendChild(
            $("periodControl"),
        );
        $(mobile ? "slotSearchMobile" : "slotSearchDesktop").appendChild(
            $("searchControl"),
        );
    }

    mqMobile.addEventListener("change", tempatkanKontrol);
    tempatkanKontrol();

    /* =========================
       INIT
    ========================= */

    renderRingkasan();
    renderDaftar();
})();
