/* =========================================================
   HISTORY  —  public/js/history.js
========================================================= */

(function () {
    "use strict";

    const app = document.getElementById("historyApp");
    if (!app) return;

    const $ = (id) => document.getElementById(id);

    const PER_PAGE = 7;

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
        menunggu: {
            label: "Menunggu Konfirmasi",
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

    const pad = (n) => String(n).padStart(2, "0");
    let logs = [];
    let periodeList = [];
    let tanggalMulai = null;
    let tanggalSelesai = null;
    let tanggalHariIni = null;

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

    function daftarPeriode(tanggalAwal, tanggalAkhir) {
        const awal = toDate(tanggalAwal);
        const akhir = toDate(tanggalAkhir);
        const periode = [];
        let tahun = awal.getUTCFullYear();
        let bulan = awal.getUTCMonth();

        while (
            Date.UTC(tahun, bulan, 1) <=
            Date.UTC(akhir.getUTCFullYear(), akhir.getUTCMonth(), 1)
        ) {
            periode.push(tahun + "-" + pad(bulan + 1));
            bulan++;
            if (bulan === 12) {
                bulan = 0;
                tahun++;
            }
        }

        return periode.reverse();
    }

    /* =========================
       STATE
    ========================= */

    const el = {
        search: $("historySearch"),
        rows: $("historyRows"),
        empty: $("historyEmpty"),
        info: $("historyInfo"),
        pager: $("historyPager"),
        dialog: $("detailDialog"),
        body: $("detailBody"),
    };

    el.empty.hidden = false;
    el.empty.textContent = "Memuat riwayat presensi...";

    fetch(app.dataset.historyUrl, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Gagal memuat riwayat presensi.");
            }

            return response.json();
        })
        .then((response) => {
            if (
                !response.success ||
                !response.meta ||
                !Array.isArray(response.data)
            ) {
                throw new Error("Format data riwayat presensi tidak valid.");
            }

            logs = response.data;
            tanggalMulai = response.meta.tanggal_mulai;
            tanggalSelesai = response.meta.tanggal_selesai;
            tanggalHariIni = response.meta.tanggal_hari_ini;
            periodeList = daftarPeriode(tanggalMulai, tanggalSelesai);

            if (periodeList.length === 0) {
                periodeList = [tanggalHariIni.slice(0, 7)];
            }

            const periodeTerakhirHadir = logs.find((log) => log.tercatat);
            const state = {
                periode:
                    periodeTerakhirHadir?.tanggal.slice(0, 7) ??
                    periodeList[0],
                query: "",
                page: 1,
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
        const periodeLogs = logs.filter(
            (l) => l.tanggal.slice(0, 7) === state.periode,
        );
        const hariKerja = periodeLogs.length;
        const hadir = periodeLogs.filter((l) =>
            ["tepat", "terlambat", "menunggu"].includes(l.status),
        ).length;
        const tepat = periodeLogs.filter((l) => l.status === "tepat").length;
        const persen = hariKerja ? Math.round((hadir / hariKerja) * 100) : 0;
        const rasioTepat = hadir ? tepat / hadir : 0;

        $("sumPct").textContent = persen + "%";
        $("sumPctM").textContent = persen + "%";
        $("sumTotal").textContent = hadir;
        $("sumHari").textContent = hariKerja;
        $("sumTepat").textContent = tepat;
        $("sumPredikat").textContent = !hadir
            ? "-"
            : rasioTepat >= 0.85
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
            infoItem("#i-clock", "Time In", esc(l.jam || "-")) +
            infoItem(modeIcon, "Mode", esc(l.mode || "-")) +
            infoItem(
                "#i-star",
                "Point",
                l.poin === null
                    ? "-"
                    : esc((l.poin >= 0 ? "+" : "") + l.poin + " poin"),
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

        if (l.status === "alpha" && !l.tercatat) {
            return (
                atas +
                '<div class="hist-info hist-info--stack">' +
                infoItem(
                    "#i-info",
                    "Keterangan",
                    "Tidak ada presensi tercatat pada hari kerja ini.",
                ) +
                "</div>"
            );
        }

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
    const tahunMin = Math.min(...daftarTahun);
    const tahunMax = Math.max(...daftarTahun);

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
        renderRingkasan();
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
        })
        .catch((error) => {
            console.error(error);
            el.rows.hidden = true;
            el.pager.innerHTML = "";
            el.info.textContent = "";
            el.empty.hidden = false;
            el.empty.textContent =
                "Riwayat presensi gagal dimuat. Silakan muat ulang halaman.";
        });
})();
