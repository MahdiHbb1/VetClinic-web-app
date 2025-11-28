# ===================================================================
# VetClinic Database Export Script for XAMPP (PowerShell)
# This script exports MySQL database from XAMPP to SQL files
# ===================================================================

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "VetClinic Database Export Tool" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$DB_NAME = "vetclinic"
$DB_USER = "root"
$MYSQL_BIN = "C:\xampp\mysql\bin"
$SCRIPT_DIR = Split-Path -Parent $MyInvocation.MyCommand.Path
$EXPORT_DIR = Join-Path $SCRIPT_DIR "exports"
$DATE_STAMP = Get-Date -Format "yyyyMMdd_HHmmss"

# Create exports directory if it doesn't exist
if (-not (Test-Path $EXPORT_DIR)) {
    Write-Host "Creating exports directory..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $EXPORT_DIR | Out-Null
}

Write-Host "Export directory: $EXPORT_DIR" -ForegroundColor Green
Write-Host "Database: $DB_NAME" -ForegroundColor Green
Write-Host ""

# Prompt for password
$password = Read-Host "Enter MySQL password (press Enter if no password)" -AsSecureString
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($password)
$DB_PASS = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

# Build password argument
$passwordArg = if ($DB_PASS) { "-p$DB_PASS" } else { "" }

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. Exporting FULL DATABASE (Structure + Data)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
$fullFile = Join-Path $EXPORT_DIR "vetclinic_full_$DATE_STAMP.sql"
Write-Host "File: vetclinic_full_$DATE_STAMP.sql" -ForegroundColor White

& "$MYSQL_BIN\mysqldump.exe" -u $DB_USER $passwordArg --routines --triggers --events --single-transaction --quick $DB_NAME | Out-File -FilePath $fullFile -Encoding utf8

if ($LASTEXITCODE -eq 0) {
    Write-Host "[SUCCESS] Full database exported" -ForegroundColor Green
    $size = (Get-Item $fullFile).Length / 1KB
    Write-Host "Size: $([math]::Round($size, 2)) KB" -ForegroundColor Gray
} else {
    Write-Host "[ERROR] Failed to export full database" -ForegroundColor Red
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "2. Exporting SCHEMA ONLY (No Data)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
$schemaFile = Join-Path $EXPORT_DIR "vetclinic_schema.sql"
Write-Host "File: vetclinic_schema.sql" -ForegroundColor White

& "$MYSQL_BIN\mysqldump.exe" -u $DB_USER $passwordArg --no-data --routines --triggers $DB_NAME | Out-File -FilePath $schemaFile -Encoding utf8

if ($LASTEXITCODE -eq 0) {
    Write-Host "[SUCCESS] Schema exported" -ForegroundColor Green
    $size = (Get-Item $schemaFile).Length / 1KB
    Write-Host "Size: $([math]::Round($size, 2)) KB" -ForegroundColor Gray
} else {
    Write-Host "[ERROR] Failed to export schema" -ForegroundColor Red
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "3. Exporting DATA ONLY (No Structure)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
$dataFile = Join-Path $EXPORT_DIR "vetclinic_data_$DATE_STAMP.sql"
Write-Host "File: vetclinic_data_$DATE_STAMP.sql" -ForegroundColor White

& "$MYSQL_BIN\mysqldump.exe" -u $DB_USER $passwordArg --no-create-info --complete-insert --skip-triggers $DB_NAME | Out-File -FilePath $dataFile -Encoding utf8

if ($LASTEXITCODE -eq 0) {
    Write-Host "[SUCCESS] Data exported" -ForegroundColor Green
    $size = (Get-Item $dataFile).Length / 1KB
    Write-Host "Size: $([math]::Round($size, 2)) KB" -ForegroundColor Gray
} else {
    Write-Host "[ERROR] Failed to export data" -ForegroundColor Red
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "4. Exporting INDIVIDUAL TABLES" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$tables = @("owner", "pet", "veterinarian", "appointment", "medical_record", "medicine", "service", "resep", "vaksinasi", "users")

foreach ($table in $tables) {
    $tableFile = Join-Path $EXPORT_DIR "table_$table.sql"
    Write-Host "Exporting table: $table" -ForegroundColor Yellow
    
    & "$MYSQL_BIN\mysqldump.exe" -u $DB_USER $passwordArg $DB_NAME $table | Out-File -FilePath $tableFile -Encoding utf8
    
    if ($LASTEXITCODE -eq 0) {
        $size = (Get-Item $tableFile).Length / 1KB
        Write-Host "  [OK] Size: $([math]::Round($size, 2)) KB" -ForegroundColor Green
    } else {
        Write-Host "  [FAILED]" -ForegroundColor Red
    }
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Export Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Get-ChildItem -Path $EXPORT_DIR -Filter "*.sql" | Sort-Object LastWriteTime -Descending | ForEach-Object {
    $size = $_.Length / 1KB
    Write-Host ("{0,-40} {1,10} KB  {2}" -f $_.Name, [math]::Round($size, 2), $_.LastWriteTime.ToString("yyyy-MM-dd HH:mm")) -ForegroundColor White
}

Write-Host ""
Write-Host "All exports saved to: $EXPORT_DIR" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. Verify the exported files" -ForegroundColor Yellow
Write-Host "2. Copy important files to database/ directory" -ForegroundColor Yellow
Write-Host "3. Add to Git: git add database/*.sql" -ForegroundColor Yellow
Write-Host "4. Commit: git commit -m 'Add database exports'" -ForegroundColor Yellow
Write-Host "5. Push to GitHub: git push origin main" -ForegroundColor Yellow
Write-Host ""

# Ask if user wants to copy files
$response = Read-Host "Do you want to copy the full export to database/ directory? (Y/N)"
if ($response -eq 'Y' -or $response -eq 'y') {
    $destFile = Join-Path $SCRIPT_DIR "vetclinic_latest_export.sql"
    Copy-Item -Path $fullFile -Destination $destFile -Force
    Write-Host "Copied to: $destFile" -ForegroundColor Green
}

Write-Host ""
Write-Host "Export complete!" -ForegroundColor Green
Write-Host ""
