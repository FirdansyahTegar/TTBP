<div class="content-body">
    <!-- FLASH MESSAGE -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- TOP ACTION BUTTONS BAR (Tanpa Heading Besar) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <?php if (!empty($list)): ?>
                <button type="button" class="btn btn-outline-danger btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-clear-all" title="Kosongkan seluruh data tabel">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan List
                </button>
            <?php endif; ?>
        </div>

        <!-- Tombol Aksi Utama -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= site_url('ttbp/export_list_excel') . '?' . http_build_query($filters); ?>" class="btn btn-outline-success btn-sm px-3 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Rekap Excel
            </a>
            <a href="<?= site_url('ttbp/import'); ?>" class="btn btn-primary btn-sm px-3 shadow-sm fw-medium">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import Excel
            </a>
        </div>
    </div>

    <!-- STATS SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Baris</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1"><?= number_format($stats['total']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-light text-secondary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-list-ol"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Lembar TTBP</span>
                        <h4 class="fw-bold text-primary mb-0 mt-1"><?= number_format($stats['total_ttbp']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Toko / Kios</span>
                        <h4 class="fw-bold text-success mb-0 mt-1"><?= number_format($stats['total_toko']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-shop"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Lembar Fisik Kembali</span>
                        <h4 class="fw-bold text-info mb-0 mt-1"><?= number_format($stats['returned']); ?></h4>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem;">
                        <i class="bi bi-arrow-return-left"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH PANEL -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= site_url('ttbp'); ?>" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Cari No, TTBP, Toko, Program, Barang, Faktur..." 
                               value="<?= htmlspecialchars($filters['search']); ?>">
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">Semua (Uang / Barang)</option>
                        <option value="barang" <?= ($filters['jenis'] === 'barang') ? 'selected' : ''; ?>>Barang Promosi</option>
                        <option value="uang" <?= ($filters['jenis'] === 'uang') ? 'selected' : ''; ?>>Uang Tunai</option>
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <select name="bulan" class="form-select form-select-sm">
                        <option value="">Semua Bulan</option>
                        <?php 
                        $months = [
                            '2026-01' => 'Januari 2026', '2026-02' => 'Februari 2026', '2026-03' => 'Maret 2026',
                            '2026-04' => 'April 2026', '2026-05' => 'Mei 2026', '2026-06' => 'Juni 2026',
                            '2026-07' => 'Juli 2026', '2026-08' => 'Agustus 2026', '2026-09' => 'September 2026',
                            '2026-10' => 'Oktober 2026', '2026-11' => 'November 2026', '2026-12' => 'Desember 2026'
                        ];
                        foreach ($months as $k => $v): ?>
                            <option value="<?= $k; ?>" <?= ($filters['bulan'] === $k) ? 'selected' : ''; ?>><?= $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-medium">Filter</button>
                    <?php if (!empty($filters['search']) || !empty($filters['jenis']) || !empty($filters['bulan'])): ?>
                        <a href="<?= site_url('ttbp'); ?>" class="btn btn-light btn-sm border" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN DATA TABLE WITH COMPACT COLUMNS & STICKY LEFT ACTION -->
    <style>
        .custom-scroll-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
        }
        .custom-scroll-container::-webkit-scrollbar {
            height: 8px;
        }
        .custom-scroll-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .custom-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Compact Table Sizing */
        .table-compact th, 
        .table-compact td {
            padding: 5px 6px !important;
            vertical-align: middle;
            font-size: 0.77rem;
        }

        /* Sticky Left Action Column */
        .sticky-col-action {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: #ffffff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            min-width: 115px;
            width: 115px;
        }
        thead .sticky-col-action {
            background-color: #f8fafc !important;
            z-index: 6;
        }
        tr:hover .sticky-col-action {
            background-color: #f8fafc !important;
        }

        .btn-xs {
            padding: 2px 5px !important;
            font-size: 0.74rem !important;
            line-height: 1.2 !important;
            border-radius: 4px !important;
        }

        /* Floating Action Popup Overlay (Outside Table) */
        .ttbp-floating-opsi {
            position: fixed;
            z-index: 9999;
            display: none;
            min-width: 185px;
            max-width: 220px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 14px 32px -4px rgba(15, 23, 42, 0.2), 0 4px 12px -2px rgba(15, 23, 42, 0.1);
            padding: 6px;
            animation: ttbpPopupFadeIn 0.14s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes ttbpPopupFadeIn {
            from { opacity: 0; transform: translateY(-4px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .ttbp-floating-opsi .opsi-header {
            padding: 5px 10px 6px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 4px;
        }
        .ttbp-floating-opsi .opsi-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 6px 10px;
            font-size: 0.78rem;
            font-weight: 500;
            color: #334155;
            background: transparent;
            border: 0;
            border-radius: 6px;
            text-decoration: none;
            text-align: left;
            transition: background-color 0.12s ease, color 0.12s ease;
            cursor: pointer;
        }
        .ttbp-floating-opsi .opsi-item:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .ttbp-floating-opsi .opsi-item.opsi-danger {
            color: #dc2626;
        }
        .ttbp-floating-opsi .opsi-item.opsi-danger:hover {
            background-color: #fef2f2;
            color: #b91c1c;
        }
        .ttbp-floating-opsi .opsi-divider {
            height: 1px;
            background-color: #f1f5f9;
            margin: 4px 0;
        }
        .btn-open-opsi.active {
            background-color: #e2e8f0 !important;
            border-color: #94a3b8 !important;
        }
    </style>

    <div class="card border-0 shadow-sm mb-4">
        <div class="custom-scroll-container">
            <table class="table table-hover table-compact align-middle mb-0">
                <thead class="table-light text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                    <tr>
                        <th class="text-center sticky-col-action">Aksi</th>
                        <th class="text-center" style="width: 32px;">No.</th>
                        <th class="text-center" style="min-width: 72px;">Tanggal TTBP</th>
                        <th class="text-center" style="min-width: 55px;">TTBP</th>
                        <th class="text-center" style="min-width: 72px;">Sdh dipotong</th>
                        <th class="text-center" style="min-width: 65px;">Faktur Promo</th>
                        <th class="text-center" style="min-width: 60px;">Kode</th>
                        <th style="min-width: 130px; max-width: 160px;">Nama Pembeli</th>
                        <th class="text-end" style="min-width: 80px;">Value Produk</th>
                        <th style="min-width: 100px; max-width: 130px;">Program</th>
                        <th style="min-width: 120px; max-width: 150px;">Nama Barang</th>
                        <th class="text-end" style="min-width: 70px;">potong nota</th>
                        <th class="text-end" style="min-width: 65px;">Pajak</th>
                        <th class="text-end" style="min-width: 75px;">Total Bonus</th>
                        <th class="text-center" style="min-width: 35px;">Qty</th>
                        <th class="text-center" style="min-width: 40px;">Sat</th>
                        <th class="text-end" style="min-width: 70px;">Volume Prog</th>
                        <th class="text-center" style="min-width: 72px;">Periode Awal</th>
                        <th class="text-center" style="min-width: 72px;">Periode Akhir</th>
                        <th class="text-center" style="min-width: 80px;">Faktur</th>
                        <th class="text-center" style="min-width: 40px;">at</th>
                        <th class="text-center" style="min-width: 75px;">tgl kembali</th>
                        <th style="min-width: 90px; max-width: 140px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="23" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada data bonus promosi. Klik <strong>Import Excel</strong> untuk mengunggah file laporan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $idx => $r): ?>
                            <?php 
                                $has_master = !empty($r['master_toko']);
                                $is_returned = !empty($r['tgl_kembali_ttbp']);

                                // Parse Periode Awal & Akhir
                                $tgl_awal = !empty($r['periode_prog_awal']) ? date('d-M-y', strtotime($r['periode_prog_awal'])) : '';
                                $tgl_akhir = !empty($r['periode_prog_akhir']) ? date('d-M-y', strtotime($r['periode_prog_akhir'])) : '';
                                if (empty($tgl_awal) && !empty($r['periode_prog_raw'])) {
                                    $parts = explode(' - ', $r['periode_prog_raw']);
                                    if (count($parts) >= 2) {
                                        $tgl_awal = trim($parts[0]);
                                        $tgl_akhir = trim($parts[1]);
                                    } else {
                                        $tgl_awal = $r['periode_prog_raw'];
                                    }
                                }
                            ?>
                            <tr>
                                <!-- Tombol Aksi TTBP (Sticky Left) -->
                                <td class="text-center sticky-col-action">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <?php $formatted_ttbp_no = Ttbp_model::format_nomor_ttbp($r['nomor_ttbp'], $r['tanggal_ttbp']); ?>
                                        <!-- Pratinjau Lembar TTBP Fisik -->
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-preview-ttbp" 
                                                data-nomor="<?= htmlspecialchars($formatted_ttbp_no ?: $r['nomor_ttbp']); ?>" 
                                                title="Pratinjau Lembar TTBP">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Cetak Lembar TTBP Fisik -->
                                        <a href="<?= site_url('ttbp/cetak/' . urlencode($formatted_ttbp_no ?: $r['nomor_ttbp']) . '?autoprint=1'); ?>" target="_blank" 
                                           class="btn btn-xs btn-outline-dark" title="Cetak Lembar TTBP">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                        <!-- Export Excel TTBP Fisik -->
                                        <a href="<?= site_url('ttbp/export_excel/' . urlencode($formatted_ttbp_no ?: $r['nomor_ttbp'])); ?>" 
                                           class="btn btn-xs btn-outline-success" title="Unduh Lembar Excel TTBP">
                                            <i class="bi bi-file-earmark-excel"></i>
                                        </a>

                                        <!-- Tombol Buka Popup Opsi (Overlay di atas Tabel) -->
                                        <button type="button" class="btn btn-xs btn-light border btn-open-opsi" 
                                                data-id="<?= $r['id']; ?>"
                                                data-nomor="<?= htmlspecialchars($formatted_ttbp_no ?: $r['nomor_ttbp']); ?>"
                                                data-pembeli="<?= htmlspecialchars($r['nama_pembeli']); ?>"
                                                data-tgl-kembali="<?= htmlspecialchars($r['tgl_kembali_ttbp'] ?: ''); ?>"
                                                data-edit-url="<?= site_url('ttbp/edit/' . $r['id']); ?>"
                                                data-delete-url="<?= site_url('ttbp/delete/' . $r['id']); ?>"
                                                title="Menu Opsi">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- No. (Bilangan Bulat Positif Urut) -->
                                <td class="text-center text-muted fw-semibold font-monospace">
                                    <?= intval($r['no_urut_lap']) > 0 ? intval($r['no_urut_lap']) : ($offset + $idx + 1); ?>
                                </td>

                                <!-- Tanggal TTBP -->
                                <td class="text-center text-nowrap">
                                    <?= $r['tanggal_ttbp'] ? date('d-M-y', strtotime($r['tanggal_ttbp'])) : '-'; ?>
                                </td>

                                <!-- TTBP (Format Resmi C + Tahun + 4 Digit) -->
                                <td class="text-center text-nowrap">
                                    <span class="text-primary fw-bold font-monospace">
                                        <?= htmlspecialchars(Ttbp_model::format_nomor_ttbp($r['nomor_ttbp'], $r['tanggal_ttbp'])); ?>
                                    </span>
                                </td>

                                <!-- Sdh dipotong -->
                                <td class="text-center text-muted text-nowrap">
                                    <?= $r['tgl_sdh_dipotong'] ? date('d-M-y', strtotime($r['tgl_sdh_dipotong'])) : '-'; ?>
                                </td>

                                <!-- Faktur Promo -->
                                <td class="text-center font-monospace text-nowrap">
                                    <?= htmlspecialchars($r['faktur_promo'] ?: '-'); ?>
                                </td>

                                <!-- Kode Toko -->
                                <td class="text-center text-nowrap">
                                    <?php if (!empty($r['kode_kios']) && $r['kode_kios'] !== '-'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace py-0 px-1" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($r['kode_kios']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Nama Pembeli -->
                                <td style="max-width: 160px; line-height: 1.25;">
                                    <div class="fw-semibold text-dark text-truncate" title="<?= htmlspecialchars($r['nama_pembeli']); ?>">
                                        <?= htmlspecialchars($r['nama_pembeli']); ?>
                                    </div>
                                    <div class="text-muted small text-truncate" style="font-size: 0.68rem;" title="<?= htmlspecialchars($has_master ? ($r['master_toko']['alamat'] . ', ' . $r['master_toko']['kota']) : ($r['alamat_manual'] ?: '')); ?>">
                                        <?= htmlspecialchars($has_master ? ($r['master_toko']['alamat'] . ', ' . $r['master_toko']['kota']) : ($r['alamat_manual'] ?: '-')); ?>
                                    </div>
                                </td>

                                <!-- Value Produk Program -->
                                <td class="text-end text-muted font-monospace text-nowrap">
                                    <?= floatval($r['value_produk_program']) > 0 ? number_format($r['value_produk_program']) : '-'; ?>
                                </td>

                                <!-- Program -->
                                <td style="max-width: 130px; line-height: 1.25;">
                                    <span class="fw-medium text-dark"><?= htmlspecialchars($r['program']); ?></span>
                                </td>

                                <!-- Nama Barang -->
                                <td style="max-width: 150px; line-height: 1.25;">
                                    <?php if (strtolower($r['nama_barang']) === 'uang'): ?>
                                        <span class="badge bg-success text-white py-0 px-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-cash me-1"></i>Uang
                                        </span>
                                    <?php else: ?>
                                        <span class="text-dark"><?= htmlspecialchars($r['nama_barang']); ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- potong nota -->
                                <td class="text-end font-monospace text-nowrap">
                                    <?= floatval($r['potong_nota']) > 0 ? number_format($r['potong_nota']) : '-'; ?>
                                </td>

                                <!-- Pajak -->
                                <td class="text-end font-monospace text-nowrap">
                                    <?= floatval($r['pajak']) > 0 ? number_format($r['pajak']) : '-'; ?>
                                </td>

                                <!-- Total Bonus -->
                                <td class="text-end font-monospace fw-bold text-success text-nowrap">
                                    <?= floatval($r['total_bonus']) > 0 ? number_format($r['total_bonus']) : '-'; ?>
                                </td>

                                <!-- Qty -->
                                <td class="text-center fw-bold text-nowrap">
                                    <?= floatval($r['qty']) > 0 ? floatval($r['qty']) : '-'; ?>
                                </td>

                                <!-- Satuan -->
                                <td class="text-center text-muted text-nowrap">
                                    <?= htmlspecialchars($r['satuan'] ?: '-'); ?>
                                </td>

                                <!-- Volume Prog -->
                                <td class="text-end text-nowrap">
                                    <?= floatval($r['volume_prog']) > 0 ? (floatval($r['volume_prog']) . ' ' . htmlspecialchars($r['satuan_vol'])) : '-'; ?>
                                </td>

                                <!-- Periode Awal (Terpisah) -->
                                <td class="text-center text-muted text-nowrap" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($tgl_awal ?: '-'); ?>
                                </td>

                                <!-- Periode Akhir (Terpisah) -->
                                <td class="text-center text-muted text-nowrap" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($tgl_akhir ?: '-'); ?>
                                </td>

                                <!-- Faktur (Terpisah angka) -->
                                <td class="text-center font-monospace text-nowrap">
                                    <?= htmlspecialchars($r['faktur'] ?: '-'); ?>
                                </td>

                                <!-- at (Terpisah huruf / wilayah) -->
                                <td class="text-center font-monospace fw-bold text-secondary text-nowrap">
                                    <?= htmlspecialchars($r['kode_wilayah'] ?: '-'); ?>
                                </td>

                                <!-- tanggal kembali TTBP -->
                                <td class="text-center text-nowrap">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <span style="font-size: 0.72rem;" class="<?= $is_returned ? 'text-success fw-bold' : 'text-muted'; ?>">
                                            <?= $r['tgl_kembali_ttbp'] ? date('d-M-y', strtotime($r['tgl_kembali_ttbp'])) : '-'; ?>
                                        </span>
                                        <button type="button" class="btn btn-link p-0 text-secondary btn-edit-tgl-kembali" 
                                                data-id="<?= $r['id']; ?>" 
                                                data-val="<?= $r['tgl_kembali_ttbp'] ?: ''; ?>" 
                                                title="Edit Tanggal Kembali">
                                            <i class="bi bi-pencil-square" style="font-size: 0.72rem;"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Keterangan -->
                                <td class="text-muted small" style="max-width: 140px; line-height: 1.25;">
                                    <span class="d-inline-block text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($r['keterangan'] ?: ''); ?>">
                                        <?= htmlspecialchars($r['keterangan'] ?: '-'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION FOOTER -->
        <?php if ($total_rows > $limit): ?>
            <div class="card-footer bg-white p-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="text-muted" style="font-size: 0.75rem;">
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
<!-- MODAL: KOSONGKAN SELURUH LIST (CLEAR ALL)                -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-clear-all" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Kosongkan Seluruh List
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-2 text-dark fw-semibold">
                    Apakah Anda yakin ingin menghapus <u>SELURUH DATA</u> bonus promosi di list ini?
                </p>
                <p class="text-muted small mb-0">
                    Tindakan ini akan mengosongkan seluruh baris data yang ada di database aplikasi TTBP dan tidak dapat dibatalkan.
                </p>
            </div>
            <div class="modal-footer bg-light py-2 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                <a href="<?= site_url('ttbp/clear_all'); ?>" class="btn btn-danger btn-sm px-4 fw-medium">
                    <i class="bi bi-trash3 me-1"></i> Ya, Kosongkan Semua
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: PRATINJAU LEMBAR TTBP FISIK (CONTINUOUS FORM)     -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-preview-sheet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>Pratinjau Lembar TTBP (CF K4 W PRS 4-ply)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="preview-sheet-body">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="small text-muted ms-2">Menyiapkan lembar TTBP...</span>
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Tutup
                </button>
                <div class="d-flex gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-success btn-sm" id="btn-modal-excel-sheet">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel TTBP
                    </a>
                    <a href="#" target="_blank" class="btn btn-primary btn-sm" id="btn-modal-print-sheet">
                        <i class="bi bi-printer me-1"></i> Cetak Lembar TTBP
                    </a>
                </div>
            </div>
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
<!-- FLOATING OVERLAY POPUP MENU OPSI (DI ATAS TABEL)         -->
<!-- ======================================================== -->
<div id="ttbp-floating-opsi" class="ttbp-floating-opsi">
    <div class="opsi-header">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <span class="badge bg-primary-subtle text-primary font-monospace" style="font-size: 0.68rem;" id="opsi-badge-ttbp">#-</span>
            <span class="text-muted text-truncate fw-semibold" style="font-size: 0.7rem; max-width: 120px;" id="opsi-label-pembeli">-</span>
        </div>
    </div>
    <a href="#" id="opsi-btn-edit" class="opsi-item">
        <i class="bi bi-pencil-square text-warning"></i>
        <span>Edit Baris Data</span>
    </a>
    <button type="button" id="opsi-btn-tgl-kembali" class="opsi-item">
        <i class="bi bi-calendar-check text-info"></i>
        <span>Set Tgl Kembali</span>
    </button>
    <div class="opsi-divider"></div>
    <a href="#" id="opsi-btn-delete" class="opsi-item opsi-danger" onclick="return confirm('Hapus baris data ini?')">
        <i class="bi bi-trash3"></i>
        <span>Hapus Data</span>
    </a>
</div>

<script>
$(document).ready(function() {
    var modalPreview = new bootstrap.Modal(document.getElementById('modal-preview-sheet'));
    var modalTgl = new bootstrap.Modal(document.getElementById('modal-tgl-kembali'));

    // Pindahkan floating popup langsung ke <body> agar 100% bebas dari overflow/clipping tabel
    var $floatingOpsi = $('#ttbp-floating-opsi').appendTo('body');
    var activeOpsiBtn = null;

    function closeFloatingOpsi() {
        $floatingOpsi.hide();
        if (activeOpsiBtn) {
            $(activeOpsiBtn).removeClass('active');
            activeOpsiBtn = null;
        }
    }

    // Buka / Toggle Floating Popup Opsi
    $(document).on('click', '.btn-open-opsi', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var btn = this;
        if (activeOpsiBtn === btn && $floatingOpsi.is(':visible')) {
            closeFloatingOpsi();
            return;
        }

        if (activeOpsiBtn) {
            $(activeOpsiBtn).removeClass('active');
        }
        activeOpsiBtn = btn;
        $(btn).addClass('active');

        var $btn = $(btn);
        var id = $btn.data('id');
        var nomor = $btn.data('nomor');
        var pembeli = $btn.data('pembeli');
        var tglKembali = $btn.data('tgl-kembali');
        var editUrl = $btn.data('edit-url');
        var deleteUrl = $btn.data('delete-url');

        $('#opsi-badge-ttbp').text('#' + nomor);
        $('#opsi-label-pembeli').text(pembeli).attr('title', pembeli);
        $('#opsi-btn-edit').attr('href', editUrl);
        $('#opsi-btn-delete').attr('href', deleteUrl);
        $('#opsi-btn-tgl-kembali').data('id', id).data('val', tglKembali);

        // Tampilkan sementara untuk hitung ukuran
        $floatingOpsi.css({ top: -9999, left: -9999, display: 'block' });
        var menuWidth = $floatingOpsi.outerWidth();
        var menuHeight = $floatingOpsi.outerHeight();
        var rect = btn.getBoundingClientRect();

        // Hitung posisi vertikal (buka ke atas jika mepet batas bawah layar)
        var topPos = rect.bottom + 6;
        if (topPos + menuHeight > window.innerHeight - 12) {
            topPos = Math.max(12, rect.top - menuHeight - 6);
        }

        // Hitung posisi horizontal (sejajar tombol, tidak pernah keluar layar)
        var leftPos = rect.left;
        if (leftPos + menuWidth > window.innerWidth - 12) {
            leftPos = window.innerWidth - menuWidth - 12;
        }
        leftPos = Math.max(12, leftPos);

        $floatingOpsi.css({
            top: topPos + 'px',
            left: leftPos + 'px'
        });
    });

    // Klik item Set Tgl Kembali dari dalam floating popup
    $('#opsi-btn-tgl-kembali').on('click', function() {
        var id = $(this).data('id');
        var val = $(this).data('val');
        closeFloatingOpsi();
        $('#tgl-id').val(id);
        $('#tgl-input-val').val(val);
        modalTgl.show();
    });

    // Tutup floating popup saat klik di luar, tekan Esc, atau scroll
    $(document).on('click', function(e) {
        if ($floatingOpsi.is(':visible') && !$(e.target).closest('#ttbp-floating-opsi, .btn-open-opsi').length) {
            closeFloatingOpsi();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $floatingOpsi.is(':visible')) {
            closeFloatingOpsi();
        }
    });

    $('.custom-scroll-container, window').on('scroll resize', function() {
        if ($floatingOpsi.is(':visible')) {
            closeFloatingOpsi();
        }
    });
    $(window).on('scroll resize', function() {
        if ($floatingOpsi.is(':visible')) {
            closeFloatingOpsi();
        }
    });

    // 1. Pratinjau Lembar TTBP Fisik
    $('.btn-preview-ttbp').on('click', function() {
        closeFloatingOpsi();
        var nomor = $(this).data('nomor');
        $('#preview-sheet-body').html('<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="small text-muted ms-2">Menyiapkan lembar TTBP...</span></div>');
        $('#btn-modal-excel-sheet').attr('href', '<?= site_url("ttbp/export_excel/"); ?>' + encodeURIComponent(nomor));
        $('#btn-modal-print-sheet').attr('href', '<?= site_url("ttbp/cetak/"); ?>' + encodeURIComponent(nomor) + '?autoprint=1');
        modalPreview.show();

        $.get('<?= site_url("ttbp/preview_partial_ajax/"); ?>' + encodeURIComponent(nomor), function(html) {
            $('#preview-sheet-body').html(html);
        }).fail(function() {
            $('#preview-sheet-body').html('<div class="alert alert-danger mb-0">Gagal memuat lembar TTBP.</div>');
        });
    });

    // 2. Edit Tanggal Kembali TTBP
    $('.btn-edit-tgl-kembali').on('click', function() {
        closeFloatingOpsi();
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

        $.post('<?= site_url("ttbp/update_tgl_kembali_ajax"); ?>', { id: id, tgl_kembali: tgl }, function(res) {
            if (res.status) {
                modalTgl.hide();
                window.location.reload();
            } else {
                alert('Gagal menyimpan tanggal.');
            }
        }, 'json');
    });
});
</script>
