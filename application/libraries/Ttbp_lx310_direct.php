<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library: Ttbp_lx310_direct
 * Engine Cetak Presisi Tinggi khusus Printer Dot Matrix Epson LX-310 ESC/P
 * untuk Kertas Continuous Form CF K4 W PRS (9.5 x 11 inch / Printable 8.5 x 11 inch).
 *
 * Keunggulan:
 * - Orientasi Portrait murni: 1 Lembar (9.5 x 11 inch) memuat 2 TTBP (Slot Atas & Slot Bawah).
 * - Mengatasi bug Chromium 2/3 lebar kertas pada resolusi non-square (120x72 / 240x144 DPI)
 *   dengan menggambar langsung menggunakan koordinat milimeter GDI+ (.NET PrintDocument).
 * - Seluruh garis tabel digambar dengan ketebalan penuh (2px / 0.45mm solid hitam) tanpa anti-aliasing
 *   sehingga 100% garis vertikal dan horizontal tercetak tegas oleh jarum 9-pin LX-310.
 */
class Ttbp_lx310_direct {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Dapatkan daftar printer yang terpasang di Windows Server/Host
     */
    public function get_installed_printers() {
        $ps_script = <<<'PS'
Add-Type -AssemblyName System.Drawing
$list = @()
foreach ($p in [System.Drawing.Printing.PrinterSettings]::InstalledPrinters) {
    $list += $p
}
ConvertTo-Json -InputObject $list -Compress
PS;
        $output = $this->run_powershell($ps_script);
        $decoded = json_decode(trim($output), true);
        if (is_string($decoded)) {
            return [$decoded];
        }
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Cari nama printer Epson LX-310 default yang terpasang
     */
    public function find_lx310_printer() {
        $printers = $this->get_installed_printers();
        foreach ($printers as $p) {
            if (stripos($p, 'LX-310') !== false || stripos($p, 'LX 310') !== false || stripos($p, 'LX310') !== false) {
                return $p;
            }
        }
        foreach ($printers as $p) {
            if (stripos($p, 'EPSON') !== false) {
                return $p;
            }
        }
        return !empty($printers[0]) ? $printers[0] : '';
    }

    /**
     * Cetak 1 atau banyak TTBP (2 TTBP per lembar 9.5 x 11 inch Portrait) langsung ke printer Epson LX-310
     *
     * @param array  $sheets       Array of TTBP sheet data dari Ttbp_model::get_ttbp_sheet_data()
     * @param string $printer_name Nama printer (opsional, otomatis deteksi LX-310 jika kosong)
     * @param string $preview_png  Path output PNG jika hanya ingin render preview (opsional)
     * @return array ['status' => bool, 'message' => string, 'printer' => string]
     */
    public function print_sheets($sheets, $printer_name = '', $preview_png = '') {
        if (empty($sheets)) {
            return ['status' => false, 'message' => 'Data TTBP kosong.'];
        }

        if (empty($printer_name) && empty($preview_png)) {
            $printer_name = $this->find_lx310_printer();
            if (empty($printer_name)) {
                return ['status' => false, 'message' => 'Printer Epson LX-310 tidak ditemukan pada sistem.'];
            }
        }

        // Siapkan payload JSON untuk dibaca oleh script GDI+ PowerShell
        $pages = array_chunk(array_values($sheets), 2);
        $payload = [
            'printer_name' => $printer_name,
            'preview_png'  => $preview_png,
            'pages'        => $pages
        ];

        $temp_dir = sys_get_temp_dir();
        $json_file = tempnam($temp_dir, 'ttbp_job_') . '.json';
        $ps1_file  = tempnam($temp_dir, 'ttbp_ps_') . '.ps1';

        file_put_contents($json_file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents($ps1_file, $this->get_gdi_print_ps_script());

        $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File '
             . escapeshellarg($ps1_file) . ' -JsonPath ' . escapeshellarg($json_file) . ' 2>&1';

        $out = shell_exec($cmd);

        @unlink($json_file);
        @unlink($ps1_file);

        $res = json_decode(trim((string)$out), true);
        if (is_array($res) && isset($res['status'])) {
            return $res;
        }

        return [
            'status'  => false,
            'message' => 'Gagal menjalankan spooler cetak: ' . trim((string)$out),
            'printer' => $printer_name
        ];
    }

    /**
     * Jalankan perintah PowerShell singkat
     */
    protected function run_powershell($script) {
        $temp_ps1 = tempnam(sys_get_temp_dir(), 'ttbp_cmd_') . '.ps1';
        file_put_contents($temp_ps1, $script);
        $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' . escapeshellarg($temp_ps1) . ' 2>&1';
        $out = shell_exec($cmd);
        @unlink($temp_ps1);
        return (string)$out;
    }

    /**
     * Script PowerShell GDI+ (.NET System.Drawing.Printing)
     * Menggambar di atas satuan Millimeter murni sehingga kebal terhadap bug rasio DPI 120x72 / 240x144.
     */
    protected function get_gdi_print_ps_script() {
        return <<<'POWERSHELL'
param(
    [Parameter(Mandatory=$true)]
    [string]$JsonPath
)

$ErrorActionPreference = 'Stop'

try {
    Add-Type -AssemblyName System.Drawing

    $rawJson = [System.IO.File]::ReadAllText($JsonPath, [System.Text.Encoding]::UTF8)
    $job = ConvertFrom-Json -InputObject $rawJson

    $pages = $job.pages
    $printerName = [string]$job.printer_name
    $previewPng  = [string]$job.preview_png

    # Fungsi menggambar 1 blok TTBP pada Graphics (satuan: Millimeter)
    # Ukuran kertas CF K4 W PRS: 9.5 x 11 inch (Lebar area bersih tanpa lubang traktor = 215.9 mm, Tinggi = 279.4 mm)
    # PrintableArea Epson LX-310: OriginLeft = 3.175mm, ExtentWidth = 202.98mm, OriginTop = 4.23mm, ExtentHeight = 270.93mm
    # 1 Kertas = 2 TTBP (Portrait Atas & Bawah):
    # - Slot Atas  : X = 3.5mm, Y = 4.5mm,   Width = 202.0mm, Height = 131.5mm (Berakhir di 136.0mm; garis potong tengah = 139.7mm)
    # - Slot Bawah : X = 3.5mm, Y = 143.4mm, Width = 202.0mm, Height = 131.5mm (Berakhir di 274.9mm; batas bawah = 279.4mm)
    $drawTtbpSlot = {
        param(
            [System.Drawing.Graphics]$g,
            $ttbp,
            [float]$startX,
            [float]$startY,
            [float]$width,
            [float]$OriginOffsetX,
            [float]$OriginOffsetY
        )

        $x0 = $startX - $OriginOffsetX
        $y0 = $startY - $OriginOffsetY
        $w  = $width

        # Pen hitam tegas (0.45 mm ~ 1.5pt/2px) tanpa anti-aliasing agar jarum 9-pin LX-310 memukul 100% garis
        $penBorder = New-Object System.Drawing.Pen([System.Drawing.Color]::Black, 0.45)
        $penThick  = New-Object System.Drawing.Pen([System.Drawing.Color]::Black, 0.55)
        $brushBlack = [System.Drawing.Brushes]::Black

        # Fonts (Times New Roman untuk judul utama, Arial untuk konten agar tajam di dot-matrix)
        $fontTitle     = New-Object System.Drawing.Font("Times New Roman", 13.5, [System.Drawing.FontStyle]::Bold)
        $fontNomor     = New-Object System.Drawing.Font("Arial", 11.5, [System.Drawing.FontStyle]::Bold)
        $fontHeader    = New-Object System.Drawing.Font("Arial", 9.5,  [System.Drawing.FontStyle]::Bold)
        $fontSubHeader = New-Object System.Drawing.Font("Arial", 9.0,  [System.Drawing.FontStyle]::Bold)
        $fontRegular   = New-Object System.Drawing.Font("Arial", 9.5,  [System.Drawing.FontStyle]::Regular)
        $fontMedium    = New-Object System.Drawing.Font("Arial", 9.5,  [System.Drawing.FontStyle]::Bold)
        $fontPeriode   = New-Object System.Drawing.Font("Arial", 9.5,  [System.Drawing.FontStyle]::Bold)
        $fontPerhatian = New-Object System.Drawing.Font("Arial", 8.0,  [System.Drawing.FontStyle]::Regular)
        $fontPerhBold  = New-Object System.Drawing.Font("Arial", 8.2,  [System.Drawing.FontStyle]::Bold)

        # StringFormats
        $sfCenter = New-Object System.Drawing.StringFormat
        $sfCenter.Alignment = [System.Drawing.StringAlignment]::Center
        $sfCenter.LineAlignment = [System.Drawing.StringAlignment]::Center

        $sfLeft = New-Object System.Drawing.StringFormat
        $sfLeft.Alignment = [System.Drawing.StringAlignment]::Near
        $sfLeft.LineAlignment = [System.Drawing.StringAlignment]::Center
        $sfLeft.FormatFlags = [System.Drawing.StringFormatFlags]::NoWrap

        $sfRight = New-Object System.Drawing.StringFormat
        $sfRight.Alignment = [System.Drawing.StringAlignment]::Far
        $sfRight.LineAlignment = [System.Drawing.StringAlignment]::Center
        $sfRight.FormatFlags = [System.Drawing.StringFormatFlags]::NoWrap

        $sfWrapCenter = New-Object System.Drawing.StringFormat
        $sfWrapCenter.Alignment = [System.Drawing.StringAlignment]::Center
        $sfWrapCenter.LineAlignment = [System.Drawing.StringAlignment]::Center

        $curY = $y0

        # =========================================================================
        # SECTION 1: HEADER (TANGGAL | TANDA TERIMA BARANG PROMOSI | NOMOR TTBP)
        # Tinggi: Row1 = 6.5mm, Row2 = 8.5mm -> Total 15.0mm + 1.0mm double-line gap
        # =========================================================================
        $hHead1 = 6.5
        $hHead2 = 8.5
        $hHeadTotal = $hHead1 + $hHead2
        $wCol1 = [float]($w * 0.22)
        $wCol2 = [float]($w * 0.56)
        $wCol3 = [float]($w - $wCol1 - $wCol2)

        # Garis vertikal Header
        $g.DrawLine($penBorder, $x0 + $wCol1, $curY, $x0 + $wCol1, $curY + $hHeadTotal)
        $g.DrawLine($penBorder, $x0 + $wCol1 + $wCol2, $curY, $x0 + $wCol1 + $wCol2, $curY + $hHeadTotal)

        # Garis horizontal tengah kolom Tanggal & Nomor TTBP
        $g.DrawLine($penBorder, $x0, $curY + $hHead1, $x0 + $wCol1, $curY + $hHead1)
        $g.DrawLine($penBorder, $x0 + $wCol1 + $wCol2, $curY + $hHead1, $x0 + $w, $curY + $hHead1)

        # Garis ganda (Double Line) di bawah Header
        $g.DrawLine($penBorder, $x0, $curY + $hHeadTotal, $x0 + $w, $curY + $hHeadTotal)
        $g.DrawLine($penBorder, $x0, $curY + $hHeadTotal + 0.9, $x0 + $w, $curY + $hHeadTotal + 0.9)

        # Teks Header
        $tglStr = if ($ttbp.tanggal) { ([datetime]$ttbp.tanggal).ToString("dd MMM yyyy") } else { (Get-Date).ToString("dd MMM yyyy") }
        $g.DrawString("Tanggal", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $curY, $wCol1, $hHead1)), $sfCenter)
        $g.DrawString($tglStr, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $curY + $hHead1, $wCol1, $hHead2)), $sfCenter)

        $g.DrawString("TANDA TERIMA BARANG PROMOSI", $fontTitle, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wCol1, $curY, $wCol2, $hHeadTotal)), $sfCenter)

        $g.DrawString("Nomor TTBP", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wCol1 + $wCol2, $curY, $wCol3, $hHead1)), $sfCenter)
        $g.DrawString([string]$ttbp.nomor_ttbp, $fontNomor, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wCol1 + $wCol2, $curY + $hHead1, $wCol3, $hHead2)), $sfCenter)

        $curY += $hHeadTotal + 0.9

        # =========================================================================
        # SECTION 2: NAMA KIOS | ALAMAT | SALESMAN
        # Tinggi: Row1 = 6.5mm, Row2 = 15.5mm -> Total 22.0mm
        # =========================================================================
        $hKios1 = 6.5
        $hKios2 = 15.5
        $hKiosTotal = $hKios1 + $hKios2
        $wK1 = [float]($w * 0.35)
        $wK2 = [float]($w * 0.48)
        $wK3 = [float]($w - $wK1 - $wK2)

        $g.DrawLine($penBorder, $x0 + $wK1, $curY, $x0 + $wK1, $curY + $hKiosTotal)
        $g.DrawLine($penBorder, $x0 + $wK1 + $wK2, $curY, $x0 + $wK1 + $wK2, $curY + $hKiosTotal)
        $g.DrawLine($penBorder, $x0, $curY + $hKios1, $x0 + $w, $curY + $hKios1)
        $g.DrawLine($penBorder, $x0, $curY + $hKiosTotal, $x0 + $w, $curY + $hKiosTotal)

        $g.DrawString("Nama Kios", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $curY, $wK1, $hKios1)), $sfCenter)
        $g.DrawString("Alamat", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wK1, $curY, $wK2, $hKios1)), $sfCenter)
        $g.DrawString("Salesman", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wK1 + $wK2, $curY, $wK3, $hKios1)), $sfCenter)

        $g.DrawString([string]$ttbp.nama_kios, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + 2, $curY + $hKios1 + 1, $wK1 - 4, $hKios2 - 2)), $sfWrapCenter)
        $g.DrawString([string]$ttbp.alamat, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wK1 + 2, $curY + $hKios1 + 1, $wK2 - 4, $hKios2 - 2)), $sfWrapCenter)
        $g.DrawString([string]$ttbp.salesman, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wK1 + $wK2 + 1, $curY + $hKios1 + 1, $wK3 - 2, $hKios2 - 2)), $sfWrapCenter)

        $curY += $hKiosTotal

        # =========================================================================
        # SECTION 3: REALISASI ATAS PROGRAM & PERIODE
        # Tinggi: 8.0mm
        # =========================================================================
        $hProg = 8.0
        $wP1 = [float]($w * 0.21)
        $wP2 = [float]($w * 0.44)
        $wP3 = [float]($w * 0.09)
        $wP4 = [float]($w - $wP1 - $wP2 - $wP3)

        $g.DrawLine($penBorder, $x0 + $wP1, $curY, $x0 + $wP1, $curY + $hProg)
        $g.DrawLine($penBorder, $x0 + $wP1 + $wP2, $curY, $x0 + $wP1 + $wP2, $curY + $hProg)
        $g.DrawLine($penBorder, $x0 + $wP1 + $wP2 + $wP3, $curY, $x0 + $wP1 + $wP2 + $wP3, $curY + $hProg)
        $g.DrawLine($penBorder, $x0, $curY + $hProg, $x0 + $w, $curY + $hProg)

        $g.DrawString("Realisasi Atas Program", $fontSubHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + 2, $curY, $wP1 - 2, $hProg)), $sfLeft)
        $g.DrawString([string]$ttbp.realisasi_program, $fontMedium, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wP1 + 2.5, $curY, $wP2 - 4, $hProg)), $sfLeft)
        $g.DrawString("Periode", $fontSubHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wP1 + $wP2, $curY, $wP3, $hProg)), $sfCenter)
        $g.DrawString([string]$ttbp.periode, $fontPeriode, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wP1 + $wP2 + $wP3, $curY, $wP4, $hProg)), $sfCenter)

        $curY += $hProg

        # =========================================================================
        # SECTION 4: ITEMS TABLE (BAGIAN DALAM TANPA GARIS)
        # Tinggi Header = 7.0mm, Body = 43.0mm -> Total = 50.0mm
        # =========================================================================
        $hItemHead = 7.0
        $hItemBody = 43.0
        $wI1 = [float]($w * 0.05)
        $wI2 = [float]($w * 0.43)
        $wI3 = [float]($w * 0.21)
        $wI4 = [float]($w * 0.07)
        $wI5 = [float]($w - $wI1 - $wI2 - $wI3 - $wI4)

        # Garis Header Tabel Item
        $g.DrawLine($penBorder, $x0 + $wI1, $curY, $x0 + $wI1, $curY + $hItemHead)
        $g.DrawLine($penBorder, $x0 + $wI1 + $wI2, $curY, $x0 + $wI1 + $wI2, $curY + $hItemHead)
        $g.DrawLine($penBorder, $x0 + $wI1 + $wI2 + $wI3, $curY, $x0 + $wI1 + $wI2 + $wI3, $curY + $hItemHead)
        $g.DrawLine($penBorder, $x0 + $wI1 + $wI2 + $wI3 + $wI4, $curY, $x0 + $wI1 + $wI2 + $wI3 + $wI4, $curY + $hItemHead)
        $g.DrawLine($penBorder, $x0, $curY + $hItemHead, $x0 + $w, $curY + $hItemHead)

        $g.DrawString("No", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $curY, $wI1, $hItemHead)), $sfCenter)
        $g.DrawString("Nama Barang", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1, $curY, $wI2, $hItemHead)), $sfCenter)
        $g.DrawString("Jumlah", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2, $curY, $wI3, $hItemHead)), $sfCenter)
        $g.DrawString("Sat", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3, $curY, $wI4, $hItemHead)), $sfCenter)
        $g.DrawString("Keterangan", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3 + $wI4, $curY, $wI5, $hItemHead)), $sfCenter)

        $curY += $hItemHead
        $bodyTopY = $curY
        $items = @($ttbp.items)

        if ($ttbp.is_uang) {
            $rowH = 7.0
            # Baris 1: Uang
            $it0 = $items[0]
            $g.DrawString("1", $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $bodyTopY, $wI1, $rowH)), $sfCenter)
            $g.DrawString("Uang", $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + 3, $bodyTopY, $wI2 - 3, $rowH)), $sfLeft)
            $g.DrawString("Rp", $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + 2.5, $bodyTopY, 10, $rowH)), $sfLeft)
            $g.DrawString([string]$it0.jumlah_fmt, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2, $bodyTopY, $wI3 - 2.5, $rowH)), $sfRight)
            $g.DrawString([string]$it0.satuan, $fontMedium, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3, $bodyTopY, $wI4, $rowH)), $sfCenter)
            if ($it0.keterangan) {
                $g.DrawString([string]$it0.keterangan, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3 + $wI4 + 2.5, $bodyTopY, $wI5 - 3, $rowH)), $sfLeft)
            }

            # Baris 2: Pajak (PPh)
            $it1 = $items[1]
            $r2Y = $bodyTopY + $rowH
            $g.DrawString("2", $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $r2Y, $wI1, $rowH)), $sfCenter)
            $g.DrawString("Pajak (PPh)", $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + 3, $r2Y, $wI2 - 3, $rowH)), $sfLeft)
            $g.DrawString("Rp", $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + 2.5, $r2Y, 10, $rowH)), $sfLeft)
            $g.DrawString([string]$it1.jumlah_fmt, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2, $r2Y, $wI3 - 2.5, $rowH)), $sfRight)

            # Baris Footer Total di bagian bawah area item
            $totY = $bodyTopY + $hItemBody - 8.0
            $g.DrawString("Total", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1, $totY, $wI2 - 6, 7.5)), $sfRight)
            $g.DrawString("Rp", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + 2.5, $totY, 10, 7.5)), $sfLeft)
            $g.DrawString([string]$ttbp.total_nominal_fmt, $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2, $totY, $wI3 - 2.5, 7.5)), $sfRight)
            $g.DrawString([string]$ttbp.footer_keterangan, $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3 + 3, $totY, $wI4 + $wI5 - 3, 7.5)), $sfLeft)
        } else {
            $maxRows = [Math]::Max(6, $items.Count)
            $rowH = [float]($hItemBody / $maxRows)
            for ($i = 0; $i -lt $items.Count; $i++) {
                $it = $items[$i]
                $ry = $bodyTopY + ($i * $rowH)
                $jmlStr = if ($it.jumlah_fmt) { [string]$it.jumlah_fmt } else { [string]$it.jumlah }
                $g.DrawString([string]$it.no_urut, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0, $ry, $wI1, $rowH)), $sfCenter)
                $g.DrawString([string]$it.nama_barang, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + 3, $ry, $wI2 - 4, $rowH)), $sfLeft)
                $g.DrawString($jmlStr, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2, $ry, $wI3 - 4, $rowH)), $sfRight)
                $g.DrawString([string]$it.satuan, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3, $ry, $wI4, $rowH)), $sfCenter)
                if ($it.keterangan) {
                    $g.DrawString([string]$it.keterangan, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wI1 + $wI2 + $wI3 + $wI4 + 2.5, $ry, $wI5 - 3, $rowH)), $sfLeft)
                }
            }
        }

        $curY += $hItemBody
        # Garis penutup bawah tabel item
        $g.DrawLine($penBorder, $x0, $curY, $x0 + $w, $curY)

        # =========================================================================
        # SECTION 5: FOOTER (PERHATIAN | DISETUJUI OLEH | DIBUAT | PENERIMA)
        # Tinggi Header = 6.5mm, Box TTD = 28.0mm -> Total = 34.5mm
        # =========================================================================
        $hFootHead = 6.5
        $hFootSign = 28.0
        $hFootTotal = $hFootHead + $hFootSign
        $wF1 = [float]($w * 0.35)
        $wF2 = [float]($w * 0.22)
        $wF3 = [float]($w * 0.21)
        $wF4 = [float]($w - $wF1 - $wF2 - $wF3)

        # Garis vertikal Footer
        $g.DrawLine($penBorder, $x0 + $wF1, $curY, $x0 + $wF1, $curY + $hFootTotal)
        $g.DrawLine($penBorder, $x0 + $wF1 + $wF2, $curY, $x0 + $wF1 + $wF2, $curY + $hFootTotal)
        $g.DrawLine($penBorder, $x0 + $wF1 + $wF2 + $wF3, $curY, $x0 + $wF1 + $wF2 + $wF3, $curY + $hFootTotal)

        # Garis horizontal di bawah judul Disetujui Oleh | Dibuat | Penerima
        $g.DrawLine($penBorder, $x0 + $wF1, $curY + $hFootHead, $x0 + $w, $curY + $hFootHead)

        # Teks Perhatian
        $g.DrawString("Perhatian :", $fontPerhBold, $brushBlack, ([System.Drawing.PointF]::new($x0 + 2.2, $curY + 1.8)))
        $perhLines = @(
            "Bila Terjadi Retur/Faktur Batal, Sehingga",
            "Target Tidak Tercapai, Maka Barang Promosi",
            "Yang Telah Diterima Harus Dikembalikan",
            "Sesuai Dengan Jumlah Retur/Faktur Batal"
        )
        for ($pl = 0; $pl -lt $perhLines.Count; $pl++) {
            $g.DrawString($perhLines[$pl], $fontPerhatian, $brushBlack, ([System.Drawing.PointF]::new($x0 + 2.2, $curY + 6.2 + ($pl * 4.2))))
        }

        # Judul Tanda Tangan
        $g.DrawString("Disetujui Oleh", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wF1, $curY, $wF2, $hFootHead)), $sfCenter)
        $g.DrawString("Dibuat", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wF1 + $wF2, $curY, $wF3, $hFootHead)), $sfCenter)
        $g.DrawString("Penerima", $fontHeader, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wF1 + $wF2 + $wF3, $curY, $wF4, $hFootHead)), $sfCenter)

        # Nama Penandatangan di bagian bawah kotak (TANPA garis di atas nama)
        $signNameY = $curY + $hFootTotal - 6.5
        if ($ttbp.disetujui_oleh) {
            $g.DrawString([string]$ttbp.disetujui_oleh, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wF1 + 1, $signNameY, $wF2 - 2, 5.5)), $sfCenter)
        }
        $dibuatStr = if ($ttbp.dibuat_oleh) { [string]$ttbp.dibuat_oleh } else { "Nonik Dia" }
        $g.DrawString($dibuatStr, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wF1 + $wF2 + 1, $signNameY, $wF3 - 2, 5.5)), $sfCenter)
        if ($ttbp.penerima) {
            $g.DrawString([string]$ttbp.penerima, $fontRegular, $brushBlack, ([System.Drawing.RectangleF]::new($x0 + $wF1 + $wF2 + $wF3 + 1, $signNameY, $wF4 - 2, 5.5)), $sfCenter)
        }

        $curY += $hFootTotal

        # =========================================================================
        # BINGKAI LUAR KOTAK TTBP (4 SISI TEGAS)
        # =========================================================================
        $totalBoxHeight = $curY - $y0
        $g.DrawRectangle($penThick, $x0, $y0, $w, $totalBoxHeight)
    }

    # Mode 1: Jika diminta merender Preview PNG (untuk verifikasi visual)
    if ($previewPng -ne "") {
        $dpiX = 120
        $dpiY = 144
        $bmpW = [int][Math]::Round(8.5 * $dpiX)
        $bmpH = [int][Math]::Round(11.0 * $dpiY)
        $bmp = New-Object System.Drawing.Bitmap($bmpW, $bmpH)
        $bmp.SetResolution($dpiX, $dpiY)
        $g = [System.Drawing.Graphics]::FromImage($bmp)
        $g.Clear([System.Drawing.Color]::White)
        $g.PageUnit = [System.Drawing.GraphicsUnit]::Millimeter
        $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::None
        $g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::SingleBitPerPixelGridFit

        $page0 = @($pages[0])
        if ($page0.Count -ge 1 -and $null -ne $page0[0]) {
            & $drawTtbpSlot $g $page0[0] 3.5 4.5 202.0 0.0 0.0
        }
        # Garis perforasi tengah kertas 5.5 inch (139.7 mm)
        $penDash = New-Object System.Drawing.Pen([System.Drawing.Color]::Gray, 0.25)
        $penDash.DashStyle = [System.Drawing.Drawing2D.DashStyle]::Dash
        $g.DrawLine($penDash, 0, 139.7, 215.9, 139.7)

        if ($page0.Count -ge 2 -and $null -ne $page0[1]) {
            & $drawTtbpSlot $g $page0[1] 3.5 143.4 202.0 0.0 0.0
        }

        $g.Dispose()
        $bmp.Save($previewPng, [System.Drawing.Imaging.ImageFormat]::Png)
        $bmp.Dispose()

        Write-Output (ConvertTo-Json @{ status = $true; message = "Preview berhasil dirender"; preview = $previewPng } -Compress)
        exit 0
    }

    # Mode 2: Cetak Fisik Langsung ke Printer Epson LX-310 ESC/P via GDI+ PrintDocument
    $doc = New-Object System.Drawing.Printing.PrintDocument
    $doc.DocumentName = "TTBP Continuous Form (CF K4 W PRS)"
    $doc.PrinterSettings.PrinterName = $printerName

    if (-not $doc.PrinterSettings.IsValid) {
        Write-Output (ConvertTo-Json @{ status = $false; message = "Printer '$printerName' tidak valid atau tidak terhubung." } -Compress)
        exit 0
    }

    # Pilih ukuran kertas Letter Fanfold 8 1/2 x 11 in atau Letter (850 x 1100)
    foreach ($ps in $doc.PrinterSettings.PaperSizes) {
        if ($ps.PaperName -match "Letter Fanfold" -or $ps.PaperName -eq "Letter") {
            $doc.DefaultPageSettings.PaperSize = $ps
            if ($ps.PaperName -match "Letter Fanfold") { break }
        }
    }

    # Pilih resolusi 120 x 144 DPI (atau 240 x 144 DPI) agar garis & teks padat
    foreach ($res in $doc.PrinterSettings.PrinterResolutions) {
        if ($res.X -eq 120 -and $res.Y -eq 144) {
            $doc.DefaultPageSettings.PrinterResolution = $res
            break
        }
    }

    # Kunci Orientasi Portrait (1 Kertas 9.5x11 inch = 2 TTBP Atas & Bawah)
    $doc.DefaultPageSettings.Landscape = $false
    $doc.DefaultPageSettings.Margins = New-Object System.Drawing.Printing.Margins(0, 0, 0, 0)

    $script:pageIndex = 0

    $doc.add_PrintPage({
        param($sender, [System.Drawing.Printing.PrintPageEventArgs]$e)

        $g = $e.Graphics
        $g.PageUnit = [System.Drawing.GraphicsUnit]::Millimeter
        $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::None
        $g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::SingleBitPerPixelGridFit

        # Kompensasi Hard Margin (OriginX & OriginY dari PrintableArea printer dalam mm)
        $hardX = [float]($e.PageSettings.HardMarginX * 0.254)
        $hardY = [float]($e.PageSettings.HardMarginY * 0.254)

        $curPageItems = @($pages[$script:pageIndex])

        # Slot 1 (Separuh Atas: 0 - 139.7 mm)
        if ($curPageItems.Count -ge 1 -and $null -ne $curPageItems[0]) {
            & $drawTtbpSlot $g $curPageItems[0] 3.5 4.5 202.0 $hardX $hardY
        }

        # Slot 2 (Separuh Bawah: 139.7 - 279.4 mm)
        if ($curPageItems.Count -ge 2 -and $null -ne $curPageItems[1]) {
            & $drawTtbpSlot $g $curPageItems[1] 3.5 143.4 202.0 $hardX $hardY
        }

        $script:pageIndex++
        $e.HasMorePages = ($script:pageIndex -lt $pages.Count)
    })

    $doc.Print()
    $doc.Dispose()

    Write-Output (ConvertTo-Json @{
        status  = $true
        message = "Berhasil mengirim cetakan ke printer $printerName"
        printer = $printerName
        pages   = $pages.Count
    } -Compress)

} catch {
    Write-Output (ConvertTo-Json @{
        status  = $false
        message = $_.Exception.Message
    } -Compress)
}
POWERSHELL;
    }
}
