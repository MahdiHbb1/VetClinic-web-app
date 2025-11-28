# Database Import Guide

This guide helps collaborators import the VetClinic database from the SQL files in this repository.

## 📋 Available SQL Files

| File | Purpose | Size | When to Use |
|------|---------|------|-------------|
| `vetclinic.sql` | Complete schema + basic sample data | ~8KB | **First time setup** |
| `test_data.sql` | Comprehensive test data | ~12KB | Development & testing |
| `schema.sql` | Database structure only | ~5KB | Empty database setup |
| `seeds/*.sql` | Various seed data sets | Varies | Specific scenarios |

## 🚀 Import Methods

### Method 1: Docker (Recommended for this project)

If you're using the Docker setup from this repository:

#### Initial Setup

```bash
# Start Docker containers
docker-compose up -d

# Wait for database to be ready (30 seconds)
timeout 30

# Import schema
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/vetclinic.sql

# Import test data (optional)
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/test_data.sql
```

#### Using PowerShell (Windows)

```powershell
# Import schema
Get-Content "database\vetclinic.sql" | docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic

# Import test data
Get-Content "database\test_data.sql" | docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic
```

#### Reset Database

```bash
# Stop and remove volumes
docker-compose down -v

# Start fresh
docker-compose up -d

# Import data
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/vetclinic.sql
```

---

### Method 2: XAMPP (Local Development)

#### Using Command Line

```bash
# Navigate to MySQL bin
cd C:\xampp\mysql\bin

# Import database
mysql -u root -p vetclinic < "D:\path\to\vetclinic\database\vetclinic.sql"

# Or import test data
mysql -u root -p vetclinic < "D:\path\to\vetclinic\database\test_data.sql"
```

#### Using phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database (if not exists):
   - Click "New" in the sidebar
   - Database name: `vetclinic`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"
3. Select `vetclinic` database
4. Click "Import" tab
5. Click "Choose File"
6. Select `database/vetclinic.sql`
7. Click "Go"
8. Wait for import to complete

---

### Method 3: MySQL Command Line

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS vetclinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Import schema
mysql -u root -p vetclinic < database/vetclinic.sql

# Import test data (optional)
mysql -u root -p vetclinic < database/test_data.sql
```

---

## 📊 Verify Import

### Check Tables

```bash
# Docker
docker exec -it vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic -e "SHOW TABLES;"

# XAMPP
mysql -u root -p vetclinic -e "SHOW TABLES;"
```

Expected output (11 tables):
```
+---------------------+
| Tables_in_vetclinic |
+---------------------+
| appointment         |
| appointment_layanan |
| medical_record      |
| medicine            |
| owner               |
| pet                 |
| resep               |
| service             |
| users               |
| vaksinasi           |
| veterinarian        |
+---------------------+
```

### Check Data Counts

```sql
SELECT 
    (SELECT COUNT(*) FROM users) as users,
    (SELECT COUNT(*) FROM owner) as owners,
    (SELECT COUNT(*) FROM pet) as pets,
    (SELECT COUNT(*) FROM veterinarian) as doctors,
    (SELECT COUNT(*) FROM appointment) as appointments,
    (SELECT COUNT(*) FROM service) as services,
    (SELECT COUNT(*) FROM medicine) as medicines;
```

### Test Login

After importing, test the default admin user:
- URL: `http://localhost:8080` (Docker) or `http://localhost/vetclinic` (XAMPP)
- Username: `admin`
- Password: `admin123`

---

## 🔧 Troubleshooting

### Error: "Database doesn't exist"

**Solution**: Create database first

```sql
CREATE DATABASE IF NOT EXISTS vetclinic 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### Error: "Table already exists"

**Solution**: Drop and recreate

```sql
DROP DATABASE IF EXISTS vetclinic;
CREATE DATABASE vetclinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vetclinic;
-- Then import SQL file
```

### Error: "Access denied"

**Solution**: Check user permissions

```sql
-- For Docker
GRANT ALL PRIVILEGES ON vetclinic.* TO 'vetclinic_user'@'%';

-- For XAMPP
GRANT ALL PRIVILEGES ON vetclinic.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Unknown database"

**Solution**: Ensure you're connected to the correct database

```bash
# Check current database
mysql -u root -p -e "SHOW DATABASES;"

# Create if missing
mysql -u root -p -e "CREATE DATABASE vetclinic;"
```

### Error: Foreign key constraint fails

**Solution**: Import in correct order or disable foreign key checks

```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Import SQL file here
SET FOREIGN_KEY_CHECKS = 1;
```

---

## 📝 Import Order for Individual Files

If importing individual tables, follow this order to avoid foreign key issues:

1. **users** (no dependencies)
2. **owner** (no dependencies)
3. **veterinarian** (no dependencies)
4. **service** (no dependencies)
5. **medicine** (no dependencies)
6. **pet** (depends on: owner)
7. **appointment** (depends on: pet, owner, veterinarian)
8. **medical_record** (depends on: pet, veterinarian, appointment)
9. **resep** (depends on: medical_record, medicine)
10. **vaksinasi** (depends on: pet, veterinarian)
11. **appointment_layanan** (depends on: appointment, service)

---

## 🎯 Common Scenarios

### Scenario 1: Fresh Setup for Development

```bash
# Clone repository
git clone <your-repo-url>
cd vetclinic

# Start Docker
docker-compose up -d

# Import with test data
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/vetclinic.sql
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/test_data.sql

# Access application
# http://localhost:8080
```

### Scenario 2: Reset Database During Development

```bash
# Quick reset
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password -e "DROP DATABASE IF EXISTS vetclinic; CREATE DATABASE vetclinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Reimport
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/vetclinic.sql
```

### Scenario 3: Production Deployment

```bash
# Use deployment-ready file
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < database/vetclinic_deployment.sql

# Verify
docker exec -it vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic -e "SELECT COUNT(*) FROM users;"
```

---

## ✅ Post-Import Checklist

- [ ] Database `vetclinic` exists
- [ ] All 11 tables are created
- [ ] Admin user exists (username: `admin`)
- [ ] Can login to the application
- [ ] Sample data is loaded (if using test_data.sql)
- [ ] No SQL errors in import log

---

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Project README](../README.md)
