@echo off
title Digi-Safe Purwakarta - Server Localhost UKK
cd /d "%~dp0"

echo ========================================================
echo   DIGI-SAFE PURWAKARTA - MODE LOCALHOST (DEMO UKK)
echo ========================================================
echo.
echo [1/4] Mengaktifkan konfigurasi .env.local ...
copy /Y ".env.local" ".env" >nul

echo [2/4] Membersihkan cache Laravel ...
php artisan optimize:clear

echo [3/4] Memeriksa koneksi database lokal (digi_safe_db) ...
php -r "new PDO('mysql:host=127.0.0.1;port=3306;dbname=digi_safe_db', 'root', ''); echo 'MySQL lokal (digi_safe_db) terhubung.' . PHP_EOL;"
if errorlevel 1 (
    echo.
    echo [PERINGATAN] Gagal terhubung ke MySQL lokal!
    echo Pastikan XAMPP Control Panel sudah dibuka dan tombol Start pada MySQL sudah diklik.
    echo.
    pause
    exit /b 1
)

echo [4/4] Membuka http://127.0.0.1:8000 di browser & menjalankan server ...
echo Jangan tutup jendela ini selama sesi demo UKK berlangsung.
echo Tekan Ctrl+C untuk menghentikan server.
echo.
start "" "http://127.0.0.1:8000"
php artisan serve --host=127.0.0.1 --port=8000
