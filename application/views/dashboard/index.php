<?php
    $user = current_user();
    $name_first = explode(' ', trim($user->name))[0];
?>

<!-- WELCOME BANNER -->
<div class="card card-custom border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-25 text-white small mb-2" style="font-size: 0.75rem;">
                <span class="badge bg-success rounded-circle p-1"></span> Project Template Siap Pakai &bull; v1.0
            </div>
            <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($name_first); ?>! 👋</h3>
            <p class="mb-0 opacity-75 small">Template starter kit siap pakai dengan sidebar dinamis, autentikasi terpadu, dan view placeholder.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('placeholder/blank'); ?>" class="btn btn-light btn-sm fw-semibold px-3 py-2 text-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Mulai Halaman Baru
            </a>
            <button type="button" class="btn btn-outline-light btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTemplateInfo">
                <i class="bi bi-question-circle me-1"></i> Panduan
            </button>
        </div>
    </div>
</div>

<!-- FLASH MESSAGES -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm auto-dismiss p-3 small mb-4" style="background-color: var(--soft-green); color: var(--text-green);" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-6"></i><?= $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm p-3 small mb-4" style="background-color: var(--soft-red); color: var(--text-red);" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i><?= $this->session->flashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- 4 METRIC KPI STATS -->
<div class="row g-3 mb-4">
    <!-- Stat 1: Total Pengguna -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Total Pengguna</span>
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold mb-0 text-dark"><?= isset($total_users) ? $total_users : '12'; ?></h3>
                <span class="badge badge-soft-green small" style="font-size: 0.7rem;"><i class="bi bi-arrow-up-short"></i>+12%</span>
            </div>
            <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">Akun terdaftar dalam sistem</small>
        </div>
    </div>

    <!-- Stat 2: Modul & Placeholder -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">View Placeholder</span>
                <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-grid-fill fs-5"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold mb-0 text-dark">5</h3>
                <span class="badge badge-soft-blue small" style="font-size: 0.7rem;">Siap Pakai</span>
            </div>
            <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">Dashboard, Blank, Table, Form, Profile</small>
        </div>
    </div>

    <!-- Stat 3: Status Sesi & Hak Akses -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Role Pengguna</span>
                <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-shield-check fs-5"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="fw-bold mb-0 text-dark text-truncate"><?= htmlspecialchars($user->role_name); ?></h4>
            </div>
            <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">Divisi: <?= htmlspecialchars($user->division_name); ?></small>
        </div>
    </div>

    <!-- Stat 4: Sistem & Responsivitas -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Kondisi Sistem</span>
                <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-activity fs-5"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold mb-0 text-success">Online</h3>
                <span class="badge badge-soft-green small" style="font-size: 0.7rem;">100% OK</span>
            </div>
            <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">PHP <?= phpversion(); ?> &bull; CI 3 &bull; Bootstrap 5</small>
        </div>
    </div>
</div>

<!-- MAIN GRID: CHART & DATA PLACEHOLDER + SIDE ACTIONS -->
<div class="row g-4">
    <!-- Kolom Kiri: Visual Widget & Tabel Placeholder -->
    <div class="col-lg-8">
        <!-- Visual Widget Placeholder -->
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="bi bi-bar-chart-line text-primary me-2"></i>Statistik Aktivitas Sistem (Placeholder)
                    </h6>
                    <small class="text-muted">Visualisasi tren aktivitas pengguna dan transaksi data bulanan</small>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active">Bulanan</button>
                    <button type="button" class="btn btn-outline-secondary">Mingguan</button>
                    <button type="button" class="btn btn-outline-secondary">Harian</button>
                </div>
            </div>

            <!-- Mock Chart Canvas / Styled Bars -->
            <div class="p-3 bg-light rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3 text-muted small">
                    <span><i class="bi bi-circle-fill text-primary me-1" style="font-size: 0.5rem;"></i> Pengunjung</span>
                    <span><i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i> Transaksi Data</span>
                    <span><i class="bi bi-circle-fill text-warning me-1" style="font-size: 0.5rem;"></i> Task Selesai</span>
                </div>
                
                <!-- Bar Chart Bars -->
                <div class="d-flex align-items-end justify-content-between gap-2 pt-4 pb-2" style="height: 180px;">
                    <?php 
                        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'];
                        $heights = [45, 60, 55, 75, 80, 65, 90, 85, 95, 70];
                        foreach ($months as $idx => $m):
                    ?>
                        <div class="d-flex flex-column align-items-center flex-grow-1 h-100 justify-content-end">
                            <div class="w-100 rounded-top" style="height: <?= $heights[$idx]; ?>%; background: linear-gradient(180deg, #0284c7 0%, rgba(2, 132, 199, 0.4) 100%); transition: height 0.4s ease;" title="<?= $m; ?>: <?= $heights[$idx]; ?>%"></div>
                            <span class="text-muted small mt-2" style="font-size: 0.7rem;"><?= $m; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tabel Ringkasan Data Placeholder -->
        <div class="card card-custom p-0 overflow-hidden">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                <div>
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-table text-success me-2"></i>Contoh Data Tabel Terbaru
                    </h6>
                    <small class="text-muted">Placeholder tabel dengan status badge dan aksi cepat</small>
                </div>
                <a href="<?= site_url('placeholder/table'); ?>" class="btn btn-outline-primary btn-sm small">
                    Buka Tabel Lengkap <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="px-3" width="8%">ID</th>
                            <th width="32%">Nama Modul / Task</th>
                            <th width="22%">Penganggung Jawab</th>
                            <th width="18%">Status</th>
                            <th width="20%" class="text-end px-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <tr>
                            <td class="px-3 fw-bold text-muted">#001</td>
                            <td>
                                <strong class="text-dark d-block">Setup Arsitektur CI3</strong>
                                <small class="text-muted">Template starter kit siap pakai</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">Admin Template</span></td>
                            <td><span class="badge badge-soft-green"><i class="bi bi-check2 me-1"></i>Selesai</span></td>
                            <td class="text-end px-3">
                                <a href="<?= site_url('placeholder/blank'); ?>" class="btn btn-sm btn-light border p-1 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3 fw-bold text-muted">#002</td>
                            <td>
                                <strong class="text-dark d-block">Integrasi Sidebar Responsif</strong>
                                <small class="text-muted">Drawer offcanvas + backdrop overlay</small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($user->name); ?></span></td>
                            <td><span class="badge badge-soft-green"><i class="bi bi-check2 me-1"></i>Selesai</span></td>
                            <td class="text-end px-3">
                                <a href="<?= site_url('placeholder/blank'); ?>" class="btn btn-sm btn-light border p-1 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3 fw-bold text-muted">#003</td>
                            <td>
                                <strong class="text-dark d-block">Form & Input Validation UI</strong>
                                <small class="text-muted">Komponen formulir siap pakai</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">Developer</span></td>
                            <td><span class="badge badge-soft-blue"><i class="bi bi-clock me-1"></i>Ready</span></td>
                            <td class="text-end px-3">
                                <a href="<?= site_url('placeholder/form'); ?>" class="btn btn-sm btn-light border p-1 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3 fw-bold text-muted">#004</td>
                            <td>
                                <strong class="text-dark d-block">Data Table CRUD Template</strong>
                                <small class="text-muted">Filter, pencarian, dan pagination</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">Developer</span></td>
                            <td><span class="badge badge-soft-yellow"><i class="bi bi-lightning-charge me-1"></i>Tersedia</span></td>
                            <td class="text-end px-3">
                                <a href="<?= site_url('placeholder/table'); ?>" class="btn btn-sm btn-light border p-1 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Quick Actions & Panduan Singkat -->
    <div class="col-lg-4">
        <!-- Quick Actions Card -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat & Navigasi
            </h6>
            <div class="d-grid gap-2">
                <a href="<?= site_url('placeholder/blank'); ?>" class="btn btn-outline-primary d-flex align-items-center justify-content-between p-2 px-3 text-start">
                    <div>
                        <strong class="d-block small">Buka Blank Page</strong>
                        <small class="text-muted" style="font-size: 0.72rem;">Kanvas dasar membuat view baru</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="<?= site_url('placeholder/table'); ?>" class="btn btn-outline-success d-flex align-items-center justify-content-between p-2 px-3 text-start">
                    <div>
                        <strong class="d-block small">Contoh Tabel Data</strong>
                        <small class="text-muted" style="font-size: 0.72rem;">Fitur filter, modal, & status</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="<?= site_url('placeholder/form'); ?>" class="btn btn-outline-warning d-flex align-items-center justify-content-between p-2 px-3 text-start">
                    <div>
                        <strong class="d-block small">Contoh Form & Validasi</strong>
                        <small class="text-muted" style="font-size: 0.72rem;">Input lengkap & upload file</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="<?= site_url('auth/register'); ?>" class="btn btn-outline-secondary d-flex align-items-center justify-content-between p-2 px-3 text-start">
                    <div>
                        <strong class="d-block small">Kelola Akun Pengguna</strong>
                        <small class="text-muted" style="font-size: 0.72rem;">Daftar user & reset kata sandi</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
        </div>

        <!-- Panduan Singkat Template -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-info-circle-fill text-info me-2"></i>Panduan Singkat Developer
            </h6>
            <ul class="list-unstyled small mb-3">
                <li class="d-flex align-items-start gap-2 mb-2">
                    <i class="bi bi-check-circle text-success mt-1"></i>
                    <span><strong>Sidebar Otomatis:</strong> Aktif mendeteksi segment URL halaman yang sedang dibuka.</span>
                </li>
                <li class="d-flex align-items-start gap-2 mb-2">
                    <i class="bi bi-check-circle text-success mt-1"></i>
                    <span><strong>Keamanan Login:</strong> Gunakan <code>check_login();</code> pada constructor controller.</span>
                </li>
                <li class="d-flex align-items-start gap-2 mb-2">
                    <i class="bi bi-check-circle text-success mt-1"></i>
                    <span><strong>Responsif Penuh:</strong> Drawer sidebar bergerak mulus di mobile & desktop.</span>
                </li>
            </ul>
            <button type="button" class="btn btn-light w-100 btn-sm text-primary border" data-bs-toggle="modal" data-bs-target="#modalTemplateInfo">
                <i class="bi bi-journal-text me-1"></i> Baca Panduan Lengkap
            </button>
        </div>
    </div>
</div>

