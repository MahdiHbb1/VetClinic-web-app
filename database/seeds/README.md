# VetClinic Complete Demo Data

## 📋 Overview

This comprehensive dummy data package provides a **fully functional demo** of the VetClinic Management System with realistic data for all features including graphs, photos, and complete relationships.

---

## 🎯 What's Included

### 👥 Staff & Users (6 members)
- **1 Admin**: Dr. Ahmad Wijaya
- **3 Veterinarians**: Dr. Sarah Putri (Bedah), Dr. Budi Santoso (Gigi), Dr. Linda Kusuma (Kulit)
- **2 Staff Members**: Rina Lestari, Dimas Prasetyo
- **All staff have photos** from RandomUser API

### 🏠 Pet Owners (8 owners)
Complete profiles with:
- Full name and address
- Phone numbers and emails
- Registration dates
- Profile notes

### 🐾 Pets (18 animals with photos)
- **9 Dogs**: Golden Retriever, German Shepherd, Labrador, Rottweiler, Husky, Beagle, Poodle, Bulldog, Chihuahua
- **5 Cats**: Persian, British Shorthair, Siamese, Maine Coon, Ragdoll
- **2 Birds**: Love Bird, Cockatiel
- **2 Rabbits**: Holland Lop, Angora
- **All pets have photos** from Unsplash

### 📅 Appointments (25 appointments)
Distributed across timeline for graph data:
- **14 Completed** (October - November 2024)
- **10 Upcoming** (November 22-30, 2024)
- **1 Cancelled**
- Status mix: Completed, Confirmed, Scheduled, Cancelled

### 🏥 Medical Records (13 records)
Complete medical documentation with:
- Diagnoses and treatments
- Doctor notes
- Vital signs (weight, temperature)
- Visit types (Consultation, Examination, Vaccination)

### 💉 Vaccinations (27 vaccinations)
- **20 Completed** vaccinations
- **5 Scheduled** future vaccinations
- **2 Overdue** (for alert testing)
- Vaccine types: Rabies, Distemper, FVRCP
- Batch numbers and schedules

### 💊 Medicines (18 items)
Inventory with:
- Antibiotics (Amoxicillin, Ceftriaxone, Metronidazole)
- Vitamins (B Complex, Multivitamin, Calcium)
- Vaccines (Rabies, Distemper, FVRCP)
- Anti-parasitic drugs (Ivermectin, Flea & Tick)
- Medical supplies (Bandages, Syringes, Antiseptic)
- Stock levels, prices, expiry dates

### 📝 Prescriptions (7 prescriptions)
Linked to medical records with:
- Dosage and frequency
- Duration and instructions
- Pricing information

### 🛠️ Services (10 services)
- Consultations
- Vaccinations
- Grooming (Basic & Premium)
- Dental care
- Surgery
- Emergency treatment
- Lab tests
- Hospitalization

---

## 🚀 Installation

### Method 1: Using PHP Script (Recommended)

1. **Navigate to the import script in browser:**
   ```
   http://localhost/vetclinic/database/seeds/import_demo_data.php
   ```

2. **The script will automatically:**
   - Clear existing data
   - Import all dummy data
   - Verify the import
   - Show a summary

3. **You'll see a success message** with data counts and species distribution

### Method 2: Manual MySQL Import

1. **Open MySQL command line or phpMyAdmin**

2. **Run the SQL file:**
   ```bash
   mysql -u vetclinic_user -p vetclinic < database/seeds/complete_demo_data.sql
   ```

3. **Or in MySQL CLI:**
   ```sql
   USE vetclinic;
   SOURCE d:/HBB/tgs kuliah/SBD/vetclinic/database/seeds/complete_demo_data.sql;
   ```

### Method 3: Docker Container

```bash
docker exec -i vetclinic-db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/seeds/complete_demo_data.sql
```

---

## 🔐 Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: admin@vetclinic.com

### Other Staff Accounts (all use password: admin123)
- **Dr. Sarah**: `sarah.vet` / admin123
- **Dr. Budi**: `budi.vet` / admin123
- **Dr. Linda**: `linda.vet` / admin123
- **Rina**: `rina.staff` / admin123
- **Dimas**: `dimas.staff` / admin123

---

## 📊 Graph Data Verification

After import, verify graphs are working:

### 1. Dashboard Appointment Timeline
```sql
-- Should show appointments from October to November
SELECT DATE(tanggal_appointment) as date, COUNT(*) as count 
FROM appointment 
WHERE status = 'Completed' 
GROUP BY DATE(tanggal_appointment) 
ORDER BY date;
```

### 2. Species Distribution
```sql
-- Should show: 9 dogs, 5 cats, 2 birds, 2 rabbits
SELECT jenis, COUNT(*) as count 
FROM pet 
GROUP BY jenis;
```

### 3. Revenue by Service
```sql
-- Should show revenue from different services
SELECT s.nama_layanan, SUM(al.subtotal) as total_revenue
FROM appointment_layanan al
JOIN service s ON al.layanan_id = s.layanan_id
GROUP BY s.nama_layanan
ORDER BY total_revenue DESC;
```

### 4. Appointment Status Distribution
```sql
-- Should show mix of statuses
SELECT status, COUNT(*) as count 
FROM appointment 
GROUP BY status;
```

### 5. Vaccination Schedule
```sql
-- Should show completed, scheduled, and overdue
SELECT status, COUNT(*) as count 
FROM vaksinasi 
GROUP BY status;
```

---

## 🖼️ Photo Sources

### Staff Photos
- **Source**: RandomUser API
- **Example**: `https://randomuser.me/api/portraits/men/32.jpg`
- **Usage**: Veterinarian profile pictures
- **Quality**: 400x400px professional portraits

### Pet Photos
- **Source**: Unsplash
- **Example**: `https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400`
- **Usage**: Pet profile pictures in listings and details
- **Quality**: High-resolution pet photography
- **Species**: Dogs, cats, birds, rabbits

---

## ✅ Features to Test

### Dashboard
- [x] Appointment count widgets (today, week, month)
- [x] Revenue statistics
- [x] Pet count by species
- [x] Upcoming appointments list
- [x] Timeline graph (past 30 days)
- [x] Species distribution pie chart

### Pets Management
- [x] Pet listing with photos
- [x] Species filter (9 dogs, 5 cats, 2 birds, 2 rabbits)
- [x] Pet detail with owner info
- [x] Medical history timeline
- [x] Vaccination schedule

### Appointments
- [x] Calendar view with multiple appointments
- [x] Status filtering (Completed, Confirmed, Scheduled, Cancelled)
- [x] Today's appointments
- [x] Upcoming appointments
- [x] Appointment details with services

### Medical Records
- [x] Complete medical history
- [x] Diagnoses and treatments
- [x] Prescriptions with medicines
- [x] Doctor notes
- [x] Vital signs tracking

### Vaccinations
- [x] Vaccination history
- [x] Upcoming schedules
- [x] Overdue alerts (2 overdue for testing)
- [x] Batch tracking

### Inventory
- [x] Medicine stock levels
- [x] Low stock warnings
- [x] Expiry date tracking
- [x] Price management

### Owners
- [x] Owner profiles
- [x] Multiple pets per owner
- [x] Contact information
- [x] Pet listing

---

## 🔄 Data Relationships

```
users (6) ← veterinarian (4)
           ↓
owner (8) → pet (18) → appointment (25) → appointment_layanan
           ↓            ↓                  ↓
           └────────────→ medical_record (13) → resep (7) → medicine (18)
                        ↓
                        vaksinasi (27)
```

---

## 📈 Statistics Summary

| Entity | Count | Notes |
|--------|-------|-------|
| Users | 6 | 1 Admin, 3 Doctors, 2 Staff |
| Veterinarians | 4 | With specializations |
| Pet Owners | 8 | Complete profiles |
| Pets | 18 | 9 dogs, 5 cats, 2 birds, 2 rabbits |
| Appointments | 25 | 14 completed, 10 upcoming, 1 cancelled |
| Medical Records | 13 | Linked to appointments |
| Vaccinations | 27 | 20 completed, 5 scheduled, 2 overdue |
| Medicines | 18 | Various categories with stock |
| Prescriptions | 7 | Linked to medical records |
| Services | 10 | All service types |

---

## 🐛 Troubleshooting

### Import fails with "Table doesn't exist"
**Solution**: Run the main schema first:
```bash
mysql -u vetclinic_user -p vetclinic < database/vetclinic.sql
```

### Foreign key constraint errors
**Solution**: The script disables foreign key checks. If manual import fails:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Run import
SET FOREIGN_KEY_CHECKS = 1;
```

### Photos not showing
**Solution**: 
- Check internet connection (photos loaded from external URLs)
- RandomUser: `https://randomuser.me/api/portraits/`
- Unsplash: `https://images.unsplash.com/`

### Graphs not displaying data
**Solution**: Verify appointments exist with date range:
```sql
SELECT MIN(tanggal_appointment), MAX(tanggal_appointment) 
FROM appointment;
```

### Character encoding issues
**Solution**: Database uses UTF-8. Check database charset:
```sql
SHOW CREATE DATABASE vetclinic;
-- Should show: CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
```

---

## 🔧 Customization

### Adjust Appointment Dates

To shift all appointments to current date range:
```sql
-- Update appointments to current month
UPDATE appointment 
SET tanggal_appointment = DATE_ADD(CURDATE(), INTERVAL (appointment_id - 15) DAY)
WHERE appointment_id > 0;
```

### Add More Pets

Insert additional pets following the pattern:
```sql
INSERT INTO pet (owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, foto_url, status) 
VALUES (1, 'NewPet', 'Anjing', 'Breed', 'Jantan', '2022-01-01', 10.0, 'Color', 'photo_url', 'Aktif');
```

### Change Staff Photos

Update veterinarian photos:
```sql
UPDATE veterinarian 
SET foto_url = 'https://randomuser.me/api/portraits/men/50.jpg' 
WHERE dokter_id = 1;
```

---

## 📚 Additional Resources

- **RandomUser API**: https://randomuser.me/
- **Unsplash Source**: https://source.unsplash.com/
- **VetClinic Documentation**: See main README.md
- **Database Schema**: See database/vetclinic.sql

---

## 📞 Support

If you encounter issues:

1. Check MySQL error logs
2. Verify database connection in `config/database.php`
3. Ensure all tables exist (run schema first)
4. Check file permissions for PHP script
5. Review browser console for JavaScript errors

---

## 📝 License

This dummy data is for **demonstration purposes only**. 

- Staff photos from RandomUser API (Free to use)
- Pet photos from Unsplash (Free to use with attribution)
- All names and data are fictional

---

**Generated**: November 22, 2024  
**Version**: 1.0  
**Author**: VetClinic Development Team

---

## ✨ Quick Start Checklist

- [ ] Run schema: `database/vetclinic.sql`
- [ ] Import demo data: `database/seeds/import_demo_data.php`
- [ ] Login with admin/admin123
- [ ] Check dashboard graphs
- [ ] Browse pets with photos
- [ ] View appointment timeline
- [ ] Test all CRUD features
- [ ] Verify vaccination alerts

**Ready for demo! 🎉**
