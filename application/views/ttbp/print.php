<?php
$sheets = !empty($sheets) ? $sheets : [$ttbp];
$pages  = array_chunk($sheets, 2);
$ttbp2_param = isset($ttbp2_param) ? $ttbp2_param : 'none';
$next_nomor  = isset($next_nomor) ? $next_nomor : null;
$ttbp_options = !empty($ttbp_options) ? $ttbp_options : [];
$printers = !empty($printers) ? $printers : [];
$default_lx310 = !empty($default_lx310) ? $default_lx310 : '';

// Daftar nomor TTBP yang sedang aktif di layar untuk tombol Cetak Langsung LX-310
$active_nomors = [];
foreach ($sheets as $s) {
    $active_nomors[] = $s['nomor_ttbp'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak TTBP - <?= htmlspecialchars($ttbp['nomor_ttbp']); ?> - <?= htmlspecialchars($ttbp['nama_kios']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ==========================================================================
           KONFIGURASI CETAK KERTAS CF K4 W PRS (9.5 x 11 INCH — PORTRAIT)
           - Lebar total dgn lubang traktor = 9.5 inch (241.3 mm)
           - Lebar bersih kertas (antara perforasi traktor kiri-kanan) = 8.5 inch (215.9 mm)
           - Tinggi 1 kertas penuh = 11 inch (279.4 mm) -> Bagi 2 (5.5 inch / 139.7 mm per TTBP)
           - Orientasi: PORTRAIT (1 Kertas memuat 2 TTBP: Slot Atas & Slot Bawah)
           ========================================================================== */
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
            padding: 8px 18px;
            z-index: 1050;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .print-canvas {
            margin-top: 72px;
            padding: 18px 10px 45px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        /* Simulasi 1 Lembar Kertas CF K4 W PRS (9.5 x 11 inch Portrait) di Layar */
        .cf-portrait-page {
            width: 215.9mm;
            min-height: 279.4mm;
            background: #ffffff;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.18);
            /* Margin aman sesuai PrintableArea hardware Epson LX-310 (OriginLeft=3.17mm, RightMargin=9.74mm, OriginTop=4.23mm) */
            padding: 4.5mm 10mm 4.5mm 3.5mm;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .cf-slot {
            width: 100%;
            height: 131.5mm;
            box-sizing: border-box;
            display: flex;
             flex-direction: column;
            justify-content: flex-start;
        }

        .cf-slot .ttbp-sheet-container {
            padding: 0 !important;
            background: transparent !important;
            border-radius: 0 !important;
            width: 100% !important;
            overflow: visible !important;
        }

        .cf-slot .ttbp-sheet {
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
        }

        /* Garis Lipat / Perforasi Tengah Kertas 5.5 inch (139.7 mm) */
        .cf-perforation-divider {
            width: 100%;
            height: 7.4mm;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-sizing: border-box;
        }

        .cf-perforation-line {
            width: 100%;
            border-top: 1.5px dashed #94a3b8;
            position: relative;
            text-align: center;
        }

        .cf-perforation-badge {
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            padding: 0 8px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            white-space: nowrap;
        }

        /* Placeholder Slot Bawah jika hanya mencetak 1 TTBP */
        .cf-slot-empty {
            width: 100%;
            height: 131.5mm;
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
        }

        @media print {
            html, body {
                width: 100% !important;
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print, .print-toolbar, .cf-perforation-badge, .cf-slot-empty {
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
                /* Padding aman agar garis kiri & kanan berada 100% di dalam PrintableArea Epson LX-310 */
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
                height: 131.5mm !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .cf-perforation-divider {
                height: 7.4mm !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR KONTROL CETAK CF K4 W PRS (9.5 x 11 INCH PORTRAIT) -->
    <div class="print-toolbar no-print">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="<?= site_url('ttbp'); ?>" class="btn btn-outline-secondary btn-sm px-2 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                <?= count($sheets) > 1 ? (count($sheets) . ' TTBP (' . count($pages) . ' Kertas)') : ('TTBP #' . htmlspecialchars($ttbp['nomor_ttbp'])); ?>
            </span>
            <span class="badge bg-light text-dark border d-none d-xl-inline" style="font-size: 0.72rem;">
                <i class="bi bi-printer me-1 text-primary"></i> CF K4 W PRS 9.5 x 11" (Portrait · 1 Kertas = 2 TTBP)
            </span>

            <?php if (count($sheets) <= 2 && empty($_GET['ids'])): ?>
                <!-- Pemilih Slot Bawah (1 Kertas = 2 TTBP Portrait) -->
                <div class="input-group input-group-sm ms-md-2" style="width: auto; max-width: 340px;">
                    <span class="input-group-text bg-light fw-semibold text-secondary" style="font-size: 0.75rem;">
                        <i class="bi bi-layout-split me-1"></i> Slot Bawah:
                    </span>
                    <select id="select-slot-bawah" class="form-select form-select-sm" style="font-size: 0.78rem;" onchange="changeSlotBawah(this.value)">
                        <option value="none" <?= ($ttbp2_param === 'none') ? 'selected' : ''; ?>>Kosong (Cetak 1 TTBP di Atas)</option>
                        <?php if (!empty($next_nomor)): ?>
                            <option value="next" <?= ($ttbp2_param === 'next' || $ttbp2_param === $next_nomor) ? 'selected' : ''; ?>>
                                Otomatis Berikutnya (<?= htmlspecialchars($next_nomor); ?>)
                            </option>
                        <?php endif; ?>
                        <option value="copy" <?= ($ttbp2_param === 'copy') ? 'selected' : ''; ?>>
                            Gandakan TTBP Ini (2x Atas & Bawah)
                        </option>
                        <optgroup label="Pilih Nomor TTBP Lain:">
                            <?php foreach ($ttbp_options as $opt): ?>
                                <?php if ($opt['nomor_ttbp'] === $ttbp['nomor_ttbp']) continue; ?>
                                <option value="<?= htmlspecialchars($opt['nomor_ttbp']); ?>" <?= ($ttbp2_param === $opt['nomor_ttbp']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($opt['nomor_ttbp'] . ' - ' . mb_strimwidth($opt['nama_pembeli'], 0, 22, '..')); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <!-- Pilihan Printer untuk Cetak Langsung LX-310 -->
            <div class="input-group input-group-sm" style="width: auto;">
                <span class="input-group-text bg-light text-secondary" style="font-size: 0.75rem;" title="Target Printer Dot Matrix">
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

            <!-- Tombol Cetak Langsung ke Epson LX-310 (GDI+ Native 100% Lebar Penuh & Tanpa Garis Hilang) -->
            <button type="button" id="btn-direct-lx310" class="btn btn-sm btn-primary px-3 fw-semibold shadow-sm" onclick="printDirectLx310()" title="Cetak langsung ke Epson LX-310 dengan lebar penuh 100% tanpa terpotong 2/3">
                <i class="bi bi-printer-fill me-1"></i> Langsung Cetak (LX-310)
            </button>

            <!-- Tombol Dialog Print Browser -->
            <button type="button" class="btn btn-sm btn-outline-dark px-2 shadow-sm" onclick="window.print()" title="Buka Dialog Print Browser (Ctrl+P)">
                <i class="bi bi-window me-1"></i> Dialog Browser
            </button>

            <a href="<?= site_url('ttbp/export_excel/' . urlencode($ttbp['nomor_ttbp'])); ?>" class="btn btn-sm btn-outline-success px-2 shadow-sm" title="Unduh File Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </div>
    </div>

    <!-- CANVAS SIMULASI KERTAS CF K4 W PRS (9.5 x 11 INCH PORTRAIT) -->
    <div class="print-canvas">
        <?php foreach ($pages as $page_idx => $page_pair): ?>
            <div class="cf-portrait-page">
                <!-- SLOT 1: SEPARUH ATAS (0 - 5.5 INCH / 0 - 139.7 MM) -->
                <div class="cf-slot">
                    <?php $this->load->view('ttbp/preview_partial', ['ttbp' => $page_pair[0], 'fixed_rows' => 6]); ?>
                </div>

                <!-- GARIS PERFORASI TENGAH KERTAS (5.5 INCH / 139.7 MM) -->
                <div class="cf-perforation-divider">
                    <div class="cf-perforation-line">
                        <span class="cf-perforation-badge no-print">
                            <i class="bi bi-scissors me-1"></i> Garis Potong Tengah Kertas CF K4 W PRS (5.5 inch / 139.7 mm)
                        </span>
                    </div>
                </div>

                <!-- SLOT 2: SEPARUH BAWAH (5.5 - 11 INCH / 139.7 - 279.4 MM) -->
                <?php if (isset($page_pair[1]) && !empty($page_pair[1])): ?>
                    <div class="cf-slot">
                        <?php $this->load->view('ttbp/preview_partial', ['ttbp' => $page_pair[1], 'fixed_rows' => 6]); ?>
                    </div>
                <?php else: ?>
                    <div class="cf-slot-empty no-print">
                        <i class="bi bi-file-earmark-plus fs-3 text-secondary mb-2"></i>
                        <div class="fw-semibold text-dark mb-1" style="font-size: 0.9rem;">
                            Slot Separuh Bawah Kertas (5.5" – 11") Masih Kosong
                        </div>
                        <div class="small text-muted mb-3" style="max-width: 440px;">
                            Kertas <strong>CF K4 W PRS (9.5 x 11 inch Portrait)</strong> dapat memuat <strong>2 TTBP sekaligus (Atas & Bawah)</strong>. Pilih TTBP ke-2 jika ingin mengisi penuh 1 lembar kertas utuh:
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <?php if (!empty($next_nomor)): ?>
                                <button type="button" class="btn btn-sm btn-primary px-3" onclick="changeSlotBawah('next')">
                                    <i class="bi bi-plus-circle me-1"></i> Isi dgn TTBP Berikutnya (<?= htmlspecialchars($next_nomor); ?>)
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="changeSlotBawah('copy')">
                                <i class="bi bi-files me-1"></i> Gandakan TTBP #<?= htmlspecialchars($ttbp['nomor_ttbp']); ?> (2x Atas-Bawah)
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
        // Hapus sisa kalibrasi lama yang dulu membatasi lebar kertas
        try { localStorage.removeItem('ttbp_printer_calibration_k4'); } catch(e) {}

        var activeNomors = <?= json_encode($active_nomors); ?>;

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

        function showToast(msg, bgClass) {
            var el = document.getElementById('printToast');
            var msgEl = document.getElementById('printToastMsg');
            el.className = 'toast align-items-center border-0 shadow ' + (bgClass || 'text-bg-dark');
            msgEl.innerHTML = msg;
            var t = bootstrap.Toast.getOrCreateInstance(el, { delay: 4500 });
            t.show();
        }

        function printDirectLx310() {
            var btn = document.getElementById('btn-direct-lx310');
            var printer = document.getElementById('select-lx310-printer').value;
            var origHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mencetak ke LX-310...';
            showToast('<i class="bi bi-printer me-1"></i> Mengirim dokumen TTBP ke printer <strong>' + (printer || 'Epson LX-310') + '</strong>...', 'text-bg-primary');

            var formData = new URLSearchParams();
            for (var i = 0; i < activeNomors.length; i++) {
                formData.append('nomors[]', activeNomors[i]);
            }
            formData.append('printer', printer);

            fetch('<?= site_url("ttbp/print_direct_lx310_ajax"); ?>', {
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
                } else {
                    showToast('<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (res.message || 'Gagal mencetak.'), 'text-bg-danger');
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                showToast('<i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal menghubungi server cetak.', 'text-bg-danger');
            });
        }
    </script>
</body>
</html>
