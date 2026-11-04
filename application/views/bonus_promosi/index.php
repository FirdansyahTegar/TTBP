<div class="content-body">
    <!-- FLASH MESSAGE -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- HEADER TITLE & ACTION BUTTONS -->
    <div class="d-flex flex-column flex-md-row justify-content-end align-items-md-center gap-3 mb-4">
        
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-import-paste">
                <i class="bi bi-clipboard-plus me-1"></i> Tempel Data Excel
            </button>
            <a href="<?= site_url('ttbp'); ?>" class="btn btn-primary btn-sm px-3 shadow-sm fw-medium">
                <i class="bi bi-file-earmark-text me-1"></i> Daftar Lembar TTBP
            </a>
        </div>
    </div>

    <!-- STATS SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Rekap</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1"><?= number_format($stats['total']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-light text-secondary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-collection"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Belum Terbit TTBP</span>
                        <h4 class="fw-bold text-warning mb-0 mt-1"><?= number_format($stats['pending']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">TTBP Terbit</span>
                        <h4 class="fw-bold text-primary mb-0 mt-1"><?= number_format($stats['generated']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Lembar Fisik Kembali</span>
                        <h4 class="fw-bold text-success mb-0 mt-1"><?= number_format($stats['returned']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-arrow-return-left"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH PANEL -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= site_url('bonus_promosi'); ?>" class="row g-2 align-items-center">
                <!-- Search input -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Cari toko, kode, program, faktur, TTBP..." 
                               value="<?= htmlspecialchars($filters['search']); ?>">
                    </div>
                </div>

                <!-- Status filter -->
                <div class="col-md-3 col-6">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status TTBP</option>
                        <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : ''; ?>>Belum Dibuat (Pending)</option>
                        <option value="generated" <?= ($filters['status'] === 'generated') ? 'selected' : ''; ?>>Sudah Dibuat (Generated)</option>
                        <option value="returned" <?= ($filters['status'] === 'returned') ? 'selected' : ''; ?>>Lembar Kembali (Returned)</option>
                    </select>
                </div>

                <!-- Program filter -->
                <div class="col-md-3 col-6">
                    <select name="program" class="form-select form-select-sm">
                        <option value="">Semua Program</option>
                        <?php foreach ($program_list as $p): ?>
                            <option value="<?= htmlspecialchars($p['program']); ?>" <?= ($filters['program'] === $p['program']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($p['program']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action filter -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-medium">
                        Filter
                    </button>
                    <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['program'])): ?>
                        <a href="<?= site_url('bonus_promosi'); ?>" class="btn btn-light btn-sm border" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                <thead class="table-light text-uppercase text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-3 py-3 text-center" style="width: 5%;">No.</th>
                        <th class="py-3 text-center" style="width: 8%;">TTBP</th>
                        <th class="py-3" style="width: 25%;">Toko / Kios</th>
                        <th class="py-3" style="width: 18%;">Program</th>
                        <th class="py-3" style="width: 18%;">Barang Bonus</th>
                        <th class="py-3 text-center" style="width: 12%;">Status TTBP</th>
                        <th class="py-3 text-center" style="width: 10%;">Tgl Kembali</th>
                        <th class="pe-3 py-3 text-center" style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Tidak ada data laporan bonus promosi yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $idx => $row): ?>
                            <?php 
                                $is_generated = ($row['status_ttbp'] === 'generated' || !empty($row['ttbp_id']));
                                $is_returned  = ($row['status_ttbp'] === 'returned');
                                $has_master   = !empty($row['master_toko']);
                            ?>
                            <tr>
                                <!-- No Urut / Tgl TTBP -->
                                <td class="ps-3 text-center text-muted">
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['no_urut_lap'] ?: ($offset + $idx + 1)); ?></div>
                                    <div style="font-size: 0.72rem;"><?= $row['tanggal_ttbp'] ? date('d/m/y', strtotime($row['tanggal_ttbp'])) : '-'; ?></div>
                                </td>

                                <!-- Nomor TTBP Rekap -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold font-monospace" style="font-size: 0.78rem;">
                                        #<?= htmlspecialchars($row['nomor_ttbp']); ?>
                                    </span>
                                </td>

                                <!-- Toko / Pembeli -->
                                <td>
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pembeli']); ?></span>
                                        <?php if ($has_master): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;" title="Terdaftar di master toko">
                                                <?= htmlspecialchars($row['kode_kios']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill" style="font-size: 0.65rem;" title="Non-master / input manual">
                                                Manual
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 0.74rem; max-width: 260px;" title="<?= htmlspecialchars($has_master ? ($row['master_toko']['alamat'] . ', ' . $row['master_toko']['kota']) : 'Alamat diisi manual'); ?>">
                                        <i class="bi bi-geo-alt me-1 text-secondary"></i>
                                        <?= htmlspecialchars($has_master ? ($row['master_toko']['alamat'] . ', ' . $row['master_toko']['kota']) : 'Alamat perlu konfirmasi'); ?>
                                    </div>
                                    <div class="text-muted d-flex gap-2 mt-1" style="font-size: 0.72rem;">
                                        <span><i class="bi bi-person me-1"></i>SC: <strong><?= htmlspecialchars($has_master ? $row['master_toko']['sc'] : ($row['kode_wilayah'] ?: '-')); ?></strong></span>
                                        <span>· CP: <?= htmlspecialchars($has_master ? $row['master_toko']['kontak_person'] : '-'); ?></span>
                                    </div>
                                </td>

                                <!-- Program -->
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['program']); ?></div>
                                    <?php if (floatval($row['volume_prog']) > 0): ?>
                                        <div class="text-muted small" style="font-size: 0.74rem;">
                                            Vol: <?= floatval($row['volume_prog']) . ' ' . htmlspecialchars($row['satuan_vol']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($row['periode_prog_raw'])): ?>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($row['periode_prog_raw']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Barang Bonus / Hadiah -->
                                <td>
                                    <?php if (strtolower($row['nama_barang']) === 'uang'): ?>
                                        <div class="fw-bold text-success">
                                            <i class="bi bi-cash-stack me-1"></i>Uang Tunai
                                        </div>
                                        <div class="text-dark small">
                                            Rp <?= number_format($row['total_bonus'], 0, ',', '.'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_barang']); ?></div>
                                        <div class="text-muted small">
                                            <?= floatval($row['qty']); ?> <?= htmlspecialchars($row['satuan'] ?: 'Unit'); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($row['faktur_promo']) || (!empty($row['faktur']) && $row['faktur'] !== '-')): ?>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            <?= !empty($row['faktur_promo']) ? ('Promo: ' . htmlspecialchars($row['faktur_promo']) . ' ') : ''; ?>
                                            <?= (!empty($row['faktur']) && $row['faktur'] !== '-') ? ('· Fkt: ' . htmlspecialchars($row['faktur'])) : ''; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Status TTBP -->
                                <td class="text-center">
                                    <?php if ($is_returned): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-all me-1"></i>Kembali
                                        </span>
                                    <?php elseif ($is_generated): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-file-earmark-check me-1"></i>Terbit <?= $row['ttbp_generated_nomor'] ? ('(' . htmlspecialchars($row['ttbp_generated_nomor']) . ')') : ''; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tanggal Kembali TTBP -->
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <span class="tgl-kembali-text" id="tgl-text-<?= $row['id']; ?>" style="font-size: 0.76rem;">
                                            <?= $row['tgl_kembali_ttbp'] ? date('d/m/y', strtotime($row['tgl_kembali_ttbp'])) : '<span class="text-muted">-</span>'; ?>
                                        </span>
                                        <button type="button" class="btn btn-link p-0 text-secondary btn-edit-tgl-kembali" 
                                                data-id="<?= $row['id']; ?>" 
                                                data-val="<?= $row['tgl_kembali_ttbp'] ?: ''; ?>" 
                                                title="Edit Tanggal Kembali">
                                            <i class="bi bi-pencil-square" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Tombol Aksi -->
                                <td class="pe-3 text-center">
                                    <?php if (!$is_generated): ?>
                                        <button type="button" class="btn btn-warning btn-sm px-2 py-1 fw-medium shadow-sm btn-generate-modal" 
                                                data-nomor="<?= htmlspecialchars($row['nomor_ttbp']); ?>" 
                                                style="font-size: 0.76rem;">
                                            <i class="bi bi-magic me-1"></i>Buat TTBP
                                        </button>
                                    <?php else: ?>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary px-2 btn-quick-preview" 
                                                    data-id="<?= $row['ttbp_id']; ?>" title="Pratinjau Lembar TTBP">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="<?= site_url('ttbp/cetak/' . $row['ttbp_id'] . '?autoprint=1'); ?>" target="_blank" 
                                               class="btn btn-outline-secondary px-2" title="Cetak TTBP">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <a href="<?= site_url('ttbp/export_excel/' . $row['ttbp_id']); ?>" 
                                               class="btn btn-outline-success px-2" title="Unduh Excel">
                                                <i class="bi bi-file-earmark-excel"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION FOOTER -->
        <?php if ($total_rows > $limit): ?>
            <div class="card-footer bg-white p-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="text-muted" style="font-size: 0.78rem;">
                    Menampilkan <strong><?= $offset + 1; ?></strong> - <strong><?= min($offset + $limit, $total_rows); ?></strong> dari <strong><?= $total_rows; ?></strong> data
                </span>
                <div>
                    <?= $pagination_links; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: KONFIRMASI & AUTO-FILL GENERATE LEMBAR TTBP       -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-generate-ttbp" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-magic fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">Konfirmasi Pembuatan Lembar TTBP</h6>
                        <span class="text-muted" style="font-size: 0.74rem;">
                            Data di bawah ini ditarik otomatis dari master toko dan laporan rekap. Anda dapat memeriksa atau menyesuaikan jika diperlukan.
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="form-generate-ttbp">
                <div class="modal-body p-4 bg-light">
                    <!-- Alert Status Auto-fill -->
                    <div id="gen-alert-container"></div>

                    <input type="hidden" name="nomor_ttbp_lap" id="gen-nomor-lap">

                    <!-- SEKSI 1: HEADER LEMBAR TTBP -->
                    <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-file-earmark-text text-primary me-1"></i> Informasi Lembar TTBP
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Nomor TTBP Fisik (Dicetak)</label>
                                <input type="text" class="form-control form-control-sm fw-bold font-monospace text-primary" 
                                       name="nomor_ttbp" id="gen-nomor-ttbp" required>
                                <span class="text-muted" style="font-size: 0.7rem;">Dapat diisi custom atau ikuti nomor rekap</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Tanggal Lembar TTBP</label>
                                <input type="date" class="form-control form-control-sm" name="tanggal" id="gen-tanggal" required>
                            </div>
                        </div>
                    </div>

                    <!-- SEKSI 2: DATA TOKO & PENERIMA -->
                    <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">
                                <i class="bi bi-shop text-primary me-1"></i> Data Toko / Kios
                            </h6>
                            <span class="badge" id="gen-master-badge"></span>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-8">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Nama Kios</label>
                                <input type="text" class="form-control form-control-sm fw-semibold" name="nama_kios" id="gen-nama-kios" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Salesman (SC)</label>
                                <input type="text" class="form-control form-control-sm" name="salesman" id="gen-salesman" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Alamat Lengkap</label>
                                <textarea class="form-control form-control-sm" name="alamat" id="gen-alamat" rows="2" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Penerima (Contact Person)</label>
                                <input type="text" class="form-control form-control-sm" name="penerima" id="gen-penerima" required>
                            </div>
                        </div>
                    </div>

                    <!-- SEKSI 3: PROGRAM & BARANG BONUS -->
                    <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-gift text-primary me-1"></i> Program & Barang Promosi
                        </h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-8">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Realisasi Atas Program</label>
                                <input type="text" class="form-control form-control-sm fw-medium" name="realisasi_program" id="gen-realisasi-program" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Periode Program</label>
                                <input type="text" class="form-control form-control-sm" name="periode" id="gen-periode" required>
                            </div>
                        </div>

                        <!-- Daftar Item Barang -->
                        <div class="table-responsive border rounded-2">
                            <table class="table table-sm align-middle mb-0" style="font-size: 0.78rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 8%;" class="text-center">No</th>
                                        <th style="width: 50%;">Nama Barang Promosi</th>
                                        <th style="width: 15%;" class="text-end">Jumlah</th>
                                        <th style="width: 12%;" class="text-center">Satuan</th>
                                        <th style="width: 15%;">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="gen-items-tbody">
                                    <!-- Diisi via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SEKSI 4: OTORISASI / TANDA TANGAN -->
                    <div class="card border-0 shadow-sm p-3 bg-white">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;">
                            <i class="bi bi-pen text-primary me-1"></i> Otorisasi & Klausul
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Dibuat Oleh</label>
                                <input type="text" class="form-control form-control-sm" name="dibuat_oleh" id="gen-dibuat-oleh" value="Nonik Dia">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Disetujui Oleh</label>
                                <input type="text" class="form-control form-control-sm" name="disetujui_oleh" id="gen-disetujui-oleh" placeholder="Opsional...">
                            </div>
                        </div>
                        <input type="hidden" name="perhatian" value="Bila Terjadi Retur/Faktur Batal, Sehingga&#10;Target Tidak Tercapai, Maka Barang Promosi&#10;Yang Telah Diterima Harus Dikembalikan&#10;Sesuai Dengan Jumlah Retur/Faktur Batal">
                    </div>
                </div>

                <div class="modal-footer bg-white py-2 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-medium shadow-sm" id="btn-submit-generate">
                        <i class="bi bi-printer me-1"></i> Terbitkan Lembar TTBP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: TEMPEL DATA DARI EXCEL (COPY-PASTE IMPORT)       -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-import-paste" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 px-4 border-bottom">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="bi bi-clipboard-plus text-primary me-1"></i> Impor Rekap Bonus Promosi (Excel Paste)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-import-paste">
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm small py-2 px-3 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Buka file Excel Anda, pilih range tabel, tekan <strong>Ctrl + C</strong>, lalu tempel (<strong>Ctrl + V</strong>) langsung ke kotak di bawah ini.
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Periode Laporan</label>
                        <input type="text" class="form-control form-control-sm" name="periode_laporan" value="Januari - Desember 2026">
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-1" style="font-size: 0.74rem;">Kotak Tempel Teks (Tab-Delimited dari Excel)</label>
                        <textarea class="form-control font-monospace" name="raw_text" id="paste-raw-text" rows="8" 
                                  placeholder="Contoh:&#10;1&#9;7-Jan-26&#9;1&#9;9-Jan-26&#9;B 516&#9;BINT05&#9;Bintang Mulia Tani/Yuda&#9;12,600,000&#9;Unibro 500/50 SC&#9;Sprayer Elektrik Joeragan 16 L Battery&#9;..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-medium" id="btn-submit-import">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Proses Impor Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: EDIT TANGGAL KEMBALI TTBP CEPAT                   -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-tgl-kembali" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-2 px-3 border-bottom">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 0.85rem;">Tanggal Kembali TTBP</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-tgl-kembali">
                <div class="modal-body p-3">
                    <input type="hidden" name="id" id="tgl-id">
                    <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Tanggal Lembar Fisik Diterima</label>
                    <input type="date" class="form-control form-control-sm" name="tgl_kembali" id="tgl-input-val">
                    <div class="form-text" style="font-size: 0.68rem;">Kosongkan jika lembar fisik belum kembali.</div>
                </div>
                <div class="modal-footer bg-white py-2 px-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: QUICK PREVIEW TTBP                                -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-quick-preview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    Pratinjau Lembar TTBP
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="quick-preview-body">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="small text-muted ms-2">Memuat...</span>
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Tutup
                </button>
                <div class="d-flex gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-success btn-sm" id="btn-modal-excel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                    </a>
                    <a href="#" target="_blank" class="btn btn-primary btn-sm" id="btn-modal-print">
                        <i class="bi bi-printer me-1"></i> Cetak
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var modalGenerate = new bootstrap.Modal(document.getElementById('modal-generate-ttbp'));
    var modalTgl = new bootstrap.Modal(document.getElementById('modal-tgl-kembali'));
    var modalPreview = new bootstrap.Modal(document.getElementById('modal-quick-preview'));

    // 1. Klik Tombol "Buat TTBP"
    $('.btn-generate-modal').on('click', function() {
        var nomor = $(this).data('nomor');
        $('#gen-nomor-lap').val(nomor);
        $('#btn-submit-generate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan...');

        // Fetch auto-fill data dari server
        $.get('<?= site_url("bonus_promosi/preview_generate_ajax/"); ?>' + nomor, function(res) {
            if (res.status && res.data) {
                var d = res.data;
                $('#gen-nomor-ttbp').val(d.nomor_ttbp_saran);
                $('#gen-tanggal').val(d.tanggal);
                $('#gen-nama-kios').val(d.nama_kios);
                $('#gen-alamat').val(d.alamat);
                $('#gen-salesman').val(d.salesman);
                $('#gen-penerima').val(d.penerima);
                $('#gen-realisasi-program').val(d.realisasi_saran);
                $('#gen-periode').val(d.periode_saran);
                $('#gen-dibuat-oleh').val(d.dibuat_oleh);
                $('#gen-disetujui-oleh').val(d.disetujui_oleh);

                if (d.has_master) {
                    $('#gen-master-badge').attr('class', 'badge bg-success-subtle text-success border border-success-subtle')
                                          .html('<i class="bi bi-check-circle me-1"></i>Tarik Otomatis dari Master Toko (' + d.kode_kios + ')');
                    $('#gen-alert-container').html('');
                } else {
                    $('#gen-master-badge').attr('class', 'badge bg-warning-subtle text-warning border border-warning-subtle')
                                          .html('<i class="bi bi-exclamation-triangle me-1"></i>Toko Non-Master (Periksa Alamat)');
                    $('#gen-alert-container').html('<div class="alert alert-warning border-0 py-2 px-3 small mb-3">' +
                        '<i class="bi bi-exclamation-triangle-fill me-1"></i> Toko ini belum terdaftar di database master. Silakan lengkapi Alamat dan Salesman sebelum menyimpan.' +
                        '</div>');
                }

                // Render items tabel
                var itemsHtml = '';
                d.items.forEach(function(it) {
                    itemsHtml += '<tr>' +
                        '<td class="text-center">' + it.no_urut + '</td>' +
                        '<td class="fw-medium text-dark">' + escapeHtml(it.nama_barang) + '</td>' +
                        '<td class="text-end fw-bold">' + it.jumlah + '</td>' +
                        '<td class="text-center">' + escapeHtml(it.satuan) + '</td>' +
                        '<td class="text-muted">' + escapeHtml(it.keterangan || '-') + '</td>' +
                        '</tr>';
                });
                $('#gen-items-tbody').html(itemsHtml);

                $('#btn-submit-generate').prop('disabled', false).html('<i class="bi bi-printer me-1"></i> Terbitkan Lembar TTBP');
                modalGenerate.show();
            } else {
                alert('Gagal mengambil data laporan.');
            }
        }).fail(function() {
            alert('Terjadi kesalahan jaringan.');
            $('#btn-submit-generate').prop('disabled', false).html('<i class="bi bi-printer me-1"></i> Terbitkan Lembar TTBP');
        });
    });

    // 2. Submit Form Generate TTBP
    $('#form-generate-ttbp').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-submit-generate');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menerbitkan...');

        $.ajax({
            url: '<?= site_url("bonus_promosi/generate_ttbp_ajax"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    modalGenerate.hide();
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal menerbitkan TTBP.');
                    $btn.prop('disabled', false).html('<i class="bi bi-printer me-1"></i> Terbitkan Lembar TTBP');
                }
            },
            error: function() {
                alert('Terjadi kesalahan sistem.');
                $btn.prop('disabled', false).html('<i class="bi bi-printer me-1"></i> Terbitkan Lembar TTBP');
            }
        });
    });

    // 3. Edit Tanggal Kembali TTBP
    $('.btn-edit-tgl-kembali').on('click', function() {
        var id = $(this).data('id');
        var val = $(this).data('val');
        $('#tgl-id').val(id);
        $('#tgl-input-val').val(val);
        modalTgl.show();
    });

    $('#form-tgl-kembali').on('submit', function(e) {
        e.preventDefault();
        var id = $('#tgl-id').val();
        var tgl = $('#tgl-input-val').val();

        $.post('<?= site_url("bonus_promosi/update_tgl_kembali_ajax"); ?>', { id: id, tgl_kembali: tgl }, function(res) {
            if (res.status) {
                modalTgl.hide();
                window.location.reload();
            } else {
                alert('Gagal memperbarui tanggal.');
            }
        }, 'json');
    });

    // 4. Quick Preview TTBP
    $('.btn-quick-preview').on('click', function() {
        var id = $(this).data('id');
        $('#quick-preview-body').html('<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="small text-muted ms-2">Memuat...</span></div>');
        $('#btn-modal-excel').attr('href', '<?= site_url("ttbp/export_excel/"); ?>' + id);
        $('#btn-modal-print').attr('href', '<?= site_url("ttbp/cetak/"); ?>' + id + '?autoprint=1');
        modalPreview.show();

        $.get('<?= site_url("ttbp/preview_partial_ajax/"); ?>' + id, function(html) {
            $('#quick-preview-body').html(html);
        }).fail(function() {
            $('#quick-preview-body').html('<div class="alert alert-danger mb-0">Gagal memuat pratinjau.</div>');
        });
    });

    // 5. Submit Form Import Paste Excel
    $('#form-import-paste').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-submit-import');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengimpor...');

        $.ajax({
            url: '<?= site_url("bonus_promosi/import_paste_ajax"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal memproses impor.');
                    $btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Proses Impor Data');
                }
            },
            error: function() {
                alert('Terjadi kesalahan jaringan.');
                $btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Proses Impor Data');
            }
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});
</script>

