<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bonus_promosi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        check_login();
        $this->load->model('Bonus_promosi_model');
        $this->load->model('Ttbp_model');
        $this->load->library('pagination');
    }

    /**
     * Tampilan Utama: Rekap Laporan Bonus Promosi (Data Analyst)
     */
    public function index() {
        $data['title'] = 'Rekap Bonus Promosi';

        $filters = [
            'status'  => $this->input->get('status', TRUE) ?: '',
            'program' => $this->input->get('program', TRUE) ?: '',
            'bulan'   => $this->input->get('bulan', TRUE) ?: '',
            'search'  => $this->input->get('search', TRUE) ?: ''
        ];

        $sort_by    = $this->input->get('sort', TRUE) ?: 'id';
        $sort_order = $this->input->get('order', TRUE) ?: 'ASC';

        // Pagination
        $limit  = 25;
        $page   = $this->input->get('page', TRUE) ?: 1;
        $offset = ($page - 1) * $limit;

        $total_rows = $this->Bonus_promosi_model->count_all($filters);
        $list       = $this->Bonus_promosi_model->get_all($filters, $limit, $offset, $sort_by, $sort_order);

        // Pagination bootstrap 5
        $config['base_url']             = site_url('bonus_promosi') . '?' . http_build_query(array_merge($filters, ['sort' => $sort_by, 'order' => $sort_order]));
        $config['total_rows']           = $total_rows;
        $config['per_page']             = $limit;
        $config['page_query_string']    = TRUE;
        $config['query_string_segment'] = 'page';
        $config['use_page_numbers']     = TRUE;

        $config['full_tag_open']    = '<nav><ul class="pagination pagination-sm mb-0">';
        $config['full_tag_close']   = '</ul></nav>';
        $config['first_tag_open']   = '<li class="page-item">';
        $config['first_tag_close']  = '</li>';
        $config['last_tag_open']    = '<li class="page-item">';
        $config['last_tag_close']   = '</li>';
        $config['next_tag_open']    = '<li class="page-item">';
        $config['next_tag_close']   = '</li>';
        $config['prev_tag_open']    = '<li class="page-item">';
        $config['prev_tag_close']   = '</li>';
        $config['num_tag_open']     = '<li class="page-item">';
        $config['num_tag_close']    = '</li>';
        $config['cur_tag_open']     = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']    = '</span></li>';
        $config['attributes']       = array('class' => 'page-link');

        $this->pagination->initialize($config);

        $data['filters']          = $filters;
        $data['sort_by']          = $sort_by;
        $data['sort_order']       = $sort_order;
        $data['list']             = $list;
        $data['total_rows']       = $total_rows;
        $data['limit']            = $limit;
        $data['offset']           = $offset;
        $data['pagination_links'] = $this->pagination->create_links();
        $data['stats']            = $this->Bonus_promosi_model->get_summary_stats();
        $data['program_list']     = $this->Bonus_promosi_model->get_program_list();

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('bonus_promosi/index', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * AJAX endpoint: Menampilkan Preview & Data Auto-Fill sebelum pembuatan TTBP
     */
    public function preview_generate_ajax($nomor_ttbp) {
        $rows = $this->Bonus_promosi_model->get_group_by_nomor_ttbp($nomor_ttbp);
        if (empty($rows)) {
            $this->output->set_status_header(404)->set_output(json_encode([
                'status'  => false,
                'message' => 'Data nomor TTBP tidak ditemukan.'
            ]));
            return;
        }

        $first = $rows[0];
        $master_toko = $first['master_toko'];

        // Siapkan data default yang diisi otomatis
        $auto_data = [
            'nomor_ttbp_lap'   => $nomor_ttbp,
            'nomor_ttbp_saran' => 'C26' . str_pad($nomor_ttbp, 4, '0', STR_PAD_LEFT),
            'tanggal'          => $first['tanggal_ttbp'] ?: date('Y-m-d'),
            'tanggal_display'  => $first['tanggal_ttbp'] ? date('d M Y', strtotime($first['tanggal_ttbp'])) : date('d M Y'),
            'kode_kios'        => $first['kode_kios'],
            'nama_kios'        => !empty($master_toko['nama_toko']) ? $master_toko['nama_toko'] : $first['nama_pembeli'],
            'alamat'           => !empty($master_toko['alamat']) ? ($master_toko['alamat'] . (!empty($master_toko['kota']) ? ', ' . $master_toko['kota'] : '')) : '',
            'salesman'         => !empty($master_toko['sc']) ? $master_toko['sc'] : ($first['kode_wilayah'] ?: ''),
            'penerima'         => !empty($master_toko['kontak_person']) ? $master_toko['kontak_person'] : $first['nama_pembeli'],
            'program'          => $first['program'],
            'volume_prog'      => $first['volume_prog'],
            'satuan_vol'       => $first['satuan_vol'],
            'realisasi_saran'  => trim($first['program'] . (floatval($first['volume_prog']) > 0 ? (' ' . floatval($first['volume_prog']) . ' ' . $first['satuan_vol']) : '')),
            'periode_saran'    => !empty($first['periode_prog_raw']) ? $first['periode_prog_raw'] : 'Januari - Desember 2026',
            'has_master'       => !empty($master_toko),
            'dibuat_oleh'      => 'Nonik Dia',
            'disetujui_oleh'   => '',
            'items'            => []
        ];

        $no = 1;
        foreach ($rows as $r) {
            $nama_item = $r['nama_barang'];
            $qty = floatval($r['qty']);
            $sat = $r['satuan'] ?: 'Unit';

            if (strtolower($nama_item) === 'uang') {
                $nama_item = 'Uang Tunai Bonus (Rp ' . number_format($r['total_bonus'], 0, ',', '.') . ')';
                $qty = 1;
                $sat = 'Paket';
            } else if ($qty <= 0) {
                $qty = 1;
            }

            $ket = '';
            if (!empty($r['faktur_promo'])) $ket .= 'Faktur Promo: ' . $r['faktur_promo'] . ' ';
            if (!empty($r['faktur']) && $r['faktur'] !== '-') $ket .= 'Faktur: ' . $r['faktur'];

            $auto_data['items'][] = [
                'no_urut'     => $no++,
                'nama_barang' => $nama_item,
                'jumlah'      => $qty,
                'satuan'      => $sat,
                'keterangan'  => trim($ket)
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => true,
            'data'   => $auto_data
        ]));
    }

    /**
     * AJAX endpoint: Eksekusi pembuatan TTBP otomatis dari laporan
     */
    public function generate_ttbp_ajax() {
        $nomor_ttbp_lap = $this->input->post('nomor_ttbp_lap', TRUE);
        if (!$nomor_ttbp_lap) {
            $this->output->set_status_header(400)->set_output(json_encode([
                'status'  => false,
                'message' => 'Parameter nomor TTBP laporan diperlukan.'
            ]));
            return;
        }

        $overrides = [
            'nomor_ttbp'        => $this->input->post('nomor_ttbp', TRUE),
            'tanggal'           => $this->input->post('tanggal', TRUE),
            'nama_kios'         => $this->input->post('nama_kios', TRUE),
            'alamat'            => $this->input->post('alamat', TRUE),
            'salesman'          => $this->input->post('salesman', TRUE),
            'penerima'          => $this->input->post('penerima', TRUE),
            'realisasi_program' => $this->input->post('realisasi_program', TRUE),
            'periode'           => $this->input->post('periode', TRUE),
            'dibuat_oleh'       => $this->input->post('dibuat_oleh', TRUE),
            'disetujui_oleh'    => $this->input->post('disetujui_oleh', TRUE),
            'perhatian'         => $this->input->post('perhatian', TRUE)
        ];

        $user = current_user();
        $created_by = $user ? $user->username : 'admin';

        $result = $this->Bonus_promosi_model->generate_ttbp_from_laporan($nomor_ttbp_lap, $overrides, $created_by);

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    /**
     * AJAX endpoint: Simpan Tanggal Kembali TTBP fisik
     */
    public function update_tgl_kembali_ajax() {
        $id = $this->input->post('id', TRUE);
        $tgl_kembali = $this->input->post('tgl_kembali', TRUE);

        if (!$id) {
            $this->output->set_status_header(400)->set_output(json_encode([
                'status'  => false,
                'message' => 'ID baris laporan tidak valid.'
            ]));
            return;
        }

        $res = $this->Bonus_promosi_model->update_tgl_kembali($id, $tgl_kembali);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'  => (bool)$res,
            'message' => $res ? 'Tanggal kembali TTBP berhasil diperbarui.' : 'Gagal memperbarui tanggal.'
        ]));
    }

    /**
     * AJAX endpoint: Import Data Laporan dari Paste Text (Excel TSV)
     */
    public function import_paste_ajax() {
        $raw_text = $this->input->post('raw_text');
        $periode_laporan = $this->input->post('periode_laporan', TRUE) ?: 'Januari - Desember 2026';

        if (empty(trim($raw_text))) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Data teks yang ditempel masih kosong.'
            ]));
            return;
        }

        $lines = explode("\n", trim($raw_text));
        $parsed_rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Tab-delimited (Excel copy-paste)
            $cols = explode("\t", $line);
            if (count($cols) < 5) {
                // Coba comma-separated jika bukan tab
                $cols = str_getcsv($line);
            }

            // Abaikan jika header baris
            if (stripos($cols[0], 'No') !== false || stripos($cols[1] ?? '', 'Tanggal') !== false) {
                continue;
            }

            // Mapping kolom sesuai format laporan:
            // 0: No., 1: Tanggal TTBP, 2: TTBP, 3: Sdh dipotong, 4: Faktur Promo, 
            // 5: Kode, 6: Nama Pembeli, 7: Value Produk, 8: Program, 9: Nama Barang,
            // 10: potong nota, 11: Pajak, 12: Total Bonus, 13: Qty, 14: Satuan,
            // 15: Volume Prog, 16: Satuan Vol, 17: Periode Prog Awal, 18: Periode Prog Akhir,
            // 19: Faktur, 20: at, 21: tgl kembali, 22: Keterangan
            $no          = trim($cols[0] ?? '');
            $tgl_ttbp    = trim($cols[1] ?? '');
            $nomor_ttbp  = trim($cols[2] ?? '');
            $sdh_potong  = trim($cols[3] ?? '');
            $faktur_promo= trim($cols[4] ?? '');
            $kode        = trim($cols[5] ?? '');
            $nama        = trim($cols[6] ?? '');
            $val_prod    = trim($cols[7] ?? '0');
            $program     = trim($cols[8] ?? '');
            $nama_barang = trim($cols[9] ?? '');
            $pot_nota    = trim($cols[10] ?? '0');
            $pajak       = trim($cols[11] ?? '0');
            $tot_bonus   = trim($cols[12] ?? '0');
            $qty         = trim($cols[13] ?? '0');
            $satuan      = trim($cols[14] ?? '');
            $vol_prog    = trim($cols[15] ?? '0');
            $sat_vol     = trim($cols[16] ?? '');
            $tgl_awal    = trim($cols[17] ?? '');
            $tgl_akhir   = trim($cols[18] ?? '');
            $faktur      = trim($cols[19] ?? '');
            $at          = trim($cols[20] ?? '');
            $tgl_kembali = trim($cols[21] ?? '');
            $ket         = trim($cols[22] ?? '');

            // Validasi baris minimal memiliki Nama Pembeli atau Program
            if (empty($nama) && empty($program)) continue;

            $parsed_rows[] = [
                'no'                   => $no,
                'tanggal_ttbp'         => $tgl_ttbp,
                'nomor_ttbp'           => $nomor_ttbp ?: $no,
                'tgl_sdh_dipotong'     => $sdh_potong,
                'faktur_promo'         => $faktur_promo,
                'kode_kios'            => $kode,
                'nama_pembeli'         => $nama,
                'value_produk_program' => $val_prod,
                'program'              => $program,
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

        if (empty($parsed_rows)) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Tidak ada baris data valid yang terdeteksi untuk diimpor.'
            ]));
            return;
        }

        $inserted = $this->Bonus_promosi_model->import_rows($parsed_rows, $periode_laporan);

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'   => true,
            'inserted' => $inserted,
            'message'  => "Berhasil mengimpor {$inserted} baris data laporan bonus promosi!"
        ]));
    }

    /**
     * Hapus baris laporan
     */
    public function delete($id) {
        $this->db->where('id', $id)->delete('laporan_bonus_promosi');
        $this->session->set_flashdata('success', 'Baris data laporan berhasil dihapus.');
        redirect('bonus_promosi');
    }
}

