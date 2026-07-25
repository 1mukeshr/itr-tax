@echo off
setlocal
set ROOT=%~dp0..
set MARIADB=%ROOT%\tools\mariadb
set DATA=%MARIADB%\data

if not exist "%MARIADB%\bin\mysqld.exe" (
  echo MariaDB not found at tools\mariadb
  exit /b 1
)

REM Already running?
"%MARIADB%\bin\mysqladmin.exe" --user=root --host=127.0.0.1 --port=3306 ping >nul 2>&1
if not errorlevel 1 (
  echo MariaDB already running on 127.0.0.1:3306
  endlocal
  exit /b 0
)

echo Starting MariaDB on 127.0.0.1:3306 ...
REM Hidden window — do not type in the server process; use Cursor terminal + mysql.exe
powershell -NoProfile -Command "Start-Process -FilePath '%MARIADB%\bin\mysqld.exe' -ArgumentList '--datadir=%DATA%','--port=3306','--bind-address=127.0.0.1' -WindowStyle Hidden"
timeout /t 3 /nobreak >nul

"%MARIADB%\bin\mysqladmin.exe" --user=root --host=127.0.0.1 --port=3306 ping >nul 2>&1
if errorlevel 1 (
  echo ERROR: MariaDB did not start. Check tools\mariadb\data\*.err
  endlocal
  exit /b 1
)

echo MariaDB started (hidden). Use Cursor terminal to query DB.
endlocal
