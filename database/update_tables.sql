-- Add kategori_id to inventory table if not exists
ALTER TABLE inventory 
ADD COLUMN IF NOT EXISTS kategori_id INT,
ADD CONSTRAINT fk_inventory_kategori 
    FOREIGN KEY (kategori_id) 
    REFERENCES kategori(kategori_id)
    ON UPDATE CASCADE;

-- Add kategori_id to service table if not exists
ALTER TABLE service
ADD COLUMN IF NOT EXISTS kategori_id INT,
ADD CONSTRAINT fk_service_kategori
    FOREIGN KEY (kategori_id)
    REFERENCES kategori(kategori_id)
    ON UPDATE CASCADE;

-- Add kategori_id to medicine table if not exists
ALTER TABLE medicine
ADD COLUMN IF NOT EXISTS kategori_id INT,
ADD CONSTRAINT fk_medicine_kategori
    FOREIGN KEY (kategori_id)
    REFERENCES kategori(kategori_id)
    ON UPDATE CASCADE;