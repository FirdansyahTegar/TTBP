<!-- PAGE HEADER -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-table text-success me-2"></i>Tabel Data & Filter Placeholder
        </h4>
        <p class="text-muted small mb-0">Contoh antarmuka tabel data lengkap dengan kolom pencarian, filter status, export, dan modal interaktif.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="alert('Export data ke format Excel / CSV.')">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahData">
            <i class="bi bi-plus-lg me-1"></i> Tambah Data Baru
        </button>
    </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="card card-custom p-3 mb-3">
    <div class="row g-2 align-items-center">
        <!-- Search Input -->
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control bg-light border-0" id="tableSearch" placeholder="Cari data berdasarkan nama atau kata kunci...">
            </div>
        </div>

        <!-- Filter Status Dropdown -->
        <div class="col-md-3">
            <select class="form-select form-select-sm bg-light border-0" id="filterStatus">
                <option value="">Semua Status</option>
                <option value="Aktif">Aktif</option>
                <option value="Pending">Menunggu</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
        </div>

        <!-- Filter Kategori -->
        <div class="col-md-3">
            <select class="form-select form-select-sm bg-light border-0" id="filterKategori">
                <option value="">Semua Kategori</option>
                <option value="Umum">Umum</option>
                <option value="Teknis">Teknis</option>
                <option value="Operasional">Operasional</option>
            </select>
        </div>

        <!-- Reset Button -->
        <div class="col-md-1 text-md-end">
            <button type="button" class="btn btn-light btn-sm w-100 text-muted border" onclick="document.getElementById('tableSearch').value=''; document.getElementById('filterStatus').value=''; document.getElementById('filterKategori').value='';" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </div>
    </div>
</div>

<!-- DATA TABLE CARD -->
<div class="card card-custom p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="sampleTable">
            <thead class="table-light text-secondary small">
                <tr>
                    <th width="4%" class="px-3">No</th>
                    <th width="12%">Kode</th>
                    <th width="26%">Nama Item / Judul</th>
                    <th width="16%">Kategori</th>
                    <th width="14%">Tanggal Dibuat</th>
                    <th width="14%">Status</th>
                    <th width="14%" class="text-end px-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php
                    $dummy_items = [
                        ['id' => 1, 'kode' => 'TMP-001', 'nama' => 'Template Header & Sidebar', 'kategori' => 'Teknis', 'tanggal' => '2026-10-18', 'status' => 'Aktif'],
                        ['id' => 2, 'kode' => 'TMP-002', 'nama' => 'Autentikasi Login Terpusat', 'kategori' => 'Teknis', 'tanggal' => '2026-10-19', 'status' => 'Aktif'],
                        ['id' => 3, 'kode' => 'TMP-003', 'nama' => 'Formulir Validasi Pengguna', 'kategori' => 'Operasional', 'tanggal' => '2026-10-20', 'status' => 'Pending'],
                        ['id' => 4, 'kode' => 'TMP-004', 'nama' => 'Konfigurasi Notifikasi Email', 'kategori' => 'Umum', 'tanggal' => '2026-10-21', 'status' => 'Nonaktif'],
                        ['id' => 5, 'kode' => 'TMP-005', 'nama' => 'Modul Export Dokumen PDF', 'kategori' => 'Teknis', 'tanggal' => '2026-10-21', 'status' => 'Pending'],
                    ];
                    foreach ($dummy_items as $idx => $item):
                        $badge_class = 'badge-soft-green';
                        if ($item['status'] == 'Pending') $badge_class = 'badge-soft-yellow';
                        if ($item['status'] == 'Nonaktif') $badge_class = 'badge-soft-red';
                ?>
                    <tr>
                        <td class="px-3 text-muted"><?= $idx + 1; ?></td>
                        <td><span class="badge bg-light text-dark border font-monospace"><?= $item['kode']; ?></span></td>
                        <td>
                            <strong class="text-dark d-block"><?= $item['nama']; ?></strong>
                            <small class="text-muted">Item sampel data placeholder</small>
                        </td>
                        <td><span class="badge bg-light text-secondary border"><?= $item['kategori']; ?></span></td>
                        <td><?= date('d M Y', strtotime($item['tanggal'])); ?></td>
                        <td>
                            <span class="badge <?= $badge_class; ?>">
                                <?= $item['status']; ?>
                            </span>
                        </td>
                        <td class="text-end px-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border p-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1 small">
                                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="alert('Detail item: <?= $item['nama']; ?>')"><i class="bi bi-eye text-primary me-2"></i>Detail</a></li>
                                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="alert('Edit item: <?= $item['nama']; ?>')"><i class="bi bi-pencil text-warning me-2"></i>Edit</a></li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li><a class="dropdown-item py-1 text-danger" href="javascript:void(0)" onclick="if(confirm('Hapus item ini?')) alert('Item dihapus.')"><i class="bi bi-trash text-danger me-2"></i>Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- TABLE PAGINATION FOOTER -->
    <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
        <small class="text-muted">Menampilkan 1 sampai 5 dari 5 entri data</small>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Selanjutnya</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- MODAL TAMBAH DATA (PLACEHOLDER) -->
<div class="modal fade" id="modalTambahData" tabindex="-1" aria-labelledby="modalTambahDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold text-dark" id="modalTambahDataLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Modal Tambah Data Placeholder
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="event.preventDefault(); alert('Data berhasil disimpan (simulasi)!'); bootstrap.Modal.getInstance(document.getElementById('modalTambahData')).hide();">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Judul / Nama Item</label>
                        <input type="text" class="form-control" placeholder="Masukkan judul..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Kategori</label>
                        <select class="form-select" required>
                            <option value="">Pilih Kategori...</option>
                            <option value="Teknis">Teknis</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Umum">Umum</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Deskripsi Singkat</label>
                        <textarea class="form-control" rows="3" placeholder="Tuliskan keterangan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

