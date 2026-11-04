<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bonus_promosi_model extends CI_Model {

    protected $st_db;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        // Koneksi sekunder ke sales_tracking untuk data toko
        $this->st_db = $this->load->database('sales_tracking', TRUE);
    }

    /**
     * Hitung total baris laporan berdasarkan filter
     */
    public function count_all($filters = []) {
        $this->apply_filters($filters);
        return $this->db->count_all_results('laporan_bonus_promosi');
    }

    /**
     * Ambil data laporan dengan pagination, sorting, dan filter
     */
    public function get_all($filters = [], $limit = null, $offset = null, $sort_by = 'id', $sort_order = 'ASC') {
        $valid_sort_cols = [
            'id' => 'l.id',
            'no' => 'l.id',
            'tanggal' => 'l.tanggal_ttbp',
            'nomor_ttbp' => 'l.nomor_ttbp',
            'kode' => 'l.kode_kios',
            'nama_pembeli' => 'l.nama_pembeli',
            'program' => 'l.program',
            'status' => 'l.status_ttbp'
        ];
        $col = isset($valid_sort_cols[$sort_by]) ? $valid_sort_cols[$sort_by] : 'l.id';
        $order = (strtoupper($sort_order) === 'DESC') ? 'DESC' : 'ASC';

        $this->db->select('l.*, t.nomor_ttbp AS ttbp_generated_nomor, t.id AS real_ttbp_id');
        $this->db->from('laporan_bonus_promosi l');
        $this->db->join('ttbp t', 'l.ttbp_id = t.id', 'left');

        $this->apply_filters($filters, 'l');

        $this->db->order_by($col, $order);

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        $rows = $query->result_array();

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
    public function get_by_id($id) {
        $this->db->select('l.*, t.nomor_ttbp AS ttbp_generated_nomor, t.id AS real_ttbp_id');
        $this->db->from('laporan_bonus_promosi l');
        $this->db->join('ttbp t', 'l.ttbp_id = t.id', 'left');
        $this->db->where('l.id', $id);
        $row = $this->db->get()->row_array();

        if ($row && !empty($row['kode_kios']) && $row['kode_kios'] !== '-') {
            $row['master_toko'] = $this->get_toko_by_kode($row['kode_kios']);
        } else if ($row) {
            $row['master_toko'] = null;
        }

        return $row;
    }

    /**
     * Ambil semua baris yang memiliki nomor_ttbp yang sama (untuk grouping)
     */
    public function get_group_by_nomor_ttbp($nomor_ttbp) {
        $this->db->select('l.*, t.nomor_ttbp AS ttbp_generated_nomor');
        $this->db->from('laporan_bonus_promosi l');
        $this->db->join('ttbp t', 'l.ttbp_id = t.id', 'left');
        $this->db->where('l.nomor_ttbp', $nomor_ttbp);
        $this->db->order_by('l.id', 'ASC');
        $rows = $this->db->get()->result_array();

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
     * Ambil statistik ringkasan Laporan Bonus Promosi
     */
    public function get_summary_stats() {
        $total = $this->db->count_all('laporan_bonus_promosi');
        
        $this->db->where('status_ttbp', 'pending');
        $pending = $this->db->count_all_results('laporan_bonus_promosi');

        $this->db->where('status_ttbp', 'generated');
        $generated = $this->db->count_all_results('laporan_bonus_promosi');

        $this->db->where('status_ttbp', 'returned');
        $returned = $this->db->count_all_results('laporan_bonus_promosi');

        return [
            'total'     => $total,
            'pending'   => $pending,
            'generated' => $generated,
            'returned'  => $returned
        ];
    }

    /**
     * Lookup data toko dari database sales_tracking.st_master_toko
     */
    public function get_toko_by_kode($kode_toko) {
        if (!$this->st_db) return null;
        return $this->st_db->where('kode_toko', $kode_toko)
                           ->get('st_master_toko')
                           ->row_array();
    }

    /**
     * Lookup multiple toko sekaligus (efisien)
     */
    public function get_toko_map($kodes = []) {
        if (!$this->st_db || empty($kodes)) return [];
        $unique_kodes = array_unique(array_filter($kodes));
        if (empty($unique_kodes)) return [];

        $this->st_db->where_in('kode_toko', $unique_kodes);
        $query = $this->st_db->get('st_master_toko');
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['kode_toko']] = $row;
        }
        return $result;
    }

    /**
     * Generate TTBP otomatis dari baris Laporan Bonus Promosi
     * Mengelompokkan semua baris dengan nomor_ttbp yang sama menjadi 1 lembar TTBP
     */
    public function generate_ttbp_from_laporan($nomor_ttbp_lap, $overrides = [], $created_by = 'admin') {
        $rows = $this->get_group_by_nomor_ttbp($nomor_ttbp_lap);
        if (empty($rows)) {
            return ['status' => false, 'message' => 'Data laporan tidak ditemukan untuk nomor TTBP ' . $nomor_ttbp_lap];
        }

        $first = $rows[0];
        $master_toko = $first['master_toko'];

        // 1. Tentukan nomor TTBP yang akan disimpan
        // Jika user menginput override nomor, pakai override; jika tidak, gunakan format default
        $target_nomor = !empty($overrides['nomor_ttbp']) ? trim($overrides['nomor_ttbp']) : ('C26' . str_pad($nomor_ttbp_lap, 4, '0', STR_PAD_LEFT));

        // 2. Tentukan Tanggal TTBP
        $tanggal = !empty($overrides['tanggal']) ? $overrides['tanggal'] : ($first['tanggal_ttbp'] ?: date('Y-m-d'));

        // 3. Tentukan Data Toko (Otomatis dari master toko dengan fallback laporan / manual override)
        $kode_kios = !empty($first['kode_kios']) ? $first['kode_kios'] : null;
        
        $nama_kios = !empty($overrides['nama_kios']) ? $overrides['nama_kios'] : 
                     (!empty($master_toko['nama_toko']) ? $master_toko['nama_toko'] : $first['nama_pembeli']);

        // Alamat: otomatis dari master toko (alamat + kota) atau override
        if (!empty($overrides['alamat'])) {
            $alamat = $overrides['alamat'];
        } else if (!empty($master_toko['alamat'])) {
            $alamat = $master_toko['alamat'] . (!empty($master_toko['kota']) ? ', ' . $master_toko['kota'] : '');
        } else {
            $alamat = 'Alamat belum terdaftar di master toko';
        }

        // Salesman: otomatis dari master toko (sc) atau override
        $salesman = !empty($overrides['salesman']) ? $overrides['salesman'] : 
                    (!empty($master_toko['sc']) ? $master_toko['sc'] : ($first['kode_wilayah'] ?: '-'));

        // Penerima: otomatis dari master toko (kontak_person) atau override
        $penerima = !empty($overrides['penerima']) ? $overrides['penerima'] : 
                    (!empty($master_toko['kontak_person']) ? $master_toko['kontak_person'] : $nama_kios);

        // 4. Realisasi Atas Program & Periode
        // Program + Volume + Satuan Vol (contoh: Unibro 500/50 SC 50.00 Ltr)
        $prog_str = $first['program'];
        if (floatval($first['volume_prog']) > 0) {
            $prog_str .= ' ' . floatval($first['volume_prog']) . ' ' . ($first['satuan_vol'] ?: '');
        }
        $realisasi_program = !empty($overrides['realisasi_program']) ? $overrides['realisasi_program'] : trim($prog_str);

        // Periode: dari periode_prog_raw atau rentang tanggal
        if (!empty($overrides['periode'])) {
            $periode = $overrides['periode'];
        } else if (!empty($first['periode_prog_raw'])) {
            $periode = $first['periode_prog_raw'];
        } else if (!empty($first['periode_prog_awal']) && !empty($first['periode_prog_akhir'])) {
            $periode = date('d/m/y', strtotime($first['periode_prog_awal'])) . ' - ' . date('d/m/y', strtotime($first['periode_prog_akhir']));
        } else {
            $periode = 'Januari - Desember 2026';
        }

        // 5. Tanda tangan & klausul
        $perhatian = !empty($overrides['perhatian']) ? $overrides['perhatian'] : 
                     "Bila Terjadi Retur/Faktur Batal, Sehingga\nTarget Tidak Tercapai, Maka Barang Promosi\nYang Telah Diterima Harus Dikembalikan\nSesuai Dengan Jumlah Retur/Faktur Batal";
        $disetujui_oleh = !empty($overrides['disetujui_oleh']) ? $overrides['disetujui_oleh'] : null;
        $dibuat_oleh = !empty($overrides['dibuat_oleh']) ? $overrides['dibuat_oleh'] : 'Nonik Dia';

        $is_manual = empty($master_toko) ? 1 : 0;

        $this->db->trans_start();

        // Insert ke tabel `ttbp`
        $ttbp_data = [
            'nomor_ttbp'        => $target_nomor,
            'tanggal'           => $tanggal,
            'kode_kios'         => $kode_kios,
            'nama_kios'         => $nama_kios,
            'alamat'            => $alamat,
            'salesman'          => $salesman,
            'realisasi_program' => $realisasi_program,
            'periode'           => $periode,
            'perhatian'         => $perhatian,
            'disetujui_oleh'    => $disetujui_oleh,
            'dibuat_oleh'       => $dibuat_oleh,
            'penerima'          => $penerima,
            'is_manual'         => $is_manual,
            'created_by'        => $created_by,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];
        $this->db->insert('ttbp', $ttbp_data);
        $ttbp_id = $this->db->insert_id();

        // 6. Masukkan item barang dari semua baris dengan nomor TTBP ini
        $no_urut = 1;
        foreach ($rows as $item_row) {
            $qty = floatval($item_row['qty']);
            $sat = !empty($item_row['satuan']) ? $item_row['satuan'] : 'Unit';
            $nama_item = $item_row['nama_barang'];

            // Jika berupa uang
            if (strtolower($nama_item) === 'uang') {
                $qty = 1;
                $sat = 'Paket';
                $nama_item = 'Uang Tunai Bonus (Rp ' . number_format($item_row['total_bonus'], 0, ',', '.') . ')';
            } else if ($qty <= 0) {
                $qty = 1;
            }

            // Keterangan: Faktur Promo / Faktur Realisasi jika ada
            $ket = '';
            if (!empty($item_row['faktur_promo'])) {
                $ket .= 'Faktur Promo: ' . $item_row['faktur_promo'] . ' ';
            }
            if (!empty($item_row['faktur']) && $item_row['faktur'] !== '-') {
                $ket .= 'Faktur: ' . $item_row['faktur'];
            }
            $ket = trim($ket) ?: ($item_row['keterangan'] ?: null);

            $this->db->insert('ttbp_items', [
                'ttbp_id'     => $ttbp_id,
                'no_urut'     => $no_urut,
                'nama_barang' => $nama_item,
                'jumlah'      => $qty,
                'satuan'      => $sat,
                'keterangan'  => $ket,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
            $no_urut++;
        }

        // 7. Update status baris laporan menjadi 'generated' dan pasang ttbp_id
        $this->db->where('nomor_ttbp', $nomor_ttbp_lap);
        $this->db->update('laporan_bonus_promosi', [
            'ttbp_id'     => $ttbp_id,
            'status_ttbp' => 'generated',
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal menyimpan lembar TTBP ke database.'];
        }

        return [
            'status'     => true,
            'ttbp_id'    => $ttbp_id,
            'nomor_ttbp' => $target_nomor,
            'nama_kios'  => $nama_kios,
            'message'    => 'Lembar TTBP ' . $target_nomor . ' berhasil dibuat secara otomatis!'
        ];
    }

    /**
     * Update Tanggal Kembali TTBP fisik dari kios
     */
    public function update_tgl_kembali($id, $tgl_kembali) {
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
     * Batch import baris laporan dari teks atau file Excel
     */
    public function import_rows($rows_data, $periode_laporan = 'Januari - Desember 2026') {
        if (empty($rows_data)) return 0;

        $insert_batch = [];
        foreach ($rows_data as $r) {
            $insert_batch[] = [
                'no_urut_lap'          => isset($r['no']) ? trim($r['no']) : null,
                'tanggal_ttbp'         => !empty($r['tanggal_ttbp']) ? date('Y-m-d', strtotime($r['tanggal_ttbp'])) : null,
                'nomor_ttbp'           => isset($r['nomor_ttbp']) ? trim($r['nomor_ttbp']) : null,
                'tgl_sdh_dipotong'     => !empty($r['tgl_sdh_dipotong']) ? date('Y-m-d', strtotime($r['tgl_sdh_dipotong'])) : null,
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
                'periode_prog_awal'    => !empty($r['periode_prog_awal']) ? date('Y-m-d', strtotime($r['periode_prog_awal'])) : null,
                'periode_prog_akhir'   => !empty($r['periode_prog_akhir']) ? date('Y-m-d', strtotime($r['periode_prog_akhir'])) : null,
                'periode_prog_raw'     => isset($r['periode_prog_raw']) ? trim($r['periode_prog_raw']) : null,
                'faktur'               => (!empty($r['faktur']) && $r['faktur'] !== '-') ? trim($r['faktur']) : null,
                'kode_wilayah'         => (!empty($r['kode_wilayah']) && $r['kode_wilayah'] !== '-') ? trim($r['kode_wilayah']) : null,
                'tgl_kembali_ttbp'     => !empty($r['tgl_kembali_ttbp']) ? date('Y-m-d', strtotime($r['tgl_kembali_ttbp'])) : null,
                'keterangan'           => isset($r['keterangan']) ? trim($r['keterangan']) : null,
                'status_ttbp'          => !empty($r['tgl_kembali_ttbp']) ? 'returned' : 'pending',
                'periode_laporan'      => $periode_laporan,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($insert_batch)) {
            $this->db->insert_batch('laporan_bonus_promosi', $insert_batch);
            return count($insert_batch);
        }
        return 0;
    }

    /**
     * Ambil daftar nama program untuk dropdown filter
     */
    public function get_program_list() {
        return $this->db->select('program')
                        ->where('program IS NOT NULL', null, false)
                        ->where('program !=', '')
                        ->group_by('program')
                        ->order_by('program', 'ASC')
                        ->get('laporan_bonus_promosi')
                        ->result_array();
    }

    /**
     * Helper privat untuk klausul WHERE filter
     */
    protected function apply_filters($filters = [], $prefix = '') {
        $p = $prefix ? ($prefix . '.') : '';

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
}

