@echo off
setlocal

set "BACKUP_ROOT=C:\ProgramData\ProjectManagementBackup"
set "CREDENTIAL_FILE=%BACKUP_ROOT%\mysql-client.cnf"
set "RUNNER=%BACKUP_ROOT%\project-management-backup.bat"
set "TASK_NAME=Project Management Database Backup"

if /i "%~1"=="run" goto :backup

net session >nul 2>&1
if errorlevel 1 (
    echo Run this file as Administrator on the VPS.
    pause
    exit /b 1
)

if not exist "%BACKUP_ROOT%" mkdir "%BACKUP_ROOT%"
if errorlevel 1 (
    echo Could not create %BACKUP_ROOT%.
    pause
    exit /b 1
)

icacls "%BACKUP_ROOT%" /inheritance:r /T /C >nul
icacls "%BACKUP_ROOT%" /grant:r "*S-1-5-18:(OI)(CI)F" "*S-1-5-32-544:(OI)(CI)F" /T /C >nul
if errorlevel 1 (
    echo Could not restrict access to the backup folder.
    pause
    exit /b 1
)

if not exist "%CREDENTIAL_FILE%" (
    > "%CREDENTIAL_FILE%" echo [client]
    >> "%CREDENTIAL_FILE%" echo host=127.0.0.1
    >> "%CREDENTIAL_FILE%" echo user=project_management_app
    >> "%CREDENTIAL_FILE%" echo password="PASTE_DB_PASSWORD_HERE"
    start /wait notepad.exe "%CREDENTIAL_FILE%"
    findstr /c:"PASTE_DB_PASSWORD_HERE" "%CREDENTIAL_FILE%" >nul
    if not errorlevel 1 (
        echo Replace PASTE_DB_PASSWORD_HERE with the database password in %CREDENTIAL_FILE%.
        pause
        exit /b 1
    )
)

copy /y "%~f0" "%RUNNER%" >nul
if errorlevel 1 (
    echo Could not install the backup runner in %BACKUP_ROOT%.
    pause
    exit /b 1
)

schtasks /create /tn "%TASK_NAME%" /sc daily /st 02:00 /ru SYSTEM /rl HIGHEST /tr "%RUNNER% run" /f
if errorlevel 1 (
    echo Could not register the daily backup task.
    pause
    exit /b 1
)

call "%RUNNER%" run
if errorlevel 1 (
    echo Initial backup failed. Check %BACKUP_ROOT%\backup.log.
    pause
    exit /b 1
)

echo.
echo Daily backup is installed for 02:00 server time. It keeps approximately 14 days of SQL dumps.
echo Backups are stored in %BACKUP_ROOT%.
pause
exit /b 0

:backup
set "MYSQLDUMP="
for /f "delims=" %%I in ('where.exe mysqldump.exe 2^>nul') do if not defined MYSQLDUMP set "MYSQLDUMP=%%I"
if not defined MYSQLDUMP set "MYSQLDUMP=%ProgramFiles%\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
if not exist "%MYSQLDUMP%" (
    >> "%BACKUP_ROOT%\backup.log" echo [%date% %time%] ERROR: mysqldump.exe was not found.
    echo mysqldump.exe was not found. Add its path to this BAT file and retry.
    exit /b 1
)

for /f %%I in ('powershell.exe -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set "STAMP=%%I"
set "BACKUP_FILE=%BACKUP_ROOT%\project-management-%STAMP%.sql"

"%MYSQLDUMP%" --defaults-extra-file="%CREDENTIAL_FILE%" --single-transaction --no-tablespaces --set-gtid-purged=OFF --result-file="%BACKUP_FILE%" project_management >> "%BACKUP_ROOT%\backup.log" 2>&1
if errorlevel 1 (
    del /q "%BACKUP_FILE%" >nul 2>&1
    >> "%BACKUP_ROOT%\backup.log" echo [%date% %time%] ERROR: mysqldump failed.
    echo Database backup failed. Check %BACKUP_ROOT%\backup.log.
    exit /b 1
)

if not exist "%BACKUP_FILE%" (
    >> "%BACKUP_ROOT%\backup.log" echo [%date% %time%] ERROR: dump file was not created.
    echo Database backup file was not created.
    exit /b 1
)

forfiles /p "%BACKUP_ROOT%" /m "project-management-*.sql" /d -14 /c "cmd /c del /q @path" >nul 2>&1
>> "%BACKUP_ROOT%\backup.log" echo [%date% %time%] Backup created: %BACKUP_FILE%
echo Backup OK: %BACKUP_FILE%
exit /b 0
