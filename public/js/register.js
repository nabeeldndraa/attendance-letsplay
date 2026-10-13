/**
 * Date picker kustom untuk halaman register.
 * - Input yang terlihat (.dp-input) hanya untuk tampilan, mis. "13 Okt 2026".
 * - Input tersembunyi (.dp-value) menyimpan format Y-m-d dan itulah yang dikirim ke backend.
 * - Tanggal selesai (data-min="tanggal_mulai") tidak bisa lebih awal dari tanggal mulai.
 */
(function () {
    const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const DOW = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    const pad = (n) => String(n).padStart(2, '0');
    const toISO = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const fromISO = (s) => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s || '')) return null;
        const [y, m, d] = s.split('-').map(Number);
        return new Date(y, m - 1, d);
    };
    const format = (d) => `${d.getDate()} ${SHORT[d.getMonth()]} ${d.getFullYear()}`;
    const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate());

    const pickers = [];

    function closeAll(except) {
        pickers.forEach((p) => { if (p !== except) p.close(); });
    }

    document.querySelectorAll('.datepicker').forEach((wrap) => {
        const input = wrap.querySelector('.dp-input');
        const hidden = wrap.querySelector('.dp-value');
        const minEl = wrap.dataset.min ? document.getElementById(wrap.dataset.min) : null;

        const pop = document.createElement('div');
        pop.className = 'dp-pop';
        pop.hidden = true;
        wrap.appendChild(pop);

        let view = new Date();
        view = new Date(view.getFullYear(), view.getMonth(), 1);

        const getMin = () => (minEl ? fromISO(minEl.value) : null);
        const getValue = () => fromISO(hidden.value);

        function setValue(d) {
            hidden.value = d ? toISO(d) : '';
            input.value = d ? format(d) : '';
            hidden.dispatchEvent(new Event('change'));
        }

        function render() {
            const selected = getValue();
            const min = getMin();
            const today = startOfDay(new Date());
            const y = view.getFullYear();
            const m = view.getMonth();
            const offset = (new Date(y, m, 1).getDay() + 6) % 7; // minggu dimulai Senin
            const total = new Date(y, m + 1, 0).getDate();

            let html = `
                <div class="dp-head">
                    <button type="button" class="dp-nav" data-nav="-1" aria-label="Bulan sebelumnya">&lsaquo;</button>
                    <div class="dp-title">${MONTHS[m]} ${y}</div>
                    <button type="button" class="dp-nav" data-nav="1" aria-label="Bulan berikutnya">&rsaquo;</button>
                </div>
                <div class="dp-grid">
                    ${DOW.map((d) => `<div class="dp-dow">${d}</div>`).join('')}
                    ${'<div class="dp-empty"></div>'.repeat(offset)}`;

            for (let day = 1; day <= total; day++) {
                const date = new Date(y, m, day);
                const cls = ['dp-day'];
                if (date.getTime() === today.getTime()) cls.push('today');
                if (selected && date.getTime() === selected.getTime()) cls.push('selected');
                const disabled = min && date < min ? 'disabled' : '';
                html += `<button type="button" class="${cls.join(' ')}" data-day="${day}" ${disabled}>${day}</button>`;
            }

            html += `</div>
                <div class="dp-foot">
                    <button type="button" class="dp-link" data-action="today">Hari ini</button>
                    <button type="button" class="dp-link" data-action="clear">Hapus</button>
                </div>`;

            pop.innerHTML = html;
        }

        function open() {
            closeAll(api);
            const base = getValue() || getMin() || new Date();
            view = new Date(base.getFullYear(), base.getMonth(), 1);
            render();
            pop.hidden = false;
        }

        function close() {
            pop.hidden = true;
        }

        const api = { close, wrap };
        pickers.push(api);

        // Klik di dalam popup
        pop.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            if (btn.dataset.nav) {
                view = new Date(view.getFullYear(), view.getMonth() + Number(btn.dataset.nav), 1);
                render();
            } else if (btn.dataset.day) {
                setValue(new Date(view.getFullYear(), view.getMonth(), Number(btn.dataset.day)));
                close();
            } else if (btn.dataset.action === 'today') {
                const today = startOfDay(new Date());
                const min = getMin();
                if (!min || today >= min) {
                    setValue(today);
                    close();
                }
            } else if (btn.dataset.action === 'clear') {
                setValue(null);
                close();
            }
        });

        // Input hanya bisa diisi lewat popup (tidak diketik manual)
        input.addEventListener('click', open);
        input.addEventListener('focus', open);
        input.addEventListener('paste', (e) => e.preventDefault());
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') return;
            if (e.key === 'Escape') { close(); return; }
            e.preventDefault();
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') open();
        });

        // Kalau tanggal mulai berubah dan melewati tanggal selesai, kosongkan tanggal selesai
        if (minEl) {
            minEl.addEventListener('change', () => {
                const min = getMin();
                const current = getValue();
                if (min && current && current < min) setValue(null);
            });
        }

        // Tampilkan nilai lama (old input) setelah validasi backend gagal
        const initial = getValue();
        if (initial) input.value = format(initial);
    });

    // Klik di luar popup menutup popup
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.datepicker')) closeAll();
    });
})();
