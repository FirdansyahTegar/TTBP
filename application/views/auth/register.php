<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-primary mb-1"><i class="bi bi-people-fill me-2"></i>Manajemen Akun & Pengguna</h4>
        <p class="text-muted small mb-0">Halaman khusus Admin untuk mendaftarkan akun baru, mengelola pengguna, dan mengubah password.</p>
    </div>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="background-color: var(--soft-green); color: var(--text-green);" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="background-color: var(--soft-red); color: var(--text-red);" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $this->session->flashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabs Navigasi: Registrasi & Daftar Akun -->
<ul class="nav nav-tabs nav-justified mb-4" id="userManagementTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active small fw-bold" id="tab-user-list-btn" data-bs-toggle="tab" data-bs-target="#tab-user-list" type="button" role="tab">
            <i class="bi bi-person-lines-fill me-1"></i> Data Akun Pengguna 
            <span class="badge bg-primary rounded-pill ms-1 shadow-sm"><?= !empty($users) ? count($users) : 0; ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link small fw-bold" id="tab-register-btn" data-bs-toggle="tab" data-bs-target="#tab-register" type="button" role="tab">
            <i class="bi bi-person-plus-fill me-1"></i> Registrasi Akun Baru
        </button>
    </li>
</ul>

<div class="tab-content" id="userManagementTabContent">
    <!-- TAB 1: DAFTAR AKUN & KELOLA PASSWORD -->
    <div class="tab-pane fade show active" id="tab-user-list" role="tabpanel" aria-labelledby="tab-user-list-btn">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold text-dark m-0"><i class="bi bi-shield-lock text-primary me-2"></i>Daftar Pengguna & Kredensial</h6>
                        <small class="text-muted">Klik tombol ganti password untuk mereset kata sandi akun pengguna.</small>
                    </div>
                    <div class="input-group input-group-sm w-100 w-sm-auto" style="max-width: 250px;">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="search-user-input" class="form-control bg-light border-0" placeholder="Cari nama/username...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-users">
                        <thead>
                            <tr class="table-light text-secondary small">
                                <th width="4%" class="px-3">No</th>
                                <th width="24%" class="px-3">Nama Lengkap</th>
                                <th width="16%" class="px-3">Username</th>
                                <th width="18%" class="px-3">Divisi & Role</th>
                                <th width="22%" class="px-3">Terakhir Login</th>
                                <th width="16%" class="px-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($users)): ?>
                                <?php $no = 1; foreach($users as $u): ?>
                                    <tr class="user-item-row" data-search="<?= strtolower(htmlspecialchars($u->name . ' ' . $u->username . ' ' . ($u->email ?? '') . ' ' . ($u->division_name ?? '') . ' ' . ($u->role_name ?? ''))); ?>">
                                        <td class="px-3 fw-bold text-muted"><?= $no++; ?></td>
                                        <td class="px-3">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($u->name); ?></div>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope me-1"></i><?= !empty($u->email) ? htmlspecialchars($u->email) : '<span class="fst-italic text-secondary">Tidak ada email</span>'; ?>
                                            </small>
                                        </td>
                                        <td class="px-3">
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <i class="bi bi-person me-1 text-primary"></i>@<?= htmlspecialchars($u->username); ?>
                                            </span>
                                        </td>
                                        <td class="px-3">
                                            <div class="small fw-semibold text-dark"><?= htmlspecialchars($u->division_name ?? '-'); ?></div>
                                            <?php 
                                                $role_badge = 'bg-secondary';
                                                if($u->role_name == 'Admin') $role_badge = 'bg-danger text-white';
                                                elseif($u->role_name == 'Manager') $role_badge = 'bg-warning text-dark';
                                                else $role_badge = 'bg-primary text-white';
                                            ?>
                                            <span class="badge <?= $role_badge; ?> px-2 py-0" style="font-size: 0.72rem;"><?= htmlspecialchars($u->role_name ?? '-'); ?></span>
                                        </td>
                                        <td class="px-3">
                                            <?php if(!empty($u->last_login)): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-clock-history text-success me-2 fs-6"></i>
                                                    <div>
                                                        <div class="fw-semibold text-dark small"><?= date('d M Y, H:i', strtotime($u->last_login)); ?> WIB</div>
                                                        <?php 
                                                            $diff = time() - strtotime($u->last_login);
                                                            if ($diff < 60) {
                                                                $time_ago = 'Baru saja';
                                                            } elseif ($diff < 3600) {
                                                                $time_ago = floor($diff / 60) . ' menit lalu';
                                                            } elseif ($diff < 86400) {
                                                                $time_ago = floor($diff / 3600) . ' jam lalu';
                                                            } else {
                                                                $time_ago = floor($diff / 86400) . ' hari lalu';
                                                            }
                                                        ?>
                                                        <small class="text-muted fst-italic" style="font-size: 0.75rem;"><?= $time_ago; ?></small>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border px-2 py-1 small">
                                                    <i class="bi bi-dash-circle me-1"></i>Belum pernah login
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 text-end">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary btn-change-pwd shadow-sm"
                                                    data-id="<?= $u->id; ?>"
                                                    data-name="<?= htmlspecialchars($u->name); ?>"
                                                    data-username="<?= htmlspecialchars($u->username); ?>">
                                                <i class="bi bi-key me-1"></i> Ganti Password
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada pengguna terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: FORM REGISTRASI AKUN BARU -->
    <div class="tab-pane fade" id="tab-register" role="tabpanel" aria-labelledby="tab-register-btn">
        <div class="card card-custom">
            <div class="card-body">
                <form action="<?= site_url('auth/register'); ?>" method="POST" id="form-register-user">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= set_value('name'); ?>" placeholder="Masukkan nama lengkap..." required>
                            <?= form_error('name', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold small">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= set_value('username'); ?>" placeholder="Tanpa spasi (contoh: john_doe)..." required>
                            <?= form_error('username', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold small">Alamat Email <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="email" name="email" class="form-control" value="<?= set_value('email'); ?>" placeholder="nama@perusahaan.com">
                            <?= form_error('email', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold small">Divisi <span class="text-danger">*</span></label>
                            <select name="division_id" class="form-select" required>
                                <option value="">Pilih Divisi...</option>
                                <?php foreach($divisions as $div): ?>
                                    <option value="<?= $div->id; ?>" <?= set_select('division_id', $div->id); ?>><?= $div->division_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= form_error('division_id', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold small">Hak Akses (Role) <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Pilih Role...</option>
                                <?php foreach($roles as $role): ?>
                                    <option value="<?= $role->id; ?>" <?= set_select('role_id', $role->id); ?>><?= $role->role_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= form_error('role_id', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold small">Password Default <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="reg-password" class="form-control" placeholder="Minimal 6 karakter" required>
                                <button class="btn btn-outline-secondary btn-toggle-pwd" type="button" data-target="#reg-password" title="Lihat/Sembunyikan Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <?= form_error('password', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm text-center" style="background-color: var(--color-blue-text); border:none;">
                            <i class="bi bi-person-plus me-1"></i> Daftarkan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ganti Password Akun Pengguna -->
<div class="modal fade" id="modalChangePassword" tabindex="-1" aria-labelledby="modalChangePasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= site_url('auth/change_password'); ?>" method="POST" id="form-change-password">
                <input type="hidden" name="user_id" id="modal_pwd_user_id">
                
                <div class="modal-header bg-light border-bottom">
                    <h6 class="modal-title fw-bold text-primary" id="modalChangePasswordLabel">
                        <i class="bi bi-key-fill me-2"></i>Ganti Password Pengguna
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-info-subtle py-2 px-3 small mb-3">
                        <div class="fw-bold" id="modal_pwd_user_name">-</div>
                        <div class="text-muted" id="modal_pwd_user_username">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimal 6 karakter..." required minlength="6">
                            <button class="btn btn-outline-secondary btn-toggle-pwd" type="button" data-target="#new_password" title="Lihat/Sembunyikan Password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Ulangi password baru..." required minlength="6">
                            <button class="btn btn-outline-secondary btn-toggle-pwd" type="button" data-target="#confirm_password" title="Lihat/Sembunyikan Password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small id="pwd-mismatch-msg" class="text-danger d-none mt-1">Konfirmasi password tidak cocok!</small>
                    </div>

                    <div class="p-2 rounded bg-light border small text-muted">
                        <i class="bi bi-info-circle me-1 text-primary"></i>Password dienkripsi satu arah menggunakan hashing aman (BCrypt). Setelah diganti, berikan kata sandi baru kepada pemilik akun.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold" id="btn-submit-pwd" style="background-color: var(--color-blue-text); border:none;">
                        <i class="bi bi-check-lg me-1"></i> Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click', '.btn-toggle-pwd', function(e) {
        e.preventDefault();
        let targetSelector = $(this).data('target');
        let inputEl = $(targetSelector);
        let iconEl = $(this).find('i');

        if (inputEl.attr('type') === 'password') {
            inputEl.attr('type', 'text');
            iconEl.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            inputEl.attr('type', 'password');
            iconEl.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    $(document).on('click', '.btn-change-pwd', function(e) {
        e.preventDefault();
        let userId = $(this).data('id');
        let name = $(this).data('name');
        let username = $(this).data('username');

        $('#modal_pwd_user_id').val(userId);
        $('#modal_pwd_user_name').text(name);
        $('#modal_pwd_user_username').text('@' + username);
        $('#new_password').val('').attr('type', 'password');
        $('#confirm_password').val('').attr('type', 'password');
        $('.btn-toggle-pwd i').removeClass('bi-eye-slash').addClass('bi-eye');
        $('#pwd-mismatch-msg').addClass('d-none');

        let modalEl = document.getElementById('modalChangePassword');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });

    $('#form-change-password').on('submit', function(e) {
        let p1 = $('#new_password').val();
        let p2 = $('#confirm_password').val();

        if (p1 !== p2) {
            e.preventDefault();
            $('#pwd-mismatch-msg').removeClass('d-none');
            $('#confirm_password').focus();
            return false;
        }
        $('#pwd-mismatch-msg').addClass('d-none');
    });

    $('#search-user-input').on('keyup', function() {
        let term = $(this).val().toLowerCase().trim();
        $('.user-item-row').each(function() {
            let rowText = $(this).data('search') || '';
            if (rowText.includes(term)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    <?php if(validation_errors()): ?>
        let regTab = new bootstrap.Tab(document.getElementById('tab-register-btn'));
        regTab.show();
    <?php endif; ?>
});
</script>
