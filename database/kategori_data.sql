-- Kategori untuk Inventaris
INSERT INTO kategori (nama_kategori, deskripsi, tipe, status, created_by, created_at) VALUES
('Peralatan Medis', 'Peralatan untuk pemeriksaan dan tindakan medis', 'Inventory', 'Active', 1, NOW()),
('Perlengkapan Kebersihan', 'Peralatan untuk menjaga kebersihan klinik', 'Inventory', 'Active', 1, NOW()),
('Peralatan Kantor', 'Perlengkapan administratif dan kantor', 'Inventory', 'Active', 1, NOW());

-- Kategori untuk Layanan
INSERT INTO kategori (nama_kategori, deskripsi, tipe, status, created_by, created_at) VALUES
('Pemeriksaan Rutin', 'Layanan pemeriksaan kesehatan rutin', 'Service', 'Active', 1, NOW()),
('Vaksinasi', 'Layanan vaksinasi hewan', 'Service', 'Active', 1, NOW()),
('Perawatan Gigi', 'Layanan perawatan gigi hewan', 'Service', 'Active', 1, NOW()),
('Grooming', 'Layanan perawatan dan kebersihan hewan', 'Service', 'Active', 1, NOW()),
('Operasi', 'Layanan pembedahan hewan', 'Service', 'Active', 1, NOW());

-- Kategori untuk Obat
INSERT INTO kategori (nama_kategori, deskripsi, tipe, status, created_by, created_at) VALUES
('Antibiotik', 'Obat untuk mengatasi infeksi bakteri', 'Medicine', 'Active', 1, NOW()),
('Antiparasit', 'Obat untuk mengatasi parasit', 'Medicine', 'Active', 1, NOW()),
('Vitamin', 'Suplemen vitamin untuk hewan', 'Medicine', 'Active', 1, NOW()),
('Obat Kulit', 'Obat untuk masalah kulit', 'Medicine', 'Active', 1, NOW()),
('Obat Mata', 'Obat untuk masalah mata', 'Medicine', 'Active', 1, NOW()),
('Obat Cair', 'Obat dalam bentuk cair/sirup', 'Medicine', 'Active', 1, NOW());