<?php
$sheets = !empty($sheets) ? $sheets : [$ttbp];
$pages  = array_chunk($sheets, 2);
$ttbp2_param = isset($ttbp2_param) ? $ttbp2_param : 'none';
$next_nomor  = isset($next_nomor) ? $next_nomor : null;
$ttbp_options = !empty($ttbp_options) ? $ttbp_options : [];
$printers = !empty($printers) ? $printers : [];
$default_lx310 = !empty($default_lx310) ? $default_lx310 : '';
$lx310_direct_supported = !empty($lx310_direct_supported);

$active_nomors = [];
foreach ($sheets as $s) {
    $active_nomors[] = $s['nomor_ttbp'];
}

// Label aktif pada tombol pemilih Slot Bawah
$slot_bawah_label = 'Kosong';
if ($ttbp2_param === 'copy') {
    $slot_bawah_label = 'Gandakan (' . $ttbp['nomor_ttbp'] . ')';
} elseif ($ttbp2_param === 'next' && !empty($next_nomor)) {
    $slot_bawah_label = 'Berikutnya (' . $next_nomor . ')';
} elseif ($ttbp2_param !== 'none' && isset($sheets[1])) {
    $slot_bawah_label = $sheets[1]['nomor_ttbp'] . ' - ' . mb_strimwidth($sheets[1]['nama_kios'], 0, 18, '..');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak TTBP - <?= htmlspecialchars($ttbp['nomor_ttbp']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @page {
            size: 8.5in 11in;
            margin: 0mm;
        }

        body {
            background-color: #dde2e8;
            margin: 0;
            padding: 0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #000;
        }

        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
            padding: 8px 18px;
            z-index: 1050;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .print-canvas {
            margin-top: 64px;
            padding: 20px 12px 45px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        /* Simulasi Kertas di Layar (Simetris & 100% Center) */
        .cf-portrait-page {
            width: 215.9mm;
            min-height: 279.4mm;
            background: #ffffff;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.16);
            padding: 5mm 6.75mm;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            margin: 0 auto;
        }

        .cf-slot {
            width: 100%;
            min-height: 129mm;
            height: auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .cf-slot .ttbp-sheet-container {
            padding: 0 !important;
            margin: 0 auto !important;
            background: transparent !important;
            border-radius: 0 !important;
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
            overflow: visible !important;
        }

        .cf-slot .ttbp-sheet {
            width: 202.4mm !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            box-shadow: none !important;
        }

        /* Garis Potong Tengah Kertas */
        .cf-perforation-divider {
            width: 100%;
            height: 7.4mm;
            margin: 1.5mm 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-sizing: border-box;
        }

        .cf-perforation-line {
            width: 100%;
            border-top: 1.5px dashed #cbd5e1;
            position: relative;
        }

        /* Placeholder Slot Bawah */
        .cf-slot-empty {
            width: 202.4mm;
            max-width: 100%;
            min-height: 129mm;
            border: 2px dashed #cbd5e1;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #64748b;
            padding: 20px;
            text-align: center;
            box-sizing: border-box;
            margin: 0 auto;
        }

        /* Dropdown Search Slot Bawah */
        .slot-search-dropdown {
            width: 320px;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16);
            border: 1px solid #e2e8f0;
        }
        .slot-search-list {
            max-height: 240px;
            overflow-y: auto;
            margin-top: 6px;
        }
        .slot-search-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            text-align: left;
            padding: 6px 10px;
            border: none;
            background: transparent;
            border-radius: 5px;
            font-size: 0.78rem;
            color: #1e293b;
            cursor: pointer;
            transition: background 0.12s;
        }
        .slot-search-item:hover, .slot-search-item.active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        @media print {
            html, body {
                width: 100% !important;
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print, .print-toolbar, .cf-slot-empty {
                display: none !important;
            }
            .cf-perforation-line {
                border-top: none !important;
            }
            .print-canvas {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }
            .cf-portrait-page {
                width: 100% !important;
                min-height: 0 !important;
                height: auto !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 4.5mm 10mm 4.5mm 3.5mm !important;
                box-sizing: border-box !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .cf-portrait-page:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            .cf-slot {
                width: 100% !important;
                min-height: 0 !important;
                height: 131.5mm !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .cf-slot .ttbp-sheet {
                width: 100% !important;
            }
            .cf-perforation-divider {
                height: 7.4mm !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR KONTROL CETAK -->
    <div class="print-toolbar no-print">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="<?= site_url('ttbp'); ?>" class="btn btn-outline-secondary btn-sm px-2 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                <?= count($sheets) > 1 ? (count($sheets) . ' TTBP') : htmlspecialchars($ttbp['nomor_ttbp']); ?>
            </span>

            <?php if (count($sheets) <= 2 && empty($_GET['ids'])): ?>
                <!-- Pemilih Slot Bawah dengan Fitur Search TTBP -->
                <div class="dropdown ms-md-1">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2 shadow-sm"
                            type="button" id="btnSlotBawahDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                            style="font-size: 0.78rem;">
                        <span class="text-secondary fw-semibold"><i class="bi bi-layout-split me-1"></i>Slot Bawah:</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($slot_bawah_label); ?></span>
                    </button>
                    <div class="dropdown-menu slot-search-dropdown" aria-labelledby="btnSlotBawahDropdown">
                        <!-- Opsi Cepat -->
                        <div class="d-flex flex-column gap-1 pb-2 mb-2 border-bottom">
                            <button type="button" class="slot-search-item <?= ($ttbp2_param === 'none') ? 'active fw-bold' : ''; ?>" onclick="changeSlotBawah('none')">
                                <span><i class="bi bi-dash-circle me-2 text-secondary"></i>Kosong</span>
                            </button>
                            <?php if (!empty($next_nomor)): ?>
                                <button type="button" class="slot-search-item <?= ($ttbp2_param === 'next' || $ttbp2_param === $next_nomor) ? 'active fw-bold' : ''; ?>" onclick="changeSlotBawah('next')">
                                    <span><i class="bi bi-arrow-down-circle me-2 text-primary"></i>Berikutnya (<?= htmlspecialchars($next_nomor); ?>)</span>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="slot-search-item <?= ($ttbp2_param === 'copy') ? 'active fw-bold' : ''; ?>" onclick="changeSlotBawah('copy')">
                                <span><i class="bi bi-files me-2 text-success"></i>Gandakan TTBP Ini</span>
                            </button>
                        </div>

                        <!-- Input Search TTBP -->
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="input-search-slot" class="form-control border-start-0"
                                   placeholder="Cari No. TTBP / Nama Kios..." autocomplete="off" style="font-size: 0.78rem;">
                        </div>

                        <!-- Daftar Hasil Pencarian TTBP -->
                        <div id="slot-search-results" class="slot-search-list">
                            <?php foreach ($ttbp_options as $opt): ?>
                                <?php if ($opt['nomor_ttbp'] === $ttbp['nomor_ttbp']) continue; ?>
                                <button type="button" class="slot-search-item <?= ($ttbp2_param === $opt['nomor_ttbp']) ? 'active fw-bold' : ''; ?>"
                                        onclick="changeSlotBawah('<?= htmlspecialchars($opt['nomor_ttbp']); ?>')">
                                    <span class="font-monospace fw-bold text-primary me-2"><?= htmlspecialchars($opt['nomor_ttbp']); ?></span>
                                    <span class="text-truncate flex-grow-1 text-muted"><?= htmlspecialchars($opt['nama_pembeli']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <!-- Pilihan Printer -->
            <div id="printer-selector-group" class="input-group input-group-sm <?= !$lx310_direct_supported ? 'd-none' : ''; ?>" style="width: auto;">
                <span class="input-group-text bg-light text-secondary" style="font-size: 0.75rem;">
                    <i class="bi bi-hdd-network"></i>
                </span>
                <select id="select-lx310-printer" class="form-select form-select-sm" style="font-size: 0.76rem; max-width: 210px;">
                    <?php if (empty($printers)): ?>
                        <option value="">Default Printer</option>
                    <?php else: ?>
                        <?php foreach ($printers as $p): ?>
                            <option value="<?= htmlspecialchars($p); ?>" <?= ($p === $default_lx310) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($p); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="button" id="btn-direct-lx310" class="btn btn-sm btn-primary px-3 fw-semibold shadow-sm" onclick="printDirectLx310()">
                <i class="bi bi-printer-fill me-1"></i> Cetak
            </button>

            <button type="button" id="btn-browser-print" class="btn btn-sm btn-outline-dark px-2 shadow-sm <?= !$lx310_direct_supported ? 'd-none' : ''; ?>" onclick="window.print()">
                <i class="bi bi-window me-1"></i> Dialog Browser
            </button>

            <a href="<?= site_url('ttbp/export_excel/' . urlencode($ttbp['nomor_ttbp'])); ?>" class="btn btn-sm btn-outline-success px-2 shadow-sm" title="Unduh Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </div>
    </div>

    <!-- CANVAS SIMULASI KERTAS -->
    <div class="print-canvas">
        <?php foreach ($pages as $page_idx => $page_pair): ?>
            <div class="cf-portrait-page">
                <!-- SLOT ATAS -->
                <div class="cf-slot">
                    <?php $this->load->view('ttbp/preview_partial', ['ttbp' => $page_pair[0], 'fixed_rows' => 6]); ?>
                </div>

                <!-- GARIS POTONG TENGAH -->
                <div class="cf-perforation-divider">
                    <div class="cf-perforation-line"></div>
                </div>

                <!-- SLOT BAWAH -->
                <?php if (isset($page_pair[1]) && !empty($page_pair[1])): ?>
                    <div class="cf-slot">
                        <?php $this->load->view('ttbp/preview_partial', ['ttbp' => $page_pair[1], 'fixed_rows' => 6]); ?>
                    </div>
                <?php else: ?>
                    <div class="cf-slot-empty no-print">
                        <div class="fw-semibold text-secondary mb-2" style="font-size: 0.85rem;">
                            Slot Bawah Kosong
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <?php if (!empty($next_nomor)): ?>
                                <button type="button" class="btn btn-sm btn-primary px-3" onclick="changeSlotBawah('next')">
                                    <i class="bi bi-plus-circle me-1"></i> TTBP Berikutnya (<?= htmlspecialchars($next_nomor); ?>)
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="changeSlotBawah('copy')">
                                <i class="bi bi-files me-1"></i> Gandakan (<?= htmlspecialchars($ttbp['nomor_ttbp']); ?>)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-dark px-3" onclick="openSlotSearchDropdown()">
                                <i class="bi bi-search me-1"></i> Cari TTBP Lain
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Toast Notifikasi Status Cetak -->
    <div class="position-fixed bottom-0 end-0 p-3 no-print" style="z-index: 1090;">
        <div id="printToast" class="toast align-items-center text-bg-dark border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="printToastMsg">Mengirim cetakan ke printer...</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        try { localStorage.removeItem('ttbp_printer_calibration_k4'); } catch(e) {}

        var activeNomors = <?= json_encode($active_nomors); ?>;
        var activeSheetsData = <?= json_encode(array_values($sheets)); ?>;
        var serverLx310Supported = <?= json_encode($lx310_direct_supported); ?>;
        var localBridgeUrl = null;
        var currentPrimaryNomor = <?= json_encode($ttbp['nomor_ttbp']); ?>;
        var currentSelectedSlot2 = <?= json_encode($ttbp2_param); ?>;
        var searchTimer = null;

        // Jika diakses dari Cloud (karismaerp.com), cek apakah bridge XAMPP lokal aktif di PC user
        if (!serverLx310Supported) {
            fetch('http://localhost/TTBP/lx310_bridge.php?action=printers')
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res && res.status && res.supported) {
                        localBridgeUrl = 'http://localhost/TTBP/lx310_bridge.php?action=print';
                        var sel = document.getElementById('select-lx310-printer');
                        if (sel && res.printers && res.printers.length > 0) {
                            var opts = '';
                            for (var i = 0; i < res.printers.length; i++) {
                                var p = res.printers[i];
                                var isSel = (p === res.default_lx310) ? 'selected' : '';
                                opts += '<option value="' + escapeHtml(p) + '" ' + isSel + '>' + escapeHtml(p) + '</option>';
                            }
                            sel.innerHTML = opts;
                        }
                        document.getElementById('printer-selector-group').classList.remove('d-none');
                        document.getElementById('btn-browser-print').classList.remove('d-none');
                    }
                })
                .catch(function() {
                    // Bridge lokal tidak aktif -> gunakan dialog print browser saat klik Cetak
                });
        }

        <?php if (!empty($_GET['autoprint'])): ?>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 350);
        });
        <?php endif; ?>

        function changeSlotBawah(val) {
            var url = new URL(window.location.href);
            url.searchParams.delete('autoprint');
            if (!val || val === 'none') {
                url.searchParams.delete('ttbp2');
            } else {
                url.searchParams.set('ttbp2', val);
            }
            window.location.href = url.toString();
        }

        function openSlotSearchDropdown() {
            var btn = document.getElementById('btnSlotBawahDropdown');
            if (!btn) return;
            var dd = bootstrap.Dropdown.getOrCreateInstance(btn);
            dd.show();
            setTimeout(function() {
                var inp = document.getElementById('input-search-slot');
                if (inp) inp.focus();
            }, 120);
        }

        // Fokus otomatis ke kotak search saat dropdown dibuka
        var btnDropdownEl = document.getElementById('btnSlotBawahDropdown');
        if (btnDropdownEl) {
            btnDropdownEl.addEventListener('shown.bs.dropdown', function() {
                var inp = document.getElementById('input-search-slot');
                if (inp) inp.focus();
            });
        }

        // Live AJAX Search untuk seluruh Nomor TTBP / Nama Kios (tanpa batas 150)
        var searchInputEl = document.getElementById('input-search-slot');
        if (searchInputEl) {
            searchInputEl.addEventListener('input', function() {
                var q = this.value.trim();
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    var box = document.getElementById('slot-search-results');
                    box.innerHTML = '<div class="text-center text-muted py-2 small"><span class="spinner-border spinner-border-sm me-1"></span>Mencari...</div>';

                    var url = '<?= site_url("ttbp/search_ttbp_slot_ajax"); ?>?q=' + encodeURIComponent(q) + '&exclude=' + encodeURIComponent(currentPrimaryNomor);
                    fetch(url)
                        .then(function(r) { return r.json(); })
                        .then(function(res) {
                            if (!res || !res.items || res.items.length === 0) {
                                box.innerHTML = '<div class="text-center text-muted py-2 small">Tidak ditemukan</div>';
                                return;
                            }
                            var html = '';
                            for (var i = 0; i < res.items.length; i++) {
                                var it = res.items[i];
                                var isAct = (it.nomor_ttbp === currentSelectedSlot2) ? 'active fw-bold' : '';
                                html += '<button type="button" class="slot-search-item ' + isAct + '" onclick="changeSlotBawah(\'' + it.nomor_ttbp.replace(/'/g, "\\'") + '\')">'
                                     +  '<span class="font-monospace fw-bold text-primary me-2">' + escapeHtml(it.nomor_ttbp) + '</span>'
                                     +  '<span class="text-truncate flex-grow-1 text-muted">' + escapeHtml(it.nama_pembeli) + '</span>'
                                     +  '</button>';
                            }
                            box.innerHTML = html;
                        })
                        .catch(function() {
                            box.innerHTML = '<div class="text-center text-danger py-2 small">Gagal memuat data</div>';
                        });
                }, 220);
            });
        }

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function showToast(msg, bgClass) {
            var el = document.getElementById('printToast');
            var msgEl = document.getElementById('printToastMsg');
            el.className = 'toast align-items-center border-0 shadow ' + (bgClass || 'text-bg-dark');
            msgEl.innerHTML = msg;
            var t = bootstrap.Toast.getOrCreateInstance(el, { delay: 4500 });
            t.show();
        }

        function printDirectLx310() {
            // Jika di Cloud dan bridge lokal tidak aktif, langsung buka dialog print browser
            if (!serverLx310Supported && !localBridgeUrl) {
                window.print();
                return;
            }

            var btn = document.getElementById('btn-direct-lx310');
            var printer = document.getElementById('select-lx310-printer').value;
            var origHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mencetak...';
            showToast('<i class="bi bi-printer me-1"></i> Mengirim dokumen ke printer <strong>' + (printer || 'Epson LX-310') + '</strong>...', 'text-bg-primary');

            var targetUrl = serverLx310Supported ? '<?= site_url("ttbp/print_direct_lx310_ajax"); ?>' : localBridgeUrl;
            var formData = new URLSearchParams();
            if (serverLx310Supported) {
                for (var i = 0; i < activeNomors.length; i++) {
                    formData.append('nomors[]', activeNomors[i]);
                }
            } else {
                formData.append('sheets_json', JSON.stringify(activeSheetsData));
            }
            formData.append('printer', printer);

            fetch(targetUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (res && res.status) {
                    showToast('<i class="bi bi-check-circle-fill me-1"></i> ' + res.message, 'text-bg-success');
                } else if (res && res.fallback_print) {
                    window.print();
                } else {
                    showToast('<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (res.message || 'Gagal mencetak.'), 'text-bg-danger');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                window.print();
            });
        }
    </script>
</body>
</html>
