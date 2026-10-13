<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Tata Letak Utama Sistem -->
<!-- ========================================== -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Portal Terpadu') - {{ config('app.name', 'Smart Campus Terpadu') }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --warna-dasar: #090d16;
            --warna-kartu: rgba(18, 24, 38, 0.85);
            --warna-kartu-hover: rgba(28, 38, 60, 0.95);
            --warna-border: rgba(255, 255, 255, 0.08);
            --warna-primer: #4f46e5;
            --warna-primer-hover: #4338ca;
            --warna-aksen: #06b6d4;
            --warna-teks: #f8fafc;
            --warna-teks-redup: #94a3b8;
            --warna-sukses: #10b981;
            --warna-peringatan: #f59e0b;
            --warna-bahaya: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--warna-dasar);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.12) 0px, transparent 50%);
            color: var(--warna-teks);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navigasi-atas {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--warna-border);
            padding: 0.875rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .merek-portal {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--warna-teks);
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.025em;
        }

        .logo-simbol {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--warna-primer), var(--warna-aksen));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 800;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .menu-pengguna {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .lencana-peran {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: rgba(79, 70, 229, 0.2);
            color: #a5b4fc;
            border: 1px solid rgba(165, 180, 252, 0.3);
        }

        .tombol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.55rem 1.15rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .tombol-primer {
            background: linear-gradient(135deg, var(--warna-primer), #6366f1);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }
        .tombol-primer:hover {
            background: linear-gradient(135deg, var(--warna-primer-hover), var(--warna-primer));
            transform: translateY(-1px);
        }

        .tombol-sekunder {
            background: rgba(255, 255, 255, 0.05);
            color: var(--warna-teks);
            border: 1px solid var(--warna-border);
        }
        .tombol-sekunder:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tombol-bahaya {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .tombol-bahaya:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        .wadah-konten {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
            width: 100%;
        }

        .kartu {
            background: var(--warna-kartu);
            border: 1px solid var(--warna-border);
            border-radius: 16px;
            padding: 1.75rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
        }

        .catatan-kaki {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.8rem;
            color: var(--warna-teks-redup);
            border-top: 1px solid var(--warna-border);
            margin-top: auto;
        }
    </style>
    @stack('gaya')
</head>
<body>
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Bilah Navigasi Atas -->
    <!-- ========================================== -->
    <header class="navigasi-atas">
        <a href="/" class="merek-portal">
            <div class="logo-simbol">SC</div>
            <span>Smart Campus Terpadu</span>
        </a>

        @auth
        <div class="menu-pengguna">
            <span style="font-size: 0.875rem; color: var(--warna-teks-redup);">
                {{ Auth::user()->NAMALENGKAP }}
            </span>

            @if(session('PERANAKTIF'))
                <span class="lencana-peran">{{ session('PERANAKTIF') }}</span>
            @endif

            @if(count(Auth::user()->daftarPeran()) > 1)
                <a href="{{ route('autentikasi.pilihperan') }}" class="tombol tombol-sekunder" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                    ⇄ Ganti Peran
                </a>
            @endif

            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="tombol tombol-bahaya" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                    Keluar
                </button>
            </form>
        </div>
        @endauth
    </header>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Area Konten Utama Halaman -->
    <!-- ========================================== -->
    <main class="wadah-konten">
        @yield('konten')
    </main>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Catatan Kaki (Footer) -->
    <!-- ========================================== -->
    <footer class="catatan-kaki">
        &copy; {{ date('Y') }} Smart Campus Terpadu &bull; Portal Multi-Role & TTE Kriptografi Ed25519
    </footer>
</body>
</html>
