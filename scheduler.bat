@echo off
REM ============================================================
REM  Money Tracker — Laravel Scheduler (Windows)
REM  Runs php artisan schedule:run every 60 seconds.
REM
REM  Usage:
REM    Double-click this file, or run from a terminal:
REM    > scheduler.bat
REM
REM  To stop: close the terminal window or press Ctrl+C.
REM ============================================================

echo ==========================================
echo  Money Tracker Scheduler
echo  Press Ctrl+C to stop
echo ==========================================
echo.

cd /d "%~dp0"

:loop
    echo [%date% %time%] Running schedule:run ...
    php artisan schedule:run
    echo.
    timeout /t 60 /nobreak > nul
goto loop
