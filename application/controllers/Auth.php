<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('identity', 'Email atau Username', 'required|trim', [
            'required' => 'Kolom %s wajib diisi.'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required|trim', [
            'required' => 'Kolom %s wajib diisi.'
        ]);

        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Login';
            $this->load->view('auth/login', $data);
        } else {
            $this->_process_login();
        }
    }

    private function _process_login() {
        $identity = $this->input->post('identity', TRUE);
        $password = $this->input->post('password', TRUE);

        $user = $this->User_model->get_user_login($identity);

        $password_match = false;
        if ($user) {
            // Cek hash bcrypt atau fallback password plain text jika ada
            if (password_verify($password, $user->password)) {
                $password_match = true;
            } elseif ($user->password === $password || $user->password === md5($password)) {
                $password_match = true;
            }
        }

        if ($user && $password_match) {
            // Update waktu login terakhir jika memungkinkan
            if (method_exists($this->User_model, 'update_last_login')) {
                $this->User_model->update_last_login($user->id);
            }

            $session_data = array(
                'user_id'       => $user->id,
                'name'          => $user->name,
                'username'      => $user->username,
                'email'         => $user->email,
                'role_id'       => $user->role_id,
                'role_name'     => !empty($user->role_name) ? $user->role_name : 'User',
                'division_id'   => isset($user->division_id) ? $user->division_id : 1,
                'division_name' => !empty($user->division_name) ? $user->division_name : 'General',
                'logged_in'     => TRUE
            );
            $this->session->set_userdata($session_data);
            $this->session->set_flashdata('success', 'Selamat datang kembali, <strong>' . htmlspecialchars($user->name) . '</strong>!');
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Username/Email atau Password yang Anda masukkan tidak sesuai.');
            redirect('auth');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }

    public function register() {
        check_login();

        // Hanya admin yang bisa mengelola pendaftaran user
        if (!is_admin()) {
            $this->session->set_flashdata('error', 'Halaman ini hanya dapat diakses oleh Administrator.');
            redirect('dashboard');
        }

        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');
        $this->form_validation->set_rules('username', 'Username', 'required|trim|alpha_dash');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('role_id', 'Role', 'required');
        $this->form_validation->set_rules('division_id', 'Divisi', 'required');

        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Manajemen Akun & Pengguna';
            $data['roles'] = $this->User_model->get_all_roles();
            $data['divisions'] = $this->User_model->get_all_divisions();
            $data['users'] = $this->User_model->get_all_users();

            $this->load->view('layouts/header', $data);
            $this->load->view('layouts/sidebar');
            $this->load->view('auth/register', $data);
            $this->load->view('layouts/footer');
        } else {
            $this->_process_register();
        }
    }

    private function _process_register() {
        $password_hashed = password_hash($this->input->post('password', TRUE), PASSWORD_BCRYPT);
        $email_input = $this->input->post('email', TRUE);
        $email = !empty($email_input) ? $email_input : NULL;

        $data = array(
            'name'        => $this->input->post('name', TRUE),
            'username'    => $this->input->post('username', TRUE),
            'email'       => $email,
            'password'    => $password_hashed,
            'role_id'     => $this->input->post('role_id', TRUE),
            'division_id' => $this->input->post('division_id', TRUE),
            'is_active'   => 1
        );

        $this->User_model->insert_user($data);
        $this->session->set_flashdata('success', 'Akun pengguna baru berhasil ditambahkan.');
        redirect('auth/register');
    }

    public function change_password() {
        check_login();

        $user_id = $this->input->post('user_id', TRUE);
        $new_password = $this->input->post('new_password', TRUE);
        $confirm_password = $this->input->post('confirm_password', TRUE);

        // Jika bukan admin dan user_id berbeda dengan user yang login, tolak
        if (!is_admin() && $user_id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak untuk mengubah kata sandi pengguna lain.');
            redirect('ttbp');
        }

        if (empty($user_id) || empty($new_password)) {
            $this->session->set_flashdata('error', 'Semua kolom password wajib diisi.');
            redirect('auth/register');
        }

        if (strlen($new_password) < 6) {
            $this->session->set_flashdata('error', 'Password baru minimal harus 6 karakter.');
            redirect('auth/register');
        }

        if ($new_password !== $confirm_password) {
            $this->session->set_flashdata('error', 'Konfirmasi password tidak cocok.');
            redirect('auth/register');
        }

        $user = $this->User_model->get_user_by_id($user_id);
        if (!$user) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('auth/register');
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $this->User_model->update_password($user_id, $hashed_password);

        $this->session->set_flashdata('success', 'Password pengguna <strong>' . htmlspecialchars($user->name) . '</strong> berhasil diperbarui.');
        
        if (is_admin()) {
            redirect('auth/register');
        } else {
            redirect('ttbp');
        }
    }
}

