    </main>

    <!-- MAIN FOOTER -->
    <footer class="main-footer d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-auto px-4 py-3 bg-white border-top text-muted small">
        <div>
            <strong>TTBP</strong> &copy; <?= date('Y'); ?> &mdash; Tanda Terima Barang Promosi.
        </div>
        <div>
            <span class="text-muted" style="font-size: 0.75rem;">CF K4 W PRS 4 Ply</span>
        </div>
    </footer>
</div> <!-- End #content -->
</div> <!-- End #wrapper -->

<!-- MODAL KONFIRMASI LOGOUT -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle-fill display-5"></i>
                </div>
                <h5 class="fw-bold mb-2">Konfirmasi Keluar</h5>
                <p class="text-muted small mb-4">Apakah Anda yakin ingin keluar dari sesi aplikasi saat ini?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-3 py-2 small" data-bs-dismiss="modal">Batal</button>
                    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger px-3 py-2 small">Ya, Keluar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS (Includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    setTimeout(function() {
        $('.alert.alert-success.auto-dismiss').fadeOut('slow');
    }, 5000);
});
</script>
</body>
</html>
