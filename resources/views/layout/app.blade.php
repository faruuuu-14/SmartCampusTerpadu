<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Tata Letak Terang Portal Smart Campus -->
<!-- ========================================== -->
<!doctype html>
<html lang="id">
<head>
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Metadata dan Kerangka Bootstrap -->
    <!-- ========================================== -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f4f7fb">
    <title>@yield('judul', 'Portal Terpadu') - {{ config('app.name', 'Smart Campus Terpadu') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            color-scheme: light;
            --warna-dasar: #f3f6fa;
            --warna-kartu: #ffffff;
            --warna-border: #e6ebf2;
            --warna-primer: #1374ad;
            --warna-primer-gelap: #075985;
            --warna-teks: #203040;
            --warna-teks-redup: #758395;
            --warna-sukses: #17866a;
            --lebar-sisi: 264px;
        }

        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            background: var(--warna-dasar);
            color: var(--warna-teks);
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: .94rem;
        }
        h1, h2, h3, h4, h5, .navbar-brand { font-family: "Manrope", "DM Sans", sans-serif; }
        .portal-header {
            min-height: 72px;
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .75rem clamp(1rem, 3vw, 2rem);
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid var(--warna-border);
            box-shadow: 0 3px 16px rgb(31 56 80 / 4%);
            backdrop-filter: blur(12px);
        }
        .merek-portal {
            display: inline-flex;
            align-items: center;
            gap: .7rem;
            color: var(--warna-teks);
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }
        .logo-portal {
            display: inline-grid;
            width: 2.65rem;
            height: 2.65rem;
            place-items: center;
            border-radius: .9rem;
            color: #fff;
            background: linear-gradient(145deg, #1da7ae, #1374ad);
            box-shadow: 0 7px 15px rgb(19 116 173 / 18%);
        }
        .portal-sidebar {
            position: fixed;
            z-index: 1020;
            inset: 72px auto 0 0;
            width: var(--lebar-sisi);
            overflow-y: auto;
            padding: 1.2rem .9rem 1rem;
            background: #fff;
            border-right: 1px solid var(--warna-border);
        }
        .menu-kelompok { margin: 0 0 1rem; }
        .menu-judul {
            padding: 0 .8rem .45rem;
            color: #9aa6b4;
            font-size: .67rem;
            font-weight: 800;
            letter-spacing: .11em;
            text-transform: uppercase;
        }
        .menu-tautan {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-height: 42px;
            margin: .15rem 0;
            padding: .58rem .8rem;
            border-radius: .7rem;
            color: #5d6c7b;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s ease, color .15s ease, transform .15s ease;
        }
        .menu-tautan:hover { color: var(--warna-primer-gelap); background: #eff7fc; transform: translateX(2px); }
        .menu-tautan.aktif { color: #086494; background: #eaf5fc; font-weight: 800; }
        .menu-ikon {
            display: inline-grid;
            width: 1.55rem;
            height: 1.55rem;
            place-items: center;
            border-radius: .5rem;
            background: #f0f4f8;
            font-size: .76rem;
        }
        .menu-tautan.aktif .menu-ikon { background: #d4eefa; }
        .portal-konten {
            width: min(100% - 2rem, 1500px);
            flex: 1;
            margin: 1.6rem auto 2rem;
        }
        @auth
            .portal-konten { width: auto; margin-left: calc(var(--lebar-sisi) + 1.7rem); margin-right: 1.7rem; }
        @endauth
        .kartu {
            padding: clamp(1rem, 2vw, 1.5rem);
            background: var(--warna-kartu);
            border: 1px solid var(--warna-border);
            border-radius: 1rem;
            box-shadow: 0 5px 20px rgb(31 56 80 / 4%);
        }
        .lencana-peran {
            display: inline-flex;
            padding: .32rem .75rem;
            border: 1px solid #cfe7f3;
            border-radius: 999px;
            background: #eff8fc;
            color: #086494;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .04em;
        }
        .judul-halaman { margin: 0; font-size: clamp(1.35rem, 2.2vw, 1.8rem); font-weight: 800; letter-spacing: -.035em; }
        .teks-bantu { color: var(--warna-teks-redup); }
        .kartu-statistik { min-height: 128px; }
        .kartu-statistik .nilai-statistik { color: #24394c; font-family: "Manrope", sans-serif; font-size: 1.8rem; font-weight: 800; }
        .form-control, .form-select { min-height: 44px; border-color: #dfe6ee; border-radius: .65rem; }
        .form-control:focus, .form-select:focus { border-color: #8ec5e2; box-shadow: 0 0 0 .2rem rgb(19 116 173 / 12%); }
        .btn { border-radius: .65rem; font-weight: 700; }
        .btn-primary { background: var(--warna-primer); border-color: var(--warna-primer); }
        .btn-primary:hover { background: var(--warna-primer-gelap); border-color: var(--warna-primer-gelap); }
        .table { --bs-table-bg: transparent; }
        .table thead th { background: #f4f7fa; color: #6d7d8c; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
        .table > :not(caption) > * > * { padding: .8rem .75rem; border-bottom-color: #edf1f5; }
        .portal-footer { padding: 1rem 1.5rem; color: #8995a2; font-size: .78rem; text-align: center; }
        .peringatan-sukses { border: 1px solid #cbe9dc; border-radius: .8rem; background: #f0faf5; color: #126849; }
        .peringatan-galat { border-radius: .8rem; }
        .login-konten { width: min(100% - 2rem, 1080px); }
        .kartu-login { overflow: hidden; border: 1px solid var(--warna-border); border-radius: 1.25rem; background: #fff; box-shadow: 0 20px 55px rgb(36 67 97 / 10%); }
        .login-sambutan { min-height: 510px; color: #fff; background: linear-gradient(145deg, #0b5b8e, #137eaa 60%, #20a5a5); }
        .login-sambutan .fitur-login { border-top: 1px solid rgb(255 255 255 / 20%); }
        .login-input { min-height: 48px; border: 1px solid #d9e2eb; border-radius: .7rem; background: #f9fbfd; }
        .daftar-tanpa-data { padding: 2rem 1rem; color: var(--warna-teks-redup); text-align: center; }
        @media (max-width: 991.98px) {
            .portal-sidebar { display: none; }
            .portal-konten, body .portal-konten { width: min(100% - 1.5rem, 1500px); margin: 1.1rem auto 1.6rem; }
            .login-sambutan { min-height: auto; }
        }
        @media (max-width: 575.98px) {
            .portal-header { min-height: 64px; padding: .65rem .85rem; }
            .portal-konten, body .portal-konten { width: calc(100% - 1rem); margin-top: .8rem; }
            .kartu { border-radius: .8rem; }
        }
    </style>
    @stack('gaya')
</head>
<body>
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Header, Navigasi Responsif, dan Konten -->
    <!-- ========================================== -->
    @include('layout.header')
    @include('layout.navigasi')

    <main class="portal-konten">
        @if (session('status'))
            <div class="alert peringatan-sukses mb-4" role="status">{{ session('status') }}</div>
        @endif
        @yield('konten')
    </main>

    @include('layout.footer')
</body>
</html>
