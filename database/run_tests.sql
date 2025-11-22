-- Test Script to verify all functionality
-- This script will check data and then perform test operations

USE vetclinic;

-- Show current data counts
SELECT 'Current Database Status' as Info;
SELECT 
    (SELECT COUNT(*) FROM owner) as Owners,
    (SELECT COUNT(*) FROM pet) as Pets,
    (SELECT COUNT(*) FROM veterinarian) as Doctors,
    (SELECT COUNT(*) FROM appointment) as Appointments,
    (SELECT COUNT(*) FROM medical_record) as Medical_Records,
    (SELECT COUNT(*) FROM medicine) as Medicines;

-- Test 1: Insert new pet for existing owner
SELECT 'TEST 1: Creating new pet' as Test;
INSERT INTO pet (owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, status, tanggal_registrasi)
VALUES (3, 'TestPet', 'Anjing', 'Labrador', 'Jantan', '2023-01-15', 25.00, 'Kuning', 'Aktif', NOW());

SELECT pet_id, nama_hewan, jenis FROM pet WHERE nama_hewan = 'TestPet';

-- Test 2: Create appointment for the test pet
SELECT 'TEST 2: Creating appointment' as Test;
SET @test_pet_id = (SELECT pet_id FROM pet WHERE nama_hewan = 'TestPet' LIMIT 1);
SET @test_doctor_id = (SELECT dokter_id FROM veterinarian LIMIT 1);

INSERT INTO appointment (pet_id, owner_id, dokter_id, tanggal_appointment, jam_appointment, jenis_layanan, status, keluhan_awal, created_at)
VALUES (@test_pet_id, 3, @test_doctor_id, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 'Test Konsultasi', 'Pending', 'Test keluhan', NOW());

SELECT appointment_id, jenis_layanan, status, tanggal_appointment FROM appointment WHERE jenis_layanan = 'Test Konsultasi';

-- Test 3: Create medical record
SELECT 'TEST 3: Creating medical record' as Test;
SET @test_appointment_id = (SELECT appointment_id FROM appointment WHERE jenis_layanan = 'Test Konsultasi' LIMIT 1);

INSERT INTO medical_record (pet_id, dokter_id, appointment_id, tanggal_kunjungan, keluhan, diagnosa, tindakan, berat_badan_saat_periksa, suhu_tubuh, status_kunjungan)
VALUES (@test_pet_id, @test_doctor_id, @test_appointment_id, NOW(), 'Test keluhan', 'Test diagnosa', 'Test tindakan', 25.50, 38.5, 'Pemeriksaan');

SELECT rekam_id, diagnosa, tanggal_kunjungan FROM medical_record WHERE diagnosa = 'Test diagnosa';

-- Test 4: Add prescription
SELECT 'TEST 4: Creating prescription' as Test;
SET @test_rekam_id = (SELECT rekam_id FROM medical_record WHERE diagnosa = 'Test diagnosa' LIMIT 1);
SET @test_obat_id = (SELECT obat_id FROM medicine LIMIT 1);

INSERT INTO resep (rekam_id, obat_id, dosis, frekuensi, durasi, cara_pakai, jumlah, harga_satuan, subtotal)
VALUES (@test_rekam_id, @test_obat_id, '500mg', '2x sehari', '7 hari', 'Sesudah makan', 2, 25000.00, 50000.00);

SELECT resep_id, dosis, frekuensi FROM resep WHERE rekam_id = @test_rekam_id;

-- Test 5: Query with JOINs (similar to actual application queries)
SELECT 'TEST 5: Complex JOIN query' as Test;
SELECT 
    a.appointment_id,
    p.nama_hewan,
    o.nama_lengkap as owner_name,
    v.nama_dokter,
    a.tanggal_appointment,
    a.jam_appointment,
    a.status
FROM appointment a
JOIN pet p ON a.pet_id = p.pet_id
JOIN owner o ON a.owner_id = o.owner_id
JOIN veterinarian v ON a.dokter_id = v.dokter_id
WHERE a.jenis_layanan = 'Test Konsultasi';

-- Test 6: Medical record with prescriptions
SELECT 'TEST 6: Medical record with prescriptions' as Test;
SELECT 
    mr.rekam_id,
    p.nama_hewan,
    v.nama_dokter,
    mr.diagnosa,
    m.nama_obat,
    r.dosis,
    r.subtotal
FROM medical_record mr
JOIN pet p ON mr.pet_id = p.pet_id
JOIN veterinarian v ON mr.dokter_id = v.dokter_id
JOIN resep r ON mr.rekam_id = r.rekam_id
JOIN medicine m ON r.obat_id = m.obat_id
WHERE mr.diagnosa = 'Test diagnosa';

-- Test 7: Check doctor availability (no conflicts)
SELECT 'TEST 7: Doctor availability check' as Test;
SELECT 
    v.nama_dokter,
    COUNT(a.appointment_id) as appointments_today
FROM veterinarian v
LEFT JOIN appointment a ON v.dokter_id = a.dokter_id 
    AND a.tanggal_appointment = CURDATE()
    AND a.status NOT IN ('Cancelled', 'No_Show')
GROUP BY v.dokter_id, v.nama_dokter;

-- Test 8: Inventory status
SELECT 'TEST 8: Inventory check' as Test;
SELECT 
    nama_obat,
    stok,
    CASE 
        WHEN expired_date < CURDATE() THEN 'Expired'
        WHEN stok = 0 THEN 'Out of Stock'
        WHEN stok <= 10 THEN 'Low Stock'
        ELSE 'In Stock'
    END as status_calculated
FROM medicine
LIMIT 5;

-- Clean up test data
SELECT 'Cleaning up test data...' as Info;
DELETE FROM resep WHERE rekam_id IN (SELECT rekam_id FROM medical_record WHERE diagnosa = 'Test diagnosa');
DELETE FROM medical_record WHERE diagnosa = 'Test diagnosa';
DELETE FROM appointment WHERE jenis_layanan = 'Test Konsultasi';
DELETE FROM pet WHERE nama_hewan = 'TestPet';

SELECT 'All tests completed successfully!' as Result;
