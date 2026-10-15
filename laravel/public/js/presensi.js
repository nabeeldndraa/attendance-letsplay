/* =========================================================
   PRESENSI  —  public/js/presensi.js
   Logika request ke backend (status, cek-lokasi, presensi)
   dipertahankan dari versi sebelumnya.
   URL diambil dari atribut data-* di #presensiApp.
========================================================= */

(function () {
    "use strict";

    const app = document.getElementById("presensiApp");
    if (!app) return;

    const API = {
        status: app.dataset.urlStatus,
        cekLokasi: app.dataset.urlCekLokasi,
        presensi: app.dataset.urlPresensi,
        izin: app.dataset.urlIzin, // TODO: endpoint izin/sakit (koordinasi dengan backend)
    };

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const $ = (id) => document.getElementById(id);

    const el = {
        tanggal: $("tanggalHariIni"),
        jam: $("jamSekarang"),
        banner: $("statusPresensi"),

        cardPresensi: $("cardPresensi"),
        bodyPresensi: $("bodyPresensi"),
        cardIzin: $("cardIzin"),
        bodyIzin: $("bodyIzin"),

        tipeKerja: $("tipe_kerja"),
        workButtons: document.querySelectorAll("[data-value]"),
        izinButtons: document.querySelectorAll("[data-jenis]"),
        lockButtons: document.querySelectorAll("[data-activate]"),

        lokasiSection: $("lokasiSection"),
        namaLokasi: $("namaLokasi"),
        lokasiBox: $("lokasiBox"),
        lokasiIcon: $("lokasiIcon"),
        statusLokasi: $("statusLokasi"),
        jarakLokasi: $("jarakLokasi"),
        btnLokasi: $("btnLokasi"),
        btnPeta: $("btnPeta"),

        progress: $("progress_hari_ini"),
        counter: $("counterProgress"),
        keterangan: $("keterangan_izin"),

        syaratWfo: $("syaratWfo"),
        syaratWfh: $("syaratWfh"),

        btnKirim: $("btnPresensi"),
        btnKirimLabel: $("btnPresensiLabel"),
        message: $("message"),
    };

    const state = {
        mode: "presensi", // 'presensi' | 'izin'
        tipeKerja: "WFO", // 'WFO' | 'WFH'
        jenisIzin: "sakit", // 'sakit' | 'izin'
        latitude: null,
        longitude: null,
        lokasiValid: false,
        cekLokasiBerjalan: false,
        sedangKirim: false,
        sudahPresensi: false,
    };

    /* =========================
       HELPER
    ========================= */

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(
                data.message || "Terjadi kesalahan. Silakan coba lagi.",
            );
        }

        return data;
    }

    function tampilkanPesan(text, type) {
        el.message.textContent = text;
        el.message.className = "pres-message " + type;
        el.message.hidden = false;
    }

    function sembunyikanPesan() {
        el.message.hidden = true;
    }

    /* =========================
       TANGGAL & JAM (WIB)
    ========================= */

    const formatTanggal = new Intl.DateTimeFormat("id-ID", {
        timeZone: "Asia/Jakarta",
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });

    const formatJam = new Intl.DateTimeFormat("id-ID", {
        timeZone: "Asia/Jakarta",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });

    function updateClock() {
        const now = new Date();

        el.tanggal.textContent = formatTanggal.format(now);

        // Jam dibekukan setelah presensi
        if (!state.sudahPresensi) {
            el.jam.textContent =
                formatJam.format(now).replace(".", ":") + " WIB";
        }
    }

    /* =========================
       MODE FORM: PRESENSI <-> IZIN/SAKIT
    ========================= */

    function aturCard(card, body, aktif) {
        card.classList.toggle("is-locked", !aktif);
        body.inert = !aktif; // nonaktifkan semua input & fokus keyboard
    }

    function setMode(mode) {
        if (state.sudahPresensi) return;

        state.mode = mode;

        aturCard(el.cardPresensi, el.bodyPresensi, mode === "presensi");
        aturCard(el.cardIzin, el.bodyIzin, mode === "izin");

        sembunyikanPesan();
        updateSubmitState();
    }

    /* =========================
       WFO / WFH
    ========================= */

    function setTipeKerja(value, auto = true) {
        state.tipeKerja = value;
        el.tipeKerja.value = value;

        el.workButtons.forEach((btn) => {
            const aktif = btn.dataset.value === value;
            btn.classList.toggle("is-active", aktif);
            btn.setAttribute("aria-pressed", aktif);
        });

        const wfo = value === "WFO";

        el.cardPresensi.classList.toggle("is-wfh", !wfo);
        el.lokasiSection.hidden = !wfo;
        el.syaratWfo.hidden = !wfo;
        el.syaratWfh.hidden = wfo;

        sembunyikanPesan();

        if (wfo && auto && !state.lokasiValid) {
            ambilLokasi();
        }

        updateSubmitState();
    }

    /* =========================
       LOKASI
    ========================= */

    const ICON_VALID =
        '<svg class="pres-ico" aria-hidden="true"><use href="#p-check-circle"/></svg>';
    const ICON_INVALID =
        '<svg class="pres-ico" aria-hidden="true"><use href="#p-x-circle"/></svg>';
    const ICON_LOADING = '<span class="pres-spinner"></span>';

    function tampilLokasi(tipe, judul, detail, nama) {
        el.lokasiBox.className = "pres-loc-status is-" + tipe;
        el.lokasiIcon.innerHTML =
            tipe === "valid"
                ? ICON_VALID
                : tipe === "invalid"
                  ? ICON_INVALID
                  : ICON_LOADING;

        el.statusLokasi.textContent = judul;
        el.jarakLokasi.textContent = detail || "";

        if (nama) el.namaLokasi.textContent = nama;
    }

    function ambilLokasi() {
        if (state.sudahPresensi || state.cekLokasiBerjalan) return;

        if (!navigator.geolocation) {
            gagalLokasi("Browser tidak mendukung GPS.");
            return;
        }

        state.cekLokasiBerjalan = true;
        state.lokasiValid = false;

        el.btnLokasi.hidden = true;
        el.btnPeta.disabled = true;
        tampilLokasi(
            "loading",
            "Mengambil lokasi...",
            "Pastikan GPS perangkatmu aktif.",
            "Mendeteksi lokasi...",
        );
        updateSubmitState();

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                state.latitude = position.coords.latitude;
                state.longitude = position.coords.longitude;
                el.btnPeta.disabled = false;

                tampilLokasi(
                    "loading",
                    "Memvalidasi lokasi...",
                    "Sedang mengecek lokasi kantor.",
                );

                try {
                    const result = await postJson(API.cekLokasi, {
                        latitude_user: state.latitude,
                        longitude_user: state.longitude,
                    });

                    state.lokasiValid = true;

                    const nama = result.lokasi.nama_lokasi;

                    tampilLokasi(
                        "valid",
                        "Lokasi kamu dalam jangkauan presensi",
                        "Jarak kamu ke " +
                            nama +
                            ": " +
                            result.lokasi.jarak_meter +
                            " m",
                        nama,
                    );

                    el.btnLokasi.hidden = true;
                    sembunyikanPesan();
                } catch (error) {
                    state.lokasiValid = false;

                    tampilLokasi(
                        "invalid",
                        "Lokasi tidak valid",
                        error.message,
                        "Lokasi tidak valid",
                    );

                    el.btnLokasi.textContent = "Coba Lagi";
                    el.btnLokasi.hidden = false;
                } finally {
                    state.cekLokasiBerjalan = false;
                    updateSubmitState();
                }
            },

            (error) => {
                let pesan = "Gagal mendapatkan lokasi.";

                if (error.code === 1)
                    pesan = "Izin lokasi ditolak oleh browser.";
                if (error.code === 2) pesan = "Lokasi tidak tersedia.";
                if (error.code === 3)
                    pesan = "Pengambilan lokasi terlalu lama.";

                gagalLokasi(pesan);
            },

            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            },
        );
    }

    function gagalLokasi(pesan) {
        state.cekLokasiBerjalan = false;
        state.lokasiValid = false;

        tampilLokasi(
            "invalid",
            "Lokasi gagal diambil",
            pesan,
            "Lokasi belum diambil",
        );

        el.btnPeta.disabled = true;
        el.btnLokasi.textContent = "Coba Lagi";
        el.btnLokasi.hidden = false;

        updateSubmitState();
    }

    function bukaPeta() {
        if (state.latitude === null || state.longitude === null) return;

        window.open(
            "https://www.google.com/maps?q=" +
                state.latitude +
                "," +
                state.longitude,
            "_blank",
            "noopener",
        );
    }

    /* =========================
       TOMBOL KIRIM
    ========================= */

    function updateSubmitState() {
        let bisaKirim = false;

        if (state.sudahPresensi || state.sedangKirim) {
            bisaKirim = false;
        } else if (state.mode === "izin") {
            bisaKirim = el.keterangan.value.trim().length > 0;
        } else {
            bisaKirim = state.tipeKerja === "WFH" || state.lokasiValid;
        }

        el.btnKirim.disabled = !bisaKirim;
    }

    function kunciForm(label) {
        state.sudahPresensi = true;

        el.btnKirimLabel.textContent = label || "Sudah Presensi";
        el.btnKirim.disabled = true;
        el.banner.hidden = false;

        // Form presensi: terkunci tapi tetap terlihat normal
        el.cardPresensi.classList.remove("is-locked");
        el.cardPresensi.classList.add("is-readonly");
        el.bodyPresensi.inert = true;

        // Form izin: abu-abu & tidak bisa diaktifkan
        aturCard(el.cardIzin, el.bodyIzin, false);
        el.lockButtons.forEach((btn) => (btn.disabled = true));
        el.cardIzin.querySelector(".pres-lock").style.display = "none";
    }

    /* =========================
       CEK STATUS PRESENSI HARI INI
    ========================= */

    async function cekStatusPresensiHariIni() {
        try {
            const response = await fetch(API.status, {
                method: "GET",
                headers: { Accept: "application/json" },
            });

            const result = await response.json();

            if (!response.ok || !result.success || !result.sudah_presensi) {
                return;
            }

            const data = result.data || {};

            if (data.jam_presensi) {
                el.jam.textContent = data.jam_presensi.substring(0, 5) + " WIB";
            }

            if (data.tipe_kerja) {
                setTipeKerja(data.tipe_kerja, false);
            }

            if (data.progress_hari_ini) {
                el.progress.value = data.progress_hari_ini;
                updateCounter();
            }

            kunciForm();
        } catch (error) {
            console.error("Gagal mengecek status presensi:", error);
        }
    }

    /* =========================
       KIRIM PRESENSI / IZIN
    ========================= */

    async function kirimPresensi() {
        if (state.tipeKerja === "WFO" && !state.lokasiValid) {
            tampilkanPesan(
                "Lokasi belum valid. Pastikan GPS aktif dan kamu berada di area kantor.",
                "error",
            );
            return;
        }

        const result = await postJson(API.presensi, {
            tipe_kerja: state.tipeKerja,
            latitude_user: state.latitude,
            longitude_user: state.longitude,
            progress_hari_ini: el.progress.value,
        });

        if (result.data && result.data.jam_presensi) {
            el.jam.textContent =
                result.data.jam_presensi.substring(0, 5) + " WIB";
        }

        tampilkanPesan(result.message, "success");
        kunciForm();
    }

    async function kirimIzin() {
        // TODO: sesuaikan nama field & endpoint dengan backend
        const result = await postJson(API.izin, {
            jenis: state.jenisIzin,
            keterangan: el.keterangan.value.trim(),
        });

        tampilkanPesan(result.message, "success");
        kunciForm("Pengajuan Terkirim");
    }

    async function kirim() {
        if (state.sudahPresensi) {
            tampilkanPesan("Anda sudah melakukan presensi hari ini.", "error");
            return;
        }

        sembunyikanPesan();

        state.sedangKirim = true;
        el.btnKirim.disabled = true;
        el.btnKirimLabel.textContent = "Memproses...";

        try {
            if (state.mode === "izin") {
                await kirimIzin();
            } else {
                await kirimPresensi();
            }
        } catch (error) {
            tampilkanPesan(error.message, "error");
            el.btnKirimLabel.textContent = "Kirim Presensi";
        } finally {
            state.sedangKirim = false;
            updateSubmitState();
        }
    }

    /* =========================
       COUNTER KARAKTER
    ========================= */

    function updateCounter() {
        el.counter.textContent = el.progress.value.length + " / 200 karakter";
    }

    /* =========================
       EVENT
    ========================= */

    // Klik form abu-abu -> aktifkan form itu, form lain jadi abu-abu
    el.lockButtons.forEach((btn) => {
        btn.addEventListener("click", () => setMode(btn.dataset.activate));
    });

    el.workButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            if (state.sudahPresensi) return;
            setTipeKerja(btn.dataset.value);
        });
    });

    el.izinButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            state.jenisIzin = btn.dataset.jenis;

            el.izinButtons.forEach((item) => {
                const aktif = item === btn;
                item.classList.toggle("is-active", aktif);
                item.setAttribute("aria-pressed", aktif);
            });
        });
    });

    el.progress.addEventListener("input", updateCounter);
    el.keterangan.addEventListener("input", updateSubmitState);

    el.btnLokasi.addEventListener("click", ambilLokasi);
    el.btnPeta.addEventListener("click", bukaPeta);
    el.btnKirim.addEventListener("click", kirim);

    /* =========================
       INIT
    ========================= */

    updateClock();
    setInterval(updateClock, 1000);

    setMode("presensi");
    setTipeKerja("WFO", false);

    // Cek status dulu; kalau belum presensi baru deteksi lokasi otomatis
    cekStatusPresensiHariIni().then(() => {
        if (!state.sudahPresensi && state.tipeKerja === "WFO") {
            ambilLokasi();
        }
    });
})();
