<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TTBP</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons 1.11 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Inter & Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('assets/css/kt_style.css'); ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico'); ?>">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        .login-header {
            padding: 32px 32px 20px 32px;
            text-align: center;
        }
        .login-body {
            padding: 0 32px 32px 32px;
        }
        .brand-badge {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 16px;
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.3);
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.15);
        }
        .input-group-text {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
        }
        .demo-chip {
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        .demo-chip:hover {
            background-color: #e0f2fe !important;
            border-color: #0284c7 !important;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Header Brand -->
        <div class="login-header">
            <div class="brand-badge">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">TTBP</h4>
            <p class="text-muted small mb-0">Tanda Terima Barang Promosi</p>
        </div>

        <div class="login-body">
            <!-- Flash Message Alerts -->
            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm p-3 small mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2"></i>
                        <div><?= $this->session->flashdata('error'); ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm p-3 small mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                        <div><?= $this->session->flashdata('success'); ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(validation_errors()): ?>
                <div class="alert alert-warning border-0 p-2 small mb-3">
                    <?= validation_errors('<div class="small">', '</div>'); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= site_url('auth'); ?>" method="POST" id="formLogin">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Email atau Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="identity" id="inputIdentity" class="form-control" placeholder="Contoh: superadmin atau admin@admin.com" value="<?= set_value('identity'); ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Password</label>
                        <a href="javascript:void(0)" class="small text-decoration-none text-muted" onclick="alert('Untuk mereset password, hubungi Administrator atau gunakan tombol Quick Fill demo di bawah.')" style="font-size: 0.75rem;">Lupa Password?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="••••••••" required>
                        <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" title="Tampilkan/Sembunyikan Password">
                            <i class="bi bi-eye" id="iconEye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label small text-muted" for="rememberMe">
                        Ingat sesi saya di perangkat ini
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                </button>
            </form>

            <!-- Quick Demo Credentials Helper for Developers -->
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        <i class="bi bi-magic me-1 text-primary"></i> Quick Fill Akun Demo
                    </span>
                    <span class="badge bg-light text-muted border small" style="font-size: 0.65rem;">Klik untuk isi otomatis</span>
                </div>
                
                <div class="d-flex flex-column gap-1">
                    <div class="p-2 border rounded-2 bg-light d-flex justify-content-between align-items-center demo-chip" onclick="fillCredentials('superadmin', 'karisma')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-primary"></i>
                            <span class="small fw-bold text-dark">Admin (Full Access)</span>
                        </div>
                        <span class="small text-muted font-monospace" style="font-size: 0.72rem;">superadmin / karisma</span>
                    </div>

                    <div class="p-2 border rounded-2 bg-light d-flex justify-content-between align-items-center demo-chip" onclick="fillCredentials('firdan', 'karisma')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-check-fill text-success"></i>
                            <span class="small fw-bold text-dark">Staff / User</span>
                        </div>
                        <span class="small text-muted font-monospace" style="font-size: 0.72rem;">firdan / karisma</span>
                    </div>
                </div>
            </div>

            <!-- Footer Text -->
            <div class="text-center mt-4">
                <small class="text-muted" style="font-size: 0.75rem;">
                    &copy; <?= date('Y'); ?> Template App CI3 &bull; Clean Architecture
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Show/Hide Password
        const btnTogglePassword = document.getElementById('btnTogglePassword');
        const inputPassword = document.getElementById('inputPassword');
        const iconEye = document.getElementById('iconEye');

        if (btnTogglePassword && inputPassword) {
            btnTogglePassword.addEventListener('click', function () {
                const isPassword = inputPassword.getAttribute('type') === 'password';
                inputPassword.setAttribute('type', isPassword ? 'text' : 'password');
                iconEye.classList.toggle('bi-eye', !isPassword);
                iconEye.classList.toggle('bi-eye-slash', isPassword);
            });
        }

        // Quick Fill Helper for Demo Testing
        function fillCredentials(username, password) {
            document.getElementById('inputIdentity').value = username;
            document.getElementById('inputPassword').value = password;
            document.getElementById('inputPassword').focus();
        }
    </script>
</body>
</html>

