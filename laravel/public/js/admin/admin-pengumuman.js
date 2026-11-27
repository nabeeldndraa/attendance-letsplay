/* ADMIN: PENGUMUMAN */
(function () {
    "use strict";

    var filter = document.getElementById("pgmFilter");
    var urut = document.getElementById("pgmUrut");
    var wrap = document.getElementById("pgmListWrap");
    var cari = filter ? filter.querySelector('input[name="q"]') : null;

    /* =====================================================
       1. GANTI DAFTAR TANPA MEMUAT ULANG HALAMAN
       Hanya isi #pgmListWrap yang diganti. Kalau fetch gagal,
       otomatis pindah halaman seperti biasa (fallback).
       ===================================================== */
    var permintaanKe = 0;

    function bangunUrl() {
        var p = new URLSearchParams();
        if (cari && cari.value.trim() !== "") p.set("q", cari.value.trim());
        if (urut) p.set("urut", urut.value);
        return filter.getAttribute("action") + "?" + p.toString();
    }

    function muat(url, dorongRiwayat, gulir) {
        if (!wrap) {
            window.location.href = url;
            return;
        }

        var nomor = ++permintaanKe;
        wrap.classList.add("is-loading");
        wrap.setAttribute("aria-busy", "true");

        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
            credentials: "same-origin",
        })
            .then(function (res) {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.text();
            })
            .then(function (html) {
                if (nomor !== permintaanKe) return; // ada permintaan yang lebih baru

                var doc = new DOMParser().parseFromString(html, "text/html");
                var baru = doc.getElementById("pgmListWrap");
                if (!baru) throw new Error("Wadah daftar tidak ditemukan");

                wrap.innerHTML = baru.innerHTML;

                if (dorongRiwayat) history.pushState({ pgm: 1 }, "", url);

                if (gulir) {
                    var judul = document.getElementById("pgmDaftar");
                    if (judul && judul.getBoundingClientRect().top < 0) {
                        judul.scrollIntoView({
                            behavior: "smooth",
                            block: "start",
                        });
                    }
                }
            })
            .catch(function () {
                window.location.href = url;
            })
            .then(function () {
                if (nomor !== permintaanKe) return;
                wrap.classList.remove("is-loading");
                wrap.removeAttribute("aria-busy");
            });
    }

    // klik angka / panah pagination
    if (wrap) {
        wrap.addEventListener("click", function (e) {
            var a = e.target.closest("a.pgm-page");
            if (!a || !wrap.contains(a)) return;
            // biarkan Ctrl/Cmd/Shift-klik membuka tab baru seperti biasa
            if (
                e.metaKey ||
                e.ctrlKey ||
                e.shiftKey ||
                e.altKey ||
                e.button !== 0
            )
                return;

            e.preventDefault();
            muat(a.href, true, true);
        });
    }

    // cari (Enter) -> hanya daftar yang berubah
    if (filter) {
        filter.addEventListener("submit", function (e) {
            e.preventDefault();
            muat(bangunUrl(), true, false);
        });
    }

    // tombol Back/Forward browser
    window.addEventListener("popstate", function () {
        var p = new URLSearchParams(window.location.search);
        if (cari) cari.value = p.get("q") || "";
        aturUrut(p.get("urut") === "terlama" ? "terlama" : "terbaru");
        muat(window.location.href, false, false);
    });

    /* =====================================================
       2. DROPDOWN URUTAN (custom)
       Nilai disimpan di input hidden #pgmUrut.
       ===================================================== */
    var kotak = document.getElementById("pgmSelect");
    var tombol = document.getElementById("pgmSelectBtn");
    var menu = document.getElementById("pgmMenu");
    var label = document.getElementById("pgmSelectLabel");
    var opsi = menu
        ? Array.prototype.slice.call(menu.querySelectorAll(".pgm-opt"))
        : [];
    var fokus = -1;

    function aturUrut(nilai) {
        if (!urut || !label) return;
        urut.value = nilai;
        opsi.forEach(function (o) {
            var aktif = o.getAttribute("data-value") === nilai;
            o.setAttribute("aria-selected", aktif ? "true" : "false");
            if (aktif) label.textContent = o.firstChild.textContent.trim();
        });
    }

    function tandaiFokus(i) {
        fokus = i;
        opsi.forEach(function (o, n) {
            o.classList.toggle("is-focus", n === i);
        });
    }

    function buka() {
        if (!menu) return;
        menu.hidden = false;
        kotak.classList.add("is-open");
        tombol.setAttribute("aria-expanded", "true");
        var aktif = opsi.findIndex(function (o) {
            return o.getAttribute("aria-selected") === "true";
        });
        tandaiFokus(aktif < 0 ? 0 : aktif);
    }

    function tutup() {
        if (!menu) return;
        menu.hidden = true;
        kotak.classList.remove("is-open");
        tombol.setAttribute("aria-expanded", "false");
        tandaiFokus(-1);
    }

    function pilih(o) {
        var nilai = o.getAttribute("data-value");
        var berubah = urut.value !== nilai;
        aturUrut(nilai);
        tutup();
        tombol.focus();
        if (berubah) muat(bangunUrl(), true, false);
    }

    if (kotak && tombol && menu && urut) {
        tombol.addEventListener("click", function () {
            menu.hidden ? buka() : tutup();
        });

        opsi.forEach(function (o, i) {
            o.addEventListener("click", function () {
                pilih(o);
            });
            o.addEventListener("mousemove", function () {
                tandaiFokus(i);
            });
        });

        tombol.addEventListener("keydown", function (e) {
            var terbuka = !menu.hidden;

            if (e.key === "ArrowDown" || e.key === "ArrowUp") {
                e.preventDefault();
                if (!terbuka) return buka();
                var arah = e.key === "ArrowDown" ? 1 : -1;
                tandaiFokus((fokus + arah + opsi.length) % opsi.length);
            } else if (e.key === "Enter" || e.key === " ") {
                if (terbuka && fokus > -1) {
                    e.preventDefault();
                    pilih(opsi[fokus]);
                }
            } else if (e.key === "Escape" && terbuka) {
                e.preventDefault();
                tutup();
            } else if (e.key === "Tab" && terbuka) {
                tutup();
            }
        });

        document.addEventListener("click", function (e) {
            if (!kotak.contains(e.target)) tutup();
        });
    }

    /* =====================================================
       3. FORM TULIS PENGUMUMAN (kode asli, tidak diubah)
       ===================================================== */
    var form = document.getElementById("pgmForm");
    var isi = document.getElementById("pgmIsi");
    var kirim = document.getElementById("pgmKirim");

    if (form && isi && kirim) {
        form.addEventListener("submit", function (e) {
            // tolak isi kosong (spasi saja)
            if (isi.value.trim() === "") {
                e.preventDefault();
                isi.focus();
                return;
            }

            // cegah kirim ganda
            kirim.disabled = true;
        });
    }
})();
