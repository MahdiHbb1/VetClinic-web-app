@echo off
:: Backup script for VetClinic database
:: Save as backup_db.bat

:: Set variables
set TIMESTAMP=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_PATH=D:\backups\vetclinic
set MYSQL_PATH=D:\New Folder\mysql\bin
set DB_NAME=vetclinic
set DB_USER=root
set DB_PASS=

:: Create backup directory if not exists
if not exist "%BACKUP_PATH%" mkdir "%BACKUP_PATH%"

:: Create backup
"%MYSQL_PATH%\mysqldump.exe" --user=%DB_USER% --databases %DB_NAME% --add-drop-database --add-drop-table --create-options --quote-names --routines --triggers --single-transaction --set-gtid-purged=OFF > "%BACKUP_PATH%\vetclinic_%TIMESTAMP%.sql"

:: Keep only last 7 days of backups
forfiles /p "%BACKUP_PATH%" /s /m *.* /d -7 /c "cmd /c del @path"

:: Create success log
echo Backup completed successfully at %date% %time% > "%BACKUP_PATH%\backup_log.txt"