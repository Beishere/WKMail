@echo off
cd /d "%~dp0"
echo ===============================================================
echo   Menjalankan SIDISURAT + Link Publik HTTPS (Cloudflare Tunnel)
echo ===============================================================
start "SIDISURAT Local Server" cmd /c "php -S localhost:8080 -t public"
timeout /t 2 /nobreak >nul
echo Membuka tunnel publik HTTPS... (Salin link https://xxxx.trycloudflare.com di bawah)
cloudflared.exe tunnel --url http://localhost:8080
pause
