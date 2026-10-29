/* =========================================================
   DATA PRESENSI (ADMIN)  —  public/js/admin/data-presensi.js
   - Dropdown dan kalender kustom untuk form filter
   - Pop-up filter (bottom sheet) di mobile
   - Pop-up "Lihat Alasan" untuk baris izin / sakit
   Nilai filter dikirim lewat <input type="hidden"> (GET), jadi backend
   menerima parameter yang sama seperti sebelumnya:
   status_presensi, tipe_kerja, izin, tanggal_mulai, tanggal_selesai (Y-m-d).
========================================================= */

(function () {
    "use strict";

    const BULAN = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];
    const SHORT = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];
    const DOW = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];

    const ISO_RE = /^\d{4}-\d{2}-\d{2}$/;
    const pad = (n) => String(n).padStart(2, "0");
    const iso = (y, m, d) => y + "-" + pad(m + 1) + "-" + pad(d); // m = 0..11

    function hariIni() {
        const n = new Date();
        return iso(n.getFullYear(), n.getMonth(), n.getDate());
    }

    function labelTanggal(s) {
        const [y, m, d] = s.split("-").map(Number);
        return d + " " + SHORT[m - 1] + " " + y;
    }

    const kunciScroll = (aktif) => {
        document.body.style.overflow = aktif ? "hidden" : "";
    };

    const ICON_KIRI =
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>';
    const ICON_KANAN =
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>';

    /* ---------- daftar komponen (untuk tutup-di-luar & Esc) ---------- */

    const komponen = []; // dropdown + kalender
    const lapisan = []; // pop-up besar: sheet filter & modal alasan

    function tutupSemua(kecuali) {
        komponen.forEach((k) => {
            if (k !== kecuali) k.tutup();
        });
    }

    // Klik di luar menutup pop-up.
    // composedPath() dicatat saat klik terjadi, jadi tetap benar walau tombol yang
    // diklik sudah terhapus oleh render ulang kalender.
    document.addEventListener("click", (e) => {
        const jalur = e.composedPath();

        komponen.forEach((k) => {
            if (!jalur.includes(k.wrap)) k.tutup();
        });
    });

    // Esc menutup lapisan paling atas: dropdown/kalender dulu, baru modal/sheet.
    document.addEventListener("keydown", (e) => {
        if (e.key !== "Escape") return;

        const terbuka = komponen.filter((k) => k.terbuka());

        if (terbuka.length) {
            terbuka.forEach((k) => {
                k.tutup();
                k.btn.focus();
            });
            return;
        }

        const besar = lapisan.find((l) => l.terbuka());
        if (besar) besar.tutup();
    });

    /* =========================
       DROPDOWN
    ========================= */

    function pasangDropdown(wrap) {
        const btn = wrap.querySelector(".adm-pres-select-btn");
        const list = wrap.querySelector(".adm-pres-select-list");
        const hidden = wrap.querySelector('input[type="hidden"]');
        const teks = wrap.querySelector(".adm-pres-select-text");
        const opsi = () =>
            Array.from(list.querySelectorAll(".adm-pres-option"));

        const api = { wrap, btn, tutup, terbuka: () => !list.hidden };

        function buka() {
            tutupSemua(api);
            list.hidden = false;
            btn.setAttribute("aria-expanded", "true");

            const aktif =
                opsi().find((o) => o.classList.contains("is-selected")) ||
                opsi()[0];
            if (aktif) aktif.focus({ preventScroll: true });
        }

        function tutup() {
            list.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        }

        function pilih(o) {
            hidden.value = o.dataset.value;
            teks.textContent = o.textContent.trim();
            teks.classList.toggle("is-placeholder", o.dataset.value === "");

            opsi().forEach((x) => {
                const aktif = x === o;
                x.classList.toggle("is-selected", aktif);
                x.setAttribute("aria-selected", aktif ? "true" : "false");
            });
        }

        btn.addEventListener("click", () => (list.hidden ? buka() : tutup()));

        btn.addEventListener("keydown", (e) => {
            if ((e.key === "ArrowDown" || e.key === "ArrowUp") && list.hidden) {
                e.preventDefault();
                buka();
            }
        });

        list.addEventListener("click", (e) => {
            const o = e.target.closest(".adm-pres-option");
            if (!o) return;

            pilih(o);
            tutup();
            btn.focus();
        });

        list.addEventListener("keydown", (e) => {
            const items = opsi();
            const i = items.indexOf(document.activeElement);

            if (e.key === "ArrowDown") {
                e.preventDefault();
                items[Math.min(i + 1, items.length - 1)].focus();
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                items[Math.max(i - 1, 0)].focus();
            } else if (e.key === "Home") {
                e.preventDefault();
                items[0].focus();
            } else if (e.key === "End") {
                e.preventDefault();
                items[items.length - 1].focus();
            } else if (e.key === "Tab") {
                tutup();
            }
        });

        komponen.push(api);
    }

    /* =========================
       KALENDER
    ========================= */

    function pasangKalender(wrap) {
        const btn = wrap.querySelector(".adm-pres-select-btn");
        const teks = wrap.querySelector(".adm-pres-select-text");
        const hidden = wrap.querySelector('input[type="hidden"]');
        const pop = wrap.querySelector(".adm-pres-cal");
        const minEl = wrap.dataset.minFrom
            ? document.getElementById(wrap.dataset.minFrom)
            : null;

        let mode = "days"; // 'days' | 'months'
        let y = 0;
        let m = 0;

        const api = { wrap, btn, tutup, terbuka: () => !pop.hidden };

        const nilai = () => (ISO_RE.test(hidden.value) ? hidden.value : "");
        const minimal = () =>
            minEl && ISO_RE.test(minEl.value) ? minEl.value : "";

        function tampil() {
            const v = nilai();
            teks.textContent = v
                ? labelTanggal(v)
                : teks.dataset.placeholder || "Pilih tanggal";
            teks.classList.toggle("is-placeholder", !v);
        }

        function set(v) {
            hidden.value = v;
            tampil();
            hidden.dispatchEvent(new Event("change"));
        }

        // fokus = selector elemen yang difokuskan setelah render
        // (render mengganti seluruh isi, jadi fokus keyboard harus dikembalikan)
        function render(fokus) {
            const terpilih = nilai();
            const mn = minimal();
            const sekarang = hariIni();

            let html =
                '<div class="adm-pres-cal-head">' +
                '<button type="button" class="adm-pres-cal-nav" data-nav="-1" aria-label="Sebelumnya">' +
                ICON_KIRI +
                "</button>" +
                '<button type="button" class="adm-pres-cal-title" data-title aria-label="Pilih bulan dan tahun">' +
                (mode === "days" ? BULAN[m] + " " + y : y) +
                "</button>" +
                '<button type="button" class="adm-pres-cal-nav" data-nav="1" aria-label="Berikutnya">' +
                ICON_KANAN +
                "</button>" +
                "</div>";

            if (mode === "days") {
                const offset = (new Date(y, m, 1).getDay() + 6) % 7; // minggu dimulai Senin
                const jumlah = new Date(y, m + 1, 0).getDate();

                html += '<div class="adm-pres-cal-grid">';
                html += DOW.map(
                    (d) => '<div class="adm-pres-cal-dow">' + d + "</div>",
                ).join("");
                html += "<span></span>".repeat(offset);

                for (let d = 1; d <= jumlah; d++) {
                    const tgl = iso(y, m, d);
                    const kelas =
                        "adm-pres-cal-day" +
                        (tgl === terpilih ? " is-selected" : "") +
                        (tgl === sekarang ? " is-today" : "");
                    const mati = mn && tgl < mn ? " disabled" : "";

                    html +=
                        '<button type="button" class="' +
                        kelas +
                        '" data-hari="' +
                        d +
                        '"' +
                        mati +
                        ">" +
                        d +
                        "</button>";
                }

                html += "</div>";
            } else {
                html +=
                    '<div class="adm-pres-cal-grid adm-pres-cal-grid--pick">';

                for (let i = 0; i < 12; i++) {
                    const aktif =
                        terpilih &&
                        Number(terpilih.slice(0, 4)) === y &&
                        Number(terpilih.slice(5, 7)) - 1 === i;
                    const kini =
                        Number(sekarang.slice(0, 4)) === y &&
                        Number(sekarang.slice(5, 7)) - 1 === i;

                    html +=
                        '<button type="button" class="adm-pres-cal-month' +
                        (aktif ? " is-selected" : "") +
                        (kini ? " is-today" : "") +
                        '" data-bulan="' +
                        i +
                        '">' +
                        SHORT[i] +
                        "</button>";
                }

                html += "</div>";
            }

            // "Hari ini" dimatikan kalau hari ini sebelum tanggal minimal
            const hariIniMati = mn && sekarang < mn ? " disabled" : "";

            html +=
                '<div class="adm-pres-cal-foot">' +
                '<button type="button" class="adm-pres-cal-link" data-aksi="hariini"' +
                hariIniMati +
                ">Hari ini</button>" +
                '<button type="button" class="adm-pres-cal-link" data-aksi="hapus">Hapus</button>' +
                "</div>";

            pop.innerHTML = html;

            if (fokus) {
                const target = pop.querySelector(fokus);
                if (target) target.focus({ preventScroll: true });
            }
        }

        function buka() {
            tutupSemua(api);

            const acuan = nilai() || minimal() || hariIni();
            y = Number(acuan.slice(0, 4));
            m = Number(acuan.slice(5, 7)) - 1;
            mode = "days";

            render(
                ".adm-pres-cal-day.is-selected, .adm-pres-cal-day.is-today, .adm-pres-cal-day:not(:disabled)",
            );
            pop.hidden = false;
            btn.setAttribute("aria-expanded", "true");

            // fokus baru bisa dipasang setelah pop-up tampil
            const target = pop.querySelector(
                ".adm-pres-cal-day.is-selected, .adm-pres-cal-day.is-today, .adm-pres-cal-day:not(:disabled)",
            );
            if (target) target.focus({ preventScroll: true });
        }

        function tutup() {
            pop.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        }

        btn.addEventListener("click", () => (pop.hidden ? buka() : tutup()));

        pop.addEventListener("click", (e) => {
            const b = e.target.closest("button");
            if (!b) return;

            if (b.dataset.nav) {
                const arah = Number(b.dataset.nav);

                if (mode === "days") {
                    m += arah;
                    if (m < 0) {
                        m = 11;
                        y -= 1;
                    }
                    if (m > 11) {
                        m = 0;
                        y += 1;
                    }
                } else {
                    y += arah;
                }

                render('[data-nav="' + arah + '"]');
            } else if (b.hasAttribute("data-title")) {
                mode = mode === "days" ? "months" : "days";
                render("[data-title]");
            } else if (b.dataset.bulan !== undefined) {
                m = Number(b.dataset.bulan);
                mode = "days";
                render("[data-title]");
            } else if (b.dataset.hari) {
                set(iso(y, m, Number(b.dataset.hari)));
                tutup();
                btn.focus();
            } else if (b.dataset.aksi === "hariini") {
                const sekarang = hariIni();
                const mn = minimal();

                if (!mn || sekarang >= mn) {
                    set(sekarang);
                    tutup();
                    btn.focus();
                }
            } else if (b.dataset.aksi === "hapus") {
                set("");
                tutup();
                btn.focus();
            }
        });

        // Tanggal "dari" berubah dan melewati "sampai" -> kosongkan "sampai"
        if (minEl) {
            minEl.addEventListener("change", () => {
                const mn = minimal();
                const sekarang = nilai();

                if (mn && sekarang && sekarang < mn) set("");
            });
        }

        tampil();
        komponen.push(api);
    }

    /* =========================
       POP-UP FILTER (MOBILE)
       Di desktop blok ini hanya baris filter biasa;
       di mobile CSS mengubahnya jadi bottom sheet.
    ========================= */

    function pasangSheet() {
        const sheet = document.getElementById("sheetFilter");
        const backdrop = document.querySelector("[data-sheet-backdrop]");
        const pembuka = document.querySelector("[data-sheet-open]");

        if (!sheet || !backdrop || !pembuka) return;

        const penutup = sheet.querySelector(".adm-pres-sheet-close");
        const mq = window.matchMedia("(max-width: 768px)");

        const api = {
            terbuka: () => sheet.classList.contains("is-open"),
            tutup,
        };

        function buka() {
            tutupSemua();
            sheet.classList.add("is-open");
            backdrop.classList.add("is-open");
            sheet.setAttribute("role", "dialog");
            sheet.setAttribute("aria-modal", "true");
            pembuka.setAttribute("aria-expanded", "true");
            kunciScroll(true);

            if (penutup) penutup.focus({ preventScroll: true });
        }

        function tutup(kembaliFokus) {
            if (!api.terbuka()) return;

            tutupSemua();
            sheet.classList.remove("is-open");
            backdrop.classList.remove("is-open");
            sheet.removeAttribute("role");
            sheet.removeAttribute("aria-modal");
            pembuka.setAttribute("aria-expanded", "false");
            kunciScroll(false);

            if (kembaliFokus !== false) pembuka.focus({ preventScroll: true });
        }

        pembuka.addEventListener("click", () =>
            api.terbuka() ? tutup() : buka(),
        );

        document.querySelectorAll("[data-sheet-close]").forEach((el) => {
            el.addEventListener("click", () => tutup());
        });

        // layar dilebarkan / diputar ke desktop -> sheet otomatis ditutup
        const saatUkuranBerubah = (ev) => {
            if (!ev.matches) tutup(false);
        };

        if (mq.addEventListener)
            mq.addEventListener("change", saatUkuranBerubah);
        else mq.addListener(saatUkuranBerubah);

        lapisan.push(api);
    }

    /* =========================
       POP-UP ALASAN IZIN
       Satu modal dipakai semua baris; isinya diisi dari data-* tombol.
    ========================= */

    function pasangModal() {
        const modal = document.getElementById("modalAlasan");
        if (!modal) return;

        const box = modal.querySelector(".adm-pres-modal-box");
        const cari = (s) => modal.querySelector(s);

        let pemicu = null;

        const api = { terbuka: () => !modal.hidden, tutup };

        function buka(tombol) {
            tutupSemua();

            const d = tombol.dataset;
            const jenis = d.jenis === "Sakit" ? "Sakit" : "Izin";
            const alasan = (d.alasan || "").trim();

            // textContent (bukan innerHTML) supaya isi alasan aman dari HTML
            cari("[data-modal-judul]").textContent = "Alasan " + jenis;
            cari("[data-modal-nama]").textContent = d.nama || "-";
            cari("[data-modal-tanggal]").textContent = d.tanggal || "-";
            cari("[data-modal-alasan]").textContent =
                alasan || "Alasan belum diisi.";

            const badge = cari("[data-modal-jenis]");
            badge.textContent = jenis;
            badge.className =
                "adm-pres-badge adm-pres-badge--" +
                (jenis === "Sakit" ? "sakit" : "izin");

            pemicu = tombol;
            modal.hidden = false;
            kunciScroll(true);
            box.focus({ preventScroll: true });
        }

        function tutup() {
            if (modal.hidden) return;

            modal.hidden = true;
            kunciScroll(false);

            if (pemicu && document.contains(pemicu))
                pemicu.focus({ preventScroll: true });
            pemicu = null;
        }

        document.addEventListener("click", (e) => {
            const tombol = e.target.closest("[data-alasan-btn]");

            if (tombol) {
                e.preventDefault();
                buka(tombol);
            }
        });

        modal.addEventListener("click", (e) => {
            if (e.target.closest("[data-modal-close]")) tutup();
        });

        // fokus keyboard tetap di dalam modal
        modal.addEventListener("keydown", (e) => {
            if (e.key !== "Tab") return;

            const fokusable = Array.from(
                modal.querySelectorAll("button:not([disabled]), [href]"),
            );
            if (!fokusable.length) return;

            const awal = fokusable[0];
            const akhir = fokusable[fokusable.length - 1];
            const aktif = document.activeElement;

            if (e.shiftKey && (aktif === awal || aktif === box)) {
                e.preventDefault();
                akhir.focus();
            } else if (!e.shiftKey && aktif === akhir) {
                e.preventDefault();
                awal.focus();
            }
        });

        lapisan.push(api);
    }

    /* =========================
       INIT
    ========================= */

    document.querySelectorAll("[data-select]").forEach(pasangDropdown);
    document.querySelectorAll("[data-date]").forEach(pasangKalender);
    pasangSheet();
    pasangModal();
})();
