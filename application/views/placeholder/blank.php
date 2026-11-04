<!-- PAGE HEADER -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-code text-primary me-2"></i>Blank Page Placeholder
        </h4>
        <p class="text-muted small mb-0">Gunakan halaman ini sebagai titik awal (boilerplate) saat membuat modul atau fitur baru di aplikasi Anda.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Ini adalah tombol aksi placeholder!')">
            <i class="bi bi-plus-lg me-1"></i> Tambah Item Baru
        </button>
    </div>
</div>

<!-- MAIN CARD CONTAINER -->
<div class="card card-custom p-4 mb-4">
    <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0">Konten Halaman Baru Anda</h6>
            <small class="text-muted">Ganti blok isi ini dengan tabel, form, grafik, atau antarmuka yang Anda butuhkan.</small>
        </div>
        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill small">Siap Dimodifikasi</span>
    </div>

    <!-- Placeholder Content Area -->
    <div class="p-4 rounded-3 bg-light border border-dashed text-center my-3">
        <div class="display-6 text-primary mb-3">
            <i class="bi bi-layers-half"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Area Kerja Komponen Anda</h5>
        <p class="text-muted small mx-auto mb-3" style="max-width: 520px;">
            Halaman ini sudah otomatis terhubung dengan <code>header</code>, <code>sidebar</code> responsif, <code>topbar</code> pengguna, dan <code>footer</code>. Cukup tuliskan kode HTML/PHP tampilan Anda di file view ini.
        </p>
        <div class="d-inline-flex gap-2">
            <a href="<?= site_url('placeholder/table'); ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-table me-1"></i> Lihat Contoh Tabel
            </a>
            <a href="<?= site_url('placeholder/form'); ?>" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-ui-checks me-1"></i> Lihat Contoh Form
            </a>
        </div>
    </div>

    <!-- Code Snippet Guide -->
    <div class="mt-4">
        <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-code-slash text-primary me-1"></i>Contoh Kode Controller Pemanggil:</h6>
        <div class="bg-dark text-light p-3 rounded-3 small font-monospace overflow-x-auto">
<pre class="mb-0 text-white"><code>&lt;?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modul_baru extends CI_Controller {
    public function __construct() {
        parent::__construct();
        check_login(); // Mengamankan halaman dari akses tanpa login
    }

    public function index() {
        $data['title'] = 'Nama Halaman Baru';
        
        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('modul_baru/index', $data);
        $this->load->view('layouts/footer');
    }
}</code></pre>
        </div>
    </div>
</div>

