-- VetClinic Management System Database Schema

-- Drop database if exists (BE CAREFUL WITH THIS IN PRODUCTION!)
DROP DATABASE IF EXISTS vetclinic;

-- Create database
CREATE DATABASE IF NOT EXISTS vetclinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use database
USE vetclinic;

-- Create owners table
CREATE TABLE owner (
    owner_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_telepon VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE,
    tanggal_registrasi DATETIME DEFAULT CURRENT_TIMESTAMP,
    catatan TEXT
) ENGINE=InnoDB;

-- Create pets table
CREATE TABLE pet (
    pet_id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    nama_hewan VARCHAR(50) NOT NULL,
    jenis ENUM('Anjing', 'Kucing', 'Burung', 'Kelinci', 'Hamster', 'Reptil', 'Lainnya') NOT NULL,
    ras VARCHAR(50),
    jenis_kelamin ENUM('Jantan', 'Betina'),
    tanggal_lahir DATE,
    berat_badan DECIMAL(5,2),
    warna VARCHAR(50),
    ciri_khusus TEXT,
    foto_url VARCHAR(255),
    status ENUM('Aktif', 'Meninggal') DEFAULT 'Aktif',
    tanggal_registrasi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES owner(owner_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create veterinarians table
CREATE TABLE veterinarian (
    dokter_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_dokter VARCHAR(100) NOT NULL,
    no_lisensi VARCHAR(50) UNIQUE,
    spesialisasi ENUM('Umum', 'Bedah', 'Gigi', 'Kulit', 'Kardio', 'Eksotik'),
    no_telepon VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    jadwal_praktek VARCHAR(100),
    status ENUM('Aktif', 'Cuti', 'Resign') DEFAULT 'Aktif',
    foto_url VARCHAR(255),
    tanggal_bergabung DATE NOT NULL
) ENGINE=InnoDB;

-- Create services table
CREATE TABLE service (
    layanan_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_layanan VARCHAR(100) NOT NULL,
    kategori ENUM('Pemeriksaan', 'Vaksinasi', 'Grooming', 'Bedah', 'Rawat_Inap', 'Tes_Lab', 'Emergency'),
    harga DECIMAL(10,2) NOT NULL,
    durasi_estimasi INT,
    deskripsi TEXT,
    status_tersedia BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

-- Create appointments table
CREATE TABLE appointment (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    pet_id INT NOT NULL,
    owner_id INT NOT NULL,
    dokter_id INT NOT NULL,
    tanggal_appointment DATE NOT NULL,
    jam_appointment TIME NOT NULL,
    jenis_layanan VARCHAR(100),
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled', 'No_Show'),
    keluhan_awal TEXT,
    catatan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pet(pet_id),
    FOREIGN KEY (owner_id) REFERENCES owner(owner_id),
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id)
) ENGINE=InnoDB;

-- Create medical records table
CREATE TABLE medical_record (
    rekam_id INT PRIMARY KEY AUTO_INCREMENT,
    pet_id INT NOT NULL,
    dokter_id INT NOT NULL,
    appointment_id INT,
    tanggal_kunjungan DATETIME DEFAULT CURRENT_TIMESTAMP,
    keluhan TEXT NOT NULL,
    diagnosa TEXT NOT NULL,
    tindakan TEXT,
    catatan_dokter TEXT,
    berat_badan_saat_periksa DECIMAL(5,2),
    suhu_tubuh DECIMAL(4,1),
    status_kunjungan ENUM('Konsultasi', 'Pemeriksaan', 'Operasi', 'Emergency'),
    FOREIGN KEY (pet_id) REFERENCES pet(pet_id),
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id),
    FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id)
) ENGINE=InnoDB;

-- Create medicines table
CREATE TABLE medicine (
    obat_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_obat VARCHAR(100) NOT NULL,
    kategori ENUM('Antibiotik', 'Vitamin', 'Vaksin', 'Anti_Parasit', 'Suplemen', 'Alat_Medis', 'Lainnya'),
    bentuk_sediaan ENUM('Tablet', 'Kapsul', 'Sirup', 'Injeksi', 'Salep', 'Tetes', 'Lainnya'),
    satuan VARCHAR(20) NOT NULL,
    stok INT DEFAULT 0,
    harga_beli DECIMAL(10,2) NOT NULL,
    harga_jual DECIMAL(10,2) NOT NULL,
    expired_date DATE,
    supplier VARCHAR(100),
    deskripsi TEXT,
    status_tersedia BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

-- Create prescriptions table
CREATE TABLE resep (
    resep_id INT PRIMARY KEY AUTO_INCREMENT,
    rekam_id INT NOT NULL,
    obat_id INT NOT NULL,
    dosis VARCHAR(50) NOT NULL,
    frekuensi VARCHAR(50) NOT NULL,
    durasi VARCHAR(50) NOT NULL,
    cara_pakai TEXT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (rekam_id) REFERENCES medical_record(rekam_id),
    FOREIGN KEY (obat_id) REFERENCES medicine(obat_id)
) ENGINE=InnoDB;

-- Create appointment services table
CREATE TABLE appointment_layanan (
    appointment_layanan_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    layanan_id INT NOT NULL,
    jumlah INT DEFAULT 1,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    keterangan TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id),
    FOREIGN KEY (layanan_id) REFERENCES service(layanan_id)
) ENGINE=InnoDB;

-- Create vaccination schedule table
CREATE TABLE vaksinasi (
    vaksinasi_id INT PRIMARY KEY AUTO_INCREMENT,
    pet_id INT NOT NULL,
    jenis_vaksin ENUM('Rabies', 'Distemper', 'Parvovirus', 'Feline_Leukemia', 'FVRCP', 'Bordetella', 'Lainnya'),
    tanggal_vaksin DATE NOT NULL,
    tanggal_vaksin_berikutnya DATE,
    dokter_id INT NOT NULL,
    nomor_batch VARCHAR(50),
    catatan TEXT,
    status ENUM('Scheduled', 'Completed', 'Overdue', 'Cancelled'),
    FOREIGN KEY (pet_id) REFERENCES pet(pet_id),
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id)
) ENGINE=InnoDB;

-- Create users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('Admin', 'Dokter', 'Staff') DEFAULT 'Staff',
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
) ENGINE=InnoDB;

-- Insert default admin user
-- Password: admin123 (hashed)
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@vetclinic.com', 'Admin');

-- Insert sample data for testing
INSERT INTO owner (nama_lengkap, alamat, no_telepon, email) VALUES
('John Doe', 'Jl. Contoh No. 123', '081234567890', 'john@example.com'),
('Jane Smith', 'Jl. Sample No. 456', '082345678901', 'jane@example.com');

INSERT INTO veterinarian (nama_dokter, no_lisensi, spesialisasi, no_telepon, email, tanggal_bergabung) VALUES
('Dr. Ahmad', 'VET001', 'Umum', '081234567891', 'ahmad@vetclinic.com', '2023-01-01'),
('Dr. Sarah', 'VET002', 'Bedah', '081234567892', 'sarah@vetclinic.com', '2023-01-01');

INSERT INTO service (nama_layanan, kategori, harga, durasi_estimasi) VALUES
('Konsultasi Umum', 'Pemeriksaan', 150000.00, 30),
('Vaksinasi Rabies', 'Vaksinasi', 250000.00, 30),
('Grooming Kucing', 'Grooming', 200000.00, 120);

-- Create indexes for better performance
CREATE INDEX idx_pet_owner ON pet(owner_id);
CREATE INDEX idx_appointment_pet ON appointment(pet_id);
CREATE INDEX idx_appointment_dokter ON appointment(dokter_id);
CREATE INDEX idx_medical_record_pet ON medical_record(pet_id);
CREATE INDEX idx_vaksinasi_pet ON vaksinasi(pet_id);
CREATE INDEX idx_resep_rekam ON resep(rekam_id);
CREATE INDEX idx_medicine_kategori ON medicine(kategori);
CREATE INDEX idx_service_kategori ON service(kategori);