<?php
    $ci =& get_instance();
    $user = current_user();
    $current_uri_1 = $ci->uri->segment(1);
    $current_uri_2 = $ci->uri->segment(2);

    // Initial avatar letters
    $name_parts = explode(' ', trim($user->name));
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (count($name_parts) > 1) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
?>

<!-- Backdrop Overlay Mobile/Tablet -->
<div id="sidebar-backdrop" class="sidebar-backdrop" onclick="closeSidebarMobile(event)"></div>

<script>
var _drawerDebounceTimer = 0;

window.openSidebarMobile = function(e) {
    if (e) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
    }
    var sb = document.getElementById('sidebar');
    var bd = document.getElementById('sidebar-backdrop');
    if (sb) sb.classList.add('show');
    if (bd) bd.classList.add('show');
    document.body.classList.add('sidebar-open');
    return false;
};

window.closeSidebarMobile = function(e) {
    if (e) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
    }
    var sb = document.getElementById('sidebar');
    var bd = document.getElementById('sidebar-backdrop');
    if (sb) sb.classList.remove('show');
    if (bd) bd.classList.remove('show');
    document.body.classList.remove('sidebar-open');
    return false;
};

window.toggleSidebarMobile = function(e) {
    if (e) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
    }
    var now = Date.now();
    if (now - _drawerDebounceTimer < 350) return false;
    _drawerDebounceTimer = now;

    var sb = document.getElementById('sidebar');
    if (sb && sb.classList.contains('show')) {
        window.closeSidebarMobile(e);
    } else {
        window.openSidebarMobile(e);
    }
    return false;
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.closeSidebarMobile(e);
    }
});
</script>

<!-- SIDEBAR NAVIGATION -->
<nav id="sidebar">
    <!-- Brand Header -->
    <div class="sidebar-header d-flex justify-content-between align-items-center position-relative px-3 py-3 border-bottom">
        <a href="<?= site_url('ttbp'); ?>" class="d-flex align-items-center text-decoration-none gap-2">
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-weight: 800; font-size: 1rem;">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="text-start">
                <span class="fw-bold text-dark fs-6 d-block lh-1">TTBP</span>
                <span class="text-muted" style="font-size: 0.72rem;">Barang Promosi</span>
            </div>
        </a>
        <button type="button" id="sidebar-close-btn" class="btn btn-sm btn-light d-lg-none rounded-circle p-1" aria-label="Tutup Sidebar" onclick="closeSidebarMobile(event)" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="bi bi-x-lg fs-6"></i>
        </button>
    </div>

    <!-- User Mini Profile -->
    <div class="p-3 mx-2 my-2 rounded-3 bg-light border border-light-subtle">
        <div class="d-flex align-items-center gap-2">
            <div class="position-relative">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    <?= $initials; ?>
                </div>
                <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="transform: translate(-10%, -10%);"></span>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($user->name); ?>">
                    <?= htmlspecialchars($user->name); ?>
                </div>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <span class="badge badge-soft-blue small" style="font-size: 0.68rem; padding: 2px 6px;">
                        <?= htmlspecialchars($user->role_name); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Items -->
    <ul class="list-unstyled components mb-0">
        <!-- Section: Menu Utama -->
        <li class="sidebar-heading px-3 pt-2 pb-1 text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.08em;">
            Menu Utama
        </li>
        <li class="<?= (($current_uri_1 == 'ttbp' && empty($current_uri_2)) || $current_uri_1 == 'dashboard' || empty($current_uri_1)) ? 'active' : ''; ?>">
            <a href="<?= site_url('ttbp'); ?>" onclick="if(window.innerWidth < 992) closeSidebarMobile()">
                <i class="bi bi-file-earmark-spreadsheet text-primary"></i> 
                <span>Bonus Promosi (TTBP)</span>
            </a>
        </li>
        <li class="<?= ($current_uri_1 == 'ttbp' && $current_uri_2 == 'import') ? 'active' : ''; ?>">
            <a href="<?= site_url('ttbp/import'); ?>" onclick="if(window.innerWidth < 992) closeSidebarMobile()">
                <i class="bi bi-cloud-arrow-up-fill text-success"></i> 
                <span>Import Excel</span>
            </a>
        </li>

        <!-- Section: Manajemen Sistem -->
        <li class="sidebar-heading px-3 pt-3 pb-1 text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.08em;">
            Sistem
        </li>
        <li class="<?= ($current_uri_1 == 'auth' && $current_uri_2 == 'register') ? 'active' : ''; ?>">
            <a href="<?= site_url('auth/register'); ?>" onclick="if(window.innerWidth < 992) closeSidebarMobile()">
                <i class="bi bi-people-fill text-secondary"></i> 
                <span>Kelola Pengguna</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal" onclick="if(window.innerWidth < 992) closeSidebarMobile()">
                <i class="bi bi-box-arrow-right text-danger"></i> 
                <span>Keluar</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Bottom Footer Info -->
    <div class="px-3 py-3 mt-4 text-center border-top text-muted" style="font-size: 0.72rem;">
        <span class="text-secondary opacity-75">&copy; <?= date('Y'); ?> TTBP</span>
    </div>
</nav>

<!-- CONTENT WRAPPER -->
<div id="content">
    <!-- TOPBAR / MAIN HEADER -->
    <header class="main-header d-flex align-items-center justify-content-between px-3 px-md-4 py-2 border-bottom bg-white sticky-top">
        <div class="d-flex align-items-center gap-3">
            <!-- Mobile Sidebar Toggle Button (HANYA MUNCUL DI MOBILE / TABLET, HIDE DI VIEW PC) -->
            <button type="button" class="btn btn-outline-secondary border-0 p-1 rounded-circle d-lg-none" id="btn-toggle-mobile" aria-label="Toggle Sidebar" onclick="toggleSidebarMobile(event)">
                <i class="bi bi-list fs-4"></i>
            </button>

            <!-- Page Title / Breadcrumb -->
            <div>
                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                    <?= isset($title) ? htmlspecialchars($title) : 'Dashboard'; ?>
                </h5>
            </div>
        </div>

        <!-- Right Header Items -->
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <!-- Current Date Pill Badge (Desktop) -->
            <div class="d-none d-md-flex align-items-center gap-1 px-3 py-1 bg-light rounded-pill text-muted small border" style="font-size: 0.75rem;">
                <i class="bi bi-calendar3 text-primary"></i>
                <span><?= date('l, d M Y'); ?></span>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center gap-2 p-1 pe-2 rounded-pill border" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.78rem;">
                        <?= $initials; ?>
                    </div>
                    <span class="d-none d-sm-inline fw-semibold text-dark small text-truncate" style="max-width: 120px;">
                        <?= htmlspecialchars($user->name); ?>
                    </span>
                    <i class="bi bi-chevron-down text-muted small" style="font-size: 0.7rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2 py-2" aria-labelledby="userMenuDropdown" style="min-width: 190px;">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark small text-truncate"><?= htmlspecialchars($user->name); ?></div>
                        <div class="text-muted small" style="font-size: 0.72rem;"><?= htmlspecialchars($user->role_name); ?></div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small d-flex align-items-center gap-2" href="<?= site_url('auth/change_password'); ?>">
                            <i class="bi bi-key text-primary"></i> Ganti Password
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small d-flex align-items-center gap-2" href="<?= site_url('auth/register'); ?>">
                            <i class="bi bi-people text-success"></i> Kelola Pengguna
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 small d-flex align-items-center gap-2 text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="bi bi-box-arrow-right text-danger"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- MAIN BODY CONTAINER -->
    <main class="p-3 p-md-4">
