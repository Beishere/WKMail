@echo off
cd /d "%~dp0"
echo ===================================================
echo   Menjalankan SIDISURAT (Sistem Disposisi Surat)
echo   Buka di browser: http://localhost:8080
echo ===================================================
start http://localhost:8080
php -S localhost:8080 -t public
pause
