@echo off
REM Start Laravel dev server for salary on port 8004
cd /d D:\Laravel\salary
start "" "C:\php829nts\php.exe" artisan serve --host=0.0.0.0 --port=8004
exit /b 0
