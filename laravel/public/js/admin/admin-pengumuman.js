/* ADMIN: PENGUMUMAN */
(function () {
    'use strict';

    // Urutan berubah -> kirim form filter otomatis
    var urut = document.getElementById('pgmUrut');
    var filter = document.getElementById('pgmFilter');
    if (urut && filter) {
        urut.addEventListener('change', function () { filter.submit(); });
    }

    // Cegah kirim ganda & tolak isi kosong (spasi saja)
    var form = document.getElementById('pgmForm');
    var isi = document.getElementById('pgmIsi');
    var kirim = document.getElementById('pgmKirim');
    if (form && isi && kirim) {
        form.addEventListener('submit', function (e) {
            if (isi.value.trim() === '') {
                e.preventDefault();
                isi.focus();
                return;
            }
            kirim.disabled = true;
        });
    }
})();
