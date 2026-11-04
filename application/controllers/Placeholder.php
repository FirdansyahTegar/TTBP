<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Placeholder extends CI_Controller {

    public function __construct() {
        parent::__construct();
        check_login();
    }

    public function index() {
        $this->blank();
    }

    public function blank() {
        $data['title'] = 'Blank Page';
        $data['breadcrumb_parent'] = 'Placeholder';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('placeholder/blank', $data);
        $this->load->view('layouts/footer');
    }

    public function table() {
        $data['title'] = 'Tabel Data & Filter';
        $data['breadcrumb_parent'] = 'Placeholder';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('placeholder/table', $data);
        $this->load->view('layouts/footer');
    }

    public function form() {
        $data['title'] = 'Form & Input Elements';
        $data['breadcrumb_parent'] = 'Placeholder';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('placeholder/form', $data);
        $this->load->view('layouts/footer');
    }

    public function profile() {
        $data['title'] = 'Profil & Akun Pengguna';
        $data['breadcrumb_parent'] = 'Pengaturan';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('placeholder/profile', $data);
        $this->load->view('layouts/footer');
    }
}

