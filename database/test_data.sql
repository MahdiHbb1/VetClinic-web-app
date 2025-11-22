-- VetClinic Test Data - Comprehensive Testing
-- Run this after the main schema is loaded

USE vetclinic;

-- Clear existing test data (keep admin user)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM appointment_layanan WHERE appointment_id > 0;
DELETE FROM resep WHERE resep_id > 0;
DELETE FROM medical_record WHERE rekam_id > 0;
DELETE FROM vaksinasi WHERE vaksinasi_id > 0;
DELETE FROM appointment WHERE appointment_id > 0;
DELETE FROM pet WHERE pet_id > 0;
DELETE FROM owner WHERE owner_id > 0;
DELETE FROM veterinarian WHERE dokter_id > 0;
DELETE FROM service WHERE layanan_id > 0;
DELETE FROM medicine WHERE obat_id > 0;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert Test Owners
INSERT INTO owner (nama_lengkap, alamat, no_telepon, email, tanggal_registrasi, catatan) VALUES
('Budi Santoso', 'Jl. Merdeka No. 123, Jakarta Pusat', '081234567890', 'budi.santoso@email.com', '2024-01-15 09:00:00', 'Pelanggan setia sejak 2024'),
('Siti Rahayu', 'Jl. Sudirman No. 456, Jakarta Selatan', '081234567891', 'siti.rahayu@email.com', '2024-02-20 10:30:00', 'Memiliki alergi terhadap kucing'),
('Ahmad Hidayat', 'Jl. Gatot Subroto No. 789, Jakarta Barat', '081234567892', 'ahmad.hidayat@email.com', '2024-03-10 14:15:00', NULL),
('Rina Wijaya', 'Jl. Thamrin No. 321, Jakarta Pusat', '081234567893', 'rina.wijaya@email.com', '2024-04-05 11:00:00', 'Pemilik breeding kennel'),
('Hendra Gunawan', 'Jl. Kuningan No. 654, Jakarta Selatan', '081234567894', 'hendra.gunawan@email.com', '2024-05-12 16:45:00', 'VIP Customer');

-- Insert Test Veterinarians
INSERT INTO veterinarian (nama_dokter, no_lisensi, spesialisasi, no_telepon, email, jadwal_praktek, status, tanggal_bergabung) VALUES
('Dr. Sarah Wijaya', 'VET-2024-001', 'Umum', '081234567801', 'dr.sarah@vetclinic.com', 'Senin-Jumat: 08:00-17:00', 'Aktif', '2024-01-01'),
('Dr. Michael Chen', 'VET-2024-002', 'Bedah', '081234567802', 'dr.michael@vetclinic.com', 'Senin-Jumat: 09:00-18:00', 'Aktif', '2024-01-01'),
('Dr. Lisa Purnama', 'VET-2024-003', 'Kulit', '081234567803', 'dr.lisa@vetclinic.com', 'Selasa-Sabtu: 10:00-19:00', 'Aktif', '2024-02-01'),
('Dr. Budi Prasetyo', 'VET-2024-004', 'Gigi', '081234567804', 'dr.budi@vetclinic.com', 'Senin-Jumat: 08:00-16:00', 'Aktif', '2024-03-01'),
('Dr. Nina Kusuma', 'VET-2024-005', 'Kardio', '081234567805', 'dr.nina@vetclinic.com', 'Rabu-Minggu: 11:00-20:00', 'Aktif', '2024-04-01');

-- Insert Test Pets
INSERT INTO pet (owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, ciri_khusus, status, tanggal_registrasi) VALUES
(1, 'Max', 'Anjing', 'Golden Retriever', 'Jantan', '2022-05-15', 28.50, 'Emas', 'Sangat ramah, suka bermain', 'Aktif', '2024-01-15 09:30:00'),
(1, 'Bella', 'Kucing', 'Persian', 'Betina', '2023-03-20', 4.20, 'Putih', 'Mata biru, bulu panjang', 'Aktif', '2024-01-15 09:45:00'),
(2, 'Charlie', 'Anjing', 'Poodle', 'Jantan', '2021-08-10', 7.80, 'Cokelat', 'Sangat pintar, mudah dilatih', 'Aktif', '2024-02-20 11:00:00'),
(3, 'Luna', 'Kucing', 'Maine Coon', 'Betina', '2022-11-05', 5.50, 'Abu-abu', 'Ukuran besar, ramah', 'Aktif', '2024-03-10 14:30:00'),
(3, 'Rocky', 'Anjing', 'German Shepherd', 'Jantan', '2020-07-25', 35.00, 'Hitam-Cokelat', 'Sangat protektif, loyal', 'Aktif', '2024-03-10 14:45:00'),
(4, 'Milo', 'Anjing', 'Beagle', 'Jantan', '2023-01-12', 12.30, 'Tricolor', 'Sangat aktif, suka berburu', 'Aktif', '2024-04-05 11:30:00'),
(4, 'Coco', 'Kucing', 'Scottish Fold', 'Betina', '2023-06-18', 3.80, 'Cream', 'Telinga lipat, sangat manis', 'Aktif', '2024-04-05 11:45:00'),
(5, 'Oscar', 'Kucing', 'British Shorthair', 'Jantan', '2022-09-30', 6.20, 'Abu-abu Biru', 'Tenang, tidak terlalu aktif', 'Aktif', '2024-05-12 17:00:00');

-- Insert Test Services
INSERT INTO service (nama_layanan, kategori, harga, durasi_estimasi, deskripsi, status_tersedia) VALUES
('Konsultasi Umum', 'Pemeriksaan', 150000.00, 30, 'Pemeriksaan kesehatan umum dan konsultasi', 1),
('Vaksinasi Rabies', 'Vaksinasi', 250000.00, 30, 'Vaksinasi pencegahan penyakit rabies', 1),
('Vaksinasi DHPP', 'Vaksinasi', 300000.00, 30, 'Vaksinasi Distemper, Hepatitis, Parvo, Parainfluenza', 1),
('Grooming Lengkap', 'Grooming', 200000.00, 120, 'Mandi, potong kuku, bersihkan telinga, styling', 1),
('Sterilisasi Kucing', 'Bedah', 800000.00, 180, 'Operasi sterilisasi untuk kucing', 1),
('Sterilisasi Anjing', 'Bedah', 1200000.00, 240, 'Operasi sterilisasi untuk anjing', 1),
('Pembersihan Karang Gigi', 'Gigi', 500000.00, 90, 'Scaling dan pembersihan gigi profesional', 1),
('Rontgen', 'Tes_Lab', 400000.00, 45, 'Pemeriksaan rontgen untuk diagnosis', 1),
('Tes Darah Lengkap', 'Tes_Lab', 350000.00, 60, 'Pemeriksaan darah lengkap', 1),
('Rawat Inap per Hari', 'Rawat_Inap', 150000.00, 1440, 'Perawatan intensif di klinik', 1);

-- Insert Test Medicines
INSERT INTO medicine (nama_obat, kategori, bentuk_sediaan, satuan, stok, harga_beli, harga_jual, expired_date, supplier, deskripsi, status_tersedia) VALUES
('Amoxicillin 500mg', 'Antibiotik', 'Tablet', 'Strip', 50, 15000.00, 25000.00, '2025-12-31', 'PT Pharma Indonesia', 'Antibiotik spektrum luas', 1),
('Ceftriaxone 1g', 'Antibiotik', 'Injeksi', 'Vial', 30, 45000.00, 75000.00, '2025-10-31', 'PT Pharma Indonesia', 'Antibiotik injeksi kuat', 1),
('Paracetamol 500mg', 'Anti_Parasit', 'Tablet', 'Strip', 100, 5000.00, 10000.00, '2026-06-30', 'PT Medika Farma', 'Pereda nyeri dan penurun panas', 1),
('Vitamin B Complex', 'Vitamin', 'Tablet', 'Botol', 40, 35000.00, 60000.00, '2026-03-31', 'PT Vitamin Sehat', 'Suplemen vitamin B kompleks', 1),
('Dexamethasone 0.5mg', 'Suplemen', 'Tablet', 'Strip', 25, 12000.00, 20000.00, '2025-09-30', 'PT Pharma Indonesia', 'Anti inflamasi dan alergi', 1),
('Ivermectin', 'Anti_Parasit', 'Tablet', 'Box', 35, 80000.00, 130000.00, '2026-01-31', 'PT Animal Health', 'Obat anti parasit internal dan eksternal', 1),
('Metronidazole 500mg', 'Antibiotik', 'Tablet', 'Strip', 45, 18000.00, 30000.00, '2025-11-30', 'PT Medika Farma', 'Antibiotik untuk infeksi anaerob', 1),
('Salep Luka', 'Alat_Medis', 'Salep', 'Tube', 60, 25000.00, 40000.00, '2026-08-31', 'PT Animal Health', 'Salep untuk penyembuhan luka', 1),
('Obat Tetes Mata', 'Alat_Medis', 'Tetes', 'Botol', 20, 30000.00, 50000.00, '2025-07-31', 'PT Medika Farma', 'Tetes mata untuk infeksi', 1),
('Antibiotik Telinga', 'Antibiotik', 'Tetes', 'Botol', 18, 40000.00, 65000.00, '2025-12-15', 'PT Animal Health', 'Tetes telinga antibiotik', 1);

-- Insert Test Appointments (Past, Present, and Future)
INSERT INTO appointment (pet_id, owner_id, dokter_id, tanggal_appointment, jam_appointment, jenis_layanan, status, keluhan_awal, catatan, created_at) VALUES
-- Past completed appointments
(1, 1, 1, '2024-10-15', '09:00:00', 'Konsultasi Umum', 'Completed', 'Check-up rutin, Max terlihat lesu', 'Pasien kooperatif', '2024-10-14 15:00:00'),
(2, 1, 3, '2024-10-20', '10:00:00', 'Grooming Lengkap', 'Completed', 'Grooming bulanan Bella', 'Bulu sangat kusut', '2024-10-19 14:00:00'),
(3, 2, 2, '2024-11-01', '11:00:00', 'Sterilisasi Anjing', 'Completed', 'Operasi sterilisasi Charlie', 'Pre-op test done', '2024-10-30 10:00:00'),
(4, 3, 1, '2024-11-10', '14:00:00', 'Vaksinasi Rabies', 'Completed', 'Vaksinasi tahunan Luna', NULL, '2024-11-09 16:00:00'),

-- Today's appointments
(5, 3, 1, '2024-11-22', '09:00:00', 'Konsultasi Umum', 'Confirmed', 'Rocky batuk-batuk sejak kemarin', 'Urgent case', '2024-11-21 10:00:00'),
(6, 4, 4, '2024-11-22', '10:30:00', 'Pembersihan Karang Gigi', 'Confirmed', 'Gigi Milo bau dan berkarang', NULL, '2024-11-21 11:00:00'),
(7, 4, 3, '2024-11-22', '13:00:00', 'Grooming Lengkap', 'Pending', 'Grooming bulanan Coco', NULL, '2024-11-21 15:00:00'),
(8, 5, 1, '2024-11-22', '15:00:00', 'Konsultasi Umum', 'Pending', 'Oscar tidak mau makan 2 hari', 'Owner sangat khawatir', '2024-11-22 08:00:00'),

-- Future appointments
(1, 1, 2, '2024-11-25', '09:30:00', 'Tes Darah Lengkap', 'Confirmed', 'Follow-up dari check-up sebelumnya', NULL, '2024-11-22 09:00:00'),
(2, 1, 1, '2024-11-28', '10:00:00', 'Konsultasi Umum', 'Confirmed', 'Check-up rutin bulanan', NULL, '2024-11-22 09:15:00'),
(3, 2, 1, '2024-12-01', '11:00:00', 'Konsultasi Umum', 'Pending', 'Post-op check sterilisasi', NULL, '2024-11-22 10:00:00'),
(4, 3, 5, '2024-12-05', '14:00:00', 'Konsultasi Umum', 'Pending', 'Check-up jantung Luna', 'Rujukan dari Dr. Sarah', '2024-11-22 11:00:00');

-- Insert Test Medical Records
INSERT INTO medical_record (pet_id, dokter_id, appointment_id, tanggal_kunjungan, keluhan, diagnosa, tindakan, catatan_dokter, berat_badan_saat_periksa, suhu_tubuh, status_kunjungan) VALUES
(1, 1, 1, '2024-10-15 09:00:00', 'Max terlihat lesu, tidak mau makan, demam', 'Infeksi saluran pencernaan ringan', 'Pemberian antibiotik oral, cairan infus', 'Kondisi membaik setelah treatment, follow-up 1 minggu', 28.50, 39.5, 'Pemeriksaan'),
(2, 3, 2, '2024-10-20 10:00:00', 'Bulu kusut, kulit kering, kutu', 'Dermatitis dan infestasi kutu', 'Grooming lengkap, pemberian obat anti kutu, salep kulit', 'Kondisi kulit cukup baik, perlu perawatan rutin', 4.20, 38.2, 'Pemeriksaan'),
(3, 2, 3, '2024-11-01 11:00:00', 'Sterilisasi elektif', 'Hewan sehat, siap operasi sterilisasi', 'Sterilisasi (orchiectomy) berhasil dilakukan', 'Operasi berjalan lancar, recovery baik, rawat inap 1 hari', 7.80, 38.5, 'Operasi'),
(4, 1, 4, '2024-11-10 14:00:00', 'Vaksinasi tahunan rutin', 'Hewan sehat, kondisi baik untuk vaksinasi', 'Vaksinasi Rabies diberikan', 'Tidak ada reaksi alergi, jadwal vaksinasi berikutnya Nov 2025', 5.50, 38.3, 'Pemeriksaan');

-- Insert Test Prescriptions
INSERT INTO resep (rekam_id, obat_id, dosis, frekuensi, durasi, cara_pakai, jumlah, harga_satuan, subtotal) VALUES
-- Prescription for Max (Medical Record 1)
(1, 1, '500mg', '2x sehari', '7 hari', 'Sesudah makan', 2, 25000.00, 50000.00),
(1, 3, '250mg', '3x sehari', '5 hari', 'Sesudah makan', 2, 10000.00, 20000.00),
(1, 4, '1 tablet', '1x sehari', '14 hari', 'Pagi hari', 2, 60000.00, 120000.00),

-- Prescription for Bella (Medical Record 2)
(2, 6, '1 tablet', '1x sehari', '3 hari', 'Pagi hari', 1, 130000.00, 130000.00),
(2, 8, 'Oles tipis', '2x sehari', '7 hari', 'Pada area yang terkena', 1, 40000.00, 40000.00),

-- Prescription for Charlie (Medical Record 3)
(3, 1, '500mg', '2x sehari', '7 hari', 'Sesudah makan, antibiotik post-operasi', 2, 25000.00, 50000.00),
(3, 5, '0.5mg', '1x sehari', '3 hari', 'Anti inflamasi post-operasi', 1, 20000.00, 20000.00);

-- Insert Test Vaccinations
INSERT INTO vaksinasi (pet_id, jenis_vaksin, tanggal_vaksin, tanggal_vaksin_berikutnya, dokter_id, nomor_batch, catatan, status) VALUES
(1, 'Rabies', '2023-11-15', '2024-11-15', 1, 'RAB-2023-1234', 'Vaksinasi tahunan', 'Completed'),
(1, 'Distemper', '2023-11-15', '2024-11-15', 1, 'DIS-2023-5678', 'Vaksinasi tahunan', 'Completed'),
(2, 'FVRCP', '2023-10-20', '2024-10-20', 3, 'FVR-2023-9012', 'Vaksinasi kucing', 'Completed'),
(3, 'Rabies', '2024-01-10', '2025-01-10', 1, 'RAB-2024-3456', 'Vaksinasi anjing dewasa', 'Completed'),
(4, 'Rabies', '2024-11-10', '2025-11-10', 1, 'RAB-2024-7890', 'Vaksinasi tahunan', 'Completed'),
(5, 'Rabies', '2023-07-25', '2024-07-25', 1, 'RAB-2023-1111', 'Vaksinasi anjing dewasa', 'Overdue'),
(6, 'Distemper', '2024-04-12', '2025-04-12', 1, 'DIS-2024-2222', 'Vaksinasi anjing muda', 'Scheduled'),
(7, 'FVRCP', '2024-08-18', '2025-08-18', 3, 'FVR-2024-3333', 'Vaksinasi kucing', 'Scheduled');

-- Insert Appointment Services (linking appointments to services for billing)
INSERT INTO appointment_layanan (appointment_id, layanan_id, jumlah, harga_satuan, subtotal, keterangan) VALUES
(1, 1, 1, 150000.00, 150000.00, 'Konsultasi + pemeriksaan fisik'),
(2, 4, 1, 200000.00, 200000.00, 'Grooming lengkap dengan styling'),
(3, 6, 1, 1200000.00, 1200000.00, 'Sterilisasi anjing dengan rawat inap'),
(4, 2, 1, 250000.00, 250000.00, 'Vaksinasi Rabies');

-- Verify all data inserted
SELECT 'Data insertion complete!' as Status;
SELECT COUNT(*) as Total_Owners FROM owner;
SELECT COUNT(*) as Total_Pets FROM pet;
SELECT COUNT(*) as Total_Doctors FROM veterinarian;
SELECT COUNT(*) as Total_Appointments FROM appointment;
SELECT COUNT(*) as Total_Medical_Records FROM medical_record;
SELECT COUNT(*) as Total_Medicines FROM medicine;
SELECT COUNT(*) as Total_Services FROM service;
SELECT COUNT(*) as Total_Prescriptions FROM resep;
SELECT COUNT(*) as Total_Vaccinations FROM vaksinasi;
