<!-- PAGE HEADER -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-ui-checks text-warning me-2"></i>Form & Input Elements Placeholder
        </h4>
        <p class="text-muted small mb-0">Kumpulan variasi elemen formulir, validasi input, tombol switch, serta pratinjau unggah file siap pakai.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- FORM UTAMA -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="bi bi-pencil-square text-primary me-2"></i>Formulir Input Standar
            </h6>

            <form onsubmit="event.preventDefault(); alert('Formulir berhasil disubmit (simulasi)!');">
                <!-- Input Teks Biasa -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" placeholder="Nama lengkap..." required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Alamat Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" placeholder="nama@email.com" required>
                        </div>
                    </div>
                </div>

                <!-- Select & Date -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kategori / Pilihan</label>
                        <select class="form-select">
                            <option value="">Pilih Opsi...</option>
                            <option value="1">Opsi Pertama</option>
                            <option value="2">Opsi Kedua</option>
                            <option value="3">Opsi Ketiga</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Tanggal Pelaksanaan</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" class="form-control" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Textarea -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Catatan / Deskripsi</label>
                    <textarea class="form-control" rows="3" placeholder="Tuliskan keterangan lebih lanjut di sini..."></textarea>
                </div>

                <!-- File Upload dengan Preview -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Unggah Berkas / Dokumen</label>
                    <input type="file" class="form-control" id="fileUploadDemo" onchange="previewFileName(this)">
                    <small class="text-muted" id="fileHelpText">Mendukung file gambar (JPG, PNG) atau dokumen PDF (Maks. 5MB).</small>
                </div>

                <!-- Switch & Checkbox -->
                <div class="p-3 bg-light rounded-3 mb-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="switchStatus" checked>
                        <label class="form-check-label small fw-semibold" for="switchStatus">Aktifkan Notifikasi Otomatis</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkTerms" required>
                        <label class="form-check-label small text-muted" for="checkTerms">
                            Saya telah memeriksa kebenaran data di atas
                        </label>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-light btn-sm px-3">Reset Form</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KOLOM SAMPING: VALIDASI & PREVIEW ELEMEN -->
    <div class="col-lg-4">
        <!-- Contoh Feedback Validasi -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="bi bi-shield-check text-success me-2"></i>Status Validasi Input
            </h6>

            <!-- Valid State -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-success">Contoh Input Valid</label>
                <input type="text" class="form-control is-valid" value="Format sudah sesuai">
                <div class="valid-feedback small">Data valid dan diterima sistem!</div>
            </div>

            <!-- Invalid State -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-danger">Contoh Input Error</label>
                <input type="text" class="form-control is-invalid" value="Format salah@!">
                <div class="invalid-feedback small">Kolom ini wajib diisi dengan benar!</div>
            </div>
        </div>

        <!-- Radio & Toggle Options -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="bi bi-check2-circle text-info me-2"></i>Pilihan Radio
            </h6>

            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="radioDemo" id="radio1" checked>
                <label class="form-check-label small" for="radio1">Opsi Prioritas Tinggi</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="radioDemo" id="radio2">
                <label class="form-check-label small" for="radio2">Opsi Prioritas Sedang</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="radioDemo" id="radio3">
                <label class="form-check-label small" for="radio3">Opsi Prioritas Rendah</label>
            </div>
        </div>
    </div>
</div>

<script>
function previewFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileHelpText').innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>File terpilih: ' + input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)</span>';
    }
}
</script>

