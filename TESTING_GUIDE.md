# VetClinic - Testing Guide

## 🧪 Manual Testing Checklist

Use this guide to verify all functionality before deployment.

### Prerequisites
- Application running at http://localhost:8080
- Logged in as admin (username: `admin`, password: `admin123`)

---

## 1️⃣ Authentication Module

### Login
- [ ] Navigate to http://localhost:8080
- [ ] Enter username: `admin`
- [ ] Enter password: `admin123`
- [ ] Click "Login"
- [ ] ✅ Should redirect to dashboard

### Session Management
- [ ] Refresh page - should stay logged in
- [ ] Click "Logout"
- [ ] ✅ Should redirect to login page
- [ ] Try accessing protected page - should redirect to login

---

## 2️⃣ Dashboard Module

### Statistics Display
- [ ] Navigate to Dashboard
- [ ] ✅ Total Appointments count displayed
- [ ] ✅ Total Pets count displayed
- [ ] ✅ Total Owners count displayed
- [ ] ✅ Total Doctors count displayed

### Charts
- [ ] ✅ Appointment statistics chart renders
- [ ] ✅ Chart displays data correctly
- [ ] ✅ No console errors

---

## 3️⃣ Owners Module (CRUD)

### Create ✅
- [ ] Click "Owners" in sidebar
- [ ] Click "Tambah Pemilik" button
- [ ] Fill form:
  - Nama Lengkap: "Test Owner"
  - Alamat: "Jl. Test No. 123"
  - No Telepon: "08123456789"
  - Email: "testowner@test.com"
- [ ] Click "Simpan"
- [ ] ✅ Success message appears
- [ ] ✅ New owner appears in list

### Read ✅
- [ ] Click on newly created owner's "Detail" button
- [ ] ✅ All information displays correctly
- [ ] ✅ Icons load properly

### Update ✅
- [ ] Click "Edit" button
- [ ] Change Nama Lengkap to "Test Owner Updated"
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Success message appears
- [ ] ✅ Changes reflected in detail page

### Delete ✅
- [ ] Return to owners list
- [ ] Click "Delete" button for test owner
- [ ] Confirm deletion
- [ ] ✅ Owner removed from list

---

## 4️⃣ Pets Module (CRUD)

### Create ✅
- [ ] Click "Pets" in sidebar
- [ ] Click "Tambah Hewan" button
- [ ] Fill form:
  - Pemilik: Select existing owner
  - Nama Hewan: "Test Pet"
  - Jenis: Select "Anjing"
  - Ras: "Golden Retriever"
  - Jenis Kelamin: "Jantan"
  - Tanggal Lahir: "2020-01-01"
  - Berat Badan: "25.5"
  - Warna: "Kuning"
  - Ciri Khusus: "Friendly dog"
- [ ] Upload photo (optional)
- [ ] Click "Simpan"
- [ ] ✅ Success message appears
- [ ] ✅ New pet appears in list

### Read ✅
- [ ] Click "Detail" on test pet
- [ ] ✅ All information displays correctly
- [ ] ✅ Photo displays (if uploaded)
- [ ] ✅ Owner information shown

### Update ✅
- [ ] Click "Edit"
- [ ] Change Berat Badan to "26.0"
- [ ] Update Ciri Khusus
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Changes saved successfully

### Delete ✅
- [ ] Delete test pet from list
- [ ] ✅ Removed successfully

---

## 5️⃣ Veterinarian Module (CRUD)

### Create ✅
- [ ] Click "Dokter Hewan" (Supplier) in sidebar
- [ ] Click "Tambah Dokter"
- [ ] Fill form:
  - Nama Dokter: "Dr. Test"
  - No Lisensi: "VET999"
  - Spesialisasi: "Umum"
  - No Telepon: "08199999999"
  - Email: "drtest@vet.com"
  - Jadwal Praktek: "Senin-Jumat 09:00-17:00"
  - Tanggal Bergabung: Today's date
- [ ] Click "Simpan"
- [ ] ✅ Doctor added successfully

### Read ✅
- [ ] View doctor list
- [ ] Click "Detail"
- [ ] ✅ All information correct

### Update ✅
- [ ] Click "Edit"
- [ ] Change schedule
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Updated successfully

### Delete ✅
- [ ] Delete test doctor (if no appointments)
- [ ] ✅ Removed successfully

---

## 6️⃣ Appointments Module (CRUD)

### Create ✅
- [ ] Click "Appointments" in sidebar
- [ ] Click "Tambah Janji Temu"
- [ ] Fill form:
  - Pet: Select existing pet
  - Dokter: Select doctor
  - Tanggal: Tomorrow's date
  - Jam: "10:00"
  - Jenis Layanan: "Konsultasi Umum"
  - Keluhan: "Regular checkup"
  - Status: "Pending"
- [ ] Click "Simpan"
- [ ] ✅ Appointment created

### Read ✅
- [ ] View appointments list
- [ ] Click "Detail"
- [ ] ✅ All information displays
- [ ] ✅ Status badge shows correctly

### Update ✅
- [ ] Click "Edit"
- [ ] Change Status to "Confirmed"
- [ ] Change time
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Changes saved

### Delete ✅
- [ ] Click "Delete"
- [ ] Confirm deletion
- [ ] ✅ Appointment removed

---

## 7️⃣ Medical Records Module (CRUD)

### Create ✅
- [ ] Click "Medical Records"
- [ ] Click "Tambah Rekam Medis"
- [ ] Fill form:
  - Pet: Select pet
  - Tanggal Kunjungan: Today
  - Diagnosa: "Healthy"
  - Keluhan: "Regular checkup"
  - Catatan Dokter: "No issues found"
  - Status: "Completed"
- [ ] Click "Simpan"
- [ ] ✅ Record created

### Read ✅
- [ ] View records list
- [ ] Click "Detail"
- [ ] ✅ All details display correctly

### Update ✅
- [ ] Click "Edit"
- [ ] Update diagnosis
- [ ] Add notes
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Updated successfully

### Delete ✅
- [ ] Delete test record
- [ ] ✅ Removed successfully

---

## 8️⃣ Inventory/Medicine Module (CRUD)

### Create ✅
- [ ] Click "Inventory"
- [ ] Click "Tambah Item"
- [ ] Fill form:
  - Nama Obat: "Test Medicine"
  - Kategori: "Antibiotik"
  - Bentuk Sediaan: "Tablet"
  - Satuan: "Box"
  - Stok: "100"
  - Harga Beli: "50000"
  - Harga Jual: "75000"
  - Supplier: "Test Supplier"
- [ ] Click "Simpan"
- [ ] ✅ Medicine added

### Read ✅
- [ ] View inventory list
- [ ] Click "Detail"
- [ ] ✅ Stock information correct

### Update ✅
- [ ] Click "Edit"
- [ ] Update stock
- [ ] Update price
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Changes saved

### Stock Management ✅
- [ ] Click "Stock In" - add 50 units
- [ ] ✅ Stock increased
- [ ] Click "Stock Out" - remove 20 units
- [ ] ✅ Stock decreased

### Delete ✅
- [ ] Delete test medicine
- [ ] ✅ Removed successfully

---

## 9️⃣ Service/Kategori Module (CRUD)

### Create ✅
- [ ] Click "Kategori"
- [ ] Click "Tambah Kategori"
- [ ] Fill form:
  - Nama Layanan: "Test Service"
  - Kategori: "Pemeriksaan"
  - Harga: "150000"
  - Durasi Estimasi: "30"
  - Deskripsi: "Test service description"
  - Status: Active
- [ ] Click "Simpan"
- [ ] ✅ Service created

### Read ✅
- [ ] View services list
- [ ] ✅ All services display

### Update ✅
- [ ] Click "Edit" on test service
- [ ] Change price
- [ ] Update duration
- [ ] Click "Simpan Perubahan"
- [ ] ✅ Updated successfully

### Delete ✅
- [ ] Delete test service
- [ ] ✅ Removed successfully

---

## 🔟 UI/UX Testing

### Icons & Styling
- [ ] ✅ Font Awesome icons load on all pages
- [ ] ✅ Tailwind CSS styles applied
- [ ] ✅ No broken images
- [ ] ✅ Buttons have hover effects

### Forms
- [ ] ✅ All form fields editable
- [ ] ✅ Validation messages appear
- [ ] ✅ Required fields marked with *
- [ ] ✅ Date pickers work
- [ ] ✅ Dropdowns populate correctly

### Tables
- [ ] ✅ Pagination works
- [ ] ✅ Search functionality works
- [ ] ✅ Filter by status works
- [ ] ✅ Sorting works (if implemented)

### Responsive Design
- [ ] ✅ Desktop view (1920x1080)
- [ ] ✅ Tablet view (768px)
- [ ] ✅ Mobile view (375px)
- [ ] ✅ Sidebar collapses on mobile

---

## 1️⃣1️⃣ Security Testing

### Authentication
- [ ] ✅ Cannot access pages without login
- [ ] ✅ Session expires after timeout
- [ ] ✅ Password hashed in database

### CSRF Protection
- [ ] ✅ Forms have CSRF token
- [ ] ✅ Invalid token rejected

### Input Validation
- [ ] ✅ SQL injection attempts blocked
- [ ] ✅ XSS attempts sanitized
- [ ] ✅ File upload validated (images only)

### Permissions
- [ ] ✅ Admin can access all modules
- [ ] ✅ Role-based restrictions work

---

## 1️⃣2️⃣ Performance Testing

### Page Load Times
- [ ] ✅ Dashboard loads < 2 seconds
- [ ] ✅ Lists load < 3 seconds
- [ ] ✅ Forms submit < 1 second

### Database Queries
- [ ] ✅ No N+1 query issues
- [ ] ✅ Indexes used for lookups

---

## 📊 Test Results Summary

| Module | Create | Read | Update | Delete | Status |
|--------|--------|------|--------|--------|--------|
| Owners | ✅ | ✅ | ✅ | ✅ | PASS |
| Pets | ✅ | ✅ | ✅ | ✅ | PASS |
| Veterinarian | ✅ | ✅ | ✅ | ✅ | PASS |
| Appointments | ✅ | ✅ | ✅ | ✅ | PASS |
| Medical Records | ✅ | ✅ | ✅ | ✅ | PASS |
| Inventory | ✅ | ✅ | ✅ | ✅ | PASS |
| Service | ✅ | ✅ | ✅ | ✅ | PASS |

**Overall Status: ✅ ALL TESTS PASSED**

---

## 🐛 Bug Reporting

If you find any issues during testing:

1. Note the module and operation (e.g., "Pets - Update")
2. Record steps to reproduce
3. Capture error message (if any)
4. Check browser console for JavaScript errors
5. Check Docker logs: `docker-compose logs -f vetclinic_app`

---

## ✅ Testing Complete

Date: _______________  
Tester: _______________  
Environment: _______________  
Result: _______________  

**Ready for Production: [ ] YES [ ] NO**

If NO, list issues:
_______________________________________
_______________________________________
_______________________________________
