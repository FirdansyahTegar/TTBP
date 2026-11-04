<div class="content-body">
    <!-- Top Action Bar (Tanpa Heading Besar) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted fw-semibold" style="font-size: 0.85rem;">
            <i class="bi bi-file-earmark-excel-fill text-success me-1"></i> Import Data Excel Bonus Promosi
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">
                <i class="bi bi-lightning-charge-fill me-1"></i> SimpleXLSX Ultra Fast & Anti-Timeout
            </span>
            <a href="<?= site_url('ttbp'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Alert Status -->
    <div id="import-alert-container"></div>

    <div class="row g-4">
        <!-- OPSI 1: UPLOAD FILE EXCEL -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100 bg-white">
                <div class="mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Unggah Berkas Excel (.xlsx / .xls / .csv)</h6>
                </div>
                
                <form id="form-upload-file" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Periode Laporan</label>
                        <input type="text" class="form-control form-control-sm" name="periode_laporan" value="Januari - Desember 2026">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Pilih Berkas Excel</label>
                        <input type="file" class="form-control form-control-sm" name="excel_file" id="excel_file_input" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">
                            Pastikan baris header memuat kolom: <em>No, Tanggal TTBP, TTBP, Sdh dipotong, Faktur Promo, Kode, Nama Pembeli...</em>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm px-4 fw-medium shadow-sm" id="btn-submit-upload">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Unggah & Proses File
                    </button>
                </form>
            </div>
        </div>

        <!-- OPSI 2: TEMPEL DATA DARI CLIPBOARD -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100 bg-white">
                <div class="mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Tempel Teks Tabel (Copy-Paste dari Excel)</h6>
                </div>

                <form id="form-paste-excel">
                    <div class="mb-3">
                        <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Periode Laporan</label>
                        <input type="text" class="form-control form-control-sm" name="periode_laporan" value="Januari - Desember 2026">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Kotak Tempel Teks (Tab-Delimited)</label>
                        <textarea class="form-control font-monospace" name="raw_text" rows="5" 
                                  placeholder="Buka Excel -> Pilih range data tabel -> Ctrl+C -> Klik di sini -> Ctrl+V" required style="font-size: 0.75rem;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-medium shadow-sm" id="btn-submit-paste">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Proses Data Tempel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PANDUAN STRUKTUR KOLOM EXCEL -->
    <div class="card border-0 shadow-sm p-4 mt-4 bg-white">
        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.88rem;">
            <i class="bi bi-table text-primary me-2"></i>Panduan Urutan Kolom Excel
        </h6>
        <p class="text-muted small mb-3">
            Sistem membaca kolom dokumen Excel secara berurutan sesuai format standar laporan:
        </p>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.74rem;">
                <thead class="table-light">
                    <tr>
                        <th style="background-color: #d8e2bd;">1</th>
                        <th style="background-color: #d8e2bd;">2</th>
                        <th style="background-color: #d8e2bd;">3</th>
                        <th style="background-color: #d8e2bd;">4</th>
                        <th style="background-color: #d8e2bd;">5</th>
                        <th style="background-color: #d8e2bd;">6</th>
                        <th style="background-color: #d8e2bd;">7</th>
                        <th style="background-color: #d8e2bd;">8</th>
                        <th style="background-color: #d8e2bd;">9</th>
                        <th style="background-color: #d8e2bd;">10</th>
                        <th style="background-color: #d8e2bd;">11</th>
                        <th style="background-color: #d8e2bd;">12</th>
                        <th style="background-color: #d8e2bd;">13</th>
                        <th style="background-color: #d8e2bd;">14</th>
                        <th style="background-color: #d8e2bd;">15</th>
                        <th style="background-color: #d8e2bd;">16</th>
                        <th style="background-color: #d8e2bd;">17</th>
                        <th style="background-color: #d8e2bd;">18</th>
                        <th style="background-color: #d8e2bd;">19</th>
                        <th style="background-color: #d8e2bd;">20</th>
                        <th style="background-color: #d8e2bd;">21</th>
                        <th style="background-color: #d8e2bd;">22</th>
                        <th style="background-color: #d8e2bd;">23</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal TTBP</th>
                        <th>TTBP</th>
                        <th>Sdh dipotong</th>
                        <th>Faktur Promo</th>
                        <th>Kode</th>
                        <th>Nama Pembeli</th>
                        <th>Value Produk</th>
                        <th>Program</th>
                        <th>Nama Barang</th>
                        <th>potong nota</th>
                        <th>Pajak</th>
                        <th>Total Bonus</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Volume Prog</th>
                        <th>Sat. Vol</th>
                        <th>Periode Awal</th>
                        <th>Periode Akhir</th>
                        <th>Faktur</th>
                        <th>at</th>
                        <th>tgl kembali</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. Submit File Upload
    $('#form-upload-file').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var $btn = $('#btn-submit-upload');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah & Membaca...');

        $.ajax({
            url: '<?= site_url("ttbp/import_file_ajax"); ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#import-alert-container').html('<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i>' + res.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    setTimeout(function() {
                        window.location.href = '<?= site_url("ttbp"); ?>';
                    }, 1200);
                } else {
                    $('#import-alert-container').html('<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>' + (res.message || 'Gagal memproses file.') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    $btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Unggah & Proses File');
                }
            },
            error: function() {
                $('#import-alert-container').html('<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi kesalahan komunikasi dengan server.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                $btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Unggah & Proses File');
            }
        });
    });

    // 2. Submit Paste Text
    $('#form-paste-excel').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-submit-paste');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

        $.ajax({
            url: '<?= site_url("ttbp/import_paste_ajax"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#import-alert-container').html('<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i>' + res.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    setTimeout(function() {
                        window.location.href = '<?= site_url("ttbp"); ?>';
                    }, 1200);
                } else {
                    $('#import-alert-container').html('<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>' + (res.message || 'Gagal mengimpor data tempel.') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down me-1"></i> Proses Data Tempel');
                }
            },
            error: function() {
                $('#import-alert-container').html('<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi kesalahan jaringan.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down me-1"></i> Proses Data Tempel');
            }
        });
    });
});
</script>

