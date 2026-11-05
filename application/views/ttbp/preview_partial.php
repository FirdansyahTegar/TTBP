<?php
/**
 * Partial View: Lembar Fixed TTBP (Tanda Terima Barang Promosi)
 * Format Presisi Kertas Continuous Form CF K4 W PRS 4-ply (9.5 x 11 inch / Printable 8.5 x 11 inch).
 * - Orientasi Portrait: 1 Lembar (9.5 x 11 inch) memuat 2 TTBP (Slot Atas 5.5" & Slot Bawah 5.5").
 * - Menggunakan border-collapse: separate (border-spacing: 0) dengan garis 2px hitam pekat
 *   sehingga 100% garis vertikal & horizontal tercetak utuh pada printer dot-matrix Epson LX-310 ESC/P.
 * - Bagian dalam kolom item tanpa garis pemisah (persis format form resmi).
 */

$tgl_display = !empty($ttbp['tanggal']) ? date('d M Y', strtotime($ttbp['tanggal'])) : date('d M Y');
$items = !empty($ttbp['items']) ? $ttbp['items'] : [];
$is_uang = !empty($ttbp['is_uang']);
$min_rows = isset($fixed_rows) ? $fixed_rows : 6;
$total_rows = max($min_rows, count($items));
?>

<?php if (!defined('TTBP_PREVIEW_STYLE_LOADED')): define('TTBP_PREVIEW_STYLE_LOADED', true); ?>
<style>
/* ==========================================================================
   SISTEM TABEL ANTI-HILANG GARIS UNTUK EPSON LX-310 ESC/P (9-PIN DOT MATRIX)
   Menggunakan border-collapse: separate + border-spacing: 0 dengan garis 2px
   agar browser (Chrome/Brave/Edge/Firefox) tidak pernah menghapus garis sub-piksel.
   ========================================================================== */
.ttbp-sheet-container {
    display: flex;
    justify-content: center;
    background-color: #eaedf2;
    padding: 16px 10px;
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
    color: #000000 !important;
    border-radius: 8px;
    width: 100%;
    overflow-x: auto;
    box-sizing: border-box !important;
}

.ttbp-sheet {
    width: 202.4mm;
    max-width: 100%;
    height: auto !important;
    background: #ffffff !important;
    border: 2px solid #000000 !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
    box-sizing: border-box !important;
    margin: 0 auto;
    color: #000000 !important;
}

.ttbp-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin: 0 !important;
    table-layout: fixed !important;
    background: transparent !important;
    border: none !important;
}

/* Setiap sel hanya menggambar dinding kanan (border-right) dan lantai bawah (border-bottom)
   sehingga tidak pernah terjadi tabrakan border atau hilang garis akibat rowspan */
.ttbp-table td, 
.ttbp-table th {
    border-top: none !important;
    border-left: none !important;
    border-right: 2px solid #000000 !important;
    border-bottom: 2px solid #000000 !important;
    padding: 3px 6px;
    font-size: 13px;
    color: #000000 !important;
    background-color: transparent;
    box-sizing: border-box !important;
}

/* Kolom paling kanan di setiap baris tidak perlu border-right karena sudah ditutup oleh dinding kanan .ttbp-sheet */
.ttbp-table tr > td:last-child,
.ttbp-table tr > th:last-child {
    border-right: none !important;
}

/* Garis Ganda (Double Line) di bawah Header: 2px garis + 2px celah putih + 2px garis atas Kios */
.ttbp-kios-table {
    margin-top: 2px !important;
}
.ttbp-kios-table tr:first-child > td {
    border-top: 2px solid #000000 !important;
}

/* Bagian Periode TTBP agar ringkas, pas dan tidak keluar kotak */
.ttbp-periode-val {
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
    font-size: 10.5px !important;
    font-weight: bold !important;
    letter-spacing: -0.2px !important;
    white-space: nowrap !important;
    text-align: center !important;
    padding: 2px 4px !important;
    overflow: hidden !important;
}

/* Bagian dalam item table TANPA GARIS pemisah internal */
.ttbp-items-table tbody tr > td {
    border-top: none !important;
    border-left: none !important;
    border-right: none !important;
    border-bottom: none !important;
    padding: 2px 6px !important;
    font-size: 13px !important;
    background-color: transparent !important;
}

/* Garis penutup bawah ruang item sebelum masuk ke Footer Perhatian/TTD */
.ttbp-items-table tbody tr:last-child > td {
    border-bottom: 2px solid #000000 !important;
}

/* Baris paling bawah Footer tidak perlu border-bottom karena langsung ditutup oleh lantai .ttbp-sheet */
.ttbp-footer-table tr:last-child > td,
.ttbp-footer-table td.ttbp-perhatian-cell {
    border-bottom: none !important;
}

.ttbp-th-gray {
    background-color: #e2e5e9;
}

.ttbp-main-title {
    font-size: 18px !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    color: #000000 !important;
}

.ttbp-nomor-val {
    font-size: 16px !important;
    font-weight: bold !important;
    color: #000000 !important;
}

@media print {
    .ttbp-sheet-container {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
        border-radius: 0 !important;
        overflow: visible !important;
        display: block !important;
    }
    .ttbp-sheet {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: 2px solid #000000 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    /* Matikan latar abu-abu saat print di Epson LX-310 agar jarum tidak mencetak bintik dithering */
    .ttbp-th-gray {
        background-color: transparent !important;
    }
}
</style>
<?php endif; ?>

<div class="ttbp-sheet-container">
    <div class="ttbp-sheet">

        <!-- 1. HEADER SECTION DENGAN GARIS GANDA DI BAWAH (Tinggi ~15.5mm) -->
        <table class="ttbp-table ttbp-header-table">
            <colgroup>
                <col style="width: 22%;">
                <col style="width: 56%;">
                <col style="width: 22%;">
            </colgroup>
            <tr>
                <td class="ttbp-th-gray text-center" style="height: 6.5mm; vertical-align: middle; text-align: center; font-weight: bold; font-size: 13px;">
                    Tanggal
                </td>
                <td rowspan="2" class="text-center ttbp-main-title" style="vertical-align: middle; text-align: center; font-weight: 800; font-size: 19px; padding: 2px 6px;">
                    TANDA TERIMA BARANG PROMOSI
                </td>
                <td class="ttbp-th-gray text-center" style="height: 6.5mm; vertical-align: middle; text-align: center; font-weight: bold; font-size: 13px;">
                    Nomor TTBP
                </td>
            </tr>
            <tr>
                <td class="text-center" style="height: 8.5mm; vertical-align: middle; text-align: center; font-size: 13px; font-weight: 600;">
                    <?= htmlspecialchars($tgl_display); ?>
                </td>
                <td class="text-center ttbp-nomor-val" style="height: 8.5mm; vertical-align: middle; text-align: center; font-weight: bold; font-size: 16px;">
                    <?= htmlspecialchars($ttbp['nomor_ttbp']); ?>
                </td>
            </tr>
        </table>

        <!-- 2. KIOS, ALAMAT, SALESMAN SECTION (Tinggi ~22mm) -->
        <table class="ttbp-table ttbp-kios-table">
            <colgroup>
                <col style="width: 35%;">
                <col style="width: 48%;">
                <col style="width: 17%;">
            </colgroup>
            <tr>
                <td class="text-center" style="height: 6.5mm; text-align: center; font-weight: bold; font-size: 13px;">
                    Nama Kios
                </td>
                <td class="text-center" style="height: 6.5mm; text-align: center; font-weight: bold; font-size: 13px;">
                    Alamat
                </td>
                <td class="text-center" style="height: 6.5mm; text-align: center; font-weight: bold; font-size: 13px;">
                    Salesman
                </td>
            </tr>
            <tr>
                <td class="text-center" style="height: 15.5mm; padding: 4px 8px; vertical-align: middle; text-align: center; font-size: 13px;">
                    <?= htmlspecialchars($ttbp['nama_kios']); ?>
                </td>
                <td class="text-center" style="height: 15.5mm; padding: 4px 8px; vertical-align: middle; text-align: center; font-size: 13px; line-height: 1.3;">
                    <?= htmlspecialchars($ttbp['alamat']); ?>
                </td>
                <td class="text-center" style="height: 15.5mm; padding: 4px 8px; vertical-align: middle; text-align: center; font-size: 13px;">
                    <?= htmlspecialchars($ttbp['salesman']); ?>
                </td>
            </tr>
        </table>

        <!-- 3. REALISASI ATAS PROGRAM & PERIODE (Tinggi ~8mm) -->
        <table class="ttbp-table ttbp-program-table">
            <colgroup>
                <col style="width: 21%;">
                <col style="width: 45%;">
                <col style="width: 9%;">
                <col style="width: 25%;">
            </colgroup>
            <tr>
                <td class="ttbp-th-gray" style="padding-left: 8px; height: 8mm; vertical-align: middle; text-align: left; font-weight: bold; font-size: 13px;">
                    Realisasi Atas Program
                </td>
                <td style="padding-left: 10px; height: 8mm; vertical-align: middle; text-align: left; font-size: 13px; font-weight: 600;">
                    <?= htmlspecialchars($ttbp['realisasi_program']); ?>
                </td>
                <td class="ttbp-th-gray text-center" style="height: 8mm; vertical-align: middle; text-align: center; font-weight: bold; font-size: 12px;">
                    Periode
                </td>
                <td class="text-center ttbp-periode-val" style="height: 8mm; vertical-align: middle;">
                    <?= htmlspecialchars($ttbp['periode']); ?>
                </td>
            </tr>
        </table>

        <!-- 4. ITEMS TABLE — BAGIAN DALAM TANPA GARIS (Tinggi ~50mm) -->
        <table class="ttbp-table ttbp-items-table">
            <colgroup>
                <col style="width: 5%;">
                <col style="width: 43%;">
                <col style="width: 21%;">
                <col style="width: 7%;">
                <col style="width: 24%;">
            </colgroup>
            <thead>
                <tr>
                    <th style="text-align: center; font-weight: bold; font-size: 13px; height: 7mm; padding: 2px 5px;">No</th>
                    <th style="text-align: center; font-weight: bold; font-size: 13px; height: 7mm; padding: 2px 5px;">Nama Barang</th>
                    <th style="text-align: center; font-weight: bold; font-size: 13px; height: 7mm; padding: 2px 5px;">Jumlah</th>
                    <th style="text-align: center; font-weight: bold; font-size: 13px; height: 7mm; padding: 2px 5px;">Sat</th>
                    <th style="text-align: center; font-weight: bold; font-size: 13px; height: 7mm; padding: 2px 5px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($is_uang): ?>
                    <!-- BARIS 1: UANG & NOMINAL -->
                    <tr>
                        <td style="text-align: center; height: 7mm; vertical-align: middle; font-size: 13px;">1</td>
                        <td style="text-align: left; padding-left: 12px; height: 7mm; vertical-align: middle; font-size: 13px;">Uang</td>
                        <td style="height: 7mm; vertical-align: middle; font-size: 13px; padding: 2px 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Rp</span>
                                <span><?= $items[0]['jumlah_fmt']; ?></span>
                            </div>
                        </td>
                        <td style="text-align: center; height: 7mm; vertical-align: middle; font-size: 13px; font-weight: bold;">
                            <?= htmlspecialchars($items[0]['satuan']); ?>
                        </td>
                        <td style="text-align: left; padding-left: 8px; height: 7mm; vertical-align: middle; font-size: 13px;">
                            <?= !empty($items[0]['keterangan']) ? htmlspecialchars($items[0]['keterangan']) : '&nbsp;'; ?>
                        </td>
                    </tr>
                    <!-- BARIS 2: PAJAK (PPH) & NOMINAL MINUS -->
                    <tr>
                        <td style="text-align: center; height: 7mm; vertical-align: middle; font-size: 13px;">2</td>
                        <td style="text-align: left; padding-left: 12px; height: 7mm; vertical-align: middle; font-size: 13px;">Pajak (PPh)</td>
                        <td style="height: 7mm; vertical-align: middle; font-size: 13px; padding: 2px 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Rp</span>
                                <span><?= $items[1]['jumlah_fmt']; ?></span>
                            </div>
                        </td>
                        <td style="height: 7mm; vertical-align: middle; font-size: 13px;">&nbsp;</td>
                        <td style="height: 7mm; vertical-align: middle; font-size: 13px;">&nbsp;</td>
                    </tr>
                    <!-- BARIS PENYEIMBANG TINGGI SETENGAH KERTAS (CF K4 W PRS) -->
                    <tr><td colspan="5" style="height: 7mm;">&nbsp;</td></tr>
                    <tr><td colspan="5" style="height: 7mm;">&nbsp;</td></tr>
                    <tr><td colspan="5" style="height: 7mm;">&nbsp;</td></tr>
                    <!-- BARIS FOOTER: TOTAL & KETERANGAN (TRANSFER / POTONG NOTA) -->
                    <tr>
                        <td style="height: 8mm;">&nbsp;</td>
                        <td style="text-align: right; font-weight: bold; font-size: 13px; padding-right: 24px; vertical-align: middle;">
                            Total
                        </td>
                        <td style="height: 8mm; vertical-align: middle; font-size: 13px; font-weight: bold; padding: 2px 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Rp</span>
                                <span><?= $ttbp['total_nominal_fmt']; ?></span>
                            </div>
                        </td>
                        <td colspan="2" style="text-align: left; font-weight: bold; font-size: 13px; padding-left: 10px; vertical-align: middle;">
                            <?= htmlspecialchars($ttbp['footer_keterangan']); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <!-- FORMAT BARANG FISIK BIASA (TANPA GARIS PEMISAH DI DALAM) -->
                    <?php $row_mm = round(43 / max(6, $total_rows), 2); ?>
                    <?php for ($i = 0; $i < $total_rows; $i++): ?>
                        <?php $item = isset($items[$i]) ? $items[$i] : null; ?>
                        <tr class="ttbp-item-row">
                            <td style="text-align: center; height: <?= $row_mm; ?>mm; vertical-align: middle; font-size: 13px; padding: 2px 5px;">
                                <?= $item ? htmlspecialchars($item['no_urut']) : '&nbsp;'; ?>
                            </td>
                            <td style="text-align: left; padding-left: 12px; height: <?= $row_mm; ?>mm; vertical-align: middle; font-size: 13px;">
                                <?= $item ? htmlspecialchars($item['nama_barang']) : '&nbsp;'; ?>
                            </td>
                            <td style="text-align: right; padding-right: 16px; height: <?= $row_mm; ?>mm; vertical-align: middle; font-size: 13px;">
                                <?= $item ? (isset($item['jumlah_fmt']) ? $item['jumlah_fmt'] : floatval($item['jumlah'])) : '&nbsp;'; ?>
                            </td>
                            <td style="text-align: center; height: <?= $row_mm; ?>mm; vertical-align: middle; font-size: 13px; padding: 2px 5px;">
                                <?= $item ? htmlspecialchars($item['satuan']) : '&nbsp;'; ?>
                            </td>
                            <td style="text-align: left; padding-left: 8px; height: <?= $row_mm; ?>mm; vertical-align: middle; font-size: 13px;">
                                <?= ($item && !empty($item['keterangan'])) ? htmlspecialchars($item['keterangan']) : '&nbsp;'; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 5. FOOTER SECTION — PERHATIAN & SIGNATURES TANPA GARIS DI ATAS NAMA (Tinggi ~34.5mm) -->
        <table class="ttbp-table ttbp-footer-table">
            <colgroup>
                <col style="width: 35%;">
                <col style="width: 22%;">
                <col style="width: 21%;">
                <col style="width: 22%;">
            </colgroup>
            <tr>
                <!-- Box Perhatian -->
                <td rowspan="2" class="ttbp-perhatian-cell" style="vertical-align: top; padding: 6px 8px; font-size: 11px; line-height: 1.4;">
                    <strong style="display: block; margin-bottom: 3px;">Perhatian :</strong>
                    Bila Terjadi Retur/Faktur Batal, Sehingga<br>
                    Target Tidak Tercapai, Maka Barang Promosi<br>
                    Yang Telah Diterima Harus Dikembalikan<br>
                    Sesuai Dengan Jumlah Retur/Faktur Batal
                </td>
                <!-- Disetujui Oleh -->
                <td style="text-align: center; font-weight: bold; height: 6.5mm; vertical-align: middle; font-size: 13px;">
                    Disetujui Oleh
                </td>
                <!-- Dibuat -->
                <td style="text-align: center; font-weight: bold; height: 6.5mm; vertical-align: middle; font-size: 13px;">
                    Dibuat
                </td>
                <!-- Penerima -->
                <td style="text-align: center; font-weight: bold; height: 6.5mm; vertical-align: middle; font-size: 13px;">
                    Penerima
                </td>
            </tr>
            <tr>
                <!-- Area Tanda Tangan & Nama (Tanpa garis pemisah di atas nama penandatangan) -->
                <td style="height: 28mm; vertical-align: bottom; text-align: center; padding: 0 4px 6px 4px; font-size: 13px;">
                    <div style="font-size: 13px; line-height: 1.2;">
                        <?= !empty($ttbp['disetujui_oleh']) ? htmlspecialchars($ttbp['disetujui_oleh']) : '&nbsp;'; ?>
                    </div>
                </td>
                <td style="height: 28mm; vertical-align: bottom; text-align: center; padding: 0 4px 6px 4px; font-weight: 600; font-size: 13px;">
                    <div style="font-size: 13px; font-weight: 600; line-height: 1.2;">
                        <?= !empty($ttbp['dibuat_oleh']) ? htmlspecialchars($ttbp['dibuat_oleh']) : 'Nonik Dia'; ?>
                    </div>
                </td>
                <td style="height: 28mm; vertical-align: bottom; text-align: center; padding: 0 4px 6px 4px; font-weight: 600; font-size: 13px;">
                    <div style="font-size: 13px; font-weight: 600; line-height: 1.2;">
                        <?= !empty($ttbp['penerima']) ? htmlspecialchars($ttbp['penerima']) : '&nbsp;'; ?>
                    </div>
                </td>
            </tr>
        </table>

    </div>
</div>
