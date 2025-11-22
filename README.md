# VetClinic - Sistem Manajemen Klinik Hewan

Aplikasi web untuk manajemen klinik hewan berbasis PHP dengan Docker.

## Fitur Utama

- 🏥 **Manajemen Pasien**: Kelola data pemilik hewan dan hewan peliharaan
- 📅 **Janji Temu**: Sistem penjadwalan appointment dengan dokter hewan
- 📋 **Rekam Medis**: Pencatatan lengkap riwayat kesehatan hewan
- 💊 **Obat-obatan**: Manajemen inventori obat dan supplies
- 💉 **Vaksinasi**: Tracking jadwal vaksinasi
- 👨‍⚕️ **Dokter Hewan**: Manajemen data dokter dan jadwal praktik
- 🏪 **Layanan**: Daftar layanan klinik (pemeriksaan, grooming, bedah, dll)
- 📊 **Laporan**: Dashboard dan statistik klinik
- 👥 **Manajemen User**: Multi-role access (Admin, Dokter, Staff)

## Tech Stack

- **Backend**: PHP 8.1-FPM
- **Database**: MySQL 8.0
- **Web Server**: Nginx Alpine
- **Frontend**: 
  - Tailwind CSS 2.2.19
  - jQuery 3.6
  - DataTables 1.13.4
  - Chart.js 3.9.1
  - SweetAlert2
  - Font Awesome 6.4

## Instalasi & Setup

### Prerequisites
- Docker & Docker Compose
- Git

### Quick Start

1. **Clone repository**
```bash
git clone <your-repo-url>
cd vetclinic
```

2. **Start Docker containers**
```bash
docker-compose up -d
```

3. **Akses aplikasi**
- URL: http://localhost:8080
- Default Login:
  - Username: `admin`
  - Password: `admin123`

### Database Setup

Database akan otomatis dibuat saat container pertama kali dijalankan. File SQL ada di folder `database/`.

Untuk import manual:
```bash
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/vetclinic.sql
```

## Struktur Direktori

```
vetclinic/
├── api/                    # API endpoints
├── appointments/           # Modul janji temu
├── assets/                 # CSS, JS, images
│   ├── css/
│   └── js/
├── auth/                   # Autentikasi & login
├── config/                 # Konfigurasi database
├── dashboard/              # Dashboard utama
├── database/              # SQL files
├── includes/              # Header, footer, functions
├── inventory/             # Manajemen obat-obatan
├── kategori/              # Layanan klinik
├── medical-records/       # Rekam medis
├── owners/                # Data pemilik hewan
├── pets/                  # Data hewan
├── reports/               # Laporan & statistik
├── supplier/              # Data dokter hewan
├── users/                 # Manajemen user
├── vaccinations/          # Vaksinasi
├── docker/                # Docker configs
├── docker-compose.yml
└── Dockerfile
```

## Docker Services

- **vetclinic_app**: PHP 8.1-FPM application
- **vetclinic_webserver**: Nginx web server (port 8080)
- **vetclinic_db**: MySQL 8.0 database

## Database Schema

Tabel utama:
- `users` - User sistem
- `owner` - Pemilik hewan
- `pet` - Data hewan peliharaan
- `veterinarian` - Dokter hewan
- `appointment` - Janji temu
- `medical_record` - Rekam medis
- `medicine` - Obat-obatan
- `resep` - Resep obat
- `vaksinasi` - Vaksinasi
- `service` - Layanan klinik

## Fitur Keamanan

- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ Rate limiting
- ✅ Prepared statements (SQL injection prevention)
- ✅ Session management
- ✅ Content Security Policy headers
- ✅ XSS protection
- ✅ File upload validation (MIME type checking)
- ✅ Input sanitization

## Recent Updates (November 2025)

### ✅ CRUD Operations - All Fixed & Verified
All Create, Read, Update, Delete operations have been thoroughly tested and aligned with the database schema:

- **Medical Records**: Fixed column names (tanggal_kunjungan, diagnosa, keluhan, catatan_dokter)
- **Inventory/Medicine**: Updated to use `medicine` table (obat_id, nama_obat, stok)
- **Pets**: Fixed ciri_khusus, berat_badan fields, added photo upload support
- **Appointments**: Complete overhaul - fixed all column names (tanggal_appointment, jam_appointment, jenis_layanan, keluhan_awal)
- **Veterinarian**: Proper table mapping with correct columns
- **Service**: Category management aligned with service table
- **Owners**: Validated all CRUD operations

### 🔧 Bug Fixes
- Font Awesome icons now load correctly (CSP headers fixed)
- Removed references to non-existent tables (appointment_history, system_log)
- Simplified operations by removing over-engineered features
- Added missing function includes
- Updated JavaScript element references

## Development

### Stop containers
```bash
docker-compose down
```

### Restart containers
```bash
docker-compose restart
```

### View logs
```bash
docker-compose logs -f
```

### Access database
```bash
docker exec -it vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic
```

## Testing

File testing tersedia di `database/run_tests.sql`:
```bash
Get-Content "database\run_tests.sql" | docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic
```

## Troubleshooting

### Port 8080 sudah digunakan
Edit `docker-compose.yml`, ubah port mapping:
```yaml
ports:
  - "8081:80"  # Ganti 8080 ke 8081
```

### Reset database
```bash
docker-compose down -v
docker-compose up -d
```

## Credits

Dikembangkan untuk tugas kuliah Sistem Basis Data.

## License

MIT License - Silakan digunakan untuk keperluan edukasi.
