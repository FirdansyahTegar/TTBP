<?php
    $user = current_user();
    $name_parts = explode(' ', trim($user->name));
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (count($name_parts) > 1) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
?>

<!-- PAGE HEADER -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-badge text-info me-2"></i>Profil & Pengaturan Akun
        </h4>
        <p class="text-muted small mb-0">Kelola identitas akun Anda, informasi divisi, serta pembaruan kata sandi.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- PROFIL CARD INFO (KIRI) -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center">
            <div class="mx-auto mb-3 position-relative" style="width: 80px; height: 80px;">
                <div class="w-100 h-100 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold display-6 shadow-sm">
                    <?= $initials; ?>
                </div>
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle"></span>
            </div>

            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user->name); ?></h5>
            <p class="text-muted small mb-2">@<?= htmlspecialchars($user->username ?: 'user'); ?></p>
            
            <div class="d-flex justify-content-center gap-1 mb-3">
                <span class="badge badge-soft-blue px-2 py-1"><i class="bi bi-shield-check me-1"></i><?= htmlspecialchars($user->role_name); ?></span>
                <span class="badge bg-light text-muted border px-2 py-1"><?= htmlspecialchars($user->division_name); ?></span>
            </div>

            <hr class="my-3">

            <div class="text-start small">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Email:</span>
                    <strong class="text-dark"><?= htmlspecialchars($user->email ?: '-'); ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">User ID:</span>
                    <strong class="text-dark">#<?= $user->id; ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Status Akun:</span>
                    <span class="badge badge-soft-green">Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB PENGATURAN (KANAN) -->
    <div class="col-lg-8">
        <!-- Card Ganti Password -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="bi bi-key-fill text-warning me-2"></i>Ubah Kata Sandi
            </h6>

            <form action="<?= site_url('auth/change_password'); ?>" method="POST">
                <input type="hidden" name="user_id" value="<?= $user->id; ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Password Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter..." required minlength="6">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Ketik ulang password baru..." required minlength="6">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-shield-lock me-1"></i> Perbarui Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Card Informasi Profil -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="bi bi-info-circle text-primary me-2"></i>Tentang Akun Anda
            </h6>
            <p class="text-muted small mb-0">
                Pengelolaan hak akses role dan perubahan divisi dapat dilakukan oleh Administrator melalui menu <a href="<?= site_url('auth/register'); ?>" class="text-primary fw-bold text-decoration-none">Kelola Pengguna</a>.
            </p>
        </div>
    </div>
</div>

