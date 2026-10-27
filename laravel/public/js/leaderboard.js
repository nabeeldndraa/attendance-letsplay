/**
 * Pop-up "Pilih Bulan" untuk halaman Leaderboard (hanya bulan + tahun, tanpa tanggal).
 * - Nilai disimpan di input tersembunyi name="bulan" dengan format YYYY-MM.
 * - Saat bulan dipilih, form (method GET) dikirim ke halaman yang sama: ?bulan=YYYY-MM
 *   sehingga backend nanti tinggal membaca request('bulan').
 * - Bulan di masa depan tidak bisa dipilih (batas: data-max="YYYY-MM").
 */
(function () {
    const MONTHS = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    const SHORT = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];

    const pad = (n) => String(n).padStart(2, "0");
    const parse = (value) => {
        const [y, m] = String(value || "")
            .split("-")
            .map(Number);
        return y && m ? { y, m } : null;
    };

    const wraps = Array.from(document.querySelectorAll(".lb-select-wrap"));

    wraps.forEach((wrap) => {
        const btn = wrap.querySelector(".lb-select");
        const text = wrap.querySelector(".lb-select-text");
        const input = wrap.querySelector('input[name="bulan"]');
        const pop = wrap.querySelector(".lb-mp");
        const form = wrap.closest("form");

        const max = parse(wrap.dataset.max);
        const now = max || {
            y: new Date().getFullYear(),
            m: new Date().getMonth() + 1,
        };
        let selected = parse(input.value) || now;
        let viewYear = selected.y;

        const isFuture = (y, m) =>
            max && (y > max.y || (y === max.y && m > max.m));

        function render() {
            let html = `
                <div class="lb-mp-head">
                    <button type="button" class="lb-mp-nav" data-year="-1" aria-label="Tahun sebelumnya">
                        <svg aria-hidden="true"><use href="#i-chevron-left"/></svg>
                    </button>
                    <div class="lb-mp-year">${viewYear}</div>
                    <button type="button" class="lb-mp-nav" data-year="1" aria-label="Tahun berikutnya"
                        ${max && viewYear >= max.y ? "disabled" : ""}>
                        <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                    </button>
                </div>
                <div class="lb-mp-grid">`;

            SHORT.forEach((name, i) => {
                const m = i + 1;
                const cls = ["lb-mp-month"];
                if (viewYear === now.y && m === now.m) cls.push("is-current");
                if (viewYear === selected.y && m === selected.m)
                    cls.push("is-selected");
                html += `<button type="button" class="${cls.join(" ")}" data-month="${m}"
                    ${isFuture(viewYear, m) ? "disabled" : ""}>${name}</button>`;
            });

            html += `</div>
                <div class="lb-mp-foot">
                    <button type="button" class="lb-mp-today" data-today>Bulan ini</button>
                </div>`;

            pop.innerHTML = html;
        }

        function open() {
            wraps.forEach((w) => {
                if (w !== wrap) w.querySelector(".lb-mp").hidden = true;
            });
            viewYear = selected.y;
            render();
            pop.hidden = false;
            btn.setAttribute("aria-expanded", "true");
        }

        function close() {
            pop.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        }

        function choose(y, m) {
            const changed = y !== selected.y || m !== selected.m;
            selected = { y, m };
            input.value = `${y}-${pad(m)}`;
            text.textContent = `${MONTHS[m - 1]} ${y}`;
            close();
            if (changed && form) form.submit();
        }

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
            }
        });

        // Pakai composedPath: tombol di dalam pop-up diganti ulang saat render(),
        // sehingga e.target sudah lepas dari DOM dan wrap.contains() salah mengira klik di luar.
        document.addEventListener("click", (e) => {
            if (!e.composedPath().includes(wrap)) close();
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") close();
        });
    });
})();
