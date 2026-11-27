/**
 * Halaman Tambah User (admin)
 * 1) Kalender kustom (tanggal masuk & selesai) dengan tampilan hari dan tampilan bulan/tahun.
 * 2) Combo "Divisi / Posisi Magang": pilih dari daftar atau ketik sendiri.
 * 3) Tampilkan / sembunyikan password + pengecekan konfirmasi password.
 * 4) Pemilih mode (Input Manual / Upload Excel): panel yang tidak dipilih menjadi abu-abu dan tidak bisa dipakai.
 * 5) Area upload Excel: pilih / tarik-lepas file, cek format & ukuran, tampilkan info file.
 * 6) Pop-up konfirmasi: sebelum menyimpan / mengupload, dan saat meninggalkan halaman dengan isian yang belum disimpan.
 *
 * Catatan teknis: isi kalender digambar ulang setiap klik. Karena itu pengecekan "klik di luar"
 * memakai event.composedPath() (bukan target.contains) supaya klik tombol next/previous
 * tidak dianggap klik di luar dan kalender tidak tertutup sendiri.
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
    const DOW = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];

    const pad = (n) => String(n).padStart(2, "0");
    const toISO = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const fromISO = (s) => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s || "")) return null;
        const [y, m, d] = s.split("-").map(Number);
        const date = new Date(y, m - 1, d);
        return date.getMonth() === m - 1 ? date : null; // tolak tanggal tidak valid (mis. 31 Feb)
    };
    const format = (d) =>
        `${pad(d.getDate())} ${SHORT[d.getMonth()]} ${d.getFullYear()}`;
    const startOfDay = (d) =>
        new Date(d.getFullYear(), d.getMonth(), d.getDate());
    const sameDay = (a, b) => a && b && a.getTime() === b.getTime();

    const closers = [];
    const closeAll = (except) =>
        closers.forEach((c) => {
            if (c.wrap !== except) c.close();
        });

    // Klik di luar / Esc menutup semua pop-up (composedPath: aman walau isi pop-up sudah digambar ulang)
    document.addEventListener("click", (e) => {
        const path = e.composedPath();
        closers.forEach((c) => {
            if (!path.includes(c.wrap)) c.close();
        });
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeAll(null);
    });

    /* =====================================================
       1) KALENDER
       ===================================================== */
    document.querySelectorAll("[data-date]").forEach((wrap) => {
        const view = wrap.querySelector(".adm-add-date-view");
        const hidden = wrap.querySelector('input[type="hidden"]');
        const toggle = wrap.querySelector(".adm-add-date-btn");
        const pop = wrap.querySelector(".adm-add-cal");
        const minEl = wrap.dataset.minInput
            ? document.getElementById(wrap.dataset.minInput)
            : null;

        let mode = "days"; // 'days' | 'months'
        let cursor = new Date();
        cursor = new Date(cursor.getFullYear(), cursor.getMonth(), 1);

        const getValue = () => fromISO(hidden.value);
        const getMin = () => (minEl ? fromISO(minEl.value) : null);

        function setValue(d) {
            hidden.value = d ? toISO(d) : "";
            view.value = d ? format(d) : "";
            hidden.dispatchEvent(new Event("change"));
        }

        function renderDays() {
            const selected = getValue();
            const min = getMin();
            const today = startOfDay(new Date());
            const y = cursor.getFullYear();
            const m = cursor.getMonth();
            const offset = (new Date(y, m, 1).getDay() + 6) % 7; // minggu dimulai Senin
            const total = new Date(y, m + 1, 0).getDate();

            let html = `
                <div class="adm-add-cal-head">
                    <button type="button" class="adm-add-cal-nav" data-nav="-1" aria-label="Bulan sebelumnya">
                        <svg aria-hidden="true"><use href="#i-chevron-left"/></svg>
                    </button>
                    <button type="button" class="adm-add-cal-title" data-mode="months" aria-label="Pilih bulan dan tahun">
                        ${MONTHS[m]} ${y}
                    </button>
                    <button type="button" class="adm-add-cal-nav" data-nav="1" aria-label="Bulan berikutnya">
                        <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                    </button>
                </div>
                <div class="adm-add-cal-grid">
                    ${DOW.map((n) => `<div class="adm-add-cal-dow">${n}</div>`).join("")}
                    ${'<div class="adm-add-cal-empty"></div>'.repeat(offset)}`;

            for (let day = 1; day <= total; day++) {
                const date = new Date(y, m, day);
                const cls = ["adm-add-cal-day"];
                if (sameDay(date, today)) cls.push("is-today");
                if (sameDay(date, selected)) cls.push("is-selected");
                const disabled = min && date < min ? "disabled" : "";
                html += `<button type="button" class="${cls.join(" ")}" data-day="${day}" ${disabled}>${day}</button>`;
            }

            html += `</div>
                <div class="adm-add-cal-foot">
                    <button type="button" class="adm-add-cal-link" data-today>Hari ini</button>
                </div>`;

            pop.innerHTML = html;
        }

        function renderMonths() {
            const selected = getValue();
            const min = getMin();
            const now = new Date();
            const y = cursor.getFullYear();

            let html = `
                <div class="adm-add-cal-head">
                    <button type="button" class="adm-add-cal-nav" data-year="-1" aria-label="Tahun sebelumnya">
                        <svg aria-hidden="true"><use href="#i-chevron-left"/></svg>
                    </button>
                    <button type="button" class="adm-add-cal-title" data-mode="days" aria-label="Kembali ke tanggal">${y}</button>
                    <button type="button" class="adm-add-cal-nav" data-year="1" aria-label="Tahun berikutnya">
                        <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                    </button>
                </div>
                <div class="adm-add-cal-grid adm-add-cal-grid--months">`;

            SHORT.forEach((name, i) => {
                const cls = ["adm-add-cal-month"];
                if (y === now.getFullYear() && i === now.getMonth())
                    cls.push("is-today");
                if (
                    selected &&
                    y === selected.getFullYear() &&
                    i === selected.getMonth()
                )
                    cls.push("is-selected");
                // bulan yang seluruhnya sebelum tanggal minimum dinonaktifkan
                const endOfMonth = new Date(y, i + 1, 0);
                const disabled = min && endOfMonth < min ? "disabled" : "";
                html += `<button type="button" class="${cls.join(" ")}" data-month="${i}" ${disabled}>${name}</button>`;
            });

            html += "</div>";
            pop.innerHTML = html;
        }

        const render = () =>
            mode === "months" ? renderMonths() : renderDays();

        function open() {
            closeAll(wrap);
            const base = getValue() || getMin() || new Date();
            cursor = new Date(base.getFullYear(), base.getMonth(), 1);
            mode = "days";
            render();
            pop.hidden = false;
        }

        function close() {
            pop.hidden = true;
        }

        closers.push({ wrap, close });

        pop.addEventListener("click", (e) => {
            const btn = e.target.closest("button");
            if (!btn || btn.disabled) return;

            if (btn.dataset.nav) {
                cursor = new Date(
                    cursor.getFullYear(),
                    cursor.getMonth() + Number(btn.dataset.nav),
                    1,
                );
                render();
            } else if (btn.dataset.year) {
                cursor = new Date(
                    cursor.getFullYear() + Number(btn.dataset.year),
                    cursor.getMonth(),
                    1,
                );
                render();
            } else if (btn.dataset.mode) {
                mode = btn.dataset.mode;
                render();
            } else if (btn.dataset.month !== undefined) {
                cursor = new Date(
                    cursor.getFullYear(),
                    Number(btn.dataset.month),
                    1,
                );
                mode = "days";
                render();
            } else if (btn.dataset.day) {
                setValue(
                    new Date(
                        cursor.getFullYear(),
                        cursor.getMonth(),
                        Number(btn.dataset.day),
                    ),
                );
                close();
            } else if (btn.hasAttribute("data-today")) {
                const today = startOfDay(new Date());
                const min = getMin();
                if (!min || today >= min) {
                    setValue(today);
                    close();
                }
            }
        });

        // Tanggal hanya bisa dipilih lewat kalender (tidak diketik manual)
        view.addEventListener("click", () => (pop.hidden ? open() : close()));
        view.addEventListener("paste", (e) => e.preventDefault());
        view.addEventListener("keydown", (e) => {
            if (e.key === "Tab") return;
            if (e.key === "Escape") {
                close();
                return;
            }
            e.preventDefault();
            if (e.key === "Enter" || e.key === " " || e.key === "ArrowDown")
                open();
        });
        toggle.addEventListener("click", () => (pop.hidden ? open() : close()));

        // Tanggal mulai berubah dan melewati tanggal selesai -> kosongkan tanggal selesai
        if (minEl) {
            minEl.addEventListener("change", () => {
                const min = getMin();
                const current = getValue();
                if (min && current && current < min) setValue(null);
            });
        }

        // Tampilkan nilai awal (data tersimpan atau old input)
        const initial = getValue();
        if (initial) view.value = format(initial);
    });

    /* =====================================================
       2) COMBO DIVISI / POSISI MAGANG
       ===================================================== */
    document.querySelectorAll("[data-combo]").forEach((wrap) => {
        const input = wrap.querySelector(".adm-add-combo-input");
        const toggle = wrap.querySelector(".adm-add-combo-toggle");
        const list = wrap.querySelector(".adm-add-combo-list");
        const empty = wrap.querySelector(".adm-add-combo-empty");
        const options = Array.from(list.querySelectorAll(".adm-add-option"));

        function filter(showAll) {
            const q = showAll ? "" : input.value.trim().toLowerCase();
            let visible = 0;

            options.forEach((opt) => {
                const match = !q || opt.dataset.value.toLowerCase().includes(q);
                opt.parentElement.hidden = !match;
                opt.classList.toggle(
                    "is-selected",
                    opt.dataset.value === input.value,
                );
                if (match) visible += 1;
            });

            empty.hidden = visible > 0;
        }

        function open(showAll) {
            closeAll(wrap);
            filter(showAll);
            list.hidden = false;
            wrap.classList.add("is-open");
            input.setAttribute("aria-expanded", "true");
        }

        function close() {
            list.hidden = true;
            wrap.classList.remove("is-open");
            input.setAttribute("aria-expanded", "false");
        }

        closers.push({ wrap, close });

        toggle.addEventListener("click", () => {
            if (list.hidden) {
                open(true);
                input.focus();
            } else {
                close();
            }
        });

        input.addEventListener("focus", () => open(true));
        input.addEventListener("input", () => open(false));

        input.addEventListener("keydown", (e) => {
            if (e.key === "ArrowDown") {
                e.preventDefault();
                if (list.hidden) open(true);
                const first = options.find((o) => !o.parentElement.hidden);
                if (first) first.focus();
            } else if (e.key === "Enter" && !list.hidden) {
                e.preventDefault(); // Enter tidak mengirim form saat daftar terbuka
                close();
            }
        });

        list.addEventListener("keydown", (e) => {
            const visible = options.filter((o) => !o.parentElement.hidden);
            const i = visible.indexOf(document.activeElement);

            if (e.key === "ArrowDown" && i < visible.length - 1) {
                e.preventDefault();
                visible[i + 1].focus();
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                if (i > 0) visible[i - 1].focus();
                else input.focus();
            } else if (e.key === "Escape") {
                close();
                input.focus();
            }
        });

        options.forEach((opt) => {
            opt.addEventListener("click", () => {
                input.value = opt.dataset.value;
                close();
                input.focus();
            });
        });
    });

    /* =====================================================
       3) PASSWORD: tampilkan/sembunyikan + cek konfirmasi
       ===================================================== */
    document.querySelectorAll("[data-pass-toggle]").forEach((btn) => {
        const input = btn.parentElement.querySelector("input");

        btn.addEventListener("click", () => {
            const show = input.type === "password";
            input.type = show ? "text" : "password";
            btn.classList.toggle("is-visible", show);
            btn.setAttribute("aria-pressed", String(show));
            btn.setAttribute(
                "aria-label",
                show ? "Sembunyikan password" : "Tampilkan password",
            );
        });
    });

    const passInput = document.getElementById("password");
    const confirmInput = document.getElementById("password_confirmation");
    const confirmError = document.querySelector("[data-confirm-error]");

    // true jika konfirmasi sudah cocok (atau belum diisi)
    function checkConfirm(showError) {
        if (!passInput || !confirmInput || !confirmError) return true;
        const mismatch =
            confirmInput.value !== "" && confirmInput.value !== passInput.value;
        confirmError.hidden = !(showError && mismatch);
        confirmInput.classList.toggle("has-error", showError && mismatch);
        return !mismatch;
    }

    if (passInput && confirmInput) {
        confirmInput.addEventListener("input", () => checkConfirm(true));
        passInput.addEventListener("input", () =>
            checkConfirm(confirmInput.value !== ""),
        );
    }

    /* =====================================================
       4) PEMILIH MODE: Input Manual / Upload Excel
       ===================================================== */
    const modeInputs = Array.from(
        document.querySelectorAll('input[name="adm_mode"]'),
    );
    const panels = Array.from(document.querySelectorAll("[data-panel]"));

    function applyMode(mode) {
        closeAll(null); // tutup kalender / daftar yang sedang terbuka

        modeInputs.forEach((input) => {
            input
                .closest(".adm-add-mode")
                .classList.toggle("is-active", input.value === mode);
        });

        panels.forEach((panel) => {
            const active = panel.dataset.panel === mode;
            panel.classList.toggle("is-disabled", !active);
            panel.toggleAttribute("inert", !active); // tidak bisa diklik / difokus
            panel.setAttribute("aria-disabled", String(!active));
        });
    }

    modeInputs.forEach((input) => {
        input.addEventListener("change", () => {
            if (input.checked) applyMode(input.value);
        });
    });

    /* =====================================================
       5) UPLOAD EXCEL
       ===================================================== */
    const uploadForm = document.getElementById("adm-add-upload-form");
    const uploadBtn = document.getElementById("adm-add-upload-btn");
    const dropzone = document.querySelector("[data-dropzone]");
    const fileInput = document.getElementById("adm-add-file");
    const fileError = document.querySelector("[data-file-error]");

    const ALLOWED = ["xlsx", "xls", "csv"];

    function formatSize(bytes) {
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024)
            return `${(bytes / 1024).toFixed(1).replace(".", ",")} KB`;
        return `${(bytes / 1024 / 1024).toFixed(2).replace(".", ",")} MB`;
    }

    if (uploadForm && dropzone && fileInput) {
        const maxBytes = Number(dropzone.dataset.maxMb || 2) * 1024 * 1024;
        const emptyView = dropzone.querySelector(".adm-add-dz-empty");
        const fileView = dropzone.querySelector(".adm-add-dz-file");
        const nameEl = dropzone.querySelector(".adm-add-dz-file-name");
        const sizeEl = dropzone.querySelector(".adm-add-dz-file-size");
        const removeBtn = dropzone.querySelector(".adm-add-dz-remove");

        function showError(message) {
            if (!fileError) return;
            fileError.textContent = message;
            fileError.hidden = !message;
            dropzone.classList.toggle("has-error", !!message);
        }

        function render() {
            const file = fileInput.files[0];
            emptyView.hidden = !!file;
            fileView.hidden = !file;
            if (file) {
                nameEl.textContent = file.name;
                sizeEl.textContent = formatSize(file.size);
            }
            if (uploadBtn) uploadBtn.disabled = !file;
        }

        function clearFile() {
            fileInput.value = "";
            render();
        }

        // Cek format dan ukuran; kembalikan pesan error atau '' jika aman
        function validate(file) {
            const ext = (file.name.split(".").pop() || "").toLowerCase();
            if (!ALLOWED.includes(ext))
                return "Format file tidak didukung. Gunakan file .xlsx, .xls, atau .csv.";
            if (file.size > maxBytes)
                return `Ukuran file terlalu besar (${formatSize(file.size)}). Maksimal ${dropzone.dataset.maxMb} MB.`;
            if (file.size === 0)
                return "File kosong. Pilih file yang berisi data.";
            return "";
        }

        function acceptFile(file) {
            const message = validate(file);
            if (message) {
                clearFile();
                showError(message);
                return;
            }

            showError("");
            // Untuk tarik-lepas, file dimasukkan ke input agar ikut terkirim bersama form
            if (!fileInput.files.length || fileInput.files[0] !== file) {
                try {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    fileInput.files = transfer.files;
                } catch (err) {
                    /* browser lama: pilih lewat dialog saja */
                }
            }
            render();
        }

        // klik / keyboard membuka dialog pilih file
        dropzone.addEventListener("click", (e) => {
            if (e.target.closest(".adm-add-dz-remove")) return;
            fileInput.click();
        });
        dropzone.addEventListener("keydown", (e) => {
            if (e.target !== dropzone) return;
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                fileInput.click();
            }
        });

        fileInput.addEventListener("change", () => {
            if (fileInput.files[0]) acceptFile(fileInput.files[0]);
            else render();
        });

        removeBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            showError("");
            clearFile();
        });

        // tarik & lepas
        ["dragenter", "dragover"].forEach((type) => {
            dropzone.addEventListener(type, (e) => {
                e.preventDefault();
                dropzone.classList.add("is-drag");
            });
        });
        ["dragleave", "drop"].forEach((type) => {
            dropzone.addEventListener(type, (e) => {
                e.preventDefault();
                dropzone.classList.remove("is-drag");
            });
        });
        dropzone.addEventListener("drop", (e) => {
            const file = e.dataTransfer && e.dataTransfer.files[0];
            if (file) acceptFile(file);
        });

        render();
    }

    /* =====================================================
       6) POP-UP KONFIRMASI
          - sebelum menyimpan
          - saat ada perubahan yang belum disimpan lalu klik Batal / menu lain / Logout
       ===================================================== */
    const form = document.getElementById("adm-add-form");
    const modal = document.getElementById("adm-add-modal");
    if (!form || !modal) return;

    const titleEl = modal.querySelector(".adm-add-modal-title");
    const textEl = modal.querySelector(".adm-add-modal-text");
    const cancelBtn = document.getElementById("adm-add-modal-cancel");
    const confirmBtn = document.getElementById("adm-add-modal-confirm");
    const saveBtn = form.querySelector(".adm-add-btn--save");

    // Ringkasan isi form (tanpa token & method) untuk mendeteksi perubahan
    const snapshot = () =>
        JSON.stringify(
            Array.from(new FormData(form).entries()).filter(
                ([key]) => key !== "_token" && key !== "_method",
            ),
        );
    const initial = snapshot();
    // Kalau halaman dimuat ulang karena validasi gagal, isian dianggap belum tersimpan
    const hadErrors = !!document.querySelector(
        ".adm-add-alert, .adm-add-error:not([hidden])",
    );
    const hasFile = () =>
        !!(fileInput && fileInput.files && fileInput.files.length);
    const isDirty = () => hadErrors || snapshot() !== initial || hasFile();

    let allowLeave = false; // true saat simpan / keluar sudah dikonfirmasi
    let onConfirm = null;
    let lastFocus = null;

    function openModal(opts) {
        lastFocus = document.activeElement;
        onConfirm = opts.onConfirm;

        modal.dataset.tone = opts.tone;
        titleEl.textContent = opts.title;
        textEl.textContent = opts.text;
        cancelBtn.textContent = opts.cancelText;
        confirmBtn.textContent = opts.confirmText;
        confirmBtn.disabled = false;

        modal.hidden = false;
        document.body.style.overflow = "hidden";
        cancelBtn.focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = "";
        onConfirm = null;
        if (lastFocus && typeof lastFocus.focus === "function")
            lastFocus.focus();
    }

    // Konfirmasi keluar halaman (ada perubahan belum disimpan)
    function askLeave(proceed) {
        openModal({
            tone: "leave",
            title: "Isian belum disimpan",
            text: "Isian yang sudah kamu masukkan belum disimpan. Jika meninggalkan halaman ini, isian tersebut akan hilang.",
            cancelText: "Tetap di Halaman",
            confirmText: "Tinggalkan Halaman",
            onConfirm: () => {
                allowLeave = true;
                proceed();
            },
        });
    }

    // 1) Simpan -> cek konfirmasi password, lalu minta konfirmasi (validasi bawaan browser sudah lolos)
    form.addEventListener("submit", (e) => {
        if (allowLeave) return;
        e.preventDefault();

        if (confirmInput && confirmInput.value !== passInput.value) {
            confirmError.hidden = false;
            confirmInput.classList.add("has-error");
            confirmInput.focus();
            return;
        }

        const nama = (form.querySelector("#nama") || {}).value || "";
        openModal({
            tone: "save",
            title: "Tambahkan user baru?",
            text: nama.trim()
                ? `Akun peserta ${nama.trim()} akan dibuat dan langsung bisa dipakai untuk login.`
                : "Akun peserta baru akan dibuat dan langsung bisa dipakai untuk login.",
            cancelText: "Periksa Lagi",
            confirmText: "Ya, Tambahkan",
            onConfirm: () => {
                allowLeave = true;
                if (saveBtn) saveBtn.disabled = true;
                form.submit(); // submit() tidak memicu event submit lagi
            },
        });
    });

    // 1b) Upload Excel -> minta konfirmasi dulu
    if (uploadForm) {
        uploadForm.addEventListener("submit", (e) => {
            if (allowLeave) return;
            e.preventDefault();

            const file = fileInput && fileInput.files[0];
            if (!file) {
                if (fileError) {
                    fileError.textContent = "Pilih file Excel terlebih dahulu.";
                    fileError.hidden = false;
                }
                return;
            }

            openModal({
                tone: "save",
                title: "Upload file Excel?",
                text: `File ${file.name} akan diproses dan seluruh peserta di dalamnya ditambahkan sebagai akun baru berstatus Aktif.`,
                cancelText: "Periksa Lagi",
                confirmText: "Ya, Upload",
                onConfirm: () => {
                    allowLeave = true;
                    if (uploadBtn) uploadBtn.disabled = true;
                    uploadForm.submit();
                },
            });
        });
    }

    // 2) Klik tautan (Batal, menu sidebar, dll.) saat ada perubahan -> minta konfirmasi
    document.addEventListener(
        "click",
        (e) => {
            if (allowLeave || !isDirty()) return;
            if (
                e.defaultPrevented ||
                e.button !== 0 ||
                e.metaKey ||
                e.ctrlKey ||
                e.shiftKey ||
                e.altKey
            )
                return;

            const link = e.target.closest("a[href]");
            if (!link || link.closest("#adm-add-modal")) return;
            if (link.target && link.target !== "_self") return;

            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("javascript:"))
                return;

            e.preventDefault();
            askLeave(() => {
                window.location.href = link.href;
            });
        },
        true,
    );

    // 3) Form lain di halaman (mis. Logout) saat ada perubahan -> minta konfirmasi
    document.addEventListener(
        "submit",
        (e) => {
            if (
                e.target === form ||
                e.target === uploadForm ||
                allowLeave ||
                !isDirty()
            )
                return;
            e.preventDefault();
            const other = e.target;
            askLeave(() => other.submit());
        },
        true,
    );

    // 4) Tutup tab / muat ulang / tombol back browser (dialog bawaan browser, teksnya tidak bisa diubah)
    window.addEventListener("beforeunload", (e) => {
        if (allowLeave || !isDirty()) return;
        e.preventDefault();
        e.returnValue = "";
    });

    // Tombol & tutup pop-up
    confirmBtn.addEventListener("click", () => {
        const action = onConfirm;
        if (!action) return;
        confirmBtn.disabled = true;
        action();
    });

    cancelBtn.addEventListener("click", closeModal);
    modal
        .querySelector("[data-modal-close]")
        .addEventListener("click", closeModal);

    document.addEventListener("keydown", (e) => {
        if (modal.hidden) return;

        if (e.key === "Escape") {
            closeModal();
            return;
        }

        if (e.key === "Tab") {
            const first = cancelBtn;
            const last = confirmBtn;

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
})();
