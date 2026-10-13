<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Navigasi Samping Responsif Sesuai Peran -->
<!-- ========================================== -->
@auth
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Navigasi Permanen untuk Layar Lebar -->
    <!-- ========================================== -->
    <aside class="portal-sidebar d-none d-lg-block" aria-label="Navigasi utama">
        @include('layout.menu-peran')
    </aside>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Navigasi Offcanvas untuk Perangkat Seluler -->
    <!-- ========================================== -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="menuSeluler" aria-labelledby="judulMenuSeluler">
        <div class="offcanvas-header border-bottom">
            <h2 class="offcanvas-title h6 fw-bold" id="judulMenuSeluler">Navigasi Portal</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-3">
            @include('layout.menu-peran')
        </div>
    </div>
@endauth
