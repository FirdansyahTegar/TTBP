<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak TTBP - <?= htmlspecialchars($ttbp['nomor_ttbp']); ?> - <?= htmlspecialchars($ttbp['nama_kios']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Standar Page Size untuk Continuous Form CF K4 W PRS (Kuarto/Half-letter standard) */
        @page {
            size: 215mm 140mm; /* Standar Continuous Form K4 / Kuarto Bagi 2 */
            margin: 0mm;
        }

        body {
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif, Arial;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            padding: 10px 20px;
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .print-canvas {
            margin-top: 75px;
            padding: 15px 10px 40px;
            display: flex;
            justify-content: center;
        }

        /* Calibration Controls Panel */
        #calibration-panel {
            display: none;
            position: fixed;
            top: 65px;
            right: 20px;
            width: 320px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.18);
            border: 1px solid #dee2e6;
            padding: 15px;
            z-index: 1060;
        }

        @media print {
            .no-print, .print-toolbar, #calibration-panel {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
            .print-canvas {
                margin: 0 !important;
                padding: 0 !important;
            }
            .ttbp-sheet-container {
                padding: 0 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR ATAS -->
    <div class="print-toolbar no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('ttbp'); ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <span class="fw-semibold text-dark ms-2"><?= htmlspecialchars($ttbp['nomor_ttbp']); ?></span>
            <span class="text-muted small d-none d-md-inline">· <?= htmlspecialchars($ttbp['nama_kios']); ?></span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="toggleCalibration()" title="Kalibrasi Ukuran / Margin Printer">
                <i class="bi bi-sliders me-1"></i> Kalibrasi
            </button>
            <a href="<?= site_url('ttbp/export_excel/' . $ttbp['id']); ?>" class="btn btn-sm btn-outline-success px-3">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
            <button type="button" class="btn btn-sm btn-primary px-3 fw-medium" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>

    <!-- PANEL KALIBRASI PRINTER (POPUP) -->
    <div id="calibration-panel" class="no-print">
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <h6 class="mb-0 fw-bold" style="font-size: 0.88rem;"><i class="bi bi-sliders me-1"></i> Kalibrasi Cetak</h6>
            <button type="button" class="btn-close btn-sm" onclick="toggleCalibration()"></button>
        </div>
        <div class="text-muted mb-2" style="font-size: 0.72rem;">
            Sesuaikan posisi cetak Continuous Form.
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Lebar (mm)</label>
                <input type="number" id="cal-width" class="form-control form-control-sm" value="215" onchange="applyCalibration()">
            </div>
            <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Tinggi (mm)</label>
                <input type="number" id="cal-height" class="form-control form-control-sm" value="140" onchange="applyCalibration()">
            </div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Margin Atas (mm)</label>
                <input type="number" id="cal-margin-top" class="form-control form-control-sm" value="0" onchange="applyCalibration()">
            </div>
            <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Margin Kiri (mm)</label>
                <input type="number" id="cal-margin-left" class="form-control form-control-sm" value="0" onchange="applyCalibration()">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Skala Font</label>
            <input type="range" id="cal-scale" class="form-range" min="80" max="115" step="1" value="100" oninput="applyCalibration()">
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.7rem;">
                <span>80%</span>
                <span id="scale-val">100%</span>
                <span>115%</span>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetCalibration()">Reset</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleCalibration()">Tutup</button>
        </div>
    </div>

    <!-- CANVAS CETAK -->
    <div class="print-canvas">
        <div id="print-area-wrapper">
            <?php $this->load->view('ttbp/preview_partial', ['ttbp' => $ttbp, 'fixed_rows' => 6]); ?>
        </div>
    </div>

    <script>
        // Load & Save Kalibrasi ke LocalStorage
        function initCalibration() {
            var cfg = localStorage.getItem('ttbp_printer_calibration');
            if (cfg) {
                try {
                    var data = JSON.parse(cfg);
                    if (data.width) document.getElementById('cal-width').value = data.width;
                    if (data.height) document.getElementById('cal-height').value = data.height;
                    if (data.marginTop !== undefined) document.getElementById('cal-margin-top').value = data.marginTop;
                    if (data.marginLeft !== undefined) document.getElementById('cal-margin-left').value = data.marginLeft;
                    if (data.scale) document.getElementById('cal-scale').value = data.scale;
                } catch(e) {}
            }
            applyCalibration();
        }

        function toggleCalibration() {
            var p = document.getElementById('calibration-panel');
            p.style.display = (p.style.display === 'block') ? 'none' : 'block';
        }

        function applyCalibration() {
            var w = document.getElementById('cal-width').value || 215;
            var h = document.getElementById('cal-height').value || 140;
            var mt = document.getElementById('cal-margin-top').value || 0;
            var ml = document.getElementById('cal-margin-left').value || 0;
            var sc = document.getElementById('cal-scale').value || 100;

            document.getElementById('scale-val').innerText = sc + '%';

            var wrapper = document.getElementById('print-area-wrapper');
            var sheet = document.querySelector('.ttbp-sheet');

            if (sheet) {
                sheet.style.width = w + 'mm';
                sheet.style.maxWidth = w + 'mm';
                sheet.style.fontSize = (sc / 100) + 'em';
            }

            if (wrapper) {
                wrapper.style.paddingTop = mt + 'mm';
                wrapper.style.paddingLeft = ml + 'mm';
            }

            // Simpan konfigurasi
            var saveObj = { width: w, height: h, marginTop: mt, marginLeft: ml, scale: sc };
            localStorage.setItem('ttbp_printer_calibration', JSON.stringify(saveObj));
        }

        function resetCalibration() {
            document.getElementById('cal-width').value = 215;
            document.getElementById('cal-height').value = 140;
            document.getElementById('cal-margin-top').value = 0;
            document.getElementById('cal-margin-left').value = 0;
            document.getElementById('cal-scale').value = 100;
            localStorage.removeItem('ttbp_printer_calibration');
            applyCalibration();
        }

        window.addEventListener('DOMContentLoaded', function() {
            initCalibration();

            // Cek autoprint param
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                setTimeout(function() {
                    window.print();
                }, 400);
            }
        });
    </script>
</body>
</html>

