@echo off
title Digi-Safe Purwakarta - Pulihkan .env Hosting
cd /d "%~dp0"

echo ========================================================
echo   PULIHKAN FILE .ENV KE PENGATURAN HOSTING RUMAHWEB
echo ========================================================
echo.
copy /Y ".env.hosting" ".env" >nul
php artisan optimize:clear
echo.
echo Selesai! File .env kini kembali menggunakan pengaturan hosting (digisafepurwakarta.web.id).
echo Untuk menjalankan kembali localhost UKK, klik dua kali JALANKAN-LOCALHOST-UKK.bat.
echo.
pause

