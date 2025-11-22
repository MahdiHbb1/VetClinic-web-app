# Quick Start Guide - Deploy VetClinic ke InfinityFree

## 🚀 10 Langkah Cepat Deployment

### 1. Persiapkan File (5 menit)
```bash
php deploy/prepare.php
```
✅ File ZIP deployment akan dibuat

### 2. Register InfinityFree (5 menit)
1. Kunjungi https://app.infinityfree.net/register
2. Buat akun baru
3. Verifikasi email

### 3. Buat Hosting (5 menit)
1. Login ke InfinityFree
2. Create Account
3. Pilih subdomain
   - Contoh: vetclinic.infinityfreeapp.com

### 4. Buat Database (5 menit)
1. Buka MySQL Databases
2. Create database
3. Catat kredensial:
   - Host
   - Database name
   - Username
   - Password

### 5. Upload File (10 menit)
1. Buka File Manager
2. Masuk ke public_html
3. Upload vetclinic_deploy.zip
4. Extract file

### 6. Update Konfigurasi (5 menit)
1. Edit config/database.php
2. Masukkan kredensial database

### 7. Import Database (5 menit)
1. Buka phpMyAdmin
2. Import vetclinic_deployment.sql

### 8. Test Website (10 menit)
1. Buka subdomain Anda
2. Login sebagai admin
   - Username: admin
   - Password: password123
3. Test fitur utama

### 9. Keamanan (5 menit)
1. Hapus file deployment
2. Ganti password admin
3. Cek error log

### 10. Backup (5 menit)
1. Export database
2. Download file penting
3. Simpan dengan aman

## ⚡ Total Waktu: ~60 menit

## 🆘 Quick Troubleshoot

### Database Error?
✅ Cek kredensial di database.php

### 500 Error?
✅ Cek .htaccess dan error log

### Blank Page?
✅ Cek PHP version dan error log

## 📞 Need Help?
Lihat panduan lengkap di DEPLOYMENT.md