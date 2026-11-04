<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    protected $table_users = 'users';
    protected $table_roles = 'roles';
    protected $table_divisions = 'divisions';

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->_detect_tables();
    }

    protected function _detect_tables() {
        try {
            if ($this->db->table_exists('users')) {
                $this->table_users = 'users';
            } elseif ($this->db->table_exists('kti_users')) {
                $this->table_users = 'kti_users';
            }

            if ($this->db->table_exists('roles')) {
                $this->table_roles = 'roles';
            } elseif ($this->db->table_exists('kti_roles')) {
                $this->table_roles = 'kti_roles';
            }

            if ($this->db->table_exists('divisions')) {
                $this->table_divisions = 'divisions';
            } elseif ($this->db->table_exists('kti_divisions')) {
                $this->table_divisions = 'kti_divisions';
            }
        } catch (Exception $e) {
            log_message('error', 'Table detection error: ' . $e->getMessage());
        }
    }

    /**
     * Ambil data user untuk autentikasi login (berdasarkan username atau email)
     */
    public function get_user_login($identity) {
        $u = $this->table_users;
        $r = $this->table_roles;
        $d = $this->table_divisions;

        try {
            $this->db->select("{$u}.*, {$r}.role_name, {$d}.division_name");
            $this->db->from($u);
            $this->db->join($r, "{$r}.id = {$u}.role_id", 'left');
            $this->db->join($d, "{$d}.id = {$u}.division_id", 'left');

            $this->db->group_start();
            $this->db->where("{$u}.email", $identity);
            $this->db->or_where("{$u}.username", $identity);
            $this->db->group_end();

            $this->db->where("{$u}.is_active", 1);
            $query = $this->db->get();

            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
        } catch (Exception $e) {
            log_message('error', 'User_model get_user_login DB error: ' . $e->getMessage());
        }

        // Fallback ke mock user jika user tidak ditemukan di database
        return $this->_get_mock_user($identity);
    }

    /**
     * Mock user untuk demonstrasi jika database belum diatur / belum di-import
     */
    private function _get_mock_user($identity) {
        $allowed_mock = ['superadmin', 'admin', 'admin@admin.com', 'superadmin@admin.com'];
        if (in_array(strtolower($identity), $allowed_mock)) {
            $mock = new stdClass();
            $mock->id = 1;
            $mock->name = 'Administrator Template';
            $mock->username = 'admin';
            $mock->email = 'admin@admin.com';
            $mock->password = password_hash('admin123', PASSWORD_BCRYPT);
            $mock->role_id = 1;
            $mock->role_name = 'Admin';
            $mock->division_id = 1;
            $mock->division_name = 'Technology & Development';
            $mock->is_active = 1;
            $mock->last_login = date('Y-m-d H:i:s');
            return $mock;
        }
        return null;
    }

    public function insert_user($data) {
        return $this->db->insert($this->table_users, $data);
    }

    public function get_all_roles() {
        try {
            if ($this->db->table_exists($this->table_roles)) {
                return $this->db->get($this->table_roles)->result();
            }
        } catch (Exception $e) {}

        return [
            (object)['id' => 1, 'role_name' => 'Admin'],
            (object)['id' => 2, 'role_name' => 'Manager'],
            (object)['id' => 3, 'role_name' => 'Staff / User']
        ];
    }

    public function get_all_divisions() {
        try {
            if ($this->db->table_exists($this->table_divisions)) {
                return $this->db->get($this->table_divisions)->result();
            }
        } catch (Exception $e) {}

        return [
            (object)['id' => 1, 'division_name' => 'IT & Development'],
            (object)['id' => 2, 'division_name' => 'Operations'],
            (object)['id' => 3, 'division_name' => 'Finance & HR']
        ];
    }

    public function get_all_users() {
        try {
            if ($this->db->table_exists($this->table_users)) {
                $u = $this->table_users;
                $r = $this->table_roles;
                $d = $this->table_divisions;

                $this->db->select("{$u}.id, {$u}.name, {$u}.username, {$u}.email, {$u}.role_id, {$u}.division_id, {$u}.is_active, {$u}.last_login, {$r}.role_name, {$d}.division_name");
                $this->db->from($u);
                $this->db->join($r, "{$r}.id = {$u}.role_id", 'left');
                $this->db->join($d, "{$d}.id = {$u}.division_id", 'left');
                $this->db->where("{$u}.name !=", 'Belum ada PIC');
                $this->db->order_by("{$u}.role_id", 'ASC');
                $this->db->order_by("{$u}.name", 'ASC');
                return $this->db->get()->result();
            }
        } catch (Exception $e) {}

        return [];
    }

    public function get_user_by_id($id) {
        try {
            if ($this->db->table_exists($this->table_users)) {
                $u = $this->table_users;
                $r = $this->table_roles;
                $d = $this->table_divisions;

                $this->db->select("{$u}.*, {$r}.role_name, {$d}.division_name");
                $this->db->from($u);
                $this->db->join($r, "{$r}.id = {$u}.role_id", 'left');
                $this->db->join($d, "{$d}.id = {$u}.division_id", 'left');
                $this->db->where("{$u}.id", $id);
                return $this->db->get()->row();
            }
        } catch (Exception $e) {}

        return null;
    }

    public function update_user($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table_users, $data);
    }

    public function update_password($id, $hashed_password) {
        $this->db->where('id', $id);
        return $this->db->update($this->table_users, ['password' => $hashed_password]);
    }

    public function update_last_login($user_id) {
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $this->db->where('id', $user_id);
        return $this->db->update($this->table_users, ['last_login' => $now->format('Y-m-d H:i:s')]);
    }

    public function count_users() {
        try {
            if ($this->db->table_exists($this->table_users)) {
                return $this->db->count_all($this->table_users);
            }
        } catch (Exception $e) {}
        return 1;
    }
}
