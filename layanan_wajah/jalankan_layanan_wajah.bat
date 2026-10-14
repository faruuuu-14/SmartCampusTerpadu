@REM ==========================================
@REM PEMBAHASAN / BAGIAN: Menjalankan Layanan Wajah Secara Lokal
@REM ==========================================
@echo off
setlocal
cd /d "%~dp0\.."
if not exist "layanan_wajah\.venv\Scripts\python.exe" (
    echo Virtual environment tidak ditemukan. Jalankan README layanan_wajah terlebih dahulu.
    exit /b 1
)
"layanan_wajah\.venv\Scripts\python.exe" -m uvicorn layanan_wajah.app:app --host 127.0.0.1 --port 8765
