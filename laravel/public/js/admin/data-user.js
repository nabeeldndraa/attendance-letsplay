/**
 * Halaman Data User (admin)
 * 1) Pop-up konfirmasi kustom untuk tombol Aktifkan / Nonaktifkan
 *    (menggantikan confirm() bawaan browser). Form tetap dikirim normal ke backend.
 * 2) Tombol tutup (x) pada notifikasi sukses / error.
 */
(function () {
    const modal = document.getElementById("adm-user-modal");
    if (!modal) return;

    const titleEl = modal.querySelector(".adm-user-modal-title");
    const textEl = modal.querySelector(".adm-user-modal-text");
    const confirmBtn = document.getElementById("adm-user-modal-confirm");
    const cancelBtn = modal.querySelector(
        ".adm-user-modal-actions [data-modal-close]",
    );

    let pendingForm = null;
    let lastFocus = null;

    function open(form) {
        pendingForm = form;
        lastFocus = document.activeElement;

        modal.dataset.tone = form.dataset.confirmTone || "danger";
        titleEl.textContent = form.dataset.confirmTitle || "Yakin?";
        textEl.textContent = form.dataset.confirmText || "";
        confirmBtn.textContent = form.dataset.confirmButton || "Ya";

        modal.hidden = false;
        document.body.style.overflow = "hidden";
        cancelBtn.focus();
    }

    function close() {
        modal.hidden = true;
        document.body.style.overflow = "";
        pendingForm = null;
        if (lastFocus && typeof lastFocus.focus === "function")
            lastFocus.focus();
    }

    // Cegat submit form yang punya atribut data-confirm
    document.querySelectorAll("form[data-confirm]").forEach((form) => {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            open(form);
        });
    });

    confirmBtn.addEventListener("click", () => {
        const form = pendingForm;
        if (!form) return;
        confirmBtn.disabled = true;

        // Simpan nama peserta supaya notifikasi sukses setelah reload bisa menyebut namanya
        try {
            sessionStorage.setItem(
                "adm-user-last-status",
                JSON.stringify({
                    name: form.dataset.confirmName || "",
                    time: Date.now(),
                }),
            );
        } catch (err) {
            /* abaikan jika storage tidak tersedia */
        }

        form.submit(); // submit() tidak memicu event submit lagi, jadi langsung terkirim
    });

    modal.querySelectorAll("[data-modal-close]").forEach((el) => {
        el.addEventListener("click", close);
    });

    document.addEventListener("keydown", (e) => {
        if (modal.hidden) return;

        if (e.key === "Escape") {
            close();
            return;
        }

        // Fokus tetap berputar di dalam pop-up
        if (e.key === "Tab") {
            const focusable = [cancelBtn, confirmBtn];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    // Sisipkan nama peserta ke notifikasi sukses:
    // "Status akun berhasil diubah menjadi nonaktif." -> "Status akun 'Nama' berhasil diubah menjadi nonaktif."
    try {
        const saved = JSON.parse(
            sessionStorage.getItem("adm-user-last-status") || "null",
        );
        const success = document.querySelector(
            ".adm-user-alert--success .adm-user-alert-text",
        );

        if (saved && success) {
            const fresh = Date.now() - saved.time < 30000;
            const original = success.textContent.trim();

            if (fresh && saved.name && /^status akun\b/i.test(original)) {
                success.textContent = original.replace(
                    /^status akun/i,
                    `Status akun \u2018${saved.name}\u2019`,
                );
            }
        }

        if (success) sessionStorage.removeItem("adm-user-last-status");
    } catch (err) {
        /* abaikan */
    }

    // Tutup notifikasi
    document.querySelectorAll(".adm-user-alert-close").forEach((btn) => {
        btn.addEventListener("click", () => {
            const alert = btn.closest(".adm-user-alert");
            if (alert) alert.remove();
        });
    });
})();

/**
 * Filter (Instansi, Bidang Magang, Periode Magang)
 * - Dropdown kustom: nilai disimpan di input tersembunyi (name = instansi / bidang).
 * - Periode: pilih bulan + tahun (tanpa tanggal), disimpan sebagai YYYY-MM di input name="periode".
 * - Filter baru dikirim ke backend (GET) saat tombol "Cari" ditekan.
 */
(function () {
    const MONTHS = [
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

    const pad = (n) => String(n).padStart(2, "0");
    const parse = (value) => {
        const [y, m] = String(value || "")
            .split("-")
            .map(Number);
        return y && m ? { y, m } : null;
    };

    const closers = [];
    const closeAll = (except) =>
        closers.forEach((c) => {
            if (c.wrap !== except) c.close();
        });

    // Klik di luar / Esc menutup semua dropdown.
    // composedPath dipakai karena isi pop-up bisa diganti ulang saat klik (target sudah lepas dari DOM).
    document.addEventListener("click", (e) => {
        const path = e.composedPath();
        closers.forEach((c) => {
            if (!path.includes(c.wrap)) c.close();
        });
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeAll(null);
    });

    /* ---------- Dropdown biasa (Instansi, Bidang Magang) ---------- */
    document.querySelectorAll("[data-select]").forEach((wrap) => {
        const btn = wrap.querySelector(".adm-user-select-btn");
        const text = wrap.querySelector(".adm-user-select-text");
        const input = wrap.querySelector('input[type="hidden"]');
        const list = wrap.querySelector(".adm-user-select-list");
        const options = Array.from(list.querySelectorAll(".adm-user-option"));
        const placeholder = options[0].textContent.trim();

        function open() {
            closeAll(wrap);
            list.hidden = false;
            btn.setAttribute("aria-expanded", "true");
            const sel = list.querySelector(".is-selected");
            if (sel) sel.scrollIntoView({ block: "nearest" });
        }

        function close() {
            list.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        }

        closers.push({ wrap, close });

        btn.addEventListener("click", () => (list.hidden ? open() : close()));

        btn.addEventListener("keydown", (e) => {
            if (e.key === "ArrowDown") {
                e.preventDefault();
                open();
                (list.querySelector(".is-selected") || options[0]).focus();
            }
        });

        list.addEventListener("keydown", (e) => {
            const i = options.indexOf(document.activeElement);
            if (e.key === "ArrowDown" && i < options.length - 1) {
                e.preventDefault();
                options[i + 1].focus();
            } else if (e.key === "ArrowUp" && i > 0) {
                e.preventDefault();
                options[i - 1].focus();
            } else if (e.key === "Escape") {
                close();
                btn.focus();
            }
        });

        options.forEach((opt) => {
            opt.addEventListener("click", () => {
                const value = opt.dataset.value || "";
                input.value = value;
                text.textContent = value || placeholder;
                text.classList.toggle("is-placeholder", value === "");
                options.forEach((o) =>
                    o.classList.toggle("is-selected", o === opt),
                );
                close();
                btn.focus();
            });
        });
    });

    /* ---------- Periode Magang (bulan + tahun) ---------- */
    document.querySelectorAll(".adm-user-mp-wrap").forEach((wrap) => {
        const btn = wrap.querySelector(".adm-user-select-btn");
        const text = wrap.querySelector(".adm-user-select-text");
        const input = wrap.querySelector('input[type="hidden"]');
        const pop = wrap.querySelector(".adm-user-mp");
        const placeholder = text.dataset.placeholder || "Semua Periode";

        const d = new Date();
        const now = { y: d.getFullYear(), m: d.getMonth() + 1 };
        let selected = parse(input.value);
        let viewYear = (selected || now).y;

        function render() {
            let html = `
                <div class="adm-user-mp-head">
                    <button type="button" class="adm-user-mp-nav" data-year="-1" aria-label="Tahun sebelumnya">
                        <svg aria-hidden="true"><use href="#i-chevron-left"/></svg>
                    </button>
                    <div class="adm-user-mp-year">${viewYear}</div>
                    <button type="button" class="adm-user-mp-nav" data-year="1" aria-label="Tahun berikutnya">
                        <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                    </button>
                </div>
                <div class="adm-user-mp-grid">`;

            SHORT.forEach((name, i) => {
                const m = i + 1;
                const cls = ["adm-user-mp-month"];
                if (viewYear === now.y && m === now.m) cls.push("is-current");
                if (selected && viewYear === selected.y && m === selected.m)
                    cls.push("is-selected");
                html += `<button type="button" class="${cls.join(" ")}" data-month="${m}">${name}</button>`;
            });

            html += `</div>
                <div class="adm-user-mp-foot">
                    <button type="button" class="adm-user-mp-link" data-clear>Semua periode</button>
                    <button type="button" class="adm-user-mp-link" data-today>Bulan ini</button>
                </div>`;

            pop.innerHTML = html;
        }

        function open() {
            closeAll(wrap);
            viewYear = (selected || now).y;
            render();
            pop.hidden = false;
            btn.setAttribute("aria-expanded", "true");
        }

        function close() {
            pop.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        }

        function choose(y, m) {
            selected = y ? { y, m } : null;
            input.value = selected ? `${y}-${pad(m)}` : "";
            text.textContent = selected ? `${MONTHS[m - 1]} ${y}` : placeholder;
            text.classList.toggle("is-placeholder", !selected);
            close();
        }

        closers.push({ wrap, close });

        btn.addEventListener("click", () => (pop.hidden ? open() : close()));

        pop.addEventListener("click", (e) => {
            const target = e.target.closest("button");
            if (!target) return;

            if (target.dataset.year) {
                viewYear += Number(target.dataset.year);
                render();
            } else if (target.dataset.month) {
                choose(viewYear, Number(target.dataset.month));
            } else if (target.hasAttribute("data-today")) {
                choose(now.y, now.m);
            } else if (target.hasAttribute("data-clear")) {
                choose(null, null);
            }
        });
    });
})();
