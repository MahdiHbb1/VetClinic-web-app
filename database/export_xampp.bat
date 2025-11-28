@echo off
REM ===================================================================
REM VetClinic Database Export Script for XAMPP
REM This script exports MySQL database from XAMPP to SQL files
REM ===================================================================

echo ========================================
echo VetClinic Database Export Tool
echo ========================================
echo.

REM Configuration
set DB_NAME=vetclinic
set DB_USER=root
set MYSQL_BIN=C:\xampp\mysql\bin
set EXPORT_DIR=%~dp0exports
set DATE_STAMP=%DATE:~-4%%DATE:~3,2%%DATE:~0,2%

REM Create exports directory if it doesn't exist
if not exist "%EXPORT_DIR%" (
    echo Creating exports directory...
    mkdir "%EXPORT_DIR%"
)

echo Export directory: %EXPORT_DIR%
echo Database: %DB_NAME%
echo.

REM Change to MySQL bin directory
cd /d "%MYSQL_BIN%"

echo ========================================
echo 1. Exporting FULL DATABASE (Structure + Data)
echo ========================================
echo File: vetclinic_full_%DATE_STAMP%.sql
mysqldump -u %DB_USER% -p --routines --triggers --events %DB_NAME% > "%EXPORT_DIR%\vetclinic_full_%DATE_STAMP%.sql"
if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Full database exported
) else (
    echo [ERROR] Failed to export full database
)
echo.

echo ========================================
echo 2. Exporting SCHEMA ONLY (No Data)
echo ========================================
echo File: vetclinic_schema.sql
mysqldump -u %DB_USER% -p --no-data --routines --triggers %DB_NAME% > "%EXPORT_DIR%\vetclinic_schema.sql"
if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Schema exported
) else (
    echo [ERROR] Failed to export schema
)
echo.

echo ========================================
echo 3. Exporting DATA ONLY (No Structure)
echo ========================================
echo File: vetclinic_data_%DATE_STAMP%.sql
mysqldump -u %DB_USER% -p --no-create-info --complete-insert %DB_NAME% > "%EXPORT_DIR%\vetclinic_data_%DATE_STAMP%.sql"
if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Data exported
) else (
    echo [ERROR] Failed to export data
)
echo.

echo ========================================
echo 4. Exporting CORE TABLES (owner, pet, veterinarian)
echo ========================================
echo File: vetclinic_core_tables.sql
mysqldump -u %DB_USER% -p %DB_NAME% owner pet veterinarian appointment medical_record > "%EXPORT_DIR%\vetclinic_core_tables.sql"
if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Core tables exported
) else (
    echo [ERROR] Failed to export core tables
)
echo.

echo ========================================
echo Export Summary
echo ========================================
echo.
dir "%EXPORT_DIR%\*.sql" /B
echo.
echo All exports saved to: %EXPORT_DIR%
echo.

echo ========================================
echo Next Steps:
echo ========================================
echo 1. Verify the exported files
echo 2. Copy to your GitHub repository
echo 3. Commit and push to GitHub
echo.

pause
