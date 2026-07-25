@echo off
cd /d "%~dp0.."
set PHPRC=%CD%
echo Starting ADMIN portal on http://127.0.0.1:8001
php artisan serve --host=127.0.0.1 --port=8001
