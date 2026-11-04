<?php
$tgl_awal_val = $row['periode_prog_awal'] ?? '';
$tgl_akhir_val = $row['periode_prog_akhir'] ?? '';
if (empty($tgl_awal_val) && !empty($row['periode_prog_raw'])) {
    $parts = explode(' - ', $row['periode_prog_raw']);
    if (count($parts) >= 2) {
        $p1 = strtotime(trim($parts[0]));
        $p2 = strtotime(trim($parts[1]));
        if ($p1) $tgl_awal_val = date('Y-m-d', $p1);
        if ($p2) $tgl_akhir_val = date('Y-m-d', $p2);
    }
}

// Format initial thousand values
$fmt_num = function($val) {
    if ($val === null || $val === '' || floatval($val) == 0) return '';
    return number_format(floatval($val), 0, '.', ',');
};
?>
<div class="content-body">
    <!-- Top Action Bar (Tanpa Heading Besar) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted fw-semibold" style="font-size: 0.85rem;">
            <i class="bi <?= $is_edit ? 'bi-pencil-square text-warning' : 'bi-plus-circle text-primary'; ?> me-1"></i>
            <?= $is_edit ? 'Edit Data Bonus Promosi #' . htmlspecialchars($row['no_urut_lap']) : 'Input Data Bonus Promosi Baru'; ?>
        </span>
        <a href="<?= site_url('ttbp'); ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Alert Flash -->
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= site_url('ttbp/save'); ?>" id="form-bonus-promosi">
        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']); ?>">

        <!-- ========================================== -->
        <!-- BAGIAN 1: IDENTITAS UTAMA (No, Tanggal, TTBP) -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <div class="mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0">Identitas TTBP</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-muted mb-0" style="font-size: 0.78rem;">
                            No. <span class="text-danger">*</span>
                        </label>
                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Otomatis</span>
                    </div>
                    <input type="text" class="form-control form-control-sm text-center fw-bold" name="no_urut_lap" id="input_no_urut_lap"
                           value="<?= htmlspecialchars(!empty($row['no_urut_lap']) ? $row['no_urut_lap'] : $next_no); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">
                        Tanggal TTBP <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control form-control-sm" name="tanggal_ttbp" 
                           value="<?= htmlspecialchars($row['tanggal_ttbp']); ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">
                        Nomor TTBP <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm fw-bold font-monospace text-primary" name="nomor_ttbp" 
                           value="<?= htmlspecialchars($row['nomor_ttbp']); ?>" placeholder="Contoh: 1, 2, C260817..." required>
                    <div class="form-text text-muted" style="font-size: 0.7rem;">Nomor TTBP yang sama digabung ke 1 lembar fisik.</div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BAGIAN 2: STATUS POTONG & DATA PEMBELI    -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <div class="mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0">Pemotongan & Toko / Pembeli</h6>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Sdh dipotong (Tgl Potong)</label>
                    <input type="date" class="form-control form-control-sm" name="tgl_sdh_dipotong" 
                           value="<?= htmlspecialchars($row['tgl_sdh_dipotong']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Faktur Promo</label>
                    <input type="text" class="form-control form-control-sm" name="faktur_promo" 
                           value="<?= htmlspecialchars($row['faktur_promo']); ?>" placeholder="Contoh: B 516, D 19350...">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Kode Toko</label>
                    <input type="text" class="form-control form-control-sm font-monospace fw-bold" name="kode_kios" id="input_kode_kios"
                           value="<?= htmlspecialchars($row['kode_kios']); ?>" placeholder="BINT05, RIZK06..." autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">
                        Nama Pembeli / Kios <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm fw-semibold" name="nama_pembeli" id="input_nama_pembeli"
                           value="<?= htmlspecialchars($row['nama_pembeli']); ?>" placeholder="Nama Toko atau Pembeli..." required>
                </div>
            </div>

            <!-- Auto-Fill Preview dari Master Toko -->
            <div class="p-3 bg-light rounded-3 border" id="box_info_master">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i> Data Master Toko (Untuk Lembar Cetak TTBP)
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary" id="badge_status_master" style="font-size: 0.68rem;">
                        Ketik Kode Toko untuk auto-fill
                    </span>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-0" style="font-size: 0.72rem;">Alamat Lengkap</label>
                        <input type="text" class="form-control form-control-sm bg-white" name="alamat_manual" id="input_alamat_master"
                               value="<?= htmlspecialchars($row['alamat_manual']); ?>" placeholder="Alamat toko dari master atau ketik manual...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted mb-0" style="font-size: 0.72rem;">Salesman (SC)</label>
                        <input type="text" class="form-control form-control-sm bg-white" name="salesman_manual" id="input_salesman_master"
                               value="<?= htmlspecialchars($row['salesman_manual']); ?>" placeholder="Nama Salesman...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted mb-0" style="font-size: 0.72rem;">Penerima (Contact Person)</label>
                        <input type="text" class="form-control form-control-sm bg-white" name="penerima_manual" id="input_penerima_master"
                               value="<?= htmlspecialchars($row['penerima_manual']); ?>" placeholder="Nama Penerima / CP...">
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BAGIAN 3: PROGRAM & BARANG BONUS           -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <div class="mb-3 pb-2 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">Program & Barang Bonus</h6>
                <span class="badge bg-primary-subtle text-primary" id="badge_program_status" style="display:none; font-size: 0.68rem;">
                    <i class="bi bi-check2 me-1"></i>Program Master Terpilih
                </span>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Value Produk Program (Rp)</label>
                    <input type="text" class="form-control form-control-sm text-end format-ribuan" name="value_produk_program" id="input_value_produk"
                           value="<?= $fmt_num($row['value_produk_program']); ?>" placeholder="0" autocomplete="off">
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">
                        Program <span class="text-danger">*</span>
                    </label>
                    <input type="text" list="programs_master_list" class="form-control form-control-sm fw-medium" name="program" id="input_program"
                           value="<?= htmlspecialchars($row['program']); ?>" placeholder="Pilih atau ketik program promosi..." required autocomplete="off">
                    <datalist id="programs_master_list">
                        <?php if (!empty($programs_master)): ?>
                            <?php foreach ($programs_master as $prg): ?>
                                <option value="<?= htmlspecialchars($prg['nama']); ?>"></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Volume Prog</label>
                    <input type="number" step="any" class="form-control form-control-sm text-end" name="volume_prog" id="input_volume_prog"
                           value="<?= htmlspecialchars($row['volume_prog']); ?>" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Satuan Vol</label>
                    <input type="text" list="satuan_vol_options" class="form-control form-control-sm text-center" name="satuan_vol" id="input_satuan_vol"
                           value="<?= htmlspecialchars($row['satuan_vol']); ?>" placeholder="Ltr, Box, Kg...">
                    <datalist id="satuan_vol_options">
                        <option value="Ltr">
                        <option value="Box">
                        <option value="Kg">
                        <option value="Ton">
                    </datalist>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">
                        Nama Barang Promosi / Bonus <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm fw-semibold" name="nama_barang" 
                           value="<?= htmlspecialchars($row['nama_barang']); ?>" placeholder="Sprayer Elektrik Joeragan 16 L / Uang" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Qty</label>
                    <input type="number" step="any" class="form-control form-control-sm text-end" name="qty" 
                           value="<?= htmlspecialchars($row['qty']); ?>" placeholder="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Satuan</label>
                    <input type="text" list="satuan_options" class="form-control form-control-sm text-center" name="satuan" 
                           value="<?= htmlspecialchars($row['satuan']); ?>" placeholder="unit, Pcs...">
                    <datalist id="satuan_options">
                        <option value="unit">
                        <option value="Unit">
                        <option value="Box">
                        <option value="Pcs">
                        <option value="Dus">
                        <option value="Paket">
                    </datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Total Bonus (Rp)</label>
                    <input type="text" class="form-control form-control-sm text-end text-success fw-bold format-ribuan" name="total_bonus" id="input_total_bonus"
                           value="<?= $fmt_num($row['total_bonus']); ?>" placeholder="0" autocomplete="off">
                </div>
            </div>

            <!-- Potong Nota & Pajak -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">potong nota (Rp)</label>
                    <input type="text" class="form-control form-control-sm text-end format-ribuan" name="potong_nota" id="input_potong_nota"
                           value="<?= $fmt_num($row['potong_nota']); ?>" placeholder="0" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Pajak (Rp)</label>
                    <input type="text" class="form-control form-control-sm text-end format-ribuan" name="pajak" id="input_pajak"
                           value="<?= $fmt_num($row['pajak']); ?>" placeholder="0" autocomplete="off">
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BAGIAN 4: PERIODE, FAKTUR, & TRACKING      -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <div class="mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0">Periode Program, Faktur, & Tracking Fisik</h6>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Periode Prog Awal</label>
                    <input type="date" class="form-control form-control-sm" name="periode_prog_awal" id="input_periode_awal"
                           value="<?= htmlspecialchars($tgl_awal_val); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Periode Prog Akhir</label>
                    <input type="date" class="form-control form-control-sm" name="periode_prog_akhir" id="input_periode_akhir"
                           value="<?= htmlspecialchars($tgl_akhir_val); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Faktur</label>
                    <input type="text" class="form-control form-control-sm" name="faktur" 
                           value="<?= htmlspecialchars($row['faktur']); ?>" placeholder="Contoh: 8833.11824">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">at (Wilayah / Kode)</label>
                    <input type="text" class="form-control form-control-sm text-center" name="kode_wilayah" 
                           value="<?= htmlspecialchars($row['kode_wilayah']); ?>" placeholder="B, B/D, T, D, Z">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">tanggal kembali TTBP</label>
                    <input type="date" class="form-control form-control-sm" name="tgl_kembali_ttbp" 
                           value="<?= htmlspecialchars($row['tgl_kembali_ttbp']); ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Keterangan</label>
                    <textarea class="form-control form-control-sm" name="keterangan" rows="2" placeholder="Catatan tambahan..."><?= htmlspecialchars($row['keterangan']); ?></textarea>
                </div>
            </div>
            <input type="hidden" name="periode_prog_raw" id="input_periode_raw" value="<?= htmlspecialchars($row['periode_prog_raw'] ?? ''); ?>">
        </div>

        <!-- ========================================== -->
        <!-- BAGIAN 5: OTORISASI LEMBAR TTBP            -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <div class="mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0">Tanda Tangan Dokumen Fisik</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Dibuat Oleh</label>
                    <input type="text" class="form-control form-control-sm" name="dibuat_oleh" 
                           value="<?= htmlspecialchars($row['dibuat_oleh']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1" style="font-size: 0.78rem;">Disetujui Oleh</label>
                    <input type="text" class="form-control form-control-sm" name="disetujui_oleh" 
                           value="<?= htmlspecialchars($row['disetujui_oleh']); ?>" placeholder="Opsional (Manager / Approver)...">
                </div>
            </div>
            <input type="hidden" name="perhatian" value="<?= htmlspecialchars($row['perhatian']); ?>">
        </div>

        <!-- TOMBOL AKSI SIMPAN -->
        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 pt-2 pb-5">
            <a href="<?= site_url('ttbp'); ?>" class="btn btn-outline-secondary px-4 btn-sm">
                Batal
            </a>
            <button type="submit" name="action_btn" value="save_and_create" class="btn btn-outline-primary px-3 btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Simpan & Buat Baru
            </button>
            <button type="submit" name="action_btn" value="save_and_print" class="btn btn-outline-success px-3 btn-sm">
                <i class="bi bi-printer me-1"></i> Simpan & Cetak TTBP
            </button>
            <button type="submit" name="action_btn" value="save_only" class="btn btn-primary px-4 btn-sm fw-medium shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Simpan Data
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Master data program dari PHP
    var programsMaster = <?= json_encode($programs_master ?? []); ?>;

    // Helper: format ribuan
    function formatThousands(val) {
        if (!val) return '';
        var clean = val.toString().replace(/,/g, '').replace(/[^0-9.]/g, '');
        var parts = clean.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.length > 1 ? (parts[0] + '.' + parts[1]) : parts[0];
    }

    function parseRawNumber(val) {
        if (!val) return 0;
        return parseFloat(val.toString().replace(/,/g, '')) || 0;
    }

    // Event listener live format ribuan
    $(document).on('input', '.format-ribuan', function() {
        var rawVal = $(this).val();
        var cursorPos = this.selectionStart;
        var prevLength = rawVal.length;

        var formatted = formatThousands(rawVal);
        $(this).val(formatted);

        var newLength = formatted.length;
        var newCursor = cursorPos + (newLength - prevLength);
        if (newCursor >= 0 && this.setSelectionRange) {
            this.setSelectionRange(newCursor, newCursor);
        }
    });

    // 1. Autocomplete & Auto-Fill Kode Toko (Selalu ganti nama pembeli ketika kode berubah)
    var lookupTimer;
    $('#input_kode_kios').on('input', function() {
        clearTimeout(lookupTimer);
        var kode = $(this).val().trim();
        if (kode.length < 2) return;

        lookupTimer = setTimeout(function() {
            $.get('<?= site_url("ttbp/api_search_toko"); ?>', { q: kode }, function(res) {
                if (res && res.length > 0) {
                    var match = res.find(function(it) {
                        return it.kode_toko.toUpperCase() === kode.toUpperCase();
                    }) || res[0];

                    if (match) {
                        // REVISI: Selalu timpa nama pembeli dengan nama toko master
                        $('#input_nama_pembeli').val(match.nama_toko);

                        var alamatLengkap = match.alamat + (match.kota ? (', ' + match.kota) : '');
                        $('#input_alamat_master').val(alamatLengkap);
                        $('#input_salesman_master').val(match.sc || '');
                        $('#input_penerima_master').val(match.kontak_person || match.nama_toko);
                        $('#badge_status_master').attr('class', 'badge bg-success-subtle text-success')
                                                 .html('<i class="bi bi-check-circle me-1"></i>Ditemukan: ' + match.kode_toko);
                    }
                } else {
                    $('#badge_status_master').attr('class', 'badge bg-warning-subtle text-warning')
                                             .html('<i class="bi bi-exclamation-triangle me-1"></i>Toko Non-Master (Isi Alamat Manual)');
                }
            }, 'json');
        }, 300);
    });

    // 2. Auto-Fill Program dari Kontrak st_programs
    function handleProgramChange() {
        var prgName = $('#input_program').val().trim().toLowerCase();
        if (!prgName) {
            $('#badge_program_status').hide();
            return;
        }

        var found = programsMaster.find(function(p) {
            return (p.nama || '').toLowerCase() === prgName;
        });

        if (found) {
            $('#badge_program_status').show();
            if (found.target_qty && parseFloat(found.target_qty) > 0) {
                $('#input_volume_prog').val(parseFloat(found.target_qty));
            }
            if (found.satuan) {
                $('#input_satuan_vol').val(found.satuan);
            }
            if (found.start_date && !$('#input_periode_awal').val()) {
                $('#input_periode_awal').val(found.start_date);
            }
            if (found.end_date && !$('#input_periode_akhir').val()) {
                $('#input_periode_akhir').val(found.end_date);
            }
        } else {
            $('#badge_program_status').hide();
        }
    }

    $('#input_program').on('input change', handleProgramChange);

    // 3. Auto hitung Total Bonus jika Potong Nota / Pajak diisi
    $('#input_potong_nota, #input_pajak').on('input', function() {
        var potong = parseRawNumber($('#input_potong_nota').val());
        var pajak = parseRawNumber($('#input_pajak').val());
        if (potong > 0 || pajak > 0) {
            var total = potong + pajak;
            $('#input_total_bonus').val(formatThousands(total));
        }
    });

    // 4. Sebelum form submit, pastikan format ribuan aman dikirim
    $('#form-bonus-promosi').on('submit', function() {
        $('.format-ribuan').each(function() {
            var raw = $(this).val().replace(/,/g, '');
            $(this).val(raw);
        });
    });
});
</script>
