@echo off
cd /d "%~dp0.."
set PHPRC=%CD%
echo Starting MAIN  http://127.0.0.1:8000
start "ITR Tax Main" cmd /k "set PHPRC=%CD% && php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 1 /nobreak >nul
echo Starting ADMIN http://127.0.0.1:8001/login
start "ITR Tax Admin" cmd /k "set PHPRC=%CD% && php artisan serve --host=127.0.0.1 --port=8001"
echo.
echo Main:  http://127.0.0.1:8000
echo Admin: http://127.0.0.1:8001/login  (admin / admin@2026)
pause
