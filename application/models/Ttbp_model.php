<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ttbp_model extends CI_Model {

    protected $sales_db = null;

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
            return $db->get()->result_array();
        }

        // Fallback jika sales_db tidak terkoneksi secara terpisah, coba query langsung via default db
        $this->db->select('kode_toko, nama_toko, alamat, kota, sc, kontak_person, telepon');
        $this->db->from('sales_tracking.st_master_toko');
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
            return $db->where('kode_toko', $kode)->get('st_master_toko')->row_array();
        }
        return $this->db->where('kode_toko', $kode)->get('sales_tracking.st_master_toko')->row_array();
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
     * Ambil semua baris dengan nomor TTBP yang sama
     */
    public function get_laporan_by_nomor_ttbp($nomor_ttbp) {
        $rows = $this->db->where('nomor_ttbp', $nomor_ttbp)->order_by('id', 'ASC')->get('laporan_bonus_promosi')->result_array();
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

        // Tentukan Periode
        if (!empty($first['periode_prog_raw'])) {
            $periode = $first['periode_prog_raw'];
        } else if (!empty($first['periode_prog_awal']) && !empty($first['periode_prog_akhir'])) {
            $periode = date('d/m/y', strtotime($first['periode_prog_awal'])) . ' - ' . date('d/m/y', strtotime($first['periode_prog_akhir']));
        } else {
            $periode = 'Januari - Desember 2026';
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

            // Baris 1: Uang
            $items[] = [
                'no_urut'     => 1,
                'nama_barang' => 'Uang',
                'jumlah'      => $gross,
                'jumlah_fmt'  => number_format($gross, 0, ',', '.'),
                'satuan'      => $sat_uang,
                'keterangan'  => '',
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

                $ket = '';
                if (!empty($r['faktur_promo'])) $ket .= 'Faktur Promo: ' . $r['faktur_promo'] . ' ';
                if (!empty($r['faktur']) && $r['faktur'] !== '-') $ket .= 'Faktur: ' . $r['faktur'];
                if (!empty($r['keterangan'])) $ket .= ' ' . $r['keterangan'];

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

        return [
            'id'                => $first['id'],
            'nomor_ttbp'        => $first['nomor_ttbp'],
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
     * Import baris data dari text paste (tab-separated dari Excel)
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

            if (stripos($cols[0], 'No') !== false || stripos($cols[1] ?? '', 'Tanggal') !== false) {
                continue;
            }

            $nama = trim($cols[6] ?? '');
            $prog = trim($cols[8] ?? '');
            if (empty($nama) && empty($prog)) continue;

            $rows[] = [
                'no'                   => trim($cols[0] ?? ''),
                'tanggal_ttbp'         => trim($cols[1] ?? ''),
                'nomor_ttbp'           => trim($cols[2] ?? '') ?: trim($cols[0] ?? ''),
                'tgl_sdh_dipotong'     => trim($cols[3] ?? ''),
                'faktur_promo'         => trim($cols[4] ?? ''),
                'kode_kios'            => trim($cols[5] ?? ''),
                'nama_pembeli'         => $nama,
                'value_produk_program' => trim($cols[7] ?? '0'),
                'program'              => $prog,
                'nama_barang'          => trim($cols[9] ?? ''),
                'potong_nota'          => trim($cols[10] ?? '0'),
                'pajak'                => trim($cols[11] ?? '0'),
                'total_bonus'          => trim($cols[12] ?? '0'),
                'qty'                  => trim($cols[13] ?? '0'),
                'satuan'               => trim($cols[14] ?? ''),
                'volume_prog'          => trim($cols[15] ?? '0'),
                'satuan_vol'           => trim($cols[16] ?? ''),
                'periode_prog_awal'    => trim($cols[17] ?? ''),
                'periode_prog_akhir'   => trim($cols[18] ?? ''),
                'periode_prog_raw'     => (trim($cols[17] ?? '') && trim($cols[18] ?? '')) ? (trim($cols[17] ?? '') . ' - ' . trim($cols[18] ?? '')) : '',
                'faktur'               => trim($cols[19] ?? ''),
                'kode_wilayah'         => trim($cols[20] ?? ''),
                'tgl_kembali_ttbp'     => trim($cols[21] ?? ''),
                'keterangan'           => trim($cols[22] ?? '')
            ];
        }

        return $this->import_laporan_batch($rows, $periode_laporan);
    }

    /**
     * Import baris data dari file Excel (.xlsx / .xls / .csv) menggunakan PhpSpreadsheet
     */
    public function import_laporan_excel_file($file_path, $periode_laporan = 'Januari - Desember 2026') {
        require_once FCPATH . 'vendor/autoload.php';

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestDataColumn();

        $rows = [];
        $header_found = false;

        for ($r = 1; $r <= $highestRow; $r++) {
            $val0 = trim((string)$worksheet->getCell('A' . $r)->getValue());
            $val1 = trim((string)$worksheet->getCell('B' . $r)->getValue());
            $val2 = trim((string)$worksheet->getCell('C' . $r)->getValue());

            // Deteksi baris header
            if (!$header_found) {
                if (stripos($val0, 'No') !== false || stripos($val1, 'Tanggal') !== false || stripos($val2, 'TTBP') !== false) {
                    $header_found = true;
                }
                continue;
            }

            // Baca nilai sel per kolom
            // A: No, B: Tgl TTBP, C: TTBP, D: Sdh dipotong, E: Faktur Promo, F: Kode, G: Nama Pembeli,
            // H: Value Produk, I: Program, J: Nama Barang, K: potong nota, L: Pajak, M: Total Bonus,
            // N: Qty, O: Satuan, P: Volume Prog, Q: Satuan Vol, R: Periode Awal, S: Periode Akhir,
            // T: Faktur, U: at, V: tgl kembali, W: Keterangan
            $no          = $val0;
            $tgl_ttbp    = $this->_format_excel_date($worksheet->getCell('B' . $r));
            $ttbp        = trim((string)$worksheet->getCell('C' . $r)->getValue());
            $sdh_potong  = $this->_format_excel_date($worksheet->getCell('D' . $r));
            $faktur_promo= trim((string)$worksheet->getCell('E' . $r)->getValue());
            $kode        = trim((string)$worksheet->getCell('F' . $r)->getValue());
            $nama        = trim((string)$worksheet->getCell('G' . $r)->getValue());
            $val_prod    = $worksheet->getCell('H' . $r)->getValue();
            $prog        = trim((string)$worksheet->getCell('I' . $r)->getValue());
            $nama_barang = trim((string)$worksheet->getCell('J' . $r)->getValue());
            $pot_nota    = $worksheet->getCell('K' . $r)->getValue();
            $pajak       = $worksheet->getCell('L' . $r)->getValue();
            $tot_bonus   = $worksheet->getCell('M' . $r)->getValue();
            $qty         = $worksheet->getCell('N' . $r)->getValue();
            $satuan      = trim((string)$worksheet->getCell('O' . $r)->getValue());
            $vol_prog    = $worksheet->getCell('P' . $r)->getValue();
            $sat_vol     = trim((string)$worksheet->getCell('Q' . $r)->getValue());
            $tgl_awal    = $this->_format_excel_date($worksheet->getCell('R' . $r));
            $tgl_akhir   = $this->_format_excel_date($worksheet->getCell('S' . $r));
            $faktur      = trim((string)$worksheet->getCell('T' . $r)->getValue());
            $at          = trim((string)$worksheet->getCell('U' . $r)->getValue());
            $tgl_kembali = $this->_format_excel_date($worksheet->getCell('V' . $r));
            $ket         = trim((string)$worksheet->getCell('W' . $r)->getValue());

            if (empty($nama) && empty($prog)) continue;

            $rows[] = [
                'no'                   => $no,
                'tanggal_ttbp'         => $tgl_ttbp,
                'nomor_ttbp'           => $ttbp ?: $no,
                'tgl_sdh_dipotong'     => $sdh_potong,
                'faktur_promo'         => $faktur_promo,
                'kode_kios'            => $kode,
                'nama_pembeli'         => $nama,
                'value_produk_program' => $val_prod,
                'program'              => $prog,
                'nama_barang'          => $nama_barang,
                'potong_nota'          => $pot_nota,
                'pajak'                => $pajak,
                'total_bonus'          => $tot_bonus,
                'qty'                  => $qty,
                'satuan'               => $satuan,
                'volume_prog'          => $vol_prog,
                'satuan_vol'           => $sat_vol,
                'periode_prog_awal'    => $tgl_awal,
                'periode_prog_akhir'   => $tgl_akhir,
                'periode_prog_raw'     => ($tgl_awal && $tgl_akhir) ? ($tgl_awal . ' - ' . $tgl_akhir) : '',
                'faktur'               => $faktur,
                'kode_wilayah'         => $at,
                'tgl_kembali_ttbp'     => $tgl_kembali,
                'keterangan'           => $ket
            ];
        }

        return $this->import_laporan_batch($rows, $periode_laporan);
    }

    /**
     * Batch insert parsed rows ke tabel laporan_bonus_promosi
     */
    public function import_laporan_batch($rows, $periode_laporan = 'Januari - Desember 2026') {
        if (empty($rows)) return 0;
        $batch = [];

        foreach ($rows as $r) {
            $tgl_ttbp    = !empty($r['tanggal_ttbp']) ? date('Y-m-d', strtotime($r['tanggal_ttbp'])) : null;
            $tgl_potong  = !empty($r['tgl_sdh_dipotong']) ? date('Y-m-d', strtotime($r['tgl_sdh_dipotong'])) : null;
            $tgl_kembali = !empty($r['tgl_kembali_ttbp']) ? date('Y-m-d', strtotime($r['tgl_kembali_ttbp'])) : null;
            $tgl_awal    = !empty($r['periode_prog_awal']) ? date('Y-m-d', strtotime($r['periode_prog_awal'])) : null;
            $tgl_akhir   = !empty($r['periode_prog_akhir']) ? date('Y-m-d', strtotime($r['periode_prog_akhir'])) : null;

            $batch[] = [
                'no_urut_lap'          => isset($r['no']) ? trim($r['no']) : null,
                'tanggal_ttbp'         => $tgl_ttbp,
                'nomor_ttbp'           => isset($r['nomor_ttbp']) ? trim($r['nomor_ttbp']) : null,
                'tgl_sdh_dipotong'     => $tgl_potong,
                'faktur_promo'         => (!empty($r['faktur_promo']) && $r['faktur_promo'] !== '-') ? trim($r['faktur_promo']) : null,
                'kode_kios'            => (!empty($r['kode_kios']) && $r['kode_kios'] !== '-') ? trim($r['kode_kios']) : null,
                'nama_pembeli'         => isset($r['nama_pembeli']) ? trim($r['nama_pembeli']) : null,
                'value_produk_program' => isset($r['value_produk_program']) ? floatval(str_replace(',', '', $r['value_produk_program'])) : 0.00,
                'program'              => isset($r['program']) ? trim($r['program']) : null,
                'nama_barang'          => isset($r['nama_barang']) ? trim($r['nama_barang']) : null,
                'potong_nota'          => isset($r['potong_nota']) ? floatval(str_replace(',', '', $r['potong_nota'])) : 0.00,
                'pajak'                => isset($r['pajak']) ? floatval(str_replace(',', '', $r['pajak'])) : 0.00,
                'total_bonus'          => isset($r['total_bonus']) ? floatval(str_replace(',', '', $r['total_bonus'])) : 0.00,
                'qty'                  => isset($r['qty']) ? floatval(str_replace(',', '', $r['qty'])) : 0.00,
                'satuan'               => (!empty($r['satuan']) && $r['satuan'] !== '-') ? trim($r['satuan']) : null,
                'volume_prog'          => isset($r['volume_prog']) ? floatval(str_replace(',', '', $r['volume_prog'])) : 0.00,
                'satuan_vol'           => (!empty($r['satuan_vol']) && $r['satuan_vol'] !== '-') ? trim($r['satuan_vol']) : null,
                'periode_prog_awal'    => $tgl_awal,
                'periode_prog_akhir'   => $tgl_akhir,
                'periode_prog_raw'     => isset($r['periode_prog_raw']) ? trim($r['periode_prog_raw']) : null,
                'faktur'               => (!empty($r['faktur']) && $r['faktur'] !== '-') ? trim($r['faktur']) : null,
                'kode_wilayah'         => (!empty($r['kode_wilayah']) && $r['kode_wilayah'] !== '-') ? trim($r['kode_wilayah']) : null,
                'tgl_kembali_ttbp'     => $tgl_kembali,
                'keterangan'           => isset($r['keterangan']) ? trim($r['keterangan']) : null,
                'status_ttbp'          => $tgl_kembali ? 'returned' : 'pending',
                'periode_laporan'      => $periode_laporan,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch('laporan_bonus_promosi', $batch);
            return count($batch);
        }
        return 0;
    }

    /**
     * Helper parsing tanggal dari sel Excel
     */
    protected function _format_excel_date($cell) {
        $val = $cell->getValue();
        if (empty($val) || $val === '-') return null;
        if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
            return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($val));
        }
        $time = strtotime($val);
        return $time ? date('Y-m-d', $time) : null;
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
     * Ambil map data toko dari sales_tracking
     */
    public function get_toko_map($kodes = []) {
        $db = $this->get_sales_db();
        if (!$db || empty($kodes)) return [];
        $unique_kodes = array_unique(array_filter($kodes));
        if (empty($unique_kodes)) return [];

        $db->where_in('kode_toko', $unique_kodes);
        $query = $db->get('st_master_toko');
        $result = [];
        if ($query) {
            foreach ($query->result_array() as $row) {
                $result[$row['kode_toko']] = $row;
            }
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

