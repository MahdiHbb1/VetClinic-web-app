-- Create kategori table
CREATE TABLE IF NOT EXISTS kategori (
    kategori_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    tipe ENUM('Inventory', 'Service', 'Medicine') NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_nama_kategori (nama_kategori),
    INDEX idx_tipe (tipe),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    CONSTRAINT uq_nama_kategori_tipe UNIQUE (nama_kategori, tipe)
) ENGINE=InnoDB;

-- Add foreign key constraints to other tables that reference kategori
ALTER TABLE inventory
    ADD COLUMN IF NOT EXISTS kategori_id INT,
    ADD CONSTRAINT fk_inventory_kategori
    FOREIGN KEY (kategori_id) REFERENCES kategori(kategori_id);

ALTER TABLE service
    ADD COLUMN IF NOT EXISTS kategori_id INT,
    ADD CONSTRAINT fk_service_kategori
    FOREIGN KEY (kategori_id) REFERENCES kategori(kategori_id);

ALTER TABLE medicine
    ADD COLUMN IF NOT EXISTS kategori_id INT,
    ADD CONSTRAINT fk_medicine_kategori
    FOREIGN KEY (kategori_id) REFERENCES kategori(kategori_id);