# 🚀 VetClinic - Quick Reference Card

## 📌 Essential Information

### Access Points
- **URL:** http://localhost:8080
- **Admin Login:** 
  - Username: `admin`
  - Password: `admin123`

### Docker Commands
```bash
# Start application
docker-compose up -d

# Stop application
docker-compose down

# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Check status
docker-compose ps
```

### Database Access
```bash
# MySQL shell
docker exec -it vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic

# Run SQL file
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/your_file.sql

# Backup database
docker exec vetclinic_db mysqldump -uvetclinic_user -pvetclinic_password vetclinic > backup_$(date +%Y%m%d).sql
```

---

## 📊 Database Schema Reference

### Main Tables
| Table | Primary Key | Description |
|-------|-------------|-------------|
| `users` | user_id | System users (admin, staff) |
| `owner` | owner_id | Pet owners/clients |
| `pet` | pet_id | Animals/patients |
| `veterinarian` | dokter_id | Doctors |
| `appointment` | appointment_id | Appointments/bookings |
| `medical_record` | rekam_id | Medical records |
| `medicine` | obat_id | Medicine inventory |
| `service` | layanan_id | Services offered |
| `vaksinasi` | vaksinasi_id | Vaccination records |
| `resep` | resep_id | Prescriptions |

### Important Column Names (Use These!)
```
Appointments:
  - tanggal_appointment (not 'tanggal')
  - jam_appointment (not 'jam_mulai')
  - jenis_layanan (not 'layanan_id')
  - keluhan_awal (not 'keluhan')

Medical Records:
  - tanggal_kunjungan (not 'tanggal')
  - diagnosa (not 'diagnosis')
  - catatan_dokter (not 'catatan')

Pets:
  - ciri_khusus (not 'catatan')
  - berat_badan

Medicine/Inventory:
  - obat_id (not 'item_id')
  - nama_obat (not 'name')
  - stok (not 'current_stock')

Veterinarian:
  - dokter_id
  - nama_dokter
  - no_lisensi
  - spesialisasi
```

---

## 🎯 Module Navigation

### Main Modules (Sidebar)
1. **Dashboard** → `/dashboard/`
2. **Appointments** → `/appointments/`
3. **Pets** → `/pets/`
4. **Owners** → `/owners/`
5. **Medical Records** → `/medical-records/`
6. **Inventory** → `/inventory/`
7. **Dokter Hewan** → `/supplier/` (uses veterinarian table)
8. **Layanan** → `/kategori/` (uses service table)
9. **Vaksinasi** → `/vaccinations/`

### CRUD Pattern
All modules follow this pattern:
- `index.php` - List/Read all
- `create.php` - Create new
- `edit.php?id=X` - Update existing
- `delete.php` - Delete (POST)
- `detail.php?id=X` - View details

---

## 🔐 Security Quick Reference

### CSRF Protection
```php
// In forms
<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

// In POST handlers
if (!validate_csrf_token($_POST['csrf_token'])) {
    die('Invalid token');
}
```

### Input Sanitization
```php
$clean_input = clean_input($_POST['field']);
$int_value = filter_var($_POST['id'], FILTER_VALIDATE_INT);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
```

### File Upload
```php
$file_path = handle_file_upload($_FILES['photo'], 'uploads/pets/');
```

---

## 🐛 Troubleshooting

### Common Issues

**1. Port 8080 already in use**
```yaml
# Edit docker-compose.yml
ports:
  - "8081:80"  # Change to different port
```

**2. Database connection error**
```bash
# Check containers
docker-compose ps

# Restart database
docker-compose restart vetclinic_db
```

**3. Icons not showing**
```
Check CSP headers in PHP files:
header("Content-Security-Policy: ... font-src 'self' cdnjs.cloudflare.com data:...");
```

**4. Permission denied**
```bash
# Fix upload folder permissions
chmod 755 uploads/
chmod 755 storage/
```

**5. White screen / 500 error**
```bash
# Check PHP logs
docker-compose logs vetclinic_app

# Check Nginx logs
docker-compose logs vetclinic_webserver
```

---

## 📁 Important Files

### Configuration
- `config/database.php` - Database credentials
- `docker-compose.yml` - Docker services
- `Dockerfile` - PHP container setup

### Core Functions
- `includes/functions.php` - Shared functions
- `includes/appointment_functions.php` - Appointment helpers
- `includes/medical_record_functions.php` - Medical record helpers
- `auth/check_auth.php` - Authentication check

### Database
- `database/vetclinic.sql` - Main schema
- `database/vetclinic_deployment.sql` - Production ready
- `database/test_data.sql` - Sample data
- `database/run_tests.sql` - Test queries

---

## 📝 Quick Testing

### Test CRUD Operation
```bash
# 1. Login at http://localhost:8080
# 2. Navigate to any module
# 3. Click "Tambah" (Create)
# 4. Fill form and submit
# 5. Click "Detail" (Read)
# 6. Click "Edit" (Update)
# 7. Click "Hapus" (Delete)
```

### Verify Database
```bash
# Count records
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic -e "SELECT COUNT(*) FROM pet;"

# View recent appointments
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic -e "SELECT * FROM appointment ORDER BY created_at DESC LIMIT 5;"
```

---

## 🎓 Code Snippets

### Add New User
```sql
INSERT INTO users (username, password, nama_lengkap, email, role, status)
VALUES ('newuser', '$2y$10$hashedpassword', 'New User', 'user@email.com', 'Staff', 'Active');
```

### Check Appointments Today
```sql
SELECT a.*, p.nama_hewan, o.nama_lengkap, v.nama_dokter
FROM appointment a
JOIN pet p ON a.pet_id = p.pet_id
JOIN owner o ON a.owner_id = o.owner_id
JOIN veterinarian v ON a.dokter_id = v.dokter_id
WHERE DATE(a.tanggal_appointment) = CURDATE()
ORDER BY a.jam_appointment;
```

### Update Medicine Stock
```sql
UPDATE medicine 
SET stok = stok + 100 
WHERE obat_id = 1;
```

---

## 🚀 Deployment Checklist

- [ ] Test all CRUD operations
- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Enable HTTPS (production)
- [ ] Set up backups
- [ ] Configure firewall
- [ ] Test on mobile devices
- [ ] Check all icons load
- [ ] Verify file uploads work
- [ ] Test with real data

---

## 📞 Quick Links

- **Documentation:** See `README.md`
- **Deployment:** See `DEPLOYMENT_CHECKLIST.md`
- **Testing:** See `TESTING_GUIDE.md`
- **Summary:** See `PROJECT_SUMMARY.md`

---

## ✅ Status Check

```bash
# All systems check
docker-compose ps && echo "✅ Containers running" || echo "❌ Containers not running"

# Database check
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password -e "SELECT 1" 2>/dev/null && echo "✅ Database OK" || echo "❌ Database error"

# Web server check
curl -s http://localhost:8080 > /dev/null && echo "✅ Web server OK" || echo "❌ Web server error"
```

---

**Last Updated:** November 22, 2025  
**Version:** 1.0.0  
**Status:** Production Ready ✅
