-- Create admin user with proper password hash
INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@vetclinic.com', 'Admin', 'Aktif', NOW());
