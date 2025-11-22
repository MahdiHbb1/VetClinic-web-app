-- Create inventory table
CREATE TABLE IF NOT EXISTS inventory (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_item VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(20) NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Create service table
CREATE TABLE IF NOT EXISTS service (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_service VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    durasi INT NOT NULL, -- in minutes
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Create medicine table
CREATE TABLE IF NOT EXISTS medicine (
    medicine_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_medicine VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(20) NOT NULL,
    harga_beli DECIMAL(10,2) NOT NULL,
    harga_jual DECIMAL(10,2) NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB;