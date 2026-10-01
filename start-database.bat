@echo off
title Djoeragan POS Database
echo Menjalankan database Djoeragan POS di port 3307...
start "Djoeragan Database" /min C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\htdocs\warkop-djoeragan\database\mariadb-djoeragan.ini
timeout /t 4 /nobreak >nul
C:\xampp\mysql\bin\mysqladmin.exe --protocol=tcp -h 127.0.0.1 -P 3307 -u root ping
pause
