<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ttbp_model extends CI_Model {

    protected $sales_db = null;
    protected $table_laporan = 'laporan_bonus_promosi';

    public function __construct() {
        parent::__construct();
        $this->_init_sales_db();
    }

    /**
     * Inisialisasi koneksi ke database sales_tracking (jika tersedia)
     */
    protected function _init_sales_db() {
        try {
            // Cek apakah group sales_tracking terdaftar di database.php
            $this->sales_db = $this->load->database('sales_tracking', TRUE);
            if (!$this->sales_db || !$this->sales_db->conn_id) {
                $this->sales_db = null;
            }
        } catch (Exception $e) {
            $this->sales_db = null;
        }
    }

    /**
     * Ambil data koneksi sales tracking aktif
     */
    public function get_sales_db() {
        if ($this->sales_db === null) {
            $this->_init_sales_db();
        }
        return $this->sales_db;
    }

    /**
     * Nama database sales_tracking di server lokal
     */
    public function get_sales_db_name() {
        return 'sales_tracking';
    }

    /**
     * Search data toko dari st_master_toko
     */
    public function search_toko($keyword, $limit = 25) {
        $db = $this->get_sales_db();
        if ($db) {
            $db->select('kode_toko, nama_toko, alamat, kota, sc, kontak_person, telepon');
            $db->from('st_master_toko');
            if (!empty($keyword)) {
                $db->group_start();
                $db->like('kode_toko', $keyword);
                $db->or_like('nama_toko', $keyword);
                $db->or_like('sc', $keyword);
                $db->group_end();
            }
            $db->limit($limit);
            $q = $db->get();
            return $q ? $q->result_array() : array();
        }

        // Fallback jika sales_db tidak terkoneksi secara terpisah, query lintas DB pada server MySQL yang sama
        $sales_db_name = $this->get_sales_db_name();
        $this->db->select('kode_toko, nama_toko, alamat, kota, sc, kontak_person, telepon');
        $this->db->from($sales_db_name . '.st_master_toko');
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('kode_toko', $keyword);
            $this->db->or_like('nama_toko', $keyword);
            $this->db->or_like('sc', $keyword);
            $this->db->group_end();
        }
        $this->db->limit($limit);
        $query = $this->db->get();
        return $query ? $query->result_array() : array();
    }

    /**
     * Ambil toko berdasarkan kode toko
     */
    public function get_toko_by_kode($kode) {
        $db = $this->get_sales_db();
        if ($db) {
            $q = $db->where('kode_toko', $kode)->get('st_master_toko');
            if ($q) return $q->row_array();
        }
        $sales_db_name = $this->get_sales_db_name();
        $q2 = $this->db->where('kode_toko', $kode)->get($sales_db_name . '.st_master_toko');
        return $q2 ? $q2->row_array() : null;
    }

    /**
     * Search data program dari st_programs (dan relasi program kios jika ada)
     */
    public function search_programs($keyword = '', $kode_toko = null, $limit = 25) {
        $db = $this->get_sales_db();
        if (!$db) {
            $db = $this->db;
        }

        // Jika kode_toko diberikan, prioritaskan program yang diikuti toko tsb
        if (!empty($kode_toko)) {
            try {
                $table_kios_prog = ($db === $this->sales_db) ? 'st_kios_programs' : 'sales_tracking.st_kios_programs';
                $table_prog = ($db === $this->sales_db) ? 'st_programs' : 'sales_tracking.st_programs';

                $db->select("p.id_program, p.nama, p.start_date, p.end_date, p.target_qty, p.satuan, kp.target_qty as custom_target, kp.custom_end_date");
                $db->from("{$table_kios_prog} kp");
                $db->join("{$table_prog} p", "p.id_program = kp.id_program", "inner");
                $db->where("kp.kode_toko", $kode_toko);
                if (!empty($keyword)) {
                    $db->like('p.nama', $keyword);
                }
                $db->limit($limit);
                $res = $db->get()->result_array();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {
                // Lanjut ke query umum
            }
        }

        // Query umum st_programs
        $table_prog = ($db === $this->sales_db) ? 'st_programs' : 'sales_tracking.st_programs';
        $db->select("id_program, nama, start_date, end_date, target_qty, satuan");
        $db->from($table_prog);
        if (!empty($keyword)) {
            $db->like('nama', $keyword);
        }
        $db->order_by('id_program', 'DESC');
        $db->limit($limit);
        return $db->get()->result_array();
    }

    /**
     * Search master barang untuk autocomplete nama barang promosi
     */
    public function search_barang($keyword = '', $limit = 25) {
        $db = $this->get_sales_db();
        if (!$db) {
            $db = $this->db;
        }

        $table_barang = ($db === $this->sales_db) ? 'st_master_barang' : 'sales_tracking.st_master_barang';
        $db->select('kode_barang, nama_barang, harga');
        $db->from($table_barang);
        if (!empty($keyword)) {
            $db->group_start();
            $db->like('kode_barang', $keyword);
            $db->or_like('nama_barang', $keyword);
            $db->group_end();
        }
        $db->limit($limit);
        $query = $db->get();
        return $query ? $query->result_array() : array();
    }

    /**
     * Generate rekomendasi nomor TTBP berikutnya
     * Contoh format: C260817 (C + tahun(2 digit) + nomor urut)
     */
    public function generate_nomor_ttbp($prefix = 'C') {
        $year_2digit = date('y');
        $base_prefix = $prefix . $year_2digit;

        $this->db->select('nomor_ttbp');
        $this->db->like('nomor_ttbp', $base_prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get('ttbp')->row();

        if ($last && !empty($last->nomor_ttbp)) {
            // Ambil nomor angka di belakang prefix
            $suffix = substr($last->nomor_ttbp, strlen($base_prefix));
            $num = intval($suffix);
            if ($num > 0) {
                $next_num = str_pad($num + 1, max(4, strlen($suffix)), '0', STR_PAD_LEFT);
                return $base_prefix . $next_num;
            }
        }

        // Default initial number jika belum ada
        // Mengikuti contoh C260817 (5 digit urut: 0817, 0818, ...)
        return $base_prefix . '0801';
    }

    /**
     * Ambil data TTBP dengan filter, pagination, dan sorting
     */
    public function get_all($filters = array(), $limit = null, $offset = null, $sort_by = 'tanggal', $sort_order = 'DESC') {
        $valid_sort_cols = [
            'id' => 't.id',
            'nomor_ttbp' => 't.nomor_ttbp',
            'tanggal' => 't.tanggal',
            'nama_kios' => 't.nama_kios',
            'salesman' => 't.salesman',
            'realisasi_program' => 't.realisasi_program',
            'created_at' => 't.created_at',
            'dibuat_oleh' => 't.dibuat_oleh'
        ];

        $order_col = isset($valid_sort_cols[$sort_by]) ? $valid_sort_cols[$sort_by] : 't.tanggal';
        $order_dir = (strtoupper($sort_order) === 'ASC') ? 'ASC' : 'DESC';

        $this->db->select('t.*, COUNT(i.id) as total_items, SUM(i.jumlah) as total_qty');
        $this->db->from('ttbp t');
        $this->db->join('ttbp_items i', 'i.ttbp_id = t.id', 'left');

        $this->_apply_filters($filters);

        $this->db->group_by('t.id');
        $this->db->order_by($order_col, $order_dir);

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Hitung total TTBP untuk pagination
     */
    public function count_all($filters = array()) {
        $this->db->from('ttbp t');
        $this->_apply_filters($filters);
        return $this->db->count_all_results();
    }

    /**
     * Helper privat untuk query filtering
     */
    protected function _apply_filters($filters) {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start();
            $this->db->like('t.nomor_ttbp', $search);
            $this->db->or_like('t.nama_kios', $search);
            $this->db->or_like('t.kode_kios', $search);
            $this->db->or_like('t.salesman', $search);
            $this->db->or_like('t.realisasi_program', $search);
            $this->db->or_like('t.penerima', $search);
            $this->db->or_like('t.dibuat_oleh', $search);
            // Cari juga nama barang di items
            $this->db->or_where("EXISTS (SELECT 1 FROM ttbp_items ti WHERE ti.ttbp_id = t.id AND ti.nama_barang LIKE '%" . $this->db->escape_like_str($search) . "%')");
            $this->db->group_end();
        }

        if (!empty($filters['bulan'])) {
            // Format YYYY-MM
            $this->db->where("DATE_FORMAT(t.tanggal, '%Y-%m') =", $filters['bulan']);
        }

        if (!empty($filters['salesman'])) {
            $this->db->where('t.salesman', $filters['salesman']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $this->db->where('t.tanggal >=', $filters['start_date']);
            $this->db->where('t.tanggal <=', $filters['end_date']);
        }
    }

    /**
     * Ambil detail TTBP berdasarkan ID beserta items-nya
     */
    public function get_by_id($id) {
        $ttbp = $this->db->where('id', $id)->get('ttbp')->row_array();
        if (!$ttbp) {
            return null;
        }

        $items = $this->db->where('ttbp_id', $id)->order_by('no_urut', 'ASC')->get('ttbp_items')->result_array();
        $ttbp['items'] = $items;
        return $ttbp;
    }

    /**
     * Ambil TTBP berdasarkan Nomor TTBP
     */
    public function get_by_nomor($nomor) {
        $ttbp = $this->db->where('nomor_ttbp', $nomor)->get('ttbp')->row_array();
        if (!$ttbp) {
            return null;
        }

        $items = $this->db->where('ttbp_id', $ttbp['id'])->order_by('no_urut', 'ASC')->get('ttbp_items')->result_array();
        $ttbp['items'] = $items;
        return $ttbp;
    }

    /**
     * Simpan TTBP baru dengan transaksi
     */
    public function insert($data, $items = array()) {
        $this->db->trans_start();

        $this->db->insert('ttbp', $data);
        $ttbp_id = $this->db->insert_id();

        if (!empty($items)) {
            $no = 1;
            foreach ($items as $item) {
                if (empty($item['nama_barang']) && empty($item['jumlah'])) {
                    continue;
                }
                $this->db->insert('ttbp_items', [
                    'ttbp_id'     => $ttbp_id,
                    'no_urut'     => isset($item['no_urut']) ? $item['no_urut'] : $no++,
                    'nama_barang' => $item['nama_barang'],
                    'jumlah'      => floatval($item['jumlah']),
                    'satuan'      => !empty($item['satuan']) ? $item['satuan'] : 'Unit',
                    'keterangan'  => isset($item['keterangan']) ? $item['keterangan'] : ''
                ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        return $ttbp_id;
    }

    /**
     * Update data TTBP beserta item-itemnya
     */
    public function update($id, $data, $items = array()) {
        $this->db->trans_start();

        $this->db->where('id', $id)->update('ttbp', $data);

        // Hapus item lama lalu insert kembali
        $this->db->where('ttbp_id', $id)->delete('ttbp_items');

        if (!empty($items)) {
            $no = 1;
            foreach ($items as $item) {
                if (empty($item['nama_barang']) && empty($item['jumlah'])) {
                    continue;
                }
                $this->db->insert('ttbp_items', [
                    'ttbp_id'     => $id,
                    'no_urut'     => isset($item['no_urut']) ? $item['no_urut'] : $no++,
                    'nama_barang' => $item['nama_barang'],
                    'jumlah'      => floatval($item['jumlah']),
                    'satuan'      => !empty($item['satuan']) ? $item['satuan'] : 'Unit',
                    'keterangan'  => isset($item['keterangan']) ? $item['keterangan'] : ''
                ]);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Hapus TTBP
     */
    public function delete($id) {
        // Cascade delete on foreign key or manual delete
        $this->db->where('ttbp_id', $id)->delete('ttbp_items');
        return $this->db->where('id', $id)->delete('ttbp');
    }

    /**
     * Ambil daftar nama Salesman (SC) unik dari TTBP dan Master Toko
     */
    public function get_salesman_list() {
        $list = [];

        // Dari TTBP
        $res = $this->db->select('DISTINCT(salesman) as sc')->where('salesman IS NOT NULL')->where('salesman !=', '')->get('ttbp')->result_array();
        foreach ($res as $r) {
            if (!empty($r['sc'])) {
                $list[$r['sc']] = trim($r['sc']);
            }
        }

        // Dari master toko
        $db = $this->get_sales_db();
        if ($db) {
            $res2 = $db->select('DISTINCT(sc) as sc')->where('sc IS NOT NULL')->where('sc !=', '')->get('st_master_toko')->result_array();
            foreach ($res2 as $r) {
                if (!empty($r['sc'])) {
                    $list[$r['sc']] = trim($r['sc']);
                }
            }
        }

        asort($list);
        return array_values($list);
    }

    /**
     * Ambil daftar bulan unik yang ada di data TTBP
     */
    public function get_month_list() {
        $this->db->select("DISTINCT(DATE_FORMAT(tanggal, '%Y-%m')) as bulan_val, DATE_FORMAT(tanggal, '%M %Y') as bulan_label");
        $this->db->where('tanggal IS NOT NULL');
        $this->db->order_by('tanggal', 'DESC');
        $res = $this->db->get('ttbp')->result_array();

        // Pastikan bulan sekarang masuk
        $current = date('Y-m');
        $has_current = false;
        foreach ($res as $r) {
            if ($r['bulan_val'] === $current) {
                $has_current = true;
                break;
            }
        }
        if (!$has_current) {
            array_unshift($res, [
                'bulan_val' => $current,
                'bulan_label' => date('F Y')
            ]);
        }
        return $res;
    }

    /**
     * Ringkasan statistik untuk dashboard
     */
    public function get_summary_stats() {
        $month = date('Y-m');
        
        $total_all = $this->db->count_all('ttbp');
        
        $this->db->where("DATE_FORMAT(tanggal, '%Y-%m') =", $month);
        $total_this_month = $this->db->count_all_results('ttbp');

        $this->db->select('COUNT(DISTINCT nama_kios) as total_kios');
        $kios_stat = $this->db->get('ttbp')->row();

        $this->db->select('COUNT(DISTINCT salesman) as total_sc');
        $sc_stat = $this->db->get('ttbp')->row();

        return [
            'total_all' => $total_all,
            'total_this_month' => $total_this_month,
            'total_kios' => $kios_stat ? $kios_stat->total_kios : 0,
            'total_sc' => $sc_stat ? $sc_stat->total_sc : 0
        ];
    }

    // ========================================================
    // METODE REKAP BONUS PROMOSI & GENERATE TTBP OTOMATIS
    // ========================================================

    /**
     * Hitung total data laporan rekap bonus promosi
     */
    public function count_laporan($filters = []) {
        $this->_apply_laporan_filters($filters);
        return $this->db->count_all_results('laporan_bonus_promosi');
    }

    /**
     * Ambil data laporan rekap bonus promosi dengan filter, pagination, dan sorting
     */
    public function get_laporan_list($filters = [], $limit = null, $offset = null, $sort_by = 'id', $sort_order = 'ASC') {
        $valid_sort_cols = [
            'id' => 'l.id',
            'no' => 'l.id',
            'tanggal' => 'l.tanggal_ttbp',
            'nomor_ttbp' => 'l.nomor_ttbp',
            'kode' => 'l.kode_kios',
            'nama_pembeli' => 'l.nama_pembeli',
            'program' => 'l.program',
            'nama_barang' => 'l.nama_barang',
            'total_bonus' => 'l.total_bonus',
            'qty' => 'l.qty',
            'status' => 'l.status_ttbp'
        ];
        $col = isset($valid_sort_cols[$sort_by]) ? $valid_sort_cols[$sort_by] : 'l.id';
        $order = (strtoupper($sort_order) === 'DESC') ? 'DESC' : 'ASC';

        $this->db->select('l.*');
        $this->db->from('laporan_bonus_promosi l');
        $this->_apply_laporan_filters($filters, 'l');
        $this->db->order_by($col, $order);

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        $rows = $this->db->get()->result_array();

        // Enrich dengan data master toko jika ada kode_kios
        if (!empty($rows)) {
            $kodes = [];
            foreach ($rows as $r) {
                if (!empty($r['kode_kios']) && $r['kode_kios'] !== '-') {
                    $kodes[] = $r['kode_kios'];
                }
            }
            $toko_map = $this->get_toko_map($kodes);

            foreach ($rows as &$r) {
                $kode = $r['kode_kios'];
                if (!empty($kode) && isset($toko_map[$kode])) {
                    $r['master_toko'] = $toko_map[$kode];
                } else {
                    $r['master_toko'] = null;
                }
            }
        }

        return $rows;
    }

    /**
     * Ambil satu baris laporan berdasarkan ID
     */
    public function get_laporan($id) {
        $row = $this->db->where('id', $id)->get('laporan_bonus_promosi')->row_array();
        if ($row && !empty($row['kode_kios']) && $row['kode_kios'] !== '-') {
            $row['master_toko'] = $this->get_toko_by_kode($row['kode_kios']);
        } else if ($row) {
            $row['master_toko'] = null;
        }
        return $row;
    }

    /**
     * Format Nomor TTBP Standar Resmi: C + 2 digit tahun + 4 digit nomor urut (contoh: C260025, C260823)
     */
    public static function format_nomor_ttbp($nomor, $tanggal = null) {
        if ($nomor === null || $nomor === '') {
            return '';
        }

        $nomor_str = trim((string)$nomor);
        if ($nomor_str === '' || $nomor_str === '-') {
            return '';
        }

        // Jika sudah dalam format C + 2 digit tahun + 4 digit nomor (misal C260025)
        if (preg_match('/^C\d{6}$/i', $nomor_str)) {
            return strtoupper($nomor_str);
        }

        // Tentukan 2 digit tahun dari tanggal TTBP (default '26' untuk periode 2026)
        $tahun = '26';
        if (!empty($tanggal)) {
            $time = strtotime($tanggal);
            if ($time !== false && $time > 0) {
                $tahun = date('y', $time);
            }
        }

        // Ambil hanya karakter angka dari nomor
        $digits = preg_replace('/[^0-9]/', '', $nomor_str);
        if ($digits === '') {
            return $nomor_str;
        }
        $num = intval($digits);

        return 'C' . $tahun . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil semua baris dengan nomor TTBP yang sama (mendukung pencarian by nomor biasa maupun format C26xxxx)
     */
    public function get_laporan_by_nomor_ttbp($nomor_ttbp) {
        $nomor_clean = trim((string)$nomor_ttbp);
        $candidates = [$nomor_clean];

        // Jika input berupa format C + 2 digit tahun + 4 digit (misal C260025 atau C260823)
        if (preg_match('/^C\d{2}(\d{4})$/i', $nomor_clean, $m)) {
            $digits_pad = $m[1];                // '0025'
            $digits_raw = (string)intval($m[1]); // '25'
            $candidates[] = $digits_pad;
            $candidates[] = $digits_raw;
        } else if (is_numeric($nomor_clean)) {
            // Jika input angka biasa (misal 25), cari juga format pad '0025' dan 'C260025'
            $pad = str_pad($nomor_clean, 4, '0', STR_PAD_LEFT);
            $candidates[] = $pad;
            $candidates[] = 'C26' . $pad;
        }

        $rows = $this->db->where_in('nomor_ttbp', array_unique($candidates))
                         ->order_by('id', 'ASC')
                         ->get('laporan_bonus_promosi')
                         ->result_array();

        if (!empty($rows)) {
            $first = $rows[0];
            $master_toko = null;
            if (!empty($first['kode_kios']) && $first['kode_kios'] !== '-') {
                $master_toko = $this->get_toko_by_kode($first['kode_kios']);
            }
            foreach ($rows as &$r) {
                $r['master_toko'] = $master_toko;
            }
        }
        return $rows;
    }

    /**
     * Hitung nomor urut laporan berikutnya secara otomatis
     */
    public function get_next_no_laporan() {
        $this->db->select('MAX(CAST(no_urut_lap AS UNSIGNED)) as max_no');
        $row = $this->db->get('laporan_bonus_promosi')->row();
        if ($row && $row->max_no > 0) {
            return $row->max_no + 1;
        }
        return $this->db->count_all('laporan_bonus_promosi') + 1;
    }

    /**
     * Cari data kontrak program dari st_programs untuk auto-fill
     */
    public function search_programs_master($keyword = '', $limit = 50) {
        $db = $this->get_sales_db();
        if (!$db) $db = $this->db;

        $table = ($db === $this->sales_db) ? 'st_programs' : 'sales_tracking.st_programs';
        $db->select('id_program, nama, target_qty, satuan, start_date, end_date');
        $db->from($table);
        if (!empty($keyword)) {
            $db->like('nama', $keyword);
        }
        $db->order_by('nama', 'ASC');
        $db->limit($limit);
        $res = $db->get();
        return $res ? $res->result_array() : [];
    }

    /**
     * Simpan atau update 1 baris laporan bonus promosi
     */
    public function save_laporan($data, $id = null) {
        // Format tanggal jika ada
        if (!empty($data['tanggal_ttbp'])) $data['tanggal_ttbp'] = date('Y-m-d', strtotime($data['tanggal_ttbp']));
        if (!empty($data['tgl_sdh_dipotong'])) $data['tgl_sdh_dipotong'] = date('Y-m-d', strtotime($data['tgl_sdh_dipotong']));
        if (!empty($data['tgl_kembali_ttbp'])) $data['tgl_kembali_ttbp'] = date('Y-m-d', strtotime($data['tgl_kembali_ttbp']));
        if (!empty($data['periode_prog_awal'])) $data['periode_prog_awal'] = date('Y-m-d', strtotime($data['periode_prog_awal']));
        if (!empty($data['periode_prog_akhir'])) $data['periode_prog_akhir'] = date('Y-m-d', strtotime($data['periode_prog_akhir']));

        if (!empty($data['periode_prog_awal']) && !empty($data['periode_prog_akhir'])) {
            $data['periode_prog_raw'] = date('d-M-y', strtotime($data['periode_prog_awal'])) . ' - ' . date('d-M-y', strtotime($data['periode_prog_akhir']));
        }

        // Numeric fields
        if (isset($data['value_produk_program'])) $data['value_produk_program'] = floatval(str_replace(',', '', $data['value_produk_program']));
        if (isset($data['potong_nota'])) $data['potong_nota'] = floatval(str_replace(',', '', $data['potong_nota']));
        if (isset($data['pajak'])) $data['pajak'] = floatval(str_replace(',', '', $data['pajak']));
        if (isset($data['total_bonus'])) $data['total_bonus'] = floatval(str_replace(',', '', $data['total_bonus']));
        if (isset($data['qty'])) $data['qty'] = floatval(str_replace(',', '', $data['qty']));
        if (isset($data['volume_prog'])) $data['volume_prog'] = floatval(str_replace(',', '', $data['volume_prog']));

        if (!empty($id)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $id);
            $this->db->update('laporan_bonus_promosi', $data);
            return $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('laporan_bonus_promosi', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Hapus batch baris laporan
     */
    public function delete_laporan_batch($ids = []) {
        if (empty($ids)) return 0;
        $this->db->where_in('id', $ids);
        $this->db->delete('laporan_bonus_promosi');
        return $this->db->affected_rows();
    }

    /**
     * Kosongkan seluruh isi tabel laporan bonus promosi
     */
    public function clear_all_laporan() {
        return $this->db->truncate('laporan_bonus_promosi');
    }

    /**
     * Hapus baris laporan
     */
    public function delete_laporan($id) {
        return $this->db->where('id', $id)->delete('laporan_bonus_promosi');
    }

    /**
     * Update tanggal kembali TTBP
     */
    public function update_laporan_tgl_kembali($id, $tgl_kembali) {
        $tgl = (!empty($tgl_kembali) && $tgl_kembali !== '-') ? date('Y-m-d', strtotime($tgl_kembali)) : null;
        $status = $tgl ? 'returned' : 'generated';

        $this->db->where('id', $id);
        return $this->db->update('laporan_bonus_promosi', [
            'tgl_kembali_ttbp' => $tgl,
            'status_ttbp'      => $status,
            'updated_at'       => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Menghasilkan struktur dokumen lembar cetak TTBP fisik (CF K4 W PRS 4 ply)
     * Mengelompokkan semua baris dengan nomor TTBP yang sama, auto-pull data master toko
     */
    public function get_ttbp_sheet_data($nomor_ttbp_or_id) {
        // Cek apakah input berupa ID baris atau nomor_ttbp
        if (is_numeric($nomor_ttbp_or_id)) {
            $row_single = $this->db->where('id', $nomor_ttbp_or_id)->get('laporan_bonus_promosi')->row_array();
            if ($row_single) {
                $nomor_ttbp = $row_single['nomor_ttbp'];
            } else {
                $nomor_ttbp = $nomor_ttbp_or_id;
            }
        } else {
            $nomor_ttbp = $nomor_ttbp_or_id;
        }

        $rows = $this->get_laporan_by_nomor_ttbp($nomor_ttbp);
        if (empty($rows)) {
            return null;
        }

        $first = $rows[0];
        $master_toko = $first['master_toko'];

        // Tentukan Nama Toko
        $nama_kios = !empty($first['nama_pembeli']) ? $first['nama_pembeli'] : (!empty($master_toko['nama_toko']) ? $master_toko['nama_toko'] : 'Kios Promosi');

        // Tentukan Alamat
        if (!empty($first['alamat_manual'])) {
            $alamat = $first['alamat_manual'];
        } else if (!empty($master_toko['alamat'])) {
            $alamat = $master_toko['alamat'] . (!empty($master_toko['kota']) ? ', ' . $master_toko['kota'] : '');
        } else {
            $alamat = 'Alamat belum terdaftar di master toko';
        }

        // Tentukan Salesman
        if (!empty($first['salesman_manual'])) {
            $salesman = $first['salesman_manual'];
        } else if (!empty($master_toko['sc'])) {
            $salesman = $master_toko['sc'];
        } else {
            $salesman = !empty($first['kode_wilayah']) ? $first['kode_wilayah'] : '-';
        }

        // Tentukan Penerima
        if (!empty($first['penerima_manual'])) {
            $penerima = $first['penerima_manual'];
        } else if (!empty($master_toko['kontak_person'])) {
            $penerima = $master_toko['kontak_person'];
        } else {
            $penerima = $nama_kios;
        }

        // Tentukan Realisasi Program
        $prog_str = $first['program'];
        if (floatval($first['volume_prog']) > 0) {
            $prog_str .= ' ' . floatval($first['volume_prog']) . ' ' . ($first['satuan_vol'] ?: '');
        }

        // Tentukan Periode dengan format standar: YYYY/MM/DD - YYYY/MM/DD
        $tgl_awal = !empty($first['periode_prog_awal']) ? $first['periode_prog_awal'] : null;
        $tgl_akhir = !empty($first['periode_prog_akhir']) ? $first['periode_prog_akhir'] : null;

        if ((!$tgl_awal || !$tgl_akhir) && !empty($first['periode_prog_raw'])) {
            $parts = explode(' - ', $first['periode_prog_raw']);
            if (count($parts) >= 2) {
                $tgl_awal = $tgl_awal ?: trim($parts[0]);
                $tgl_akhir = $tgl_akhir ?: trim($parts[1]);
            }
        }

        if ($tgl_awal && $tgl_akhir) {
            $time_awal = strtotime(str_replace('/', '-', $tgl_awal));
            $time_akhir = strtotime(str_replace('/', '-', $tgl_akhir));
            if ($time_awal && $time_akhir) {
                $periode = date('Y/m/d', $time_awal) . ' - ' . date('Y/m/d', $time_akhir);
            } else {
                $periode = $first['periode_prog_raw'];
            }
        } else if (!empty($first['periode_prog_raw'])) {
            $periode = $first['periode_prog_raw'];
        } else {
            $periode = '2026/01/01 - 2026/12/31';
        }

        // Tentukan apakah lembar TTBP ini untuk bonus 'Uang'
        $is_uang = false;
        foreach ($rows as $r) {
            if (strtolower(trim($r['nama_barang'])) === 'uang') {
                $is_uang = true;
                break;
            }
        }

        // Tentukan footer keterangan (Transfer atau Potong Nota)
        // Aturan: untuk Mega Tani, Inti Tani, Alvin Tani / Toha selalu Transfer, selainnya Potong Nota
        $nama_cek = strtolower($nama_kios . ' ' . ($first['nama_pembeli'] ?? '') . ' ' . ($first['kode_kios'] ?? ''));
        $is_transfer = false;
        if (strpos($nama_cek, 'mega tani') !== false || 
            strpos($nama_cek, 'inti tani') !== false || 
            strpos($nama_cek, 'alvin tani') !== false ||
            strpos($nama_cek, 'toha') !== false) {
            $is_transfer = true;
        }
        $footer_keterangan = $is_transfer ? 'Transfer' : 'Potong Nota';

        // Items list
        $items = [];
        $total_nominal = 0;
        $total_nominal_fmt = '';

        if ($is_uang) {
            $r = $rows[0];
            $gross = floatval($r['total_bonus']);
            $pajak = floatval($r['pajak']);
            $potong = floatval($r['potong_nota']);

            if ($gross <= 0 && ($potong > 0 || $pajak > 0)) {
                $gross = $potong + $pajak;
            }
            $netto = ($potong > 0) ? $potong : ($gross - $pajak);
            $total_nominal = $netto;
            $total_nominal_fmt = number_format($netto, 0, ',', '.');

            $sat_uang = (!empty($r['satuan']) && strtolower($r['satuan']) !== 'unit' && strtolower($r['satuan']) !== 'paket') ? $r['satuan'] : 'OTP';
            $ket_uang = !empty($r['keterangan']) ? trim($r['keterangan']) : '';

            // Baris 1: Uang
            $items[] = [
                'no_urut'     => 1,
                'nama_barang' => 'Uang',
                'jumlah'      => $gross,
                'jumlah_fmt'  => number_format($gross, 0, ',', '.'),
                'satuan'      => $sat_uang,
                'keterangan'  => $ket_uang,
                'is_currency' => true,
                'is_minus'    => false
            ];

            // Baris 2: Pajak (PPh) angka minus
            $items[] = [
                'no_urut'     => 2,
                'nama_barang' => 'Pajak (PPh)',
                'jumlah'      => -$pajak,
                'jumlah_fmt'  => '(' . number_format(abs($pajak), 0, ',', '.') . ')',
                'satuan'      => '',
                'keterangan'  => '',
                'is_currency' => true,
                'is_minus'    => true
            ];
        } else {
            $no_urut = 1;
            foreach ($rows as $r) {
                $nama_item = $r['nama_barang'];
                $qty = floatval($r['qty']);
                $sat = !empty($r['satuan']) ? $r['satuan'] : 'Unit';

                if ($qty <= 0) {
                    $qty = 1;
                }

                $ket = !empty($r['keterangan']) ? trim($r['keterangan']) : '';

                $items[] = [
                    'no_urut'     => $no_urut++,
                    'nama_barang' => $nama_item,
                    'jumlah'      => $qty,
                    'jumlah_fmt'  => (string)$qty,
                    'satuan'      => $sat,
                    'keterangan'  => trim($ket),
                    'is_currency' => false,
                    'is_minus'    => false
                ];
            }
        }

        $formatted_ttbp = self::format_nomor_ttbp($first['nomor_ttbp'], $first['tanggal_ttbp']);

        return [
            'id'                => $first['id'],
            'nomor_ttbp'        => $formatted_ttbp,
            'nomor_ttbp_raw'    => $first['nomor_ttbp'],
            'tanggal'           => $first['tanggal_ttbp'] ?: date('Y-m-d'),
            'kode_kios'         => $first['kode_kios'],
            'nama_kios'         => $nama_kios,
            'alamat'            => $alamat,
            'salesman'          => $salesman,
            'penerima'          => $penerima,
            'realisasi_program' => trim($prog_str),
            'periode'           => $periode,
            'dibuat_oleh'       => !empty($first['dibuat_oleh']) ? $first['dibuat_oleh'] : 'Nonik Dia',
            'disetujui_oleh'    => !empty($first['disetujui_oleh']) ? $first['disetujui_oleh'] : '',
            'perhatian'         => !empty($first['perhatian']) ? $first['perhatian'] : "Bila Terjadi Retur/Faktur Batal, Sehingga\nTarget Tidak Tercapai, Maka Barang Promosi\nYang Telah Diterima Harus Dikembalikan\nSesuai Dengan Jumlah Retur/Faktur Batal",
            'is_uang'           => $is_uang,
            'total_nominal'     => $total_nominal,
            'total_nominal_fmt' => $total_nominal_fmt,
            'footer_keterangan' => $footer_keterangan,
            'items'             => $items
        ];
    }

    /**
     * Dapatkan nomor TTBP berikutnya secara berurutan dan daftar pilihan TTBP
     * untuk fitur cetak 1 kertas = 2 TTBP (Portrait Atas & Bawah).
     */
    public function get_next_and_selectable_ttbp($current_id, $current_nomor_raw, $limit = 150) {
        // Cari TTBP berikutnya setelah ID saat ini
        $next_row = $this->db->select('id, nomor_ttbp, tanggal_ttbp, nama_pembeli, nama_barang')
                             ->from('laporan_bonus_promosi')
                             ->where('id >', (int)$current_id)
                             ->where('nomor_ttbp !=', $current_nomor_raw)
                             ->where('nomor_ttbp IS NOT NULL', null, false)
                             ->where('nomor_ttbp !=', '')
                             ->order_by('id', 'ASC')
                             ->limit(1)
                             ->get()
                             ->row_array();

        $next_formatted = null;
        if ($next_row) {
            $next_formatted = self::format_nomor_ttbp($next_row['nomor_ttbp'], $next_row['tanggal_ttbp']);
        }

        // Daftar pilihan TTBP unik di sekitar data
        $rows = $this->db->select('MIN(id) as id, nomor_ttbp, MAX(tanggal_ttbp) as tanggal_ttbp, MAX(nama_pembeli) as nama_pembeli, MAX(nama_barang) as nama_barang')
                         ->from('laporan_bonus_promosi')
                         ->where('nomor_ttbp IS NOT NULL', null, false)
                         ->where('nomor_ttbp !=', '')
                         ->group_by('nomor_ttbp')
                         ->order_by('id', 'ASC')
                         ->limit((int)$limit)
                         ->get()
                         ->result_array();

        $options = [];
        foreach ($rows as $r) {
            $fmt = self::format_nomor_ttbp($r['nomor_ttbp'], $r['tanggal_ttbp']);
            $options[] = [
                'id'           => $r['id'],
                'nomor_ttbp'   => $fmt,
                'nama_pembeli' => $r['nama_pembeli'],
                'nama_barang'  => $r['nama_barang']
            ];
        }

        return [
            'next_nomor' => $next_formatted,
            'options'    => $options
        ];
    }

    /**
     * Pencarian seluruh nomor TTBP (tanpa batas 150) untuk fitur Search Slot Bawah pada halaman cetak
     */
    public function search_selectable_ttbp($keyword = '', $exclude_nomor = '', $limit = 60) {
        $this->db->select('MIN(id) as id, nomor_ttbp, MAX(tanggal_ttbp) as tanggal_ttbp, MAX(nama_pembeli) as nama_pembeli, MAX(nama_barang) as nama_barang')
                 ->from('laporan_bonus_promosi')
                 ->where('nomor_ttbp IS NOT NULL', null, false)
                 ->where('nomor_ttbp !=', '');

        $kw = trim((string)$keyword);
        if ($kw !== '') {
            // Jika user mengetik format C260250 atau #250, ekstrak juga digit angkanya (250)
            $kw_digits = '';
            if (preg_match('/^#?C\d{2}(\d+)$/i', $kw, $m)) {
                $kw_digits = (string)intval($m[1]);
            } elseif (preg_match('/^#?0*(\d+)$/', $kw, $m2)) {
                $kw_digits = $m2[1];
            }

            $this->db->group_start();
            $this->db->like('nomor_ttbp', $kw);
            if ($kw_digits !== '') {
                $this->db->or_like('nomor_ttbp', $kw_digits);
            }
            $this->db->or_like('nama_pembeli', $kw);
            $this->db->or_like('kode_kios', $kw);
            $this->db->or_like('nama_barang', $kw);
            $this->db->or_like('program', $kw);
            $this->db->group_end();
        }

        $rows = $this->db->group_by('nomor_ttbp')
                         ->order_by('id', 'ASC')
                         ->limit((int)$limit)
                         ->get()
                         ->result_array();

        $results = [];
        foreach ($rows as $r) {
            $fmt = self::format_nomor_ttbp($r['nomor_ttbp'], $r['tanggal_ttbp']);
            if ($exclude_nomor !== '' && strcasecmp($fmt, $exclude_nomor) === 0) {
                continue;
            }
            $results[] = [
                'id'           => $r['id'],
                'nomor_ttbp'   => $fmt,
                'nama_pembeli' => $r['nama_pembeli'] ?: '-',
                'nama_barang'  => $r['nama_barang'] ?: ''
            ];
        }

        return $results;
    }

    /**
     * Import baris data dari text paste (tab-separated dari Excel)
     */
    /**
     * Helper parsing tanggal dari data Excel (baik serial number numerik maupun string teks)
     */
    public function parse_excel_date($val) {
        if ($val === null || $val === '' || $val === '-' || $val === '0' || $val === 0) {
            return null;
        }
        $val = trim((string)$val);
        if ($val === '' || $val === '-' || $val === '0') {
            return null;
        }

        // Jika Excel serial number (misal 46029 -> 2026-01-07)
        if (is_numeric($val) && floatval($val) > 20000 && floatval($val) < 80000) {
            $timestamp = ($val - 25569) * 86400;
            return gmdate('Y-m-d', (int)round($timestamp));
        }

        // Coba strtotime langsung
        $t = strtotime($val);
        if ($t !== false && $t > 0) {
            return date('Y-m-d', $t);
        }

        // Tangani format bulan Indonesia (misal: 7-Jan-26, 12-Nov-25, 03-Ags-25, 20-Des-26)
        $indo_months = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'ags' => 'Aug',
            'agu' => 'Aug', 'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov', 'des' => 'Dec'
        ];
        $val_norm = str_ireplace(array_keys($indo_months), array_values($indo_months), strtolower($val));
        $t2 = strtotime($val_norm);
        if ($t2 !== false && $t2 > 0) {
            return date('Y-m-d', $t2);
        }

        return null;
    }

    /**
     * Import teks tabel dari clipboard (Copy-Paste dari Excel)
     */
    public function import_laporan_paste($raw_text, $periode_laporan = 'Januari - Desember 2026') {
        if (empty(trim($raw_text))) return 0;
        $lines = explode("\n", trim($raw_text));
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $cols = explode("\t", $line);
            if (count($cols) < 5) $cols = str_getcsv($line);
            $rows[] = $cols;
        }

        return $this->_process_and_insert_raw_rows($rows, $periode_laporan);
    }

    /**
     * Import baris data dari berkas Excel (.xlsx / .xls / .csv)
     * Menggunakan SimpleXLSX dan SimpleXLS untuk kecepatan tinggi & hemat memori.
     */
    public function import_laporan_excel_file($file_path, $periode_laporan = 'Januari - Desember 2026') {
        if (!class_exists('\\Shuchkin\\SimpleXLSX') && file_exists(APPPATH . 'third_party/SimpleXLSX.php')) {
            require_once APPPATH . 'third_party/SimpleXLSX.php';
        }
        if (!class_exists('\\Shuchkin\\SimpleXLS') && file_exists(APPPATH . 'third_party/SimpleXLS.php')) {
            require_once APPPATH . 'third_party/SimpleXLS.php';
        }
        if (!class_exists('\\Shuchkin\\SimpleXLSX') && file_exists(FCPATH . 'vendor/autoload.php')) {
            require_once FCPATH . 'vendor/autoload.php';
        }

        $rows = [];
        $parsed = false;

        // Cek header berkas (magic bytes)
        $fp = @fopen($file_path, 'rb');
        $magic = $fp ? fread($fp, 4) : '';
        if ($fp) fclose($fp);

        $is_xlsx = (substr($magic, 0, 4) === "PK\x03\x04");
        $is_xls  = (substr($magic, 0, 4) === "\xd0\xcf\x11\xe0");

        // 1. Jika format .xlsx (ZIP PK) atau default
        if ($is_xlsx || (!$is_xls && class_exists('\\Shuchkin\\SimpleXLSX'))) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($file_path)) {
                $rows = $xlsx->rows();
                $parsed = true;
            }
        }

        // 2. Jika format .xls (OLE2) atau SimpleXLSX gagal
        if (!$parsed && ($is_xls || class_exists('\\Shuchkin\\SimpleXLS'))) {
            if ($xls = \Shuchkin\SimpleXLS::parse($file_path)) {
                $rows = $xls->rows();
                $parsed = true;
            }
        }

        // 3. Fallback CSV
        if (!$parsed) {
            if (($handle = @fopen($file_path, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
                $parsed = !empty($rows);
            }
        }

        // 4. Jika masih gagal membaca
        if (!$parsed || empty($rows)) {
            $err = \Shuchkin\SimpleXLSX::parseError() ?: (\Shuchkin\SimpleXLS::parseError() ?: 'Berkas tidak dapat diparse');
            throw new Exception("Gagal membaca berkas Excel: " . $err);
        }

        return $this->_process_and_insert_raw_rows($rows, $periode_laporan);
    }

    /**
     * Memproses baris raw dari Excel/CSV, memetakan 23 kolom, dan batch insert per-chunk 250 baris
     */
    protected function _process_and_insert_raw_rows($rows, $periode_laporan = 'Januari - Desember 2026') {
        if (empty($rows)) return 0;

        $header_found = false;
        $batch = [];
        $now = date('Y-m-d H:i:s');
        $no_seq = 1;

        foreach ($rows as $row) {
            $col0 = trim((string)($row[0] ?? ''));
            $col1 = trim((string)($row[1] ?? ''));
            $col2 = trim((string)($row[2] ?? ''));

            // Deteksi baris header
            if (!$header_found) {
                if (stripos($col0, 'No') !== false || stripos($col1, 'Tanggal') !== false || stripos($col2, 'TTBP') !== false) {
                    $header_found = true;
                }
                continue;
            }

            // Abaikan baris sub-header (misal baris 'at' di kolom 19/20)
            if (trim((string)($row[19] ?? '')) === 'at' || (empty($col0) && empty($col1) && empty($col2) && empty($row[6] ?? ''))) {
                continue;
            }

            $nama = trim((string)($row[6] ?? ''));
            $prog = trim((string)($row[8] ?? ''));
            // Jika nama dan program kosong, berarti baris kosong di akhir sheet Excel -> skip!
            if (empty($nama) && empty($prog)) {
                continue;
            }

            $tgl_ttbp    = $this->parse_excel_date($row[1] ?? null);
            $tgl_potong  = $this->parse_excel_date($row[3] ?? null);
            $tgl_awal    = $this->parse_excel_date($row[17] ?? null);
            $tgl_akhir   = $this->parse_excel_date($row[18] ?? null);
            $tgl_kembali = $this->parse_excel_date($row[21] ?? null);

            $val_prod  = floatval(str_replace(',', '', (string)($row[7] ?? 0)));
            $pot_nota  = floatval(str_replace(',', '', (string)($row[10] ?? 0)));
            $pajak     = floatval(str_replace(',', '', (string)($row[11] ?? 0)));
            $tot_bonus = floatval(str_replace(',', '', (string)($row[12] ?? 0)));
            $qty       = floatval(str_replace(',', '', (string)($row[13] ?? 0)));
            $vol_prog  = floatval(str_replace(',', '', (string)($row[15] ?? 0)));

            $faktur_promo = trim((string)($row[4] ?? ''));
            $kode_kios    = trim((string)($row[5] ?? ''));
            $satuan       = trim((string)($row[14] ?? ''));
            $satuan_vol   = trim((string)($row[16] ?? ''));
            $faktur       = trim((string)($row[19] ?? ''));
            $at           = trim((string)($row[20] ?? ''));
            $ket          = trim((string)($row[22] ?? ''));

            // Pastikan Nomor Urut adalah bilangan bulat positif urut (1, 2, 3...)
            $clean_no = $no_seq++;
            $ttbp_raw = trim((string)($row[2] ?? '')) ?: (string)$clean_no;

            $batch[] = [
                'no_urut_lap'          => (string)$clean_no,
                'tanggal_ttbp'         => $tgl_ttbp,
                'nomor_ttbp'           => $ttbp_raw,
                'tgl_sdh_dipotong'     => $tgl_potong,
                'faktur_promo'         => ($faktur_promo !== '-' && $faktur_promo !== '') ? $faktur_promo : null,
                'kode_kios'            => ($kode_kios !== '-' && $kode_kios !== '') ? $kode_kios : null,
                'nama_pembeli'         => $nama,
                'value_produk_program' => $val_prod,
                'program'              => $prog,
                'nama_barang'          => trim((string)($row[9] ?? '')),
                'potong_nota'          => $pot_nota,
                'pajak'                => $pajak,
                'total_bonus'          => $tot_bonus,
                'qty'                  => $qty,
                'satuan'               => ($satuan !== '-' && $satuan !== '0' && $satuan !== '') ? $satuan : null,
                'volume_prog'          => $vol_prog,
                'satuan_vol'           => ($satuan_vol !== '-' && $satuan_vol !== '0' && $satuan_vol !== '') ? $satuan_vol : null,
                'periode_prog_awal'    => $tgl_awal,
                'periode_prog_akhir'   => $tgl_akhir,
                'periode_prog_raw'     => ($tgl_awal && $tgl_akhir) ? (date('Y/m/d', strtotime($tgl_awal)) . ' - ' . date('Y/m/d', strtotime($tgl_akhir))) : '',
                'faktur'               => ($faktur !== '-' && $faktur !== '') ? $faktur : null,
                'kode_wilayah'         => ($at !== '-' && $at !== '') ? $at : null,
                'tgl_kembali_ttbp'     => $tgl_kembali,
                'keterangan'           => $ket ?: null,
                'status_ttbp'          => $tgl_kembali ? 'returned' : 'pending',
                'periode_laporan'      => $periode_laporan,
                'created_at'           => $now,
                'updated_at'           => $now
            ];
        }

        if (empty($batch)) {
            return 0;
        }

        // Batch chunk insert dalam transaksi database untuk performa maksimal & anti-timeout
        $chunks = array_chunk($batch, 250);
        $this->db->trans_start();
        foreach ($chunks as $chunk) {
            $this->db->insert_batch('laporan_bonus_promosi', $chunk);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception("Gagal menyimpan data ke database saat proses batch insert.");
        }

        return count($batch);
    }

    /**
     * Batch insert parsed rows ke tabel laporan_bonus_promosi (legacy wrapper)
     */
    public function import_laporan_batch($rows, $periode_laporan = 'Januari - Desember 2026') {
        return $this->_process_and_insert_raw_rows($rows, $periode_laporan);
    }

    /**
     * Helper parsing tanggal dari sel Excel (backward compatibility)
     */
    protected function _format_excel_date($cell) {
        if (is_object($cell)) {
            return $this->parse_excel_date($cell->getValue());
        }
        return $this->parse_excel_date($cell);
    }

    /**
     * Filter query untuk laporan
     */
    protected function _apply_laporan_filters($filters = [], $prefix = '') {
        $p = $prefix ? ($prefix . '.') : '';

        if (!empty($filters['jenis'])) {
            if (strtolower($filters['jenis']) === 'uang') {
                $this->db->where("LOWER({$p}nama_barang) =", 'uang');
            } else if (strtolower($filters['jenis']) === 'barang') {
                $this->db->where("LOWER({$p}nama_barang) !=", 'uang');
            }
        }
        if (!empty($filters['status'])) {
            $this->db->where($p . 'status_ttbp', $filters['status']);
        }
        if (!empty($filters['program'])) {
            $this->db->where($p . 'program', $filters['program']);
        }
        if (!empty($filters['bulan'])) {
            $this->db->where("DATE_FORMAT({$p}tanggal_ttbp, '%Y-%m') =", $filters['bulan']);
        }
        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start();
            $this->db->like($p . 'nama_pembeli', $q);
            $this->db->or_like($p . 'kode_kios', $q);
            $this->db->or_like($p . 'nomor_ttbp', $q);
            $this->db->or_like($p . 'program', $q);
            $this->db->or_like($p . 'nama_barang', $q);
            $this->db->or_like($p . 'faktur', $q);
            $this->db->or_like($p . 'faktur_promo', $q);
            $this->db->group_end();
        }
    }

    /**
     * Ambil map data toko dari sales_tracking (mendukung u676129830_salestracking di production)
     */
    public function get_toko_map($kodes = []) {
        if (empty($kodes)) return [];
        $unique_kodes = array_unique(array_filter($kodes));
        if (empty($unique_kodes)) return [];

        $result = [];
        $db = $this->get_sales_db();
        if ($db) {
            $db->where_in('kode_toko', $unique_kodes);
            $query = $db->get('st_master_toko');
            if ($query) {
                foreach ($query->result_array() as $row) {
                    $result[$row['kode_toko']] = $row;
                }
                return $result;
            }
        }

        // Fallback lintas database di server MySQL yang sama (jika user DB memiliki akses ke u676129830_salestracking)
        try {
            $sales_db_name = $this->get_sales_db_name();
            $this->db->where_in('kode_toko', $unique_kodes);
            $query2 = $this->db->get($sales_db_name . '.st_master_toko');
            if ($query2) {
                foreach ($query2->result_array() as $row) {
                    $result[$row['kode_toko']] = $row;
                }
            }
        } catch (Exception $e) {
            // Abaikan jika tidak ada akses lintas DB
        }
        return $result;
    }

    /**
     * Ambil summary stats untuk laporan bonus promosi
     */
    public function get_laporan_summary_stats() {
        $total = $this->db->count_all('laporan_bonus_promosi');

        $this->db->where('tgl_kembali_ttbp IS NOT NULL');
        $returned = $this->db->count_all_results('laporan_bonus_promosi');

        $this->db->select('COUNT(DISTINCT nomor_ttbp) as total_ttbp');
        $ttbp_row = $this->db->get('laporan_bonus_promosi')->row();

        $this->db->select('COUNT(DISTINCT nama_pembeli) as total_toko');
        $toko_row = $this->db->get('laporan_bonus_promosi')->row();

        return [
            'total'      => $total,
            'returned'   => $returned,
            'total_ttbp' => $ttbp_row ? $ttbp_row->total_ttbp : 0,
            'total_toko' => $toko_row ? $toko_row->total_toko : 0
        ];
    }
}

