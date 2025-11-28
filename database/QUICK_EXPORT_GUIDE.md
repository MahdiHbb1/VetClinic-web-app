# Quick Export Guide - XAMPP to GitHub

## ⚡ **The Fastest Way to Export**

### Option 1: Using PowerShell Script (RECOMMENDED)

```powershell
# Navigate to database directory
cd "D:\HBB\tgs kuliah\SBD\vetclinic\database"

# Run the export script
.\export_xampp.ps1
```

**What it does:**
- ✅ Exports full database with all data
- ✅ Exports schema only (structure)
- ✅ Exports data only
- ✅ Exports each table individually
- ✅ Creates timestamped files

---

### Option 2: Using phpMyAdmin (Visual)

1. **Open** `http://localhost/phpmyadmin`
2. **Click** on `vetclinic` database (left sidebar)
3. **Click** "Export" tab
4. **Select** "Custom" method
5. **Configure**:
   - Format: SQL
   - ✅ Add DROP TABLE
   - ✅ Add CREATE TABLE
   - ✅ Complete INSERTs
6. **Click** "Go"
7. **Save** to `database/exports/vetclinic_export.sql`

---

### Option 3: Quick Command Line

```bash
cd C:\xampp\mysql\bin
mysqldump -u root -p vetclinic > "D:\HBB\tgs kuliah\SBD\vetclinic\database\exports\vetclinic_full.sql"
```

---

## 🚀 After Export - Push to GitHub

```bash
# Navigate to project directory
cd "D:\HBB\tgs kuliah\SBD\vetclinic"

# Check status
git status

# Add exported files
git add database/exports/.gitkeep
git add database/EXPORT_GUIDE.md
git add database/IMPORT_GUIDE.md

# If you want to include a specific export (optional)
git add database/exports/vetclinic_full_YYYYMMDD.sql

# Commit
git commit -m "Add database export guides and scripts"

# Push to GitHub
git push origin main
```

---

## 📋 What Gets Shared on GitHub

### Automatically Included (Already in Git):
- ✅ `database/vetclinic.sql` - Main schema + basic data
- ✅ `database/test_data.sql` - Test data
- ✅ `database/schema.sql` - Structure only (after first export)
- ✅ `database/EXPORT_GUIDE.md` - Full export instructions
- ✅ `database/IMPORT_GUIDE.md` - Import instructions for collaborators

### Excluded by .gitignore (Too Large):
- ❌ Timestamped full exports `vetclinic_full_*.sql`
- ❌ Individual table exports `table_*.sql`

### Optional - You Can Choose:
- ⚠️ Latest export `vetclinic_latest_export.sql`
- ⚠️ Specific snapshot for collaborators

---

## ✅ Verification Checklist

Before pushing to GitHub:

- [ ] Export completed successfully
- [ ] Files exist in `database/exports/`
- [ ] File sizes look reasonable (not 0 bytes)
- [ ] Tested import in fresh database (optional but recommended)
- [ ] Added to Git
- [ ] Committed with descriptive message
- [ ] Pushed to GitHub
- [ ] Collaborators can see the files

---

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| "mysqldump not found" | Use full path: `C:\xampp\mysql\bin\mysqldump.exe` |
| "Access denied" | Check password or use `-p` flag |
| "Database doesn't exist" | Verify database name is `vetclinic` |
| "File too large for Git" | Use .gitignore (already configured) |
| Script won't run | Right-click → "Run with PowerShell" |

---

## 📞 Need More Details?

- **Full Documentation**: See `EXPORT_GUIDE.md`
- **Import Instructions**: See `IMPORT_GUIDE.md`
- **Database Schema**: See `vetclinic.sql`
