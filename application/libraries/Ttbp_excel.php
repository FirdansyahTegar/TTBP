<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Ttbp_excel {

    protected $ci;

    public function __construct() {
        if (function_exists('get_instance')) {
            $this->ci =& get_instance();
        }
    }

    /**
     * Generate dan download Excel lembar TTBP
     */
    public function download_ttbp($ttbp, $filename = null) {
        $spreadsheet = $this->create_ttbp_sheet($ttbp);

        if (empty($filename)) {
            $safe_toko = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ttbp['nama_kios']);
            $filename = 'TTBP_' . $ttbp['nomor_ttbp'] . '_' . $safe_toko . '.xlsx';
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Alias untuk download_ttbp
     */
    public function download_single_ttbp($ttbp, $filename = null) {
        return $this->download_ttbp($ttbp, $filename);
    }

    /**
     * Bangun objek Spreadsheet dengan format fixed TTBP
     */
    public function create_ttbp_sheet($ttbp) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('TTBP ' . $ttbp['nomor_ttbp']);

        // Default font
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Atur lebar kolom (A sampai H)
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(18);  // Barang pt 1
        $sheet->getColumnDimension('C')->setWidth(18);  // Barang pt 2
        $sheet->getColumnDimension('D')->setWidth(16);  // Barang pt 3 / Alamat
        $sheet->getColumnDimension('E')->setWidth(16);  // Alamat
        $sheet->getColumnDimension('F')->setWidth(12);  // Jumlah
        $sheet->getColumnDimension('G')->setWidth(10);  // Satuan
        $sheet->getColumnDimension('H')->setWidth(26);  // Keterangan / Salesman

        $gray_fill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D9D9D9']
        ];

        $border_all_thin = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $border_outline_thin = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        // Format tanggal display (contoh: 30 Oct 2026)
        $tgl_obj = !empty($ttbp['tanggal']) ? strtotime($ttbp['tanggal']) : time();
        $tgl_formatted = date('d M Y', $tgl_obj);

        // --- ROW 1 & 2: HEADER ---
        // Tanggal
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'Tanggal');
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => $gray_fill
        ]);

        $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', $tgl_formatted);
        $sheet->getStyle('A2:B2')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        // Judul Utama (Center)
        $sheet->mergeCells('C1:F2');
        $sheet->setCellValue('C1', "TANDA TERIMA BARANG PROMOSI");
        $sheet->getStyle('C1:F2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        // Nomor TTBP (Kanan)
        $sheet->mergeCells('G1:H1');
        $sheet->setCellValue('G1', 'Nomor TTBP');
        $sheet->getStyle('G1:H1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => $gray_fill
        ]);

        $sheet->mergeCells('G2:H2');
        $sheet->setCellValue('G2', $ttbp['nomor_ttbp']);
        $sheet->getStyle('G2:H2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->getStyle('A1:H2')->applyFromArray($border_all_thin);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // --- ROW 4 & 5: NAMA KIOS, ALAMAT, SALESMAN ---
        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', 'Nama Kios');
        $sheet->mergeCells('D4:G4');
        $sheet->setCellValue('D4', 'Alamat');
        $sheet->setCellValue('H4', 'Salesman');

        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', $ttbp['nama_kios']);
        $sheet->mergeCells('D5:G5');
        $sheet->setCellValue('D5', $ttbp['alamat']);
        $sheet->setCellValue('H5', $ttbp['salesman']);

        $sheet->getStyle('A5:H5')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);

        $sheet->getStyle('A4:H5')->applyFromArray($border_all_thin);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(36);

        // --- ROW 6: REALISASI PROGRAM & PERIODE ---
        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'Realisasi Atas Program');
        $sheet->getStyle('A6:B6')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => $gray_fill
        ]);

        $sheet->mergeCells('C6:F6');
        $sheet->setCellValue('C6', $ttbp['realisasi_program']);
        $sheet->getStyle('C6:F6')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->setCellValue('G6', 'Periode');
        $sheet->getStyle('G6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => $gray_fill
        ]);

        $sheet->setCellValue('H6', $ttbp['periode']);
        $sheet->getStyle('H6')->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->getStyle('A6:H6')->applyFromArray($border_all_thin);
        $sheet->getRowDimension(6)->setRowHeight(24);

        // --- ROW 7: ITEM TABLE HEADER ---
        $sheet->setCellValue('A7', 'No');
        $sheet->mergeCells('B7:E7');
        $sheet->setCellValue('B7', 'Nama Barang');
        $sheet->setCellValue('F7', 'Jumlah');
        $sheet->setCellValue('G7', 'Sat');
        $sheet->setCellValue('H7', 'Keterangan');

        $sheet->getStyle('A7:H7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(7)->setRowHeight(22);

        // --- ROWS 8+: ITEMS (BAGIAN DALAM TANPA GARIS PERSIS CONTOH GAMBAR) ---
        $items = !empty($ttbp['items']) ? $ttbp['items'] : [];
        $is_uang = !empty($ttbp['is_uang']);
        $current_row = 8;
        $total_rows_needed = max(6, count($items));

        if ($is_uang) {
            // Baris 1: Uang
            $sheet->setCellValue('A8', '1');
            $sheet->mergeCells('B8:E8');
            $sheet->setCellValue('B8', 'Uang');
            $sheet->setCellValue('F8', 'Rp  ' . $items[0]['jumlah_fmt']);
            $sheet->setCellValue('G8', $items[0]['satuan']);
            $sheet->setCellValue('H8', '');

            $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G8')->getFont()->setBold(true);
            $sheet->getRowDimension(8)->setRowHeight(22);

            // Baris 2: Pajak (PPh) angka minus
            $sheet->setCellValue('A9', '2');
            $sheet->mergeCells('B9:E9');
            $sheet->setCellValue('B9', 'Pajak (PPh)');
            $sheet->setCellValue('F9', 'Rp  ' . $items[1]['jumlah_fmt']);
            $sheet->setCellValue('G9', '');
            $sheet->setCellValue('H9', '');

            $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getRowDimension(9)->setRowHeight(22);

            // Baris 10 - 12 kosong
            for ($r = 10; $r <= 12; $r++) {
                $sheet->mergeCells('B' . $r . ':E' . $r);
                $sheet->setCellValue('A' . $r, '');
                $sheet->setCellValue('B' . $r, '');
                $sheet->setCellValue('F' . $r, '');
                $sheet->setCellValue('G' . $r, '');
                $sheet->setCellValue('H' . $r, '');
                $sheet->getRowDimension($r)->setRowHeight(20);
            }

            // Baris 13: Footer Total & Keterangan (Transfer / Potong Nota)
            $sheet->setCellValue('A13', '');
            $sheet->mergeCells('B13:E13');
            $sheet->setCellValue('B13', 'Total');
            $sheet->setCellValue('F13', 'Rp  ' . $ttbp['total_nominal_fmt']);
            $sheet->mergeCells('G13:H13');
            $sheet->setCellValue('G13', $ttbp['footer_keterangan']);

            $sheet->getStyle('B13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B13')->getFont()->setBold(true);
            $sheet->getStyle('F13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F13')->getFont()->setBold(true);
            $sheet->getStyle('G13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G13')->getFont()->setBold(true);
            $sheet->getRowDimension(13)->setRowHeight(22);

            $current_row = 14;
        } else {
            for ($i = 0; $i < $total_rows_needed; $i++) {
                $item = isset($items[$i]) ? $items[$i] : null;

                if ($item) {
                    $sheet->setCellValue('A' . $current_row, $item['no_urut']);
                    $sheet->mergeCells('B' . $current_row . ':E' . $current_row);
                    $sheet->setCellValue('B' . $current_row, $item['nama_barang']);
                    $sheet->setCellValue('F' . $current_row, isset($item['jumlah_fmt']) ? $item['jumlah_fmt'] : $item['jumlah']);
                    $sheet->setCellValue('G' . $current_row, $item['satuan']);
                    $sheet->setCellValue('H' . $current_row, $item['keterangan']);

                    $sheet->getStyle('A' . $current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B' . $current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('F' . $current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('G' . $current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('H' . $current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                } else {
                    $sheet->mergeCells('B' . $current_row . ':E' . $current_row);
                    $sheet->setCellValue('A' . $current_row, '');
                    $sheet->setCellValue('B' . $current_row, '');
                    $sheet->setCellValue('F' . $current_row, '');
                    $sheet->setCellValue('G' . $current_row, '');
                    $sheet->setCellValue('H' . $current_row, '');
                }

                $sheet->getRowDimension($current_row)->setRowHeight(22);
                $current_row++;
            }
        }

        // Terapkan border: Header diberi allBorders, Body item diberi outline saja (tanpa garis dalam)
        $sheet->getStyle('A7:H7')->applyFromArray($border_all_thin);
        $sheet->getStyle('A8:H' . ($current_row - 1))->applyFromArray($border_outline_thin);

        // --- FOOTER: PERHATIAN & TANDA TANGAN ---
        $footer_start = $current_row;
        $footer_end = $current_row + 5;

        // Box Perhatian (Kiri)
        $sheet->mergeCells("A{$footer_start}:C{$footer_end}");
        $perhatian_text = !empty($ttbp['perhatian']) ? $ttbp['perhatian'] : 
            "Perhatian :\nBila Terjadi Retur/Faktur Batal, Sehingga\nTarget Tidak Tercapai, Maka Barang Promosi\nYang Telah Diterima Harus Dikembalikan\nSesuai Dengan Jumlah Retur/Faktur Batal";
        
        $sheet->setCellValue("A{$footer_start}", $perhatian_text);
        $sheet->getStyle("A{$footer_start}:C{$footer_end}")->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true
            ]
        ]);

        // Disetujui Oleh
        $sheet->mergeCells("D{$footer_start}:E{$footer_start}");
        $sheet->setCellValue("D{$footer_start}", "Disetujui Oleh");
        $sheet->getStyle("D{$footer_start}:E{$footer_start}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->mergeCells("D{$footer_end}:E{$footer_end}");
        $sheet->setCellValue("D{$footer_end}", !empty($ttbp['disetujui_oleh']) ? $ttbp['disetujui_oleh'] : '');
        $sheet->getStyle("D{$footer_end}:E{$footer_end}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        // Dibuat
        $sheet->mergeCells("F{$footer_start}:G{$footer_start}");
        $sheet->setCellValue("F{$footer_start}", "Dibuat");
        $sheet->getStyle("F{$footer_start}:G{$footer_start}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->mergeCells("F{$footer_end}:G{$footer_end}");
        $sheet->setCellValue("F{$footer_end}", !empty($ttbp['dibuat_oleh']) ? $ttbp['dibuat_oleh'] : 'Nonik Dia');
        $sheet->getStyle("F{$footer_end}:G{$footer_end}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        // Penerima
        $sheet->setCellValue("H{$footer_start}", "Penerima");
        $sheet->getStyle("H{$footer_start}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->setCellValue("H{$footer_end}", !empty($ttbp['penerima']) ? $ttbp['penerima'] : '');
        $sheet->getStyle("H{$footer_end}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        // Border footer boxes (tanpa garis di atas nama penandatangan)
        $sheet->getStyle("A{$footer_start}:C{$footer_end}")->applyFromArray($border_all_thin);
        $sheet->getStyle("D{$footer_start}:E{$footer_start}")->applyFromArray($border_outline_thin);
        $sheet->getStyle("D" . ($footer_start + 1) . ":E{$footer_end}")->applyFromArray($border_outline_thin);
        $sheet->getStyle("F{$footer_start}:G{$footer_start}")->applyFromArray($border_outline_thin);
        $sheet->getStyle("F" . ($footer_start + 1) . ":G{$footer_end}")->applyFromArray($border_outline_thin);
        $sheet->getStyle("H{$footer_start}:H{$footer_start}")->applyFromArray($border_outline_thin);
        $sheet->getStyle("H" . ($footer_start + 1) . ":H{$footer_end}")->applyFromArray($border_outline_thin);

        // Atur tinggi baris footer
        $sheet->getRowDimension($footer_start)->setRowHeight(22);
        for ($r = $footer_start + 1; $r < $footer_end; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }
        $sheet->getRowDimension($footer_end)->setRowHeight(24);

        // Setting halaman cetak Excel (Fit 1 page wide)
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getSheetView()->setView(\PhpOffice\PhpSpreadsheet\Worksheet\SheetView::SHEETVIEW_NORMAL);

        return $spreadsheet;
    }

    /**
     * Export daftar rekapitulasi TTBP ke file Excel
     */
    public function download_list_ttbp($list, $filters = []) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap TTBP');

        $sheet->setCellValue('A1', 'DAFTAR TANDA TERIMA BARANG PROMOSI (TTBP)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $subtitle = 'Tanggal Cetak: ' . date('d-m-Y H:i:s');
        if (!empty($filters['bulan'])) $subtitle .= ' | Bulan: ' . $filters['bulan'];
        if (!empty($filters['salesman'])) $subtitle .= ' | Salesman: ' . $filters['salesman'];
        $sheet->setCellValue('A2', $subtitle);

        // Header tabel
        $headers = ['No', 'Nomor TTBP', 'Tanggal', 'Kode Toko', 'Nama Kios', 'Salesman', 'Realisasi Program', 'Periode', 'Penerima', 'Dibuat Oleh', 'Total Qty'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $sheet->getStyle($col . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');
            $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        $row = 5;
        $no = 1;
        foreach ($list as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item['nomor_ttbp']);
            $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($item['tanggal'])));
            $sheet->setCellValue('D' . $row, $item['kode_kios']);
            $sheet->setCellValue('E' . $row, $item['nama_kios']);
            $sheet->setCellValue('F' . $row, $item['salesman']);
            $sheet->setCellValue('G' . $row, $item['realisasi_program']);
            $sheet->setCellValue('H' . $row, $item['periode']);
            $sheet->setCellValue('I' . $row, $item['penerima']);
            $sheet->setCellValue('J' . $row, $item['dibuat_oleh']);
            $sheet->setCellValue('K' . $row, isset($item['total_qty']) ? $item['total_qty'] : '-');
            $row++;
        }

        // Auto width
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->getStyle('A4:K' . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $filename = 'Rekap_TTBP_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
