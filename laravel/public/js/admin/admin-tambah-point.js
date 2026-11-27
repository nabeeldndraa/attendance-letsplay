/* ADMIN: TAMBAH POINT USER */
(function () {
    "use strict";

    var form = document.getElementById("tpForm");
    if (!form) return;

    var cari = document.getElementById("tpCari");
    var semua = document.getElementById("tpSemua");
    var rows = [].slice.call(form.querySelectorAll(".tp-row"));
    var checks = rows.map(function (r) { return r.querySelector(".tp-check"); });
    var empty = document.getElementById("tpEmpty");
    var count = document.getElementById("tpCount");

    var poin = document.getElementById("tpPoin");
    var presets = [].slice.call(form.querySelectorAll(".tp-preset"));
    var chips = document.getElementById("tpChips");
    var chipsEmpty = document.getElementById("tpChipsEmpty");
    var summary = document.getElementById("tpSummary");
    var errPeserta = document.getElementById("tpErrPeserta");
    var errPoin = document.getElementById("tpErrPoin");
    var statDipilih = document.getElementById("tpStatDipilih");
    var statTotal = document.getElementById("tpStatTotal");
    var jump = document.getElementById("tpJump");
    var give = document.getElementById("tpGive");
    var kirim = document.getElementById("tpKirim");

    var dialog = document.getElementById("tpConfirm");
    var dialogText = document.getElementById("tpConfirmText");

    function fmt(n) { return Number(n).toLocaleString("id-ID"); }

    // hanya bilangan bulat positif yang dianggap valid
    function nilaiPoin() {
        var v = poin.value.trim();
        return /^\d+$/.test(v) ? parseInt(v, 10) : 0;
    }

    function terpilih() {
        return checks.filter(function (c) { return c.checked; });
    }

    function barisTampil() {
        return rows.filter(function (r) { return !r.hidden; });
    }

    /* ---------- cari (di sisi browser, tanpa reload) ---------- */
    function saring() {
        var q = cari.value.trim().toLowerCase();
        rows.forEach(function (r) {
            r.hidden = q !== "" && r.getAttribute("data-cari").indexOf(q) === -1;
        });
        empty.hidden = barisTampil().length !== 0;
        perbarui();
    }

    /* ---------- perbarui semua tampilan ---------- */
    function perbarui() {
        var dipilih = terpilih();
        var n = dipilih.length;
        var p = nilaiPoin();

        statDipilih.textContent = fmt(n);
        statTotal.textContent = fmt(n * p);

        // sorot baris
        rows.forEach(function (r, i) {
            r.classList.toggle("is-selected", checks[i].checked);
        });

        // chip peserta dipilih
        chips.innerHTML = "";
        dipilih.forEach(function (c) {
            var li = document.createElement("li");
            li.className = "tp-chip";

            var teks = document.createElement("span");
            teks.textContent = c.getAttribute("data-nama");

            var x = document.createElement("button");
            x.type = "button";
            x.setAttribute("aria-label", "Batalkan pilihan " + c.getAttribute("data-nama"));
            x.innerHTML = '<svg class="ico" aria-hidden="true"><use href="#i-x"/></svg>';
            x.addEventListener("click", function () {
                c.checked = false;
                perbarui();
            });

            li.appendChild(teks);
            li.appendChild(x);
            chips.appendChild(li);
        });
        chipsEmpty.hidden = n > 0;

        // pilih semua (hanya yang tampil)
        var tampil = barisTampil();
        var tampilDipilih = tampil.filter(function (r) {
            return r.querySelector(".tp-check").checked;
        }).length;
        semua.disabled = tampil.length === 0;
        semua.checked = tampil.length > 0 && tampilDipilih === tampil.length;
        semua.indeterminate = tampilDipilih > 0 && tampilDipilih < tampil.length;

        count.textContent = "Menampilkan " + tampil.length + " dari " + rows.length + " peserta";

        // ringkasan
        if (n > 0 && p > 0) {
            summary.classList.add("is-ready");
            summary.innerHTML = "<b>+" + fmt(p) + " poin</b> untuk <b>" + fmt(n) +
                " peserta</b><br>Total poin dibagikan: <b>" + fmt(n * p) + "</b>";
        } else {
            summary.classList.remove("is-ready");
            summary.textContent = "Pilih peserta dan isi jumlah poin.";
        }

        // tombol cepat
        presets.forEach(function (b) {
            b.classList.toggle("is-active", parseInt(b.getAttribute("data-poin"), 10) === p);
        });

        // tombol "lanjut" (hanya terlihat di layar sempit lewat CSS layout)
        if (jump) jump.hidden = n === 0 || window.innerWidth > 1100;

        if (n > 0) errPeserta.textContent = "";
        if (p > 0) {
            errPoin.textContent = "";
            poin.classList.remove("is-invalid");
        }
    }

    /* ---------- event ---------- */
    checks.forEach(function (c) { c.addEventListener("change", perbarui); });

    semua.addEventListener("change", function () {
        var nilai = semua.checked;
        barisTampil().forEach(function (r) {
            r.querySelector(".tp-check").checked = nilai;
        });
        perbarui();
    });

    cari.addEventListener("input", saring);
    // Enter di kolom cari tidak boleh mengirim form
    cari.addEventListener("keydown", function (e) {
        if (e.key === "Enter") e.preventDefault();
    });

    poin.addEventListener("input", perbarui);
    // blokir karakter yang bukan angka bulat
    poin.addEventListener("keydown", function (e) {
        if (["e", "E", "+", "-", ".", ","].indexOf(e.key) > -1) e.preventDefault();
    });

    presets.forEach(function (b) {
        b.addEventListener("click", function () {
            poin.value = b.getAttribute("data-poin");
            perbarui();
        });
    });

    if (jump) {
        jump.addEventListener("click", function () {
            give.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }
    window.addEventListener("resize", perbarui);

    /* ---------- kirim ---------- */
    function kirimSungguhan() {
        if (form.hasAttribute("data-demo")) {
            // MODE TAMPILAN: route simpan belum ada, tidak ada yang dikirim.
            // Otomatis nonaktif begitu route admin.tambah-point.store tersedia.
            var info = document.getElementById("tpDemo");
            info.textContent = "Mode tampilan: poin belum disimpan karena backend belum tersedia.";
            info.hidden = false;
            info.scrollIntoView({ behavior: "smooth", block: "center" });
            return;
        }
        kirim.disabled = true;
        form.submit();
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        var n = terpilih().length;
        var p = nilaiPoin();
        var salah = null;

        if (n === 0) {
            errPeserta.textContent = "Pilih minimal satu peserta.";
            salah = salah || give;
        }
        if (p < 1) {
            errPoin.textContent = "Masukkan jumlah poin berupa angka lebih dari 0.";
            poin.classList.add("is-invalid");
            salah = salah || poin;
        }
        if (salah) {
            if (salah === poin) poin.focus();
            else give.scrollIntoView({ behavior: "smooth", block: "start" });
            return;
        }

        var teks = "Anda akan memberi +" + fmt(p) + " poin kepada " + fmt(n) +
            " peserta (total " + fmt(n * p) + " poin). Lanjutkan?";

        if (dialog && typeof dialog.showModal === "function") {
            dialogText.textContent = teks;
            dialog.showModal();
        } else if (window.confirm(teks)) {
            kirimSungguhan();
        }
    });

    if (dialog) {
        document.getElementById("tpBatal").addEventListener("click", function () { dialog.close(); });
        document.getElementById("tpYa").addEventListener("click", function () {
            dialog.close();
            kirimSungguhan();
        });
    }

    perbarui();
})();
