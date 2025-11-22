-- Performance Optimization: Add indexes to improve query speed
-- This will reduce loading time and flickering issues

USE vetclinic;

-- Appointment table indexes for faster date queries
ALTER TABLE appointment 
ADD INDEX idx_tanggal_appointment (tanggal_appointment),
ADD INDEX idx_status (status),
ADD INDEX idx_dokter_id (dokter_id),
ADD INDEX idx_owner_id (owner_id),
ADD INDEX idx_pet_id (pet_id);

-- Appointment layanan indexes
ALTER TABLE appointment_layanan
ADD INDEX idx_appointment_id (appointment_id),
ADD INDEX idx_layanan_id (layanan_id);

-- Medical record indexes
ALTER TABLE medical_record
ADD INDEX idx_pet_id (pet_id),
ADD INDEX idx_tanggal_kunjungan (tanggal_kunjungan);

-- Pet indexes
ALTER TABLE pet
ADD INDEX idx_owner_id (owner_id),
ADD INDEX idx_status (status);

-- Medicine indexes
ALTER TABLE medicine
ADD INDEX idx_kategori (kategori),
ADD INDEX idx_status_tersedia (status_tersedia);

-- Service indexes
ALTER TABLE service
ADD INDEX idx_kategori (kategori),
ADD INDEX idx_status_tersedia (status_tersedia);

-- Show all indexes to verify
SHOW INDEX FROM appointment;
SHOW INDEX FROM appointment_layanan;
SHOW INDEX FROM medical_record;
SHOW INDEX FROM pet;
SHOW INDEX FROM medicine;
SHOW INDEX FROM service;
