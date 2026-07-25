@echo off
setlocal
set ROOT=%~dp0..
set MARIADB=%ROOT%\tools\mariadb

echo Stopping MariaDB...
"%MARIADB%\bin\mysqladmin.exe" -uroot -h127.0.0.1 -P3306 shutdown 2>nul
echo Done.
endlocal
