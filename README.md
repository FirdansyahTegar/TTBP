# 🚀 Template App &mdash; CodeIgniter 3 Starter Kit

Project template web application modern, responsif, dan siap pakai berbasis **CodeIgniter 3**, **Bootstrap 5.3**, dan **Bootstrap Icons**.

---

## ✨ Fitur Utama Template

1. **🎨 Tampilan & Arsitektur Siap Pakai**:
   - Layout modular: `layouts/header.php`, `layouts/sidebar.php`, `layouts/footer.php`.
   - Gaya visual modern dengan nuansa soft-tone theme (`assets/css/kt_style.css`).
   - Kompatibilitas penuh dengan URL lama (alias route & controller otomatis).

2. **📱 Sidebar Navigasi Responsif**:
   - Logo & identitas aplikasi di bagian atas.
   - Kartu profil ringkas pengguna (Avatar inisial, Nama, Role badge, status aktif).
   - Menu navigasi berkategori (*Menu Utama*, *View Placeholder*, *Manajemen Sistem*, *Aksi Cepat*).
   - Status navigasi aktif otomatis (*active link detection* sesuai URL).
   - Mode responsif mobile drawer offcanvas + backdrop overlay dengan sentuhan transisi halus.

3. **🔐 Antarmuka Login (Login Interface)**:
   - Desain kartu login modern dan elegan.
   - Input Email / Username + Password dengan ikon interaktif.
   - Tombol toggle untuk menampilkan / menyembunyikan password (*show/hide eye toggle*).
   - Notifikasi flash error & sukses dismissible.
   - **Quick Fill Akun Demo**: Tombol 1-klik untuk otomatis mengisi kredensial demo pengujian.

4. **🧩 View Placeholders (Siap Dimodifikasi)**:
   - **Dashboard Placeholder** (`/dashboard`): Welcome hero banner, 4 KPI metric cards, grafik visual bulanan, ringkasan tabel data, panduan developer, dan status sistem.
   - **Blank Page Placeholder** (`/placeholder/blank`): Kanvas bersih siap pakai sebagai panduan membuat modul baru.
   - **Tabel Data & Filter Placeholder** (`/placeholder/table`): Antarmuka CRUD tabel, kolom pencarian, filter kategori/status, sorting header, pagination, dan modal pop-up tambah data.
   - **Form & Input Elements Placeholder** (`/placeholder/form`): Contoh input teks, email, select, switch, radio, validasi pesan input (`is-valid` / `is-invalid`), dan upload file dengan pratinjau.
   - **Profil Pengguna Placeholder** (`/placeholder/profile`): Halaman rincian profil pengguna dan form ubah kata sandi mandiri.
   - **Manajemen Pengguna** (`/auth/register`): Halaman pendaftaran user baru dan reset password khusus Administrator.

---

## 🔑 Kredensial Login Bawaan

| Username | Password | Role | Divisi |
| :--- | :--- | :--- | :--- |
| `superadmin` | `karisma` | Admin | IT & Software |
| `admin` | `admin123` | Admin | IT & Software |
| `manager` | `karisma` | Manager | Operasional |
| `firdan` | `karisma` | Staff / User | IT & Software |

*(Anda juga dapat mengklik langsung kartu **Quick Fill Akun Demo** di halaman login).*

---

## 🛠️ Panduan Memulai (Quick Start)

### 1. Konfigurasi Database
File konfigurasi database berada di `application/config/database.php`.
Skema SQL bawaan tersedia di file:
```
database/template_app.sql
```
Impor file tersebut ke database MySQL Anda (misal `timeline_it` atau `template_app`).

### 2. Cara Menambahkan Halaman Baru
Untuk membuat halaman baru dengan tata letak template lengkap:

1. Buat controller baru di `application/controllers/`, contoh `Produk.php`:
   ```php
   <?php
   defined('BASEPATH') OR exit('No direct script access allowed');

   class Produk extends CI_Controller {
       public function __construct() {
           parent::__construct();
           check_login(); // Mengamankan halaman agar wajib login
       }

       public function index() {
           $data['title'] = 'Data Produk';
           
           $this->load->view('layouts/header', $data);
           $this->load->view('layouts/sidebar');
           $this->load->view('produk/index', $data);
           $this->load->view('layouts/footer');
       }
   }
   ```

2. Buat view Anda di `application/views/produk/index.php`. Anda bisa meniru struktur dari `application/views/placeholder/blank.php`.

3. Daftarkan menu baru pada sidebar di `application/views/layouts/sidebar.php`.

---

## 📂 Struktur Direktori Utama

```
template_app/
├── application/
│   ├── config/             # Konfigurasi autoload, database, routes
│   ├── controllers/
│   │   ├── Auth.php        # Autentikasi, login, logout, kelola akun
│   │   ├── Dashboard.php   # Dashboard utama aplikasi
│   │   └── Placeholder.php # Controller halaman-halaman placeholder
│   ├── helpers/
│   │   └── auth_helper.php # Helper check_login(), current_user(), is_admin()
│   ├── models/
│   │   └── User_model.php  # Model user terpadu & kompatibilitas multi-tabel
│   └── views/
│       ├── auth/           # View halaman login & registrasi
│       ├── dashboard/      # View halaman dashboard
│       ├── layouts/        # Header, Sidebar responsif, Topbar, Footer
│       └── placeholder/    # Blank page, Table CRUD UI, Form, Profile
├── assets/
│   ├── css/
│   │   └── kt_style.css    # Stylesheet utama responsif
│   └── img/                # Aset gambar & logo
└── database/
    └── template_app.sql    # Skema tabel & seed data awal
```

---

Dibuat dengan ❤️ untuk kemudahan memulai pengembangan proyek web Anda.

