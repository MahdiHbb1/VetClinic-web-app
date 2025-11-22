# Panduan Deployment VetClinic ke InfinityFree

## 📋 Persiapan

### 1. Persiapkan File Deployment
1. Jalankan script persiapan deployment:
   ```bash
   php deploy/prepare.php
   ```
2. Script akan membuat:
   - File ZIP untuk deployment
   - Konfigurasi production
   - File .htaccess yang optimal

### 2. Register InfinityFree
1. Kunjungi https://app.infinityfree.net/register
2. Daftar akun baru
3. Verifikasi email Anda
4. Login ke control panel

## 🚀 Langkah Deployment

### 1. Buat Hosting Account
1. Di dashboard InfinityFree, klik "Create Account"
2. Pilih subdomain (misal: vetclinic.infinityfreeapp.com)
3. Tunggu proses pembuatan selesai (± 5 menit)

### 2. Setup Database
1. Di control panel, klik "MySQL Databases"
2. Buat database baru
3. Catat informasi database:
   - Database Host
   - Database Name
   - Database Username
   - Database Password

### 3. Upload File
1. Di control panel, klik "File Manager"
2. Buka folder `htdocs` atau `public_html`
3. Upload file `vetclinic_deploy.zip`
4. Extract file ZIP
5. Hapus file ZIP setelah extract selesai

### 4. Konfigurasi Database
1. Buka file `config/database.php`
2. Update kredensial database:
   ```php
   define('DB_HOST', 'host_dari_infinityfree');
   define('DB_USER', 'username_dari_infinityfree');
   define('DB_PASS', 'password_dari_infinityfree');
   define('DB_NAME', 'database_dari_infinityfree');
   ```

### 5. Import Database
1. Di control panel, klik "MySQL Manager" (phpMyAdmin)
2. Pilih database Anda
3. Klik tab "Import"
4. Upload file SQL dari `database/vetclinic_deployment.sql`
5. Klik "Go" untuk memulai import

### 6. Pengujian
1. Kunjungi website Anda (misal: http://vetclinic.infinityfreeapp.com)
2. Login dengan akun admin:
   - Username: admin
   - Password: password123
3. Test semua fitur:
   - CRUD Kategori
   - Upload file
   - Generate laporan
   - dll

## 🔧 Troubleshooting

### Database Connection Error
1. Periksa kredensial database
2. Pastikan host yang digunakan benar
3. Cek apakah database sudah diimport

### 500 Internal Server Error
1. Periksa file .htaccess
2. Cek error log di control panel
3. Pastikan semua file terupload dengan benar

### File Upload Error
1. Pastikan folder `uploads` memiliki permission 755
2. Cek ukuran file (max 10MB)
3. Periksa setting `upload_max_filesize` di PHP

### White Screen / Blank Page
1. Cek error log
2. Aktifkan error display sementara untuk debug
3. Periksa versi PHP compatibility

## 📝 Maintenance

### Backup Regular
1. Export database secara regular
2. Download file-file penting
3. Simpan backup di tempat yang aman

### Update Sistem
1. Test update di lokal dulu
2. Backup sebelum update
3. Upload file yang diupdate
4. Test setelah update

### Monitor Resource
1. Cek disk space regular
2. Monitor bandwidth usage
3. Periksa error log

## 🔒 Security Checklist

- [ ] Hapus file instalasi dan deployment
- [ ] Ganti password admin default
- [ ] Pastikan semua input tervalidasi
- [ ] Periksa error log regular
- [ ] Update PHP regular
- [ ] Backup database regular

## 📞 Support

Jika mengalami masalah:
1. Cek dokumentasi InfinityFree
2. Periksa error log
3. Hubungi support InfinityFree

## 🔄 Rollback Plan

Jika terjadi masalah serius:
1. Restore database dari backup
2. Upload file backup
3. Update konfigurasi jika perlu

---

**Remember:**
- Simpan semua kredensial dengan aman
- Backup regular
- Monitor error log
- Update sistem secara berkala