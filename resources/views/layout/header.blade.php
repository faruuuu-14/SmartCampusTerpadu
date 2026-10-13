<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Header Merek dan Identitas Portal -->
<!-- ========================================== -->
<header class="bg-white border-bottom">
    <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <a class="d-flex align-items-center gap-2 text-decoration-none text-dark fw-bold" href="{{ route('login') }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fw-bold" style="width: 2.5rem; height: 2.5rem;">SC</span>
            <span>Smart Campus Terpadu</span>
        </a>

        @auth
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-secondary">{{ Auth::user()->NAMALENGKAP }}</span>
                @if (session('PERANAKTIF'))
                    <span class="lencana-peran">{{ session('PERANAKTIF') }}</span>
                @endif
            </div>
        @endauth
    </div>
</header>
