/* ADMIN - LEADERBOARD */
(function () {
    "use strict";

    var BODY_LOCK = "adm-lb-lock";

    function openModal(id) {
        var dlg = document.getElementById(id);
        if (!dlg) return;

        if (typeof dlg.showModal === "function") {
            dlg.showModal();
        } else {
            dlg.setAttribute("open", "");
        }
        document.body.classList.add(BODY_LOCK);
    }

    function closeModal(dlg) {
        if (!dlg) return;
        if (typeof dlg.close === "function") {
            dlg.close();
        } else {
            dlg.removeAttribute("open");
        }
        document.body.classList.remove(BODY_LOCK);
    }

    document.addEventListener("click", function (e) {
        // buka pop-up
        var opener = e.target.closest("[data-adm-lb-open]");
        if (opener) {
            openModal(opener.getAttribute("data-adm-lb-open"));
            return;
        }

        // tombol tutup
        var closer = e.target.closest("[data-adm-lb-close]");
        if (closer) {
            closeModal(closer.closest("dialog"));
            return;
        }

        // klik area gelap (backdrop) menutup pop-up
        if (
            e.target.tagName === "DIALOG" &&
            e.target.classList.contains("adm-lb-modal")
        ) {
            var r = e.target.getBoundingClientRect();
            var outside =
                e.clientX < r.left ||
                e.clientX > r.right ||
                e.clientY < r.top ||
                e.clientY > r.bottom;
            if (outside) closeModal(e.target);
        }
    });

    // lepas kunci scroll saat dialog ditutup (termasuk lewat tombol Esc)
    document.querySelectorAll(".adm-lb-modal").forEach(function (dlg) {
        dlg.addEventListener("close", function () {
            document.body.classList.remove(BODY_LOCK);
        });
    });

    /* ------------------------------------------------------
       Pop-up pilih bulan & tahun (tanpa tanggal)
       ------------------------------------------------------ */
    var cal = document.getElementById("adm-lb-modal-month");
    var trigger = document.getElementById("adm-lb-month-btn");
    var form = document.getElementById("adm-lb-month-form");
    var hidden = document.getElementById("adm-lb-bulan");
    var grid = document.getElementById("adm-lb-cal-grid");
    var yearEl = document.getElementById("adm-lb-cal-year");
    var prevBtn = document.getElementById("adm-lb-cal-prev");
    var nextBtn = document.getElementById("adm-lb-cal-next");
    var applyBtn = document.getElementById("adm-lb-cal-apply");

    if (
        cal &&
        trigger &&
        form &&
        hidden &&
        grid &&
        yearEl &&
        prevBtn &&
        nextBtn &&
        applyBtn
    ) {
        function parse(v) {
            var m = /^(\d{4})-(\d{2})$/.exec(v || "");
            return m ? { y: +m[1], m: +m[2] - 1 } : null;
        }
        function idx(o) {
            return o.y * 12 + o.m;
        }
        function pad(n) {
            return (n < 10 ? "0" : "") + n;
        }

        function monthNames(style) {
            var lang = document.documentElement.lang || "id";
            var out = [];
            var fmt;
            try {
                fmt = new Intl.DateTimeFormat(lang, { month: style });
            } catch (err) {
                fmt = new Intl.DateTimeFormat("id", { month: style });
            }
            for (var i = 0; i < 12; i++)
                out.push(fmt.format(new Date(2000, i, 1)));
            return out;
        }

        var shortNames = monthNames("short");
        var longNames = monthNames("long");

        var now = new Date();
        var today = { y: now.getFullYear(), m: now.getMonth() };
        var current = parse(cal.getAttribute("data-value")) || today;
        var min = parse(cal.getAttribute("data-min"));
        var max = parse(cal.getAttribute("data-max"));

        var viewYear = current.y;
        var picked = current;

        function render() {
            yearEl.textContent = viewYear;
            prevBtn.disabled = !!min && viewYear <= min.y;
            nextBtn.disabled = !!max && viewYear >= max.y;
            applyBtn.disabled = !picked;

            grid.innerHTML = "";

            for (var i = 0; i < 12; i++) {
                (function (i) {
                    var cell = { y: viewYear, m: i };
                    var btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "adm-lb-cal-m";
                    btn.textContent = shortNames[i];
                    btn.setAttribute(
                        "aria-label",
                        longNames[i] + " " + viewYear,
                    );

                    if (cell.y === today.y && cell.m === today.m)
                        btn.classList.add("is-now");

                    var off =
                        (min && idx(cell) < idx(min)) ||
                        (max && idx(cell) > idx(max));
                    btn.disabled = !!off;

                    var on =
                        !!picked && picked.y === cell.y && picked.m === cell.m;
                    btn.setAttribute("aria-pressed", on ? "true" : "false");

                    btn.addEventListener("click", function () {
                        picked = cell;
                        render();
                    });

                    grid.appendChild(btn);
                })(i);
            }
        }

        // tiap dibuka: mulai dari bulan yang sedang aktif
        trigger.addEventListener("click", function () {
            picked = current;
            viewYear = current.y;
            render();
        });

        prevBtn.addEventListener("click", function () {
            viewYear--;
            render();
        });
        nextBtn.addEventListener("click", function () {
            viewYear++;
            render();
        });

        applyBtn.addEventListener("click", function () {
            if (!picked) return;

            var value = picked.y + "-" + pad(picked.m + 1);
            if (value === hidden.value) {
                closeModal(cal);
                return;
            }

            hidden.value = value;
            form.submit();
        });

        render();
    }
})();
