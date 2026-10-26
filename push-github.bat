@echo off
cd /d "%~dp0"
echo =========================================================
echo   Upload Project ke https://github.com/Beishere/WKMail
echo =========================================================
git config credential.https://github.com.useHttpPath true
git remote set-url origin https://Beishere@github.com/Beishere/WKMail.git
git push -u origin main
pause
