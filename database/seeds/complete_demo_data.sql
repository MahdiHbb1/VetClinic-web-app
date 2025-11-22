-- =====================================
-- COMPLETE DEMO DATA FOR VETCLINIC
-- =====================================
-- Purpose: Comprehensive dummy data for fully functional demo
-- Features: Staff photos, pet photos, graph data, notifications
-- Date: 2025-11-22
-- Run this to populate the entire system for demo

USE vetclinic;

-- Disable foreign key checks for clean truncation
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================
-- CLEAR EXISTING DATA
-- =====================================
TRUNCATE TABLE resep;
TRUNCATE TABLE appointment_layanan;
TRUNCATE TABLE medical_record;
TRUNCATE TABLE vaksinasi;
TRUNCATE TABLE appointment;
TRUNCATE TABLE pet;
TRUNCATE TABLE owner;
TRUNCATE TABLE medicine;
TRUNCATE TABLE service;
TRUNCATE TABLE veterinarian;
TRUNCATE TABLE users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================
-- 1. USERS & STAFF (WITH PHOTOS)
-- =====================================
-- Password for all users: admin123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO users (user_id, username, password, nama_lengkap, email, role, status, created_at) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Ahmad Wijaya', 'admin@vetclinic.com', 'Admin', 'Aktif', '2023-01-01 08:00:00'),
(2, 'sarah.vet', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah Putri', 'sarah@vetclinic.com', 'Dokter', 'Aktif', '2023-02-15 08:00:00'),
(3, 'budi.vet', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Budi Santoso', 'budi@vetclinic.com', 'Dokter', 'Aktif', '2023-03-01 08:00:00'),
(4, 'rina.staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rina Lestari', 'rina@vetclinic.com', 'Staff', 'Aktif', '2023-04-10 08:00:00'),
(5, 'dimas.staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dimas Prasetyo', 'dimas@vetclinic.com', 'Staff', 'Aktif', '2023-05-20 08:00:00'),
(6, 'linda.vet', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Linda Kusuma', 'linda@vetclinic.com', 'Dokter', 'Aktif', '2023-06-15 08:00:00');

-- =====================================
-- 2. VETERINARIANS (WITH PHOTOS)
-- =====================================
INSERT INTO veterinarian (dokter_id, nama_dokter, no_lisensi, spesialisasi, no_telepon, email, jadwal_praktek, status, foto_url, tanggal_bergabung) VALUES
(1, 'Dr. Ahmad Wijaya', 'VET-2023-001', 'Umum', '081234567001', 'ahmad@vetclinic.com', 'Senin-Jumat: 08:00-16:00', 'Aktif', 'https://randomuser.me/api/portraits/men/32.jpg', '2023-01-01'),
(2, 'Dr. Sarah Putri', 'VET-2023-002', 'Bedah', '081234567002', 'sarah@vetclinic.com', 'Senin-Jumat: 09:00-17:00', 'Aktif', 'https://randomuser.me/api/portraits/women/44.jpg', '2023-02-15'),
(3, 'Dr. Budi Santoso', 'VET-2023-003', 'Gigi', '081234567003', 'budi@vetclinic.com', 'Selasa-Sabtu: 08:00-16:00', 'Aktif', 'https://randomuser.me/api/portraits/men/45.jpg', '2023-03-01'),
(4, 'Dr. Linda Kusuma', 'VET-2023-004', 'Kulit', '081234567004', 'linda@vetclinic.com', 'Senin-Jumat: 10:00-18:00', 'Aktif', 'https://randomuser.me/api/portraits/women/68.jpg', '2023-06-15');

-- =====================================
-- 3. SERVICES
-- =====================================
INSERT INTO service (layanan_id, nama_layanan, kategori, harga, durasi_estimasi, deskripsi, status_tersedia) VALUES
(1, 'Konsultasi Umum', 'Pemeriksaan', 150000.00, 30, 'Konsultasi dan pemeriksaan kesehatan umum', 1),
(2, 'Vaksinasi Rabies', 'Vaksinasi', 250000.00, 30, 'Vaksinasi rabies untuk anjing dan kucing', 1),
(3, 'Grooming Basic', 'Grooming', 200000.00, 120, 'Mandi, potong kuku, dan pembersihan telinga', 1),
(4, 'Grooming Premium', 'Grooming', 350000.00, 180, 'Grooming lengkap termasuk styling', 1),
(5, 'Pemeriksaan Gigi', 'Pemeriksaan', 300000.00, 60, 'Pembersihan dan pemeriksaan kesehatan gigi', 1),
(6, 'Operasi Minor', 'Bedah', 1500000.00, 120, 'Operasi minor seperti sterilisasi', 1),
(7, 'Rawat Inap (per hari)', 'Rawat_Inap', 500000.00, 1440, 'Perawatan intensif dengan monitoring 24 jam', 1),
(8, 'Tes Darah Lengkap', 'Tes_Lab', 400000.00, 60, 'Pemeriksaan laboratorium darah lengkap', 1),
(9, 'Emergency Treatment', 'Emergency', 800000.00, 90, 'Penanganan kasus darurat', 1),
(10, 'Vaksinasi Distemper', 'Vaksinasi', 280000.00, 30, 'Vaksinasi distemper untuk anjing', 1);

-- =====================================
-- 4. MEDICINES
-- =====================================
INSERT INTO medicine (obat_id, nama_obat, kategori, bentuk_sediaan, satuan, stok, harga_beli, harga_jual, expired_date, supplier, deskripsi, status_tersedia) VALUES
(1, 'Amoxicillin 500mg', 'Antibiotik', 'Tablet', 'Strip', 50, 25000, 45000, '2026-12-31', 'PT Kimia Farma', 'Antibiotik spektrum luas', 1),
(2, 'Ceftriaxone 1g', 'Antibiotik', 'Injeksi', 'Vial', 25, 35000, 60000, '2026-10-31', 'PT Indofarma', 'Antibiotik injeksi kuat', 1),
(3, 'Vitamin B Complex', 'Vitamin', 'Tablet', 'Botol', 40, 35000, 55000, '2027-03-31', 'PT Kalbe Farma', 'Vitamin B kompleks untuk hewan', 1),
(4, 'Multivitamin Pet Syrup', 'Suplemen', 'Sirup', 'Botol', 30, 45000, 75000, '2026-08-31', 'PT Pet Care Indonesia', 'Multivitamin lengkap', 1),
(5, 'Vaksin Rabies Nobivac', 'Vaksin', 'Injeksi', 'Vial', 20, 150000, 250000, '2025-12-31', 'PT Bio Farma', 'Vaksin rabies berkualitas', 1),
(6, 'Vaksin Distemper', 'Vaksin', 'Injeksi', 'Vial', 18, 160000, 280000, '2026-01-31', 'PT Bio Farma', 'Vaksin distemper anjing', 1),
(7, 'Vaksin FVRCP', 'Vaksin', 'Injeksi', 'Vial', 15, 170000, 300000, '2026-02-28', 'PT Bio Farma', 'Vaksin kombinasi kucing', 1),
(8, 'Ivermectin 10mg', 'Anti_Parasit', 'Tablet', 'Strip', 35, 40000, 70000, '2026-11-30', 'PT Medika', 'Obat anti parasit', 1),
(9, 'Dexamethasone 4mg', 'Vitamin', 'Injeksi', 'Ampul', 30, 15000, 30000, '2026-09-30', 'PT Pharma', 'Anti inflamasi', 1),
(10, 'Calcium Syrup', 'Suplemen', 'Sirup', 'Botol', 25, 40000, 65000, '2026-07-31', 'PT Vitamin Plus', 'Suplemen kalsium', 1),
(11, 'Metronidazole 500mg', 'Antibiotik', 'Tablet', 'Strip', 45, 30000, 50000, '2026-06-30', 'PT Kimia Farma', 'Antibiotik untuk infeksi', 1),
(12, 'Ear Drops', 'Alat_Medis', 'Tetes', 'Botol', 20, 25000, 45000, '2027-01-31', 'PT Medika', 'Tetes telinga untuk hewan', 1),
(13, 'Eye Ointment', 'Alat_Medis', 'Salep', 'Tube', 18, 30000, 55000, '2026-12-31', 'PT Medika', 'Salep mata veteriner', 1),
(14, 'Antiseptic Spray', 'Alat_Medis', 'Lainnya', 'Botol', 40, 20000, 40000, '2027-06-30', 'PT Medika', 'Spray antiseptik', 1),
(15, 'Bandage Roll 5cm', 'Alat_Medis', 'Lainnya', 'Roll', 100, 5000, 12000, '2027-12-31', 'PT Medika', 'Perban gulung', 1),
(16, 'Syringe 5ml Sterile', 'Alat_Medis', 'Lainnya', 'Pcs', 200, 2000, 5000, '2027-10-31', 'PT Medika', 'Syringe steril', 1),
(17, 'Glucose 5%', 'Vitamin', 'Injeksi', 'Botol', 22, 25000, 45000, '2026-05-31', 'PT Pharma', 'Cairan infus glukosa', 1),
(18, 'Flea & Tick Treatment', 'Anti_Parasit', 'Tetes', 'Tube', 28, 50000, 85000, '2026-08-31', 'PT Pet Care', 'Obat kutu dan caplak', 1);

-- =====================================
-- 5. PET OWNERS (WITH COMPLETE PROFILES)
-- =====================================
INSERT INTO owner (owner_id, nama_lengkap, alamat, no_telepon, email, tanggal_registrasi, catatan) VALUES
(1, 'Andi Wijaya', 'Jl. Merdeka No. 45, Jakarta Selatan', '081234567891', 'andi.wijaya@email.com', '2024-01-15 09:30:00', 'Pemilik anjing dan kucing, sangat peduli kesehatan hewan'),
(2, 'Siti Nurhaliza', 'Jl. Sudirman No. 123, Jakarta Pusat', '081234567892', 'siti.nur@email.com', '2024-02-20 10:15:00', 'Pemilik kucing Persia, rutin grooming'),
(3, 'Bambang Prasetyo', 'Jl. Gatot Subroto No. 78, Jakarta Barat', '081234567893', 'bambang.p@email.com', '2024-03-10 11:00:00', 'Pemilik beberapa anjing ras, aktif di komunitas pecinta hewan'),
(4, 'Dewi Lestari', 'Jl. Asia Afrika No. 56, Bandung', '081234567894', 'dewi.lestari@email.com', '2024-04-05 14:20:00', 'Pemilik kucing dan burung'),
(5, 'Rudi Hartono', 'Jl. Ahmad Yani No. 234, Surabaya', '081234567895', 'rudi.h@email.com', '2024-05-18 08:45:00', 'Pemilik anjing Labrador, suka outdoor'),
(6, 'Maya Anggraini', 'Jl. Diponegoro No. 89, Jakarta Timur', '081234567896', 'maya.ang@email.com', '2024-06-22 15:30:00', 'Pemilik kelinci dan hamster'),
(7, 'Hendra Kusuma', 'Jl. Veteran No. 167, Tangerang', '081234567897', 'hendra.k@email.com', '2024-07-30 09:00:00', 'Pemilik anjing penjaga, concern dengan vaksinasi'),
(8, 'Kartika Sari', 'Jl. Pahlawan No. 45, Bekasi', '081234567898', 'kartika.sari@email.com', '2024-08-12 13:15:00', 'Pemilik kucing Maine Coon, rajin medical checkup');

-- =====================================
-- 6. PETS (WITH PHOTOS FROM UNSPLASH)
-- =====================================
INSERT INTO pet (pet_id, owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, ciri_khusus, foto_url, status, tanggal_registrasi) VALUES
-- Dogs (9 pets)
(1, 1, 'Rocky', 'Anjing', 'Golden Retriever', 'Jantan', '2021-03-15', 32.5, 'Golden', 'Sangat aktif dan ramah dengan anak-anak', 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400', 'Aktif', '2024-01-15 09:30:00'),
(2, 3, 'Bruno', 'Anjing', 'German Shepherd', 'Jantan', '2020-08-10', 35.0, 'Hitam Coklat', 'Anjing penjaga yang sangat loyal', 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400', 'Aktif', '2024-03-10 11:00:00'),
(3, 5, 'Max', 'Anjing', 'Labrador', 'Jantan', '2019-12-05', 30.0, 'Coklat', 'Suka berenang dan bermain air', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400', 'Aktif', '2024-05-18 08:45:00'),
(4, 7, 'Rex', 'Anjing', 'Rottweiler', 'Jantan', '2020-06-20', 45.0, 'Hitam Coklat', 'Penjaga rumah yang baik', 'https://images.unsplash.com/photo-1614027164847-1b28cfe1df60?w=400', 'Aktif', '2024-07-30 09:00:00'),
(5, 3, 'Duke', 'Anjing', 'Siberian Husky', 'Jantan', '2021-07-20', 25.5, 'Abu-abu Putih', 'Mata biru, sangat aktif', 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?w=400', 'Aktif', '2024-03-25 10:00:00'),
(6, 1, 'Bella', 'Anjing', 'Beagle', 'Betina', '2022-02-14', 12.0, 'Tricolor', 'Sangat playful dan energik', 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?w=400', 'Aktif', '2024-02-01 14:00:00'),
(7, 5, 'Coco', 'Anjing', 'Poodle', 'Betina', '2021-11-08', 8.5, 'Putih', 'Bulu keriting, sangat pintar', 'https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?w=400', 'Aktif', '2024-06-10 11:30:00'),
(8, 3, 'Charlie', 'Anjing', 'Bulldog', 'Jantan', '2020-04-18', 22.0, 'Putih Coklat', 'Tenang dan suka tidur', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400', 'Aktif', '2024-04-15 15:20:00'),
(9, 7, 'Molly', 'Anjing', 'Chihuahua', 'Betina', '2022-09-30', 2.5, 'Coklat', 'Mini size, sangat protective', 'https://images.unsplash.com/photo-1477884213360-7e9d7dcc1e48?w=400', 'Aktif', '2024-08-05 09:45:00'),

-- Cats (5 pets)
(10, 1, 'Luna', 'Kucing', 'Persian', 'Betina', '2021-05-22', 4.5, 'Putih', 'Bulu panjang, sangat manja', 'https://images.unsplash.com/photo-1543852786-1cf6624b9987?w=400', 'Aktif', '2024-01-20 10:00:00'),
(11, 2, 'Mimi', 'Kucing', 'British Shorthair', 'Betina', '2022-06-20', 5.2, 'Abu-abu', 'Mata bulat, sangat jinak', 'https://images.unsplash.com/photo-1529778873920-4da4926a72c2?w=400', 'Aktif', '2024-02-20 10:15:00'),
(12, 4, 'Whiskers', 'Kucing', 'Siamese', 'Jantan', '2020-10-15', 4.0, 'Cream Point', 'Mata biru, sangat vokal', 'https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=400', 'Aktif', '2024-04-05 14:20:00'),
(13, 8, 'Oliver', 'Kucing', 'Maine Coon', 'Jantan', '2019-08-12', 7.5, 'Orange Tabby', 'Bulu panjang, giant cat', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400', 'Aktif', '2024-08-12 13:15:00'),
(14, 2, 'Nala', 'Kucing', 'Ragdoll', 'Betina', '2021-12-03', 5.0, 'Seal Point', 'Sangat lembut dan tenang', 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400', 'Aktif', '2024-03-15 11:45:00'),

-- Birds (2 pets)
(15, 4, 'Tweety', 'Burung', 'Love Bird', 'Betina', '2023-01-10', 0.05, 'Hijau Kuning', 'Sangat aktif dan suka bernyanyi', 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=400', 'Aktif', '2024-04-10 15:00:00'),
(16, 6, 'Kiwi', 'Burung', 'Cockatiel', 'Jantan', '2022-11-25', 0.09, 'Abu-abu Kuning', 'Bisa dilatih, suka main', 'https://images.unsplash.com/photo-1568515045052-f9a854d70bfd?w=400', 'Aktif', '2024-06-22 15:30:00'),

-- Rabbits (2 pets)
(17, 6, 'Fluffy', 'Kelinci', 'Holland Lop', 'Betina', '2022-07-18', 1.8, 'Putih Coklat', 'Telinga turun, sangat lucu', 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=400', 'Aktif', '2024-06-25 16:00:00'),
(18, 4, 'Cotton', 'Kelinci', 'Angora', 'Jantan', '2023-03-22', 2.2, 'Putih', 'Bulu sangat panjang dan lembut', 'https://images.unsplash.com/photo-1535241749838-299277b6305f?w=400', 'Aktif', '2024-04-15 10:30:00');

-- =====================================
-- 7. APPOINTMENTS (DISTRIBUTED FOR GRAPHS)
-- =====================================
-- Past appointments (for graph history)
INSERT INTO appointment (appointment_id, owner_id, pet_id, dokter_id, tanggal_appointment, jam_appointment, jenis_layanan, status, keluhan_awal, catatan, created_at) VALUES
-- October 2024 (8 appointments)
(1, 1, 1, 1, '2024-10-15', '09:00:00', 'Konsultasi Umum', 'Completed', 'Checkup rutin bulanan', 'Hewan dalam kondisi sehat', '2024-10-13 10:00:00'),
(2, 2, 11, 2, '2024-10-18', '10:30:00', 'Grooming', 'Completed', 'Grooming dan potong kuku', 'Grooming berhasil', '2024-10-15 14:00:00'),
(3, 3, 2, 3, '2024-10-20', '14:00:00', 'Pemeriksaan Gigi', 'Completed', 'Pembersihan karang gigi', 'Karang gigi dibersihkan', '2024-10-17 11:00:00'),
(4, 4, 12, 1, '2024-10-22', '11:00:00', 'Vaksinasi', 'Completed', 'Vaksin FVRCP', 'Vaksinasi berhasil', '2024-10-19 09:00:00'),
(5, 5, 3, 2, '2024-10-25', '15:30:00', 'Konsultasi Umum', 'Completed', 'Cek kesehatan umum', 'Kondisi baik', '2024-10-22 15:00:00'),
(6, 1, 6, 4, '2024-10-27', '09:30:00', 'Grooming', 'Completed', 'Grooming basic', 'Selesai dengan baik', '2024-10-24 13:00:00'),
(7, 6, 17, 1, '2024-10-29', '13:00:00', 'Konsultasi Umum', 'Completed', 'Pemeriksaan kesehatan kelinci', 'Sehat', '2024-10-26 10:00:00'),
(8, 7, 4, 3, '2024-10-31', '10:00:00', 'Vaksinasi', 'Completed', 'Vaksin rabies', 'Vaksinasi sukses', '2024-10-28 14:00:00'),

-- November 2024 (Past)
(9, 3, 5, 2, '2024-11-05', '14:30:00', 'Konsultasi Umum', 'Completed', 'Checkup Husky', 'Kondisi prima', '2024-11-02 11:00:00'),
(10, 8, 13, 1, '2024-11-08', '11:30:00', 'Grooming', 'Completed', 'Grooming Maine Coon', 'Bulu dipotong', '2024-11-05 15:00:00'),
(11, 2, 14, 4, '2024-11-12', '09:00:00', 'Vaksinasi', 'Completed', 'Vaksin FVRCP', 'Berhasil', '2024-11-09 10:00:00'),
(12, 5, 7, 2, '2024-11-15', '10:30:00', 'Grooming', 'Completed', 'Grooming Poodle', 'Styling selesai', '2024-11-12 13:00:00'),
(13, 4, 15, 3, '2024-11-18', '14:00:00', 'Konsultasi Umum', 'Completed', 'Pemeriksaan burung', 'Sehat', '2024-11-15 09:00:00'),
(14, 1, 10, 1, '2024-11-20', '11:00:00', 'Grooming', 'Completed', 'Grooming Persian', 'Bulu rapi', '2024-11-17 14:00:00'),

-- Today and Upcoming (November 22-30, 2024)
(15, 3, 8, 2, '2024-11-22', '09:00:00', 'Konsultasi Umum', 'Confirmed', 'Checkup Bulldog', NULL, '2024-11-20 10:00:00'),
(16, 6, 16, 4, '2024-11-22', '10:30:00', 'Konsultasi Umum', 'Confirmed', 'Pemeriksaan Cockatiel', NULL, '2024-11-20 11:00:00'),
(17, 7, 9, 1, '2024-11-23', '14:00:00', 'Vaksinasi', 'Confirmed', 'Vaksin Chihuahua', NULL, '2024-11-21 09:00:00'),
(18, 5, 3, 3, '2024-11-24', '11:00:00', 'Pemeriksaan Gigi', 'Confirmed', 'Scaling gigi Labrador', NULL, '2024-11-21 15:00:00'),
(19, 8, 13, 2, '2024-11-25', '15:00:00', 'Grooming', 'Scheduled', 'Grooming Maine Coon', NULL, '2024-11-22 08:00:00'),
(20, 1, 1, 1, '2024-11-26', '09:30:00', 'Konsultasi Umum', 'Scheduled', 'Checkup Golden Retriever', NULL, '2024-11-22 09:00:00'),
(21, 2, 11, 4, '2024-11-27', '13:00:00', 'Vaksinasi', 'Scheduled', 'Booster vaksin', NULL, '2024-11-22 10:00:00'),
(22, 4, 18, 3, '2024-11-28', '10:00:00', 'Konsultasi Umum', 'Scheduled', 'Pemeriksaan kelinci', NULL, '2024-11-22 11:00:00'),
(23, 3, 2, 2, '2024-11-29', '14:30:00', 'Grooming', 'Scheduled', 'Grooming German Shepherd', NULL, '2024-11-22 13:00:00'),
(24, 6, 17, 1, '2024-11-30', '11:30:00', 'Vaksinasi', 'Scheduled', 'Vaksin kelinci', NULL, '2024-11-22 14:00:00'),

-- One cancelled appointment
(25, 7, 4, 3, '2024-11-21', '15:00:00', 'Konsultasi Umum', 'Cancelled', 'Pemilik berhalangan', 'Dibatalkan oleh owner', '2024-11-18 10:00:00');

-- =====================================
-- 8. APPOINTMENT SERVICES (FOR REVENUE CHART)
-- =====================================
INSERT INTO appointment_layanan (appointment_layanan_id, appointment_id, layanan_id, jumlah, harga_satuan, subtotal, keterangan) VALUES
-- Completed appointments
(1, 1, 1, 1, 150000, 150000, 'Konsultasi umum'),
(2, 2, 3, 1, 200000, 200000, 'Grooming basic'),
(3, 3, 5, 1, 300000, 300000, 'Pemeriksaan gigi'),
(4, 4, 2, 1, 250000, 250000, 'Vaksinasi FVRCP'),
(5, 5, 1, 1, 150000, 150000, 'Konsultasi'),
(6, 6, 3, 1, 200000, 200000, 'Grooming'),
(7, 7, 1, 1, 150000, 150000, 'Pemeriksaan'),
(8, 8, 2, 1, 250000, 250000, 'Vaksinasi rabies'),
(9, 9, 1, 1, 150000, 150000, 'Checkup'),
(10, 10, 4, 1, 350000, 350000, 'Grooming premium'),
(11, 11, 2, 1, 250000, 250000, 'Vaksinasi'),
(12, 12, 4, 1, 350000, 350000, 'Grooming premium'),
(13, 13, 1, 1, 150000, 150000, 'Konsultasi'),
(14, 14, 3, 1, 200000, 200000, 'Grooming'),
-- Upcoming appointments
(15, 15, 1, 1, 150000, 150000, 'Konsultasi'),
(16, 16, 1, 1, 150000, 150000, 'Pemeriksaan'),
(17, 17, 2, 1, 250000, 250000, 'Vaksinasi'),
(18, 18, 5, 1, 300000, 300000, 'Scaling gigi'),
(19, 19, 4, 1, 350000, 350000, 'Grooming premium'),
(20, 20, 1, 1, 150000, 150000, 'Checkup'),
(21, 21, 2, 1, 250000, 250000, 'Booster vaksin'),
(22, 22, 1, 1, 150000, 150000, 'Pemeriksaan'),
(23, 23, 3, 1, 200000, 200000, 'Grooming'),
(24, 24, 2, 1, 250000, 250000, 'Vaksinasi');

-- =====================================
-- 9. MEDICAL RECORDS
-- =====================================
INSERT INTO medical_record (rekam_id, pet_id, dokter_id, appointment_id, tanggal_kunjungan, keluhan, diagnosa, tindakan, catatan_dokter, berat_badan_saat_periksa, suhu_tubuh, status_kunjungan) VALUES
(1, 1, 1, 1, '2024-10-15 09:30:00', 'Checkup rutin bulanan', 'Kondisi sehat, tidak ada masalah', 'Pemeriksaan fisik lengkap', 'Golden Retriever dalam kondisi prima, vaksinasi up to date', 32.0, 38.5, 'Pemeriksaan'),
(2, 11, 2, 2, '2024-10-18 11:00:00', 'Grooming dan potong kuku', 'Kesehatan kulit baik', 'Grooming lengkap, potong kuku', 'British Shorthair sehat, bulu dalam kondisi baik', 5.1, 38.2, 'Pemeriksaan'),
(3, 2, 3, 3, '2024-10-20 14:30:00', 'Pembersihan karang gigi', 'Karang gigi ringan', 'Scaling dan polishing gigi', 'German Shepherd memiliki karang gigi ringan, sudah dibersihkan', 34.5, 38.4, 'Pemeriksaan'),
(4, 12, 1, 4, '2024-10-22 11:30:00', 'Vaksinasi FVRCP', 'Sehat untuk vaksinasi', 'Pemberian vaksin FVRCP', 'Siamese sehat, vaksinasi berhasil tanpa reaksi', 3.9, 38.3, 'Pemeriksaan'),
(5, 3, 2, 5, '2024-10-25 16:00:00', 'Cek kesehatan umum', 'Kondisi baik, sedikit overweight', 'Pemeriksaan lengkap, rekomendasi diet', 'Labrador perlu diet, dikurangi porsi makan 20%', 31.0, 38.6, 'Konsultasi'),
(6, 17, 1, 7, '2024-10-29 13:30:00', 'Pemeriksaan kesehatan kelinci', 'Sehat', 'Pemeriksaan fisik, cek gigi', 'Holland Lop sehat, gigi normal, berat ideal', 1.8, 38.7, 'Pemeriksaan'),
(7, 4, 3, 8, '2024-10-31 10:30:00', 'Vaksinasi rabies', 'Sehat untuk vaksinasi', 'Pemberian vaksin rabies', 'Rottweiler sangat kooperatif, vaksinasi sukses', 44.5, 38.5, 'Pemeriksaan'),
(8, 5, 2, 9, '2024-11-05 15:00:00', 'Checkup Husky', 'Kondisi prima', 'Pemeriksaan lengkap', 'Siberian Husky sangat aktif, kondisi kesehatan excellent', 25.0, 38.4, 'Pemeriksaan'),
(9, 13, 1, 10, '2024-11-08 12:00:00', 'Grooming Maine Coon', 'Bulu sehat tapi perlu perawatan', 'Grooming lengkap, potong bulu', 'Maine Coon bulu panjang perlu grooming rutin 6 minggu sekali', 7.3, 38.3, 'Pemeriksaan'),
(10, 14, 4, 11, '2024-11-12 09:30:00', 'Vaksinasi FVRCP', 'Sehat', 'Pemberian vaksin FVRCP', 'Ragdoll sangat tenang saat vaksinasi, berhasil', 4.9, 38.2, 'Pemeriksaan'),
(11, 7, 2, 12, '2024-11-15 11:00:00', 'Grooming Poodle', 'Kulit sehat', 'Grooming dan styling', 'Poodle styling lengkap, bulu keriting dipotong', 8.3, 38.5, 'Pemeriksaan'),
(12, 15, 3, 13, '2024-11-18 14:30:00', 'Pemeriksaan burung', 'Sehat', 'Pemeriksaan fisik burung', 'Love Bird aktif dan sehat, bulu bagus', 0.05, 40.2, 'Pemeriksaan'),
(13, 10, 1, 14, '2024-11-20 11:30:00', 'Grooming Persian', 'Bulu sehat', 'Grooming lengkap', 'Persian bulu panjang rapi, mata bersih', 4.4, 38.3, 'Pemeriksaan');

-- =====================================
-- 10. VACCINATIONS (WITH SCHEDULES)
-- =====================================
INSERT INTO vaksinasi (vaksinasi_id, pet_id, jenis_vaksin, tanggal_vaksin, tanggal_vaksin_berikutnya, dokter_id, nomor_batch, catatan, status) VALUES
-- Completed vaccinations
(1, 1, 'Rabies', '2024-01-20', '2025-01-20', 1, 'RAB-2024-001', 'Vaksinasi tahunan rabies Golden Retriever', 'Completed'),
(2, 1, 'Distemper', '2024-02-15', '2025-02-15', 1, 'DIS-2024-002', 'Vaksinasi distemper', 'Completed'),
(3, 2, 'Rabies', '2024-03-15', '2025-03-15', 2, 'RAB-2024-003', 'Vaksinasi rabies German Shepherd', 'Completed'),
(4, 2, 'Distemper', '2024-03-15', '2025-03-15', 2, 'DIS-2024-004', 'Vaksinasi distemper', 'Completed'),
(5, 3, 'Rabies', '2024-05-20', '2025-05-20', 3, 'RAB-2024-005', 'Vaksinasi rabies Labrador', 'Completed'),
(6, 3, 'Distemper', '2024-05-20', '2025-05-20', 3, 'DIS-2024-006', 'Vaksinasi distemper', 'Completed'),
(7, 4, 'Rabies', '2024-10-31', '2025-10-31', 3, 'RAB-2024-007', 'Vaksinasi rabies Rottweiler', 'Completed'),
(8, 5, 'Rabies', '2024-04-10', '2025-04-10', 2, 'RAB-2024-008', 'Vaksinasi rabies Husky', 'Completed'),
(9, 5, 'Distemper', '2024-04-10', '2025-04-10', 2, 'DIS-2024-009', 'Vaksinasi distemper', 'Completed'),
(10, 6, 'Rabies', '2024-02-15', '2025-02-15', 1, 'RAB-2024-010', 'Vaksinasi rabies Beagle', 'Completed'),
(11, 7, 'Rabies', '2024-06-15', '2025-06-15', 4, 'RAB-2024-011', 'Vaksinasi rabies Poodle', 'Completed'),
(12, 8, 'Rabies', '2024-04-20', '2025-04-20', 2, 'RAB-2024-012', 'Vaksinasi rabies Bulldog', 'Completed'),
(13, 9, 'Rabies', '2024-08-10', '2025-08-10', 1, 'RAB-2024-013', 'Vaksinasi rabies Chihuahua', 'Completed'),
(14, 10, 'FVRCP', '2024-01-25', '2024-07-25', 1, 'FVRCP-2024-001', 'Vaksinasi kombinasi Persian', 'Completed'),
(15, 10, 'FVRCP', '2024-07-25', '2025-07-25', 1, 'FVRCP-2024-002', 'Booster vaksin Persian', 'Completed'),
(16, 11, 'FVRCP', '2024-02-25', '2024-08-25', 2, 'FVRCP-2024-003', 'Vaksinasi British Shorthair', 'Completed'),
(17, 11, 'FVRCP', '2024-08-25', '2025-08-25', 2, 'FVRCP-2024-004', 'Booster vaksin', 'Completed'),
(18, 12, 'FVRCP', '2024-10-22', '2025-04-22', 1, 'FVRCP-2024-005', 'Vaksinasi Siamese', 'Completed'),
(19, 13, 'FVRCP', '2024-08-15', '2025-08-15', 4, 'FVRCP-2024-006', 'Vaksinasi Maine Coon', 'Completed'),
(20, 14, 'FVRCP', '2024-11-12', '2025-05-12', 4, 'FVRCP-2024-007', 'Vaksinasi Ragdoll', 'Completed'),

-- Scheduled vaccinations (upcoming)
(21, 1, 'Rabies', '2025-01-20', '2026-01-20', 1, NULL, 'Vaksinasi tahunan terjadwal', 'Scheduled'),
(22, 2, 'Rabies', '2025-03-15', '2026-03-15', 2, NULL, 'Vaksinasi tahunan terjadwal', 'Scheduled'),
(23, 6, 'Distemper', '2025-02-15', '2026-02-15', 1, NULL, 'Vaksinasi distemper terjadwal', 'Scheduled'),
(24, 7, 'Distemper', '2025-06-15', '2026-06-15', 4, NULL, 'Vaksinasi distemper terjadwal', 'Scheduled'),
(25, 8, 'Distemper', '2025-04-20', '2026-04-20', 2, NULL, 'Vaksinasi distemper terjadwal', 'Scheduled'),

-- Some overdue vaccinations (for alerts)
(26, 4, 'Distemper', '2024-07-30', '2024-11-30', 3, NULL, 'Booster vaksin perlu segera', 'Overdue'),
(27, 9, 'Distemper', '2024-08-10', '2024-11-10', 1, NULL, 'Vaksinasi distemper overdue', 'Overdue');

-- =====================================
-- 11. PRESCRIPTIONS
-- =====================================
INSERT INTO resep (resep_id, rekam_id, obat_id, dosis, frekuensi, durasi, cara_pakai, jumlah, harga_satuan, subtotal) VALUES
(1, 3, 1, '500mg', '2x sehari', '7 hari', 'Sesudah makan, berikan dengan makanan', 14, 45000, 630000),
(2, 5, 8, '10mg', '1x sehari', '14 hari', 'Pagi hari dengan makanan', 14, 70000, 980000),
(3, 5, 4, '10ml', '1x sehari', '30 hari', 'Dicampur dengan makanan', 1, 75000, 75000),
(4, 7, 5, '1 vial', 'Sekali', '1 hari', 'Injeksi subkutan', 1, 250000, 250000),
(5, 9, 3, '1 tablet', '1x sehari', '30 hari', 'Pagi hari', 30, 55000, 1650000),
(6, 11, 12, '3 tetes', '2x sehari', '7 hari', 'Teteskan ke telinga', 1, 45000, 45000),
(7, 13, 14, 'Semprotkan', '3x sehari', '5 hari', 'Spray ke luka', 1, 40000, 40000);

-- =====================================
-- 12. SUCCESS SUMMARY
-- =====================================
SELECT '✅ DEMO DATA SUCCESSFULLY CREATED!' as status;

SELECT 
    'Database Summary' as info,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM veterinarian) as total_veterinarians,
    (SELECT COUNT(*) FROM owner) as total_owners,
    (SELECT COUNT(*) FROM pet) as total_pets,
    (SELECT COUNT(*) FROM appointment) as total_appointments,
    (SELECT COUNT(*) FROM medical_record) as total_medical_records,
    (SELECT COUNT(*) FROM vaksinasi) as total_vaccinations,
    (SELECT COUNT(*) FROM medicine) as total_medicines,
    (SELECT COUNT(*) FROM resep) as total_prescriptions;

SELECT 
    'Pet Species Distribution' as info,
    jenis as species,
    COUNT(*) as count
FROM pet 
GROUP BY jenis;

SELECT 
    'Appointment Status' as info,
    status,
    COUNT(*) as count
FROM appointment 
GROUP BY status;

SELECT '✅ All graph data populated!' as message;
SELECT '✅ Staff photos included from RandomUser!' as message;
SELECT '✅ Pet photos included from Unsplash!' as message;
SELECT '✅ Ready for full demo!' as message;
