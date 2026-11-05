<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ttbp extends CI_Controller {

    public function __construct() {
        parent::__construct();
        check_login();
        $this->load->model('Ttbp_model');
        $this->load->library('Ttbp_excel');
        $this->load->library('pagination');
    }

    /**
     * Dashboard Utama: Rekap Bonus Promosi & TTBP
     */
    public function index() {
        $data['title'] = 'Rekap Bonus Promosi & TTBP';

        $filters = [
            'jenis'    => $this->input->get('jenis', TRUE) ?: '',
            'status'   => $this->input->get('status', TRUE) ?: '',
            'program'  => $this->input->get('program', TRUE) ?: '',
            'bulan'    => $this->input->get('bulan', TRUE) ?: '',
            'search'   => $this->input->get('search', TRUE) ?: ''
        ];

        $sort_by    = $this->input->get('sort', TRUE) ?: 'id';
        $sort_order = $this->input->get('order', TRUE) ?: 'ASC';

        // Pagination
        $limit  = 30;
        $page   = $this->input->get('page', TRUE) ?: 1;
        $offset = ($page - 1) * $limit;

        $total_rows = $this->Ttbp_model->count_laporan($filters);
        $list       = $this->Ttbp_model->get_laporan_list($filters, $limit, $offset, $sort_by, $sort_order);

        // Pagination config Bootstrap 5
        $config['base_url']             = site_url('ttbp') . '?' . http_build_query(array_merge($filters, ['sort' => $sort_by, 'order' => $sort_order]));
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
        $data['stats']            = $this->Ttbp_model->get_laporan_summary_stats();

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('ttbp/index', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Input data manual ditiadakan, dialihkan ke Import Excel
     */
    public function create() {
        $this->session->set_flashdata('info', 'Input data manual telah dialihkan ke fitur Import Excel.');
        redirect('ttbp/import');
    }

    /**
     * Form Edit Data
     */
    public function edit($id) {
        $row = $this->Ttbp_model->get_laporan($id);
        if (!$row) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('ttbp');
        }

        $data['title']           = 'Edit Data Bonus Promosi';
        $data['is_edit']         = true;
        $data['next_no']         = $row['no_urut_lap'];
        $data['programs_master'] = $this->Ttbp_model->search_programs_master('', 100);
        $data['row']             = $row;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('ttbp/form', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Simpan Data Baru / Edit (POST)
     */
    public function save() {
        $id = $this->input->post('id', TRUE);

        $tgl_awal  = $this->input->post('periode_prog_awal', TRUE) ?: null;
        $tgl_akhir = $this->input->post('periode_prog_akhir', TRUE) ?: null;
        $raw_periode = ($tgl_awal && $tgl_akhir) ? (date('d-M-y', strtotime($tgl_awal)) . ' - ' . date('d-M-y', strtotime($tgl_akhir))) : ($this->input->post('periode_prog_raw', TRUE) ?: null);

        $data = [
            'no_urut_lap'          => $this->input->post('no_urut_lap', TRUE),
            'tanggal_ttbp'         => $this->input->post('tanggal_ttbp', TRUE) ?: null,
            'nomor_ttbp'           => $this->input->post('nomor_ttbp', TRUE),
            'tgl_sdh_dipotong'     => $this->input->post('tgl_sdh_dipotong', TRUE) ?: null,
            'faktur_promo'         => $this->input->post('faktur_promo', TRUE) ?: null,
            'kode_kios'            => $this->input->post('kode_kios', TRUE) ?: null,
            'nama_pembeli'         => $this->input->post('nama_pembeli', TRUE),
            'alamat_manual'        => $this->input->post('alamat_manual', TRUE) ?: null,
            'salesman_manual'      => $this->input->post('salesman_manual', TRUE) ?: null,
            'penerima_manual'      => $this->input->post('penerima_manual', TRUE) ?: null,
            'value_produk_program' => $this->input->post('value_produk_program', TRUE) ?: 0,
            'program'              => $this->input->post('program', TRUE),
            'nama_barang'          => $this->input->post('nama_barang', TRUE),
            'potong_nota'          => $this->input->post('potong_nota', TRUE) ?: 0,
            'pajak'                => $this->input->post('pajak', TRUE) ?: 0,
            'total_bonus'          => $this->input->post('total_bonus', TRUE) ?: 0,
            'qty'                  => $this->input->post('qty', TRUE) ?: 0,
            'satuan'               => $this->input->post('satuan', TRUE) ?: null,
            'volume_prog'          => $this->input->post('volume_prog', TRUE) ?: 0,
            'satuan_vol'           => $this->input->post('satuan_vol', TRUE) ?: null,
            'periode_prog_awal'    => $tgl_awal,
            'periode_prog_akhir'   => $tgl_akhir,
            'periode_prog_raw'     => $raw_periode,
            'faktur'               => $this->input->post('faktur', TRUE) ?: null,
            'kode_wilayah'         => $this->input->post('kode_wilayah', TRUE) ?: null,
            'tgl_kembali_ttbp'     => $this->input->post('tgl_kembali_ttbp', TRUE) ?: null,
            'keterangan'           => $this->input->post('keterangan', TRUE) ?: null,
            'dibuat_oleh'          => $this->input->post('dibuat_oleh', TRUE) ?: 'Nonik Dia',
            'disetujui_oleh'       => $this->input->post('disetujui_oleh', TRUE) ?: null,
            'perhatian'            => $this->input->post('perhatian', TRUE) ?: null
        ];

        // Validasi minimal nama pembeli / toko
        if (empty($data['nama_pembeli'])) {
            $this->session->set_flashdata('error', 'Nama Pembeli / Kios wajib diisi.');
            redirect($id ? 'ttbp/edit/' . $id : 'ttbp/create');
        }

        $saved_id = $this->Ttbp_model->save_laporan($data, $id);

        $action_btn = $this->input->post('action_btn', TRUE);
        $this->session->set_flashdata('success', 'Data Bonus Promosi berhasil disimpan.');

        if ($action_btn === 'save_and_print') {
            redirect('ttbp/cetak/' . $saved_id . '?autoprint=1');
        } else if ($action_btn === 'save_and_create') {
            redirect('ttbp/create');
        } else {
            redirect('ttbp');
        }
    }

    /**
     * Hapus Data Baris Tunggal
     */
    public function delete($id) {
        $this->Ttbp_model->delete_laporan($id);
        $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        redirect('ttbp');
    }

    /**
     * Hapus Data Batch (Banyak Baris Terpilih)
     */
    public function delete_batch() {
        $ids = $this->input->post('ids');
        if (!empty($ids) && is_array($ids)) {
            $count = $this->Ttbp_model->delete_laporan_batch($ids);
            $this->session->set_flashdata('success', "Berhasil menghapus {$count} baris data.");
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data yang dipilih untuk dihapus.');
        }
        redirect('ttbp');
    }

    /**
     * Kosongkan Seluruh List Data Laporan
     */
    public function clear_all() {
        $this->Ttbp_model->clear_all_laporan();
        $this->session->set_flashdata('success', 'Seluruh data list berhasil dikosongkan.');
        redirect('ttbp');
    }

    /**
     * API: Pencarian Program dari st_programs untuk Auto-Fill
     */
    public function api_search_programs() {
        $q = $this->input->get('q', TRUE) ?: '';
        $results = $this->Ttbp_model->search_programs_master($q, 50);
        $this->output->set_content_type('application/json')->set_output(json_encode($results));
    }

    /**
     * Halaman & Modal Import Excel (.xlsx / .xls / .csv / paste)
     */
    public function import() {
        $data['title'] = 'Import Data Excel Bonus Promosi';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('ttbp/import', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * AJAX endpoint: Upload File Excel (.xlsx, .xls, .csv)
     */
    public function import_file_ajax() {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        if (empty($_FILES['excel_file']['tmp_name'])) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Pilih file Excel yang akan diunggah.'
            ]));
            return;
        }

        $periode = $this->input->post('periode_laporan', TRUE) ?: 'Januari - Desember 2026';
        $tmp_file = $_FILES['excel_file']['tmp_name'];

        try {
            $count = $this->Ttbp_model->import_laporan_excel_file($tmp_file, $periode);
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => true,
                'count'   => $count,
                'message' => "Berhasil mengimpor {$count} baris data dari file Excel!"
            ]));
        } catch (Exception $e) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * AJAX endpoint: Import Text Paste dari Clipboard Excel
     */
    public function import_paste_ajax() {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $raw_text = $this->input->post('raw_text');
        $periode  = $this->input->post('periode_laporan', TRUE) ?: 'Januari - Desember 2026';

        if (empty(trim($raw_text))) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Teks yang ditempel masih kosong.'
            ]));
            return;
        }

        $count = $this->Ttbp_model->import_laporan_paste($raw_text, $periode);
        if ($count > 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => true,
                'count'   => $count,
                'message' => "Berhasil mengimpor {$count} baris data dari teks yang ditempel!"
            ]));
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Tidak ada baris data valid yang berhasil diimpor.'
            ]));
        }
    }

    /**
     * Cetak Lembar TTBP Fisik Continuous Form (CF K4 W PRS 4 ply - 9.5 x 11 inch Portrait)
     * Mendukung:
     * - 1 TTBP tunggal (Slot Atas)
     * - 2 TTBP per 1 lembar kertas 9.5 x 11 inch (Portrait: Slot Atas & Slot Bawah) via ?ttbp2=...
     * - Multi-TTBP / Batch print (2 TTBP per lembar) via ?ids=C260001,C260002,...
     */
    public function cetak($id_or_nomor = null) {
        $ids_param = $this->input->get('ids', TRUE);
        $ttbp2_param = $this->input->get('ttbp2', TRUE);

        $sheets = [];
        $primary_ttbp = null;

        if (!empty($ids_param)) {
            $id_list = array_filter(array_map('trim', explode(',', $ids_param)));
            foreach ($id_list as $item_id) {
                $s = $this->Ttbp_model->get_ttbp_sheet_data($item_id);
                if ($s) {
                    $sheets[] = $s;
                }
            }
            if (!empty($sheets)) {
                $primary_ttbp = $sheets[0];
            }
        } else if (!empty($id_or_nomor)) {
            $primary_ttbp = $this->Ttbp_model->get_ttbp_sheet_data($id_or_nomor);
            if ($primary_ttbp) {
                $sheets[] = $primary_ttbp;
            }
        }

        if (empty($sheets) || !$primary_ttbp) {
            show_404();
            return;
        }

        // Ambil informasi TTBP berikutnya & daftar pilihan untuk Slot Bawah (1 Kertas = 2 TTBP)
        $adjacent = $this->Ttbp_model->get_next_and_selectable_ttbp($primary_ttbp['id'], $primary_ttbp['nomor_ttbp_raw']);

        // Jika mode 1 kertas utama dan ada parameter ttbp2 (atau default pasangan)
        if (empty($ids_param) && $ttbp2_param !== null && $ttbp2_param !== '' && $ttbp2_param !== 'none') {
            if ($ttbp2_param === 'copy') {
                $sheets[] = $primary_ttbp;
            } else if ($ttbp2_param === 'next' && !empty($adjacent['next_nomor'])) {
                $second = $this->Ttbp_model->get_ttbp_sheet_data($adjacent['next_nomor']);
                if ($second) $sheets[] = $second;
            } else {
                $second = $this->Ttbp_model->get_ttbp_sheet_data($ttbp2_param);
                if ($second) $sheets[] = $second;
            }
        }

        $this->load->library('ttbp_lx310_direct');
        $printers = $this->ttbp_lx310_direct->get_installed_printers();
        $default_lx310 = $this->ttbp_lx310_direct->find_lx310_printer();

        $this->load->view('ttbp/print', [
            'ttbp'          => $primary_ttbp,
            'sheets'        => $sheets,
            'ttbp2_param'   => $ttbp2_param ?: 'none',
            'next_nomor'    => $adjacent['next_nomor'],
            'ttbp_options'  => $adjacent['options'],
            'printers'      => $printers,
            'default_lx310' => $default_lx310
        ]);
    }

    /**
     * AJAX endpoint: Cetak Langsung Presisi Tinggi ke Printer Epson LX-310 ESC/P
     * Menggunakan engine GDI+ Millimeter (.NET PrintDocument) agar:
     * - 100% lebar kertas terpakai penuh (bebas bug 2/3 lebar kertas di Chromium/Brave)
     * - 100% garis tabel tercetak utuh tanpa ada garis hilang
     * - Orientasi Portrait (1 Kertas 9.5 x 11 inch = 2 TTBP Atas & Bawah)
     */
    public function print_direct_lx310_ajax() {
        $nomors = $this->input->post('nomors', TRUE);
        $printer = $this->input->post('printer', TRUE);

        if (empty($nomors) || !is_array($nomors)) {
            $single = $this->input->post('nomor', TRUE);
            $nomors = !empty($single) ? [$single] : [];
        }

        $sheets = [];
        foreach ($nomors as $no) {
            $no = trim((string)$no);
            if ($no === '') continue;
            $s = $this->Ttbp_model->get_ttbp_sheet_data($no);
            if ($s) {
                $sheets[] = $s;
            }
        }

        if (empty($sheets)) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Data TTBP tidak ditemukan untuk dicetak.'
            ]));
            return;
        }

        $this->load->library('ttbp_lx310_direct');
        $result = $this->ttbp_lx310_direct->print_sheets($sheets, $printer);

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    /**
     * AJAX endpoint: Pratinjau Lembar TTBP Fisik (Modal Preview)
     */
    public function preview_partial_ajax($id_or_nomor) {
        $ttbp = $this->Ttbp_model->get_ttbp_sheet_data($id_or_nomor);
        if (!$ttbp) {
            echo '<div class="alert alert-danger mb-0">Data TTBP tidak ditemukan.</div>';
            return;
        }

        $this->load->view('ttbp/preview_partial', ['ttbp' => $ttbp, 'fixed_rows' => 6]);
    }

    /**
     * Export Lembar TTBP Fisik Tunggal ke Excel
     */
    public function export_excel($id_or_nomor) {
        $ttbp = $this->Ttbp_model->get_ttbp_sheet_data($id_or_nomor);
        if (!$ttbp) {
            show_404();
        }

        $this->ttbp_excel->download_single_ttbp($ttbp);
    }

    /**
     * Export Rekapitulasi Laporan Lengkap ke Excel
     */
    public function export_list_excel() {
        $filters = [
            'jenis'    => $this->input->get('jenis', TRUE) ?: '',
            'status'   => $this->input->get('status', TRUE) ?: '',
            'program'  => $this->input->get('program', TRUE) ?: '',
            'bulan'    => $this->input->get('bulan', TRUE) ?: '',
            'search'   => $this->input->get('search', TRUE) ?: ''
        ];

        $list = $this->Ttbp_model->get_laporan_list($filters, null, null, 'id', 'ASC');

        require_once FCPATH . 'vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BONUS PROMOSI');

        // Header Title (Merge across columns so column A doesn't stretch!)
        $sheet->mergeCells('A1:V1');
        $sheet->setCellValue('A1', 'BONUS PROMOSI PERIODE JANUARI - DESEMBER 2026');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Header Table (Row 3)
        $headers = [
            'A3' => 'No.', 'B3' => 'Tanggal TTBP', 'C3' => 'TTBP', 'D3' => 'Sdh dipotong', 'E3' => 'Faktur Promo',
            'F3' => 'Kode', 'G3' => 'Nama Pembeli', 'H3' => 'Value Produk Program', 'I3' => 'Program', 'J3' => 'Nama Barang',
            'K3' => 'potong nota', 'L3' => 'Pajak', 'M3' => 'Total Bonus', 'N3' => 'Qty', 'O3' => 'Satuan',
            'P3' => 'Volume Prog', 'Q3' => 'Periode Awal', 'R3' => 'Periode Akhir', 'S3' => 'Faktur', 'T3' => 'at',
            'U3' => 'tanggal kembali TTBP', 'V3' => 'Keterangan'
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $sheet->getStyle('A3:V3')->getFont()->setBold(true);
        $sheet->getStyle('A3:V3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD8E2BD');

        $row_idx = 4;
        foreach ($list as $r) {
            // Pemisahan Periode Awal & Akhir
            $tgl_awal = !empty($r['periode_prog_awal']) ? date('d-M-y', strtotime($r['periode_prog_awal'])) : '';
            $tgl_akhir = !empty($r['periode_prog_akhir']) ? date('d-M-y', strtotime($r['periode_prog_akhir'])) : '';
            if (empty($tgl_awal) && !empty($r['periode_prog_raw'])) {
                $parts = explode(' - ', $r['periode_prog_raw']);
                if (count($parts) >= 2) {
                    $tgl_awal = trim($parts[0]);
                    $tgl_akhir = trim($parts[1]);
                } else {
                    $tgl_awal = $r['periode_prog_raw'];
                }
            }

            $sheet->setCellValue('A' . $row_idx, intval($r['no_urut_lap']) > 0 ? intval($r['no_urut_lap']) : ($row_idx - 3));
            $sheet->setCellValue('B' . $row_idx, $r['tanggal_ttbp'] ? date('d-M-y', strtotime($r['tanggal_ttbp'])) : '');
            $sheet->setCellValue('C' . $row_idx, Ttbp_model::format_nomor_ttbp($r['nomor_ttbp'], $r['tanggal_ttbp']));
            $sheet->setCellValue('D' . $row_idx, $r['tgl_sdh_dipotong'] ? date('d-M-y', strtotime($r['tgl_sdh_dipotong'])) : '');
            $sheet->setCellValue('E' . $row_idx, $r['faktur_promo']);
            $sheet->setCellValue('F' . $row_idx, $r['kode_kios']);
            $sheet->setCellValue('G' . $row_idx, $r['nama_pembeli']);
            $sheet->setCellValue('H' . $row_idx, floatval($r['value_produk_program']));
            $sheet->setCellValue('I' . $row_idx, $r['program']);
            $sheet->setCellValue('J' . $row_idx, $r['nama_barang']);
            $sheet->setCellValue('K' . $row_idx, floatval($r['potong_nota']));
            $sheet->setCellValue('L' . $row_idx, floatval($r['pajak']));
            $sheet->setCellValue('M' . $row_idx, floatval($r['total_bonus']));
            $sheet->setCellValue('N' . $row_idx, floatval($r['qty']));
            $sheet->setCellValue('O' . $row_idx, $r['satuan']);
            $sheet->setCellValue('P' . $row_idx, floatval($r['volume_prog']) > 0 ? (floatval($r['volume_prog']) . ' ' . $r['satuan_vol']) : '');
            $sheet->setCellValue('Q' . $row_idx, $tgl_awal);
            $sheet->setCellValue('R' . $row_idx, $tgl_akhir);
            // Faktur & at terpisah
            $sheet->setCellValue('S' . $row_idx, $r['faktur']);
            $sheet->setCellValue('T' . $row_idx, $r['kode_wilayah']);
            $sheet->setCellValue('U' . $row_idx, $r['tgl_kembali_ttbp'] ? date('d-M-y', strtotime($r['tgl_kembali_ttbp'])) : '');
            $sheet->setCellValue('V' . $row_idx, $r['keterangan']);

            // Number formatting
            $sheet->getStyle('H' . $row_idx)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K' . $row_idx)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('L' . $row_idx)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $row_idx)->getNumberFormat()->setFormatCode('#,##0');

            $row_idx++;
        }

        // Atur lebar kolom No agar proporsional (tidak melebar)
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(8);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto width untuk kolom B sampai V
        foreach (range('B', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Rekap_Bonus_Promosi_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * AJAX endpoint: Update Tanggal Kembali TTBP
     */
    public function update_tgl_kembali_ajax() {
        $id = $this->input->post('id', TRUE);
        $tgl_kembali = $this->input->post('tgl_kembali', TRUE);

        if (!$id) {
            $this->output->set_status_header(400)->set_output(json_encode([
                'status'  => false,
                'message' => 'ID data tidak valid.'
            ]));
            return;
        }

        $res = $this->Ttbp_model->update_laporan_tgl_kembali($id, $tgl_kembali);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'  => (bool)$res,
            'message' => $res ? 'Tanggal kembali TTBP berhasil disimpan.' : 'Gagal menyimpan tanggal.'
        ]));
    }

    /**
     * JSON API: Pencarian Toko Autocomplete (dari st_master_toko)
     */
    public function api_search_toko() {
        $q = $this->input->get('q', TRUE) ?: '';
        $results = $this->Ttbp_model->search_toko($q, 25);
        $this->output->set_content_type('application/json')->set_output(json_encode($results));
    }
}
