<?php
/**
 * Partial View: Lembar Fixed TTBP (Tanda Terima Barang Promosi)
 * Format fixed lembar cetak kertas Continuous Form CF K4 W PRS 4 ply
 * Dilengkapi border dan outline hitam tegas agar tertata rapi.
 */

$tgl_display = !empty($ttbp['tanggal']) ? date('d M Y', strtotime($ttbp['tanggal'])) : date('d M Y');
$items = !empty($ttbp['items']) ? $ttbp['items'] : [];
$min_rows = isset($fixed_rows) ? $fixed_rows : 6;
$total_rows = max($min_rows, count($items));
?>

<style>
.ttbp-sheet-container {
    display: flex;
    justify-content: center;
    background-color: #eaedf2 !important;
    padding: 24px 16px !important;
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
    color: #000000 !important;
    border-radius: 8px;
    width: 100%;
    overflow-x: auto;
}
.ttbp-sheet {
    width: 100%;
    max-width: 820px;
    background: #ffffff !important;
    border: 2px solid #000000 !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.16) !important;
    box-sizing: border-box !important;
    margin: 0 auto;
}
.ttbp-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 0 !important;
    table-layout: fixed !important;
    background: #ffffff !important;
    border-spacing: 0 !important;
}
.ttbp-table td, 
.ttbp-table th {
    border: 1px solid #000000 !important;
    padding: 4px 6px !important;
    font-size: 13px !important;
    color: #000000 !important;
    background-color: #ffffff;
    box-sizing: border-box !important;
}
.ttbp-th-gray {
    background-color: #d9d9d9 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
.ttbp-main-title {
    font-size: 19px !important;
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
    body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
    .no-print { display: none !important; }
    .ttbp-sheet-container { padding: 0 !important; background: transparent !important; margin: 0 !important; }
    .ttbp-sheet { box-shadow: none !important; border: 2px solid #000 !important; width: 100% !important; max-width: 100% !important; page-break-inside: avoid; }
    .ttbp-th-gray { background-color: #d9d9d9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<div class="ttbp-sheet-container" id="ttbp-print-sheet" style="display:flex; justify-content:center; background:#eaedf2; padding:24px 16px; border-radius:8px;">
    <div class="ttbp-sheet" style="width:100%; max-width:820px; background:#fff; border:2px solid #000; box-shadow:0 4px 18px rgba(0,0,0,0.16); box-sizing:border-box;">
        
        <!-- HEADER SECTION -->
        <table class="ttbp-table ttbp-header-table" style="width:100%; border-collapse:collapse; margin:0; border:1px solid #000; table-layout:fixed; background:#fff;">
            <colgroup>
                <col style="width: 22%;">
                <col style="width: 56%;">
                <col style="width: 22%;">
            </colgroup>
            <tr>
                <td class="ttbp-th-gray text-center" style="border:1px solid #000; background-color:#d9d9d9; height:24px; vertical-align:middle; text-align:center; font-weight:bold; font-size:13px; color:#000;">
                    Tanggal
                </td>
                <td rowspan="2" class="text-center font-bold ttbp-main-title" style="border:1px solid #000; vertical-align:middle; text-align:center; font-weight:800; font-size:19px; padding:4px 6px; color:#000; letter-spacing:0.5px;">
                    TANDA TERIMA BARANG PROMOSI
                </td>
                <td class="ttbp-th-gray text-center" style="border:1px solid #000; background-color:#d9d9d9; height:24px; vertical-align:middle; text-align:center; font-weight:bold; font-size:13px; color:#000;">
                    Nomor TTBP
                </td>
            </tr>
            <tr>
                <td class="text-center" style="border:1px solid #000; height:30px; vertical-align:middle; text-align:center; font-size:13px; font-weight:600; color:#000;">
                    <?= htmlspecialchars($tgl_display); ?>
                </td>
                <td class="text-center font-bold ttbp-nomor-val" style="border:1px solid #000; height:30px; vertical-align:middle; text-align:center; font-weight:bold; font-size:16px; color:#000;">
                    <?= htmlspecialchars($ttbp['nomor_ttbp']); ?>
                </td>
            </tr>
        </table>

        <!-- KIOS, ALAMAT, SALESMAN SECTION -->
        <table class="ttbp-table ttbp-kios-table" style="width:100%; border-collapse:collapse; margin-top:-1px; border:1px solid #000; table-layout:fixed; background:#fff;">
            <colgroup>
                <col style="width: 35%;">
                <col style="width: 48%;">
                <col style="width: 17%;">
            </colgroup>
            <tr>
                <td class="text-center" style="border:1px solid #000; height:24px; text-align:center; font-weight:bold; font-size:13px; color:#000; background:#fff;">
                    Nama Kios
                </td>
                <td class="text-center" style="border:1px solid #000; height:24px; text-align:center; font-weight:bold; font-size:13px; color:#000; background:#fff;">
                    Alamat
                </td>
                <td class="text-center" style="border:1px solid #000; height:24px; text-align:center; font-weight:bold; font-size:13px; color:#000; background:#fff;">
                    Salesman
                </td>
            </tr>
            <tr>
                <td class="text-center" style="border:1px solid #000; min-height:48px; padding:6px 8px; vertical-align:middle; text-align:center; font-size:13px; color:#000;">
                    <?= htmlspecialchars($ttbp['nama_kios']); ?>
                </td>
                <td class="text-center" style="border:1px solid #000; min-height:48px; padding:6px 8px; vertical-align:middle; text-align:center; font-size:13px; line-height:1.3; color:#000;">
                    <?= htmlspecialchars($ttbp['alamat']); ?>
                </td>
                <td class="text-center" style="border:1px solid #000; min-height:48px; padding:6px 8px; vertical-align:middle; text-align:center; font-size:13px; color:#000;">
                    <?= htmlspecialchars($ttbp['salesman']); ?>
                </td>
            </tr>
        </table>

        <!-- REALISASI ATAS PROGRAM & PERIODE -->
        <table class="ttbp-table ttbp-program-table" style="width:100%; border-collapse:collapse; margin-top:-1px; border:1px solid #000; table-layout:fixed; background:#fff;">
            <colgroup>
                <col style="width: 22%;">
                <col style="width: 50%;">
                <col style="width: 12%;">
                <col style="width: 16%;">
            </colgroup>
            <tr>
                <td class="ttbp-th-gray" style="border:1px solid #000; background-color:#d9d9d9; padding-left:8px; height:26px; vertical-align:middle; text-align:left; font-weight:bold; font-size:13px; color:#000;">
                    Realisasi Atas Program
                </td>
                <td style="border:1px solid #000; padding-left:10px; height:26px; vertical-align:middle; text-align:left; font-size:13px; font-weight:500; color:#000;">
                    <?= htmlspecialchars($ttbp['realisasi_program']); ?>
                </td>
                <td class="ttbp-th-gray text-center" style="border:1px solid #000; background-color:#d9d9d9; height:26px; vertical-align:middle; text-align:center; font-weight:bold; font-size:13px; color:#000;">
                    Periode
                </td>
                <td class="text-center" style="border:1px solid #000; height:26px; vertical-align:middle; text-align:center; font-size:13px; font-weight:500; color:#000;">
                    <?= htmlspecialchars($ttbp['periode']); ?>
                </td>
            </tr>
        </table>

        <!-- ITEMS TABLE -->
        <table class="ttbp-table ttbp-items-table" style="width:100%; border-collapse:collapse; margin-top:-1px; border:1px solid #000; table-layout:fixed; background:#fff;">
            <colgroup>
                <col style="width: 6%;">
                <col style="width: 48%;">
                <col style="width: 12%;">
                <col style="width: 8%;">
                <col style="width: 26%;">
            </colgroup>
            <thead>
                <tr>
                    <th style="border:1px solid #000; text-align:center; font-weight:bold; font-size:13px; height:24px; padding:3px 5px; color:#000; background:#fff;">No</th>
                    <th style="border:1px solid #000; text-align:center; font-weight:bold; font-size:13px; height:24px; padding:3px 5px; color:#000; background:#fff;">Nama Barang</th>
                    <th style="border:1px solid #000; text-align:center; font-weight:bold; font-size:13px; height:24px; padding:3px 5px; color:#000; background:#fff;">Jumlah</th>
                    <th style="border:1px solid #000; text-align:center; font-weight:bold; font-size:13px; height:24px; padding:3px 5px; color:#000; background:#fff;">Sat</th>
                    <th style="border:1px solid #000; text-align:center; font-weight:bold; font-size:13px; height:24px; padding:3px 5px; color:#000; background:#fff;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $total_rows; $i++): ?>
                    <?php $item = isset($items[$i]) ? $items[$i] : null; ?>
                    <tr class="ttbp-item-row">
                        <td style="border:1px solid #000; text-align:center; height:24px; vertical-align:middle; font-size:13px; padding:3px 5px; color:#000;">
                            <?= $item ? htmlspecialchars($item['no_urut']) : '&nbsp;'; ?>
                        </td>
                        <td style="border:1px solid #000; text-align:left; padding-left:10px; height:24px; vertical-align:middle; font-size:13px; color:#000;">
                            <?= $item ? htmlspecialchars($item['nama_barang']) : '&nbsp;'; ?>
                        </td>
                        <td style="border:1px solid #000; text-align:right; padding-right:14px; height:24px; vertical-align:middle; font-size:13px; color:#000;">
                            <?= $item ? floatval($item['jumlah']) : '&nbsp;'; ?>
                        </td>
                        <td style="border:1px solid #000; text-align:center; height:24px; vertical-align:middle; font-size:13px; padding:3px 5px; color:#000;">
                            <?= $item ? htmlspecialchars($item['satuan']) : '&nbsp;'; ?>
                        </td>
                        <td style="border:1px solid #000; text-align:left; padding-left:8px; height:24px; vertical-align:middle; font-size:13px; color:#000;">
                            <?= ($item && !empty($item['keterangan'])) ? htmlspecialchars($item['keterangan']) : '&nbsp;'; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- FOOTER SECTION (PERHATIAN & SIGNATURES) -->
        <table class="ttbp-table ttbp-footer-table" style="width:100%; border-collapse:collapse; margin-top:-1px; border:1px solid #000; table-layout:fixed; background:#fff;">
            <colgroup>
                <col style="width: 35%;">
                <col style="width: 22%;">
                <col style="width: 21%;">
                <col style="width: 22%;">
            </colgroup>
            <tr>
                <!-- Box Perhatian -->
                <td rowspan="3" style="border:1px solid #000; vertical-align:top; padding:6px 8px; font-size:11px; line-height:1.35; color:#000;">
                    <strong style="display:block; margin-bottom:2px;">Perhatian :</strong>
                    Bila Terjadi Retur/Faktur Batal, Sehingga<br>
                    Target Tidak Tercapai, Maka Barang Promosi<br>
                    Yang Telah Diterima Harus Dikembalikan<br>
                    Sesuai Dengan Jumlah Retur/Faktur Batal
                </td>
                <!-- Disetujui Oleh -->
                <td style="border:1px solid #000; text-align:center; font-weight:bold; height:24px; vertical-align:middle; font-size:13px; color:#000;">
                    Disetujui Oleh
                </td>
                <!-- Dibuat -->
                <td style="border:1px solid #000; text-align:center; font-weight:bold; height:24px; vertical-align:middle; font-size:13px; color:#000;">
                    Dibuat
                </td>
                <!-- Penerima -->
                <td style="border:1px solid #000; text-align:center; font-weight:bold; height:24px; vertical-align:middle; font-size:13px; color:#000;">
                    Penerima
                </td>
            </tr>
            <tr>
                <!-- Area Tanda Tangan Kosong -->
                <td style="border:1px solid #000; height:60px;"></td>
                <td style="border:1px solid #000; height:60px;"></td>
                <td style="border:1px solid #000; height:60px;"></td>
            </tr>
            <tr>
                <!-- Nama Penandatangan -->
                <td style="border:1px solid #000; text-align:center; height:26px; vertical-align:middle; font-size:13px; color:#000;">
                    <?= !empty($ttbp['disetujui_oleh']) ? htmlspecialchars($ttbp['disetujui_oleh']) : '&nbsp;'; ?>
                </td>
                <td style="border:1px solid #000; text-align:center; height:26px; vertical-align:middle; font-weight:500; font-size:13px; color:#000;">
                    <?= !empty($ttbp['dibuat_oleh']) ? htmlspecialchars($ttbp['dibuat_oleh']) : 'Nonik Dia'; ?>
                </td>
                <td style="border:1px solid #000; text-align:center; height:26px; vertical-align:middle; font-weight:500; font-size:13px; color:#000;">
                    <?= !empty($ttbp['penerima']) ? htmlspecialchars($ttbp['penerima']) : '&nbsp;'; ?>
                </td>
            </tr>
        </table>
    </div>
</div>
