<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Kerangka HTML Utama Aplikasi -->
<!-- ========================================== -->
<!doctype html>
<html lang="id">
<head>
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Metadata dan Bootstrap 5 -->
    <!-- ========================================== -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Portal Terpadu') - {{ config('app.name', 'Smart Campus Terpadu') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --warna-dasar: #f3f6fb;
            --warna-kartu: #ffffff;
            --warna-border: #e2e8f0;
            --warna-primer: #4f46e5;
            --warna-teks: #172033;
            --warna-teks-redup: #64748b;
            --warna-sukses: #059669;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--warna-dasar);
            color: var(--warna-teks);
        }

        .wadah-konten {
            width: 100%;
            max-width: 1200px;
            flex: 1;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .kartu {
            padding: 1.5rem;
            background: var(--warna-kartu);
            border: 1px solid var(--warna-border);
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgb(15 23 42 / 6%);
        }

        .lencana-peran {
            padding: 0.3rem 0.75rem;
            border: 1px solid #c7d2fe;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .tombol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.55rem 1rem;
            border: 0;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .tombol-primer {
            background: var(--warna-primer);
            color: #ffffff;
        }

        .tombol-sekunder {
            background: #eef2ff;
            color: #3730a3;
        }

        .tombol-bahaya {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
    @stack('gaya')
</head>
<body>
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Header, Navigasi, dan Konten Halaman -->
    <!-- ========================================== -->
    @include('layout.header')
    @include('layout.navigasi')

    <main class="wadah-konten">
        @yield('konten')
    </main>

    @include('layout.footer')
</body>
</html>
