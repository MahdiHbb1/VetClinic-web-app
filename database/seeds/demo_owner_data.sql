-- Demo Owner Data for Testing Owner Portal
-- Password for all demo owners: "password"
-- Bcrypt hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- Create demo owner user account (use existing owner Budi Santoso - owner_id=3)
INSERT IGNORE INTO users (username, password, nama_lengkap, email, role, status)
VALUES 
('budi_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@example.com', 'Owner', 'Aktif');

-- Link existing owner to user account
UPDATE owner SET user_id = (SELECT user_id FROM users WHERE username = 'budi_owner') WHERE owner_id = 3;

-- Add more demo pets for owner 3 (Budi Santoso)
INSERT INTO pet (nama_hewan, jenis, ras, tanggal_lahir, jenis_kelamin, warna, berat_badan, owner_id, tanggal_registrasi)
VALUES 
('Max', 'Anjing', 'Golden Retriever', '2020-03-15', 'Jantan', 'Golden', 28.5, 3, '2023-01-10'),
('Bella', 'Kucing', 'Persian', '2021-06-20', 'Betina', 'White', 4.2, 3, '2023-03-15'),
('Charlie', 'Anjing', 'Beagle', '2019-11-10', 'Jantan', 'Tricolor', 12.3, 3, '2022-08-20');

-- Get the new pet IDs for vaccinations
SET @max_id = LAST_INSERT_ID();
SET @bella_id = @max_id + 1;
SET @charlie_id = @max_id + 2;

-- Add demo vaccination records (using existing dokter_id=3)
INSERT INTO vaksinasi (pet_id, jenis_vaksin, tanggal_vaksin, tanggal_vaksin_berikutnya, dokter_id, catatan, status)
VALUES 
(@max_id, 'Rabies', '2024-10-01', '2025-10-01', 3, 'Annual rabies vaccination - pet in good health', 'Completed'),
(@bella_id, 'FVRCP', '2024-09-15', '2025-03-15', 3, 'Core vaccine for cats - no adverse reactions', 'Completed'),
(@charlie_id, 'Distemper', '2024-08-20', '2025-02-20', 3, 'Routine vaccination', 'Completed');

-- Add demo appointments (using existing dokter_id=3)
INSERT INTO appointment (pet_id, owner_id, dokter_id, tanggal_appointment, jam_appointment, jenis_layanan, keluhan_awal, status, created_at)
VALUES 
(@max_id, 3, 3, '2025-11-28', '10:00:00', 'General Checkup', 'Regular health checkup', 'Scheduled', NOW()),
(@bella_id, 3, 3, '2025-12-05', '14:00:00', 'Vaccination', 'Annual vaccine due', 'Scheduled', NOW());

-- Add demo medical record for Max (using existing dokter_id=3)
INSERT INTO medical_record (pet_id, dokter_id, tanggal_kunjungan, keluhan, diagnosa, tindakan, catatan_dokter, berat_badan_saat_periksa, suhu_tubuh, status_kunjungan)
VALUES 
(@max_id, 3, '2024-10-01', 'Routine checkup', 'Healthy - all vitals normal', 'Physical examination, weight measurement', 'Pet is in excellent condition', 28.0, 38.5, 'Pemeriksaan');

-- Success message
SELECT 'Demo owner data created successfully! Login credentials:' as message
UNION ALL
SELECT 'Username: budi_owner | Password: password' as message
UNION ALL
SELECT 'Owner: Budi Santoso with 3 pets (Max, Bella, Charlie)' as message;
