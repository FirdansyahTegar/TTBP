<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('check_login')) {
    function check_login() {
        $ci = &get_instance();
        if (!$ci->session->userdata('logged_in')) {
            $ci->session->set_flashdata('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
            redirect('auth');
        }
    }
}

if (!function_exists('check_role')) {
    function check_role($allowed_roles = array()) {
        $ci = &get_instance();
        check_login();
        $user_role = $ci->session->userdata('role_id');
        if (!in_array($user_role, $allowed_roles)) {
            show_error('Anda tidak memiliki izin untuk mengakses halaman ini.', 403, 'Akses Ditolak');
        }
    }
}

if (!function_exists('logged_in')) {
    function logged_in() {
        $ci = &get_instance();
        return (bool) $ci->session->userdata('logged_in');
    }
}

if (!function_exists('current_user')) {
    function current_user($field = null) {
        $ci = &get_instance();
        if ($field) {
            return $ci->session->userdata($field);
        }
        return (object) [
            'id'            => $ci->session->userdata('user_id'),
            'name'          => $ci->session->userdata('name') ?: 'Pengguna',
            'username'      => $ci->session->userdata('username'),
            'email'         => $ci->session->userdata('email'),
            'role_id'       => $ci->session->userdata('role_id'),
            'role_name'     => $ci->session->userdata('role_name') ?: 'User',
            'division_id'   => $ci->session->userdata('division_id'),
            'division_name' => $ci->session->userdata('division_name') ?: '-'
        ];
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        $ci = &get_instance();
        $role_name = strtolower($ci->session->userdata('role_name') ?: '');
        $role_id = $ci->session->userdata('role_id');
        return ($role_id == 1 || $role_name === 'admin' || $role_name === 'superadmin');
    }
}
