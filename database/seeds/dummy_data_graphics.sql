-- Comprehensive Dummy Data for VetClinic Graphics
-- Run this to populate all charts and statistics
-- Date: November 22, 2025

USE vetclinic;

-- =====================================
-- 1. ADD MORE PETS (for pet statistics)
-- =====================================
INSERT INTO pet (nama_hewan, jenis, ras, tanggal_lahir, jenis_kelamin, berat_badan, warna, ciri_khusus, owner_id, foto_url, status, tanggal_registrasi) VALUES
('Rocky', 'Anjing', 'German Shepherd', '2021-03-15', 'Jantan', 32.5, 'Coklat Hitam', 'Sangat aktif, suka bermain', 1, NULL, 'Aktif', '2024-01-10'),
('Mimi', 'Kucing', 'British Shorthair', '2022-06-20', 'Betina', 4.8, 'Abu-abu', 'Mata biru, sangat jinak', 2, NULL, 'Aktif', '2024-02-15'),
('Bruno', 'Anjing', 'Labrador', '2020-08-10', 'Jantan', 28.0, 'Kuning', 'Sangat ramah dengan anak-anak', 3, NULL, 'Aktif', '2024-03-20'),
('Luna', 'Kucing', 'Maine Coon', '2021-11-05', 'Betina', 6.2, 'Hitam Putih', 'Bulu panjang, suka tidur', 1, NULL, 'Aktif', '2024-04-25'),
('Rex', 'Anjing', 'Rottweiler', '2019-12-01', 'Jantan', 45.0, 'Hitam Coklat', 'Penjaga rumah yang baik', 2, NULL, 'Aktif', '2024-05-30'),
('Bella', 'Kucing', 'Siamese', '2023-02-14', 'Betina', 3.5, 'Krem', 'Mata biru, sangat vokal', 3, NULL, 'Aktif', '2024-06-10'),
('Duke', 'Anjing', 'Husky', '2021-07-20', 'Jantan', 25.5, 'Putih Abu', 'Mata berbeda warna', 1, NULL, 'Aktif', '2024-07-15'),
('Cleo', 'Kucing', 'Sphynx', '2022-09-30', 'Betina', 4.0, 'Pink', 'Tanpa bulu, sangat hangat', 2, NULL, 'Aktif', '2024-08-20'),
('Max', 'Anjing', 'Poodle', '2020-04-18', 'Jantan', 8.5, 'Putih', 'Bulu keriting, sangat pintar', 3, NULL, 'Aktif', '2024-09-25'),
('Kitty', 'Kucing', 'Ragdoll', '2021-05-22', 'Betina', 5.5, 'Putih Coklat', 'Sangat lembut dan tenang', 1, NULL, 'Aktif', '2024-10-01');

-- =====================================
-- 2. ADD APPOINTMENTS (for dashboard & charts)
-- =====================================
-- Past appointments (completed)
INSERT INTO appointment (owner_id, pet_id, dokter_id, tanggal_appointment, jam_appointment, jenis_layanan, keluhan_awal, status, catatan, created_at) VALUES
-- November 2024
(1, 1, 1, '2024-11-01', '09:00:00', 'Pemeriksaan Umum', 'Checkup rutin', 'Completed', 'Hewan sehat', '2024-10-28'),
(2, 2, 2, '2024-11-03', '10:30:00', 'Vaksinasi', 'Vaksin rabies', 'Completed', 'Vaksinasi berhasil', '2024-10-30'),
(3, 3, 1, '2024-11-05', '14:00:00', 'Grooming', 'Perawatan bulu', 'Completed', 'Grooming selesai', '2024-11-01'),
(1, 4, 3, '2024-11-07', '11:00:00', 'Pemeriksaan Umum', 'Lesu dan tidak mau makan', 'Completed', 'Diberikan obat', '2024-11-03'),
(2, 5, 2, '2024-11-10', '15:30:00', 'Vaksinasi', 'Vaksin distemper', 'Completed', 'Vaksinasi berhasil', '2024-11-05'),
(3, 6, 1, '2024-11-12', '09:30:00', 'Pemeriksaan Gigi', 'Pembersihan karang gigi', 'Completed', 'Gigi bersih', '2024-11-08'),
(1, 7, 3, '2024-11-15', '13:00:00', 'Pemeriksaan Umum', 'Checkup bulanan', 'Completed', 'Kondisi baik', '2024-11-10'),
(2, 8, 2, '2024-11-17', '10:00:00', 'Konsultasi', 'Diet khusus', 'Completed', 'Rekomendasi diet', '2024-11-12'),
(3, 9, 1, '2024-11-19', '14:30:00', 'Grooming', 'Potong kuku', 'Completed', 'Grooming selesai', '2024-11-15'),
(1, 10, 3, '2024-11-20', '11:30:00', 'Vaksinasi', 'Vaksin FVRCP', 'Completed', 'Vaksinasi berhasil', '2024-11-17'),

-- Current week (upcoming & today)
(2, 1, 1, '2025-11-22', '09:00:00', 'Pemeriksaan Umum', 'Checkup rutin', 'Scheduled', NULL, '2025-11-20'),
(3, 2, 2, '2025-11-22', '10:30:00', 'Vaksinasi', 'Booster vaksin', 'Scheduled', NULL, '2025-11-20'),
(1, 3, 3, '2025-11-22', '14:00:00', 'Konsultasi', 'Masalah kulit', 'Confirmed', NULL, '2025-11-21'),
(2, 4, 1, '2025-11-23', '11:00:00', 'Pemeriksaan Umum', 'Batuk-batuk', 'Scheduled', NULL, '2025-11-21'),
(3, 5, 2, '2025-11-24', '15:00:00', 'Grooming', 'Mandi dan potong kuku', 'Scheduled', NULL, '2025-11-22'),
(1, 6, 3, '2025-11-25', '09:30:00', 'Vaksinasi', 'Vaksin annual', 'Confirmed', NULL, '2025-11-22'),
(2, 7, 1, '2025-11-26', '13:00:00', 'Pemeriksaan Umum', 'Checkup', 'Scheduled', NULL, '2025-11-22'),
(3, 8, 2, '2025-11-27', '10:00:00', 'Konsultasi', 'Nutrisi', 'Scheduled', NULL, '2025-11-22'),
(1, 9, 3, '2025-11-28', '14:30:00', 'Pemeriksaan Gigi', 'Scaling gigi', 'Scheduled', NULL, '2025-11-22'),
(2, 10, 1, '2025-11-29', '11:30:00', 'Grooming', 'Perawatan bulu', 'Scheduled', NULL, '2025-11-22');

-- =====================================
-- 3. ADD MEDICAL RECORDS (for health timeline)
-- =====================================
INSERT INTO medical_record (pet_id, dokter_id, appointment_id, tanggal_kunjungan, keluhan, diagnosa, tindakan, catatan_dokter, berat_badan_saat_periksa, suhu_tubuh, status_kunjungan) VALUES
-- Recent records for existing pets
(1, 1, 1, '2024-11-01', 'Checkup rutin', 'Kondisi sehat', 'Pemeriksaan fisik lengkap', 'Tidak ada masalah kesehatan', 32.0, 38.5, 'Pemeriksaan'),
(2, 2, 2, '2024-11-03', 'Vaksin rabies', 'Sehat untuk vaksinasi', 'Pemberian vaksin rabies', 'Vaksinasi sukses, tidak ada reaksi', 4.7, 38.2, 'Vaksinasi'),
(3, 1, 3, '2024-11-05', 'Perawatan bulu', 'Kulit sehat', 'Grooming lengkap', 'Bulu dalam kondisi baik', 27.5, 38.6, 'Pemeriksaan'),
(4, 3, 4, '2024-11-07', 'Lesu dan tidak mau makan', 'Gastritis ringan', 'Pemberian obat anti-mual dan vitamin', 'Direkomendasikan diet khusus', 6.0, 39.1, 'Pemeriksaan'),
(5, 2, 5, '2024-11-10', 'Vaksin distemper', 'Sehat', 'Vaksinasi distemper', 'Vaksinasi berhasil', 44.5, 38.4, 'Vaksinasi'),
(6, 1, 6, '2024-11-12', 'Pembersihan gigi', 'Karang gigi ringan', 'Scaling dan polishing gigi', 'Gigi bersih, kesehatan mulut baik', 3.4, 38.3, 'Pemeriksaan'),
(7, 3, 7, '2024-11-15', 'Checkup bulanan', 'Sehat', 'Pemeriksaan rutin', 'Kondisi optimal', 25.0, 38.5, 'Pemeriksaan'),
(8, 2, 8, '2024-11-17', 'Konsultasi diet', 'Berat badan ideal', 'Rekomendasi diet', 'Diet maintenance', 3.9, 38.2, 'Pemeriksaan'),
(9, 1, 9, '2024-11-19', 'Potong kuku', 'Kuku panjang', 'Pemotongan kuku', 'Kuku sudah dipotong', 8.3, 38.4, 'Pemeriksaan'),
(10, 3, 10, '2024-11-20', 'Vaksin FVRCP', 'Sehat', 'Vaksinasi FVRCP', 'Vaksinasi sukses', 5.4, 38.3, 'Vaksinasi');

-- =====================================
-- 4. ADD VACCINATIONS (for vaccination chart)
-- =====================================
INSERT INTO vaksinasi (pet_id, jenis_vaksin, tanggal_vaksin, tanggal_vaksin_berikutnya, dokter_id, catatan, status) VALUES
-- Completed vaccinations
(1, 'Rabies', '2024-11-01', '2025-11-01', 1, 'Vaksinasi tahunan rabies', 'Completed'),
(2, 'FVRCP', '2024-11-03', '2025-05-03', 2, 'Vaksin kombinasi kucing', 'Completed'),
(3, 'Distemper', '2024-11-05', '2025-11-05', 1, 'Vaksin distemper anjing', 'Completed'),
(4, 'Rabies', '2024-11-07', '2025-11-07', 3, 'Vaksinasi rabies', 'Completed'),
(5, 'Parvovirus', '2024-11-10', '2025-05-10', 2, 'Vaksin parvo', 'Completed'),
(6, 'FVRCP', '2024-11-12', '2025-05-12', 1, 'Vaksin kombinasi', 'Completed'),
(7, 'Rabies', '2024-11-15', '2025-11-15', 3, 'Vaksinasi rabies', 'Completed'),
(8, 'Rabies', '2024-11-17', '2025-11-17', 2, 'Vaksinasi tahunan', 'Completed'),
(9, 'Distemper', '2024-11-19', '2025-11-19', 1, 'Vaksin distemper', 'Completed'),
(10, 'FVRCP', '2024-11-20', '2025-05-20', 3, 'Vaksin kombinasi', 'Completed'),

-- Upcoming vaccinations
(1, 'Distemper', '2025-11-30', '2026-11-30', 1, 'Vaksinasi terjadwal', 'Scheduled'),
(2, 'Rabies', '2025-12-05', '2026-12-05', 2, 'Booster vaksin', 'Scheduled'),
(3, 'Rabies', '2025-12-10', '2026-12-10', 3, 'Vaksinasi terjadwal', 'Scheduled'),
(4, 'FVRCP', '2025-12-15', '2026-06-15', 1, 'Vaksin kombinasi', 'Scheduled'),
(5, 'Distemper', '2025-12-20', '2026-12-20', 2, 'Booster vaksin', 'Scheduled');

-- =====================================
-- 5. ADD MEDICINES (for inventory chart)
-- =====================================
INSERT INTO medicine (nama_obat, kategori, bentuk_sediaan, satuan, stok, harga_beli, harga_jual, expired_date, supplier, deskripsi, status_tersedia) VALUES
('Ceftriaxone 1g', 'Antibiotik', 'Injeksi', 'Vial', 25, 35000, 50000, '2026-12-31', 'PT Pharma Indo', 'Antibiotik spektrum luas', 1),
('Ciprofloxacin 500mg', 'Antibiotik', 'Tablet', 'Strip', 40, 45000, 65000, '2026-06-30', 'PT Medika', 'Antibiotik fluoroquinolone', 1),
('Vitamin C 1000mg', 'Vitamin', 'Tablet', 'Botol', 60, 25000, 40000, '2026-09-30', 'PT Vitamin', 'Suplemen vitamin C', 1),
('Multivitamin Pet', 'Suplemen', 'Sirup', 'Botol', 35, 55000, 80000, '2026-03-31', 'PT Pet Care', 'Multivitamin hewan', 1),
('Vaksin Rabies', 'Vaksin', 'Injeksi', 'Vial', 15, 125000, 200000, '2025-12-31', 'PT Bio Farma', 'Vaksin rabies', 1),
('Vaksin Distemper', 'Vaksin', 'Injeksi', 'Vial', 20, 150000, 220000, '2026-01-31', 'PT Bio Farma', 'Vaksin distemper', 1),
('Dexamethasone', 'Anti_Parasit', 'Injeksi', 'Ampul', 30, 15000, 25000, '2026-08-31', 'PT Pharma', 'Anti inflamasi', 1),
('Ivermectin', 'Anti_Parasit', 'Tablet', 'Strip', 45, 35000, 55000, '2026-11-30', 'PT Medika', 'Anti parasit', 1),
('Calcium Syrup', 'Suplemen', 'Sirup', 'Botol', 28, 40000, 60000, '2026-07-31', 'PT Vitamin', 'Suplemen kalsium', 1),
('Antiseptik', 'Alat_Medis', 'Cairan', 'Botol', 50, 20000, 35000, '2027-01-31', 'PT Medika', 'Cairan antiseptik', 1),
('Bandage Roll', 'Alat_Medis', 'Roll', 'Pcs', 100, 5000, 10000, '2027-12-31', 'PT Medika', 'Perban gulung', 1),
('Syringe 5ml', 'Alat_Medis', 'Disposable', 'Pcs', 200, 2000, 4000, '2027-06-30', 'PT Medika', 'Syringe sekali pakai', 1);

-- =====================================
-- 6. ADD APPOINTMENT SERVICES (for revenue chart)
-- =====================================
INSERT INTO appointment_layanan (appointment_id, layanan_id, quantity, subtotal) VALUES
-- Link services to appointments for revenue tracking
(1, 1, 1, 150000), -- Pemeriksaan Umum
(2, 2, 1, 200000), -- Vaksinasi
(3, 3, 1, 180000), -- Grooming
(4, 1, 1, 150000),
(5, 2, 1, 200000),
(6, 4, 1, 250000), -- Pemeriksaan Gigi
(7, 1, 1, 150000),
(8, 5, 1, 100000), -- Konsultasi
(9, 3, 1, 180000),
(10, 2, 1, 200000),
(11, 1, 1, 150000),
(12, 2, 1, 200000),
(13, 5, 1, 100000),
(14, 1, 1, 150000),
(15, 3, 1, 180000),
(16, 2, 1, 200000),
(17, 1, 1, 150000),
(18, 5, 1, 100000),
(19, 4, 1, 250000),
(20, 3, 1, 180000);

-- =====================================
-- 7. ADD PRESCRIPTIONS (for medical records)
-- =====================================
INSERT INTO resep (rekam_id, obat_id, dosis, frekuensi, durasi, catatan) VALUES
-- Link medicines to medical records
(1, 1, '500mg', '2x sehari', '7 hari', 'Setelah makan'),
(2, 5, '1 vial', 'Sekali', '1 hari', 'Vaksinasi'),
(4, 2, '500mg', '3x sehari', '5 hari', 'Sebelum makan'),
(4, 3, '1 tablet', '1x sehari', '14 hari', 'Setelah makan'),
(5, 6, '1 vial', 'Sekali', '1 hari', 'Vaksinasi'),
(7, 4, '10ml', '1x sehari', '30 hari', 'Pagi hari'),
(8, 9, '5ml', '2x sehari', '21 hari', 'Dengan makanan'),
(10, 5, '1 vial', 'Sekali', '1 hari', 'Vaksinasi');

-- Success message
SELECT '✅ Dummy data successfully created!' as status,
       (SELECT COUNT(*) FROM pet) as total_pets,
       (SELECT COUNT(*) FROM appointment) as total_appointments,
       (SELECT COUNT(*) FROM medical_record) as total_medical_records,
       (SELECT COUNT(*) FROM vaksinasi) as total_vaccinations,
       (SELECT COUNT(*) FROM medicine) as total_medicines;
