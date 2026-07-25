@echo off
setlocal
cd /d "%~dp0"
set PHPRC=%~dp0
if not exist "tools\mariadb\bin\mysqld.exe" goto serve
netstat -an | find "3306" >nul
if errorlevel 1 call tools\start-mysql.bat
:serve
php artisan serve --host=0.0.0.0 --port=8000
endlocal
