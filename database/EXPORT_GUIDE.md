# Database Export Guide - XAMPP to GitHub

This guide will help you export your local XAMPP database data and share it on GitHub.

## 📋 Overview

You need to export data from your **local XAMPP database** to `.sql` files that can be:
1. Stored in your GitHub repository
2. Used by collaborators to set up the same database
3. Imported into Docker or other environments

## 🎯 Export Methods

### Method 1: Using phpMyAdmin (Easiest)

This is the **recommended method** for XAMPP users.

#### Steps:

1. **Open phpMyAdmin**
   - Go to: `http://localhost/phpmyadmin`
   - Login with your MySQL credentials (usually `root` with no password)

2. **Select the `vetclinic` database**
   - Click on `vetclinic` in the left sidebar

3. **Export the Database**
   - Click on the **"Export"** tab at the top
   - Choose **"Custom"** export method (not Quick)

4. **Configure Export Settings**
   - **Format**: SQL
   - **Tables**: Select all tables
   - **Output**: Save output to file
   - **Format-specific options**:
     - ✅ Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER statement
     - ✅ Add CREATE TABLE statement
     - ✅ Enclose export in a transaction
     - ⚠️ Disable foreign key checks
   - **Data dump options**:
     - ✅ INSERT
     - Column names: ✅ (checked)
     - Insert syntax: `INSERT INTO` (or `REPLACE INTO` to avoid duplicates)
     - Max length: 1000 rows

5. **Download the File**
   - Click "Go" button
   - Save as: `database/exports/vetclinic_full_backup_YYYY-MM-DD.sql`

---

### Method 2: Using Command Line (Advanced)

#### Export Full Database (Structure + Data)

```bash
# Navigate to XAMPP MySQL bin directory
cd C:\xampp\mysql\bin

# Export complete database
mysqldump -u root -p vetclinic > "D:\HBB\tgs kuliah\SBD\vetclinic\database\exports\vetclinic_full.sql"
```

#### Export Only Structure (No Data)

```bash
mysqldump -u root -p --no-data vetclinic > "D:\HBB\tgs kuliah\SBD\vetclinic\database\exports\vetclinic_structure.sql"
```

#### Export Only Data (No Structure)

```bash
mysqldump -u root -p --no-create-info vetclinic > "D:\HBB\tgs kuliah\SBD\vetclinic\database\exports\vetclinic_data_only.sql"
```

#### Export Specific Tables

```bash
mysqldump -u root -p vetclinic owner pet veterinarian > "D:\HBB\tgs kuliah\SBD\vetclinic\database\exports\vetclinic_core_tables.sql"
```

---

## 📦 Recommended Export Structure

Create these files for your GitHub repository:

### 1. **Schema Only** (`schema.sql`)
- Database structure without data
- Use for initial setup

```bash
mysqldump -u root -p --no-data vetclinic > "D:\HBB\tgs kuliah\SBD\vetclinic\database\schema.sql"
```

### 2. **Sample Data** (`sample_data.sql`)
- Small dataset for testing
- 2-3 records per table

### 3. **Full Data Export** (`vetclinic_backup.sql`)
- Complete backup with all data
- For restoration purposes

### 4. **Seed Data** (`seeds/production_data.sql`)
- Essential data for production
- Users, services, initial settings

---

## 🚀 Using PowerShell Scripts

I'll create automated scripts for you to use.

---

## ✅ After Export

### Verify the Export

1. **Check file size** - Should not be 0 bytes
2. **Open in text editor** - Should contain SQL statements
3. **Test import** in a fresh database:

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE vetclinic_test"

# Import the exported file
mysql -u root -p vetclinic_test < "database/exports/vetclinic_full.sql"

# Verify
mysql -u root -p vetclinic_test -e "SHOW TABLES;"
```

---

## 📤 Pushing to GitHub

### Add to Git

```bash
# Add the exported files
git add database/exports/
git add database/schema.sql
git add database/sample_data.sql

# Commit
git commit -m "Add database exports with sample data"

# Push to GitHub
git push origin main
```

### Update .gitignore (Optional)

If you don't want to track full backups:

```gitignore
# Ignore full database dumps
database/exports/vetclinic_full_backup_*.sql

# Keep schema and sample data
!database/schema.sql
!database/sample_data.sql
!database/seeds/*.sql
```

---

## 🔄 Import Guide for Collaborators

Once on GitHub, collaborators can import using:

### Using Docker (Current Setup)

```bash
# Copy SQL file to Docker container
docker cp database/exports/vetclinic_full.sql vetclinic_db:/tmp/

# Execute import
docker exec -i vetclinic_db mysql -uvetclinic_user -pvetclinic_password vetclinic < /tmp/vetclinic_full.sql
```

### Using XAMPP

```bash
mysql -u root vetclinic < database/exports/vetclinic_full.sql
```

### Using phpMyAdmin

1. Open phpMyAdmin
2. Select `vetclinic` database
3. Click "Import" tab
4. Choose file
5. Click "Go"

---

## 🛠️ Troubleshooting

### Error: "Table already exists"

**Solution**: Export with DROP TABLE statements enabled, or drop tables before import:

```sql
DROP DATABASE IF EXISTS vetclinic;
CREATE DATABASE vetclinic;
USE vetclinic;
```

### Error: "Access denied"

**Solution**: Check MySQL user permissions:

```sql
GRANT ALL PRIVILEGES ON vetclinic.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Large File Size

**Solution**: Compress the export:

```bash
# Export and compress
mysqldump -u root -p vetclinic | gzip > vetclinic_backup.sql.gz

# Import from compressed
gunzip < vetclinic_backup.sql.gz | mysql -u root -p vetclinic
```

---

## 📋 Checklist

- [ ] Export full database backup
- [ ] Export schema only (structure)
- [ ] Export sample/test data
- [ ] Test import in fresh database
- [ ] Verify all tables and data
- [ ] Update README.md with import instructions
- [ ] Add to Git and commit
- [ ] Push to GitHub
- [ ] Test collaborator can import

---

## 📚 Additional Resources

- [MySQL Documentation - mysqldump](https://dev.mysql.com/doc/refman/8.0/en/mysqldump.html)
- [phpMyAdmin Documentation](https://docs.phpmyadmin.net/)
