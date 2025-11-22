-- Migration: Owner Portal Support
-- Created: 2025-11-22
-- Description: Add tables and columns to support owner portal functionality

-- Note: user_id column already exists in owner table, skipping
-- Add foreign key constraint if not exists
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
                  WHERE CONSTRAINT_SCHEMA = 'vetclinic' 
                  AND CONSTRAINT_NAME = 'fk_owner_user' 
                  AND TABLE_NAME = 'owner');
SET @sql = IF(@fk_exists = 0, 
              'ALTER TABLE owner ADD CONSTRAINT fk_owner_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL', 
              'SELECT "FK already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create owner sessions table for security
CREATE TABLE IF NOT EXISTS owner_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    owner_id INT NOT NULL,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (owner_id) REFERENCES owner(owner_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_expires (expires_at),
    INDEX idx_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for performance optimization (skip if exists)
SET @idx1 = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='vetclinic' AND TABLE_NAME='pet' AND INDEX_NAME='idx_owner_registered');
SET @sql1 = IF(@idx1 = 0, 'ALTER TABLE pet ADD INDEX idx_owner_registered (owner_id, tanggal_registrasi)', 'SELECT "idx_owner_registered exists"');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @idx2 = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='vetclinic' AND TABLE_NAME='appointment' AND INDEX_NAME='idx_pet_status_date');
SET @sql2 = IF(@idx2 = 0, 'ALTER TABLE appointment ADD INDEX idx_pet_status_date (pet_id, status, tanggal_appointment)', 'SELECT "idx_pet_status_date exists"');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

SET @idx3 = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='vetclinic' AND TABLE_NAME='vaksinasi' AND INDEX_NAME='idx_pet_date');
SET @sql3 = IF(@idx3 = 0, 'ALTER TABLE vaksinasi ADD INDEX idx_pet_date (pet_id, tanggal_vaksin DESC)', 'SELECT "idx_pet_date exists"');
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

SET @idx4 = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='vetclinic' AND TABLE_NAME='medical_record' AND INDEX_NAME='idx_date');
SET @sql4 = IF(@idx4 = 0, 'ALTER TABLE medical_record ADD INDEX idx_date (tanggal_kunjungan DESC)', 'SELECT "idx_date exists"');
PREPARE stmt4 FROM @sql4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

-- Update users table to support owner role
ALTER TABLE users MODIFY COLUMN role ENUM('Admin', 'Dokter', 'Staff', 'Owner') NOT NULL DEFAULT 'Staff';

-- Update appointment status enum to include Scheduled
ALTER TABLE appointment MODIFY COLUMN status ENUM('Pending','Confirmed','Scheduled','Completed','Cancelled','No_Show') DEFAULT 'Pending';
