<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user has permission
if (!in_array($_SESSION['role'], ['Admin', 'Inventory', 'Service'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut!";
    header("Location: index.php");
    exit;
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

$page_title = "Tambah Kategori";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['nama_kategori', 'tipe', 'status'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field $field harus diisi!");
            }
        }

        // Validate tipe
        if (!in_array($_POST['tipe'], ['Inventory', 'Service', 'Medicine'])) {
            throw new Exception("Tipe kategori tidak valid!");
        }

        // Validate status
        if (!in_array($_POST['status'], ['Active', 'Inactive'])) {
            throw new Exception("Status tidak valid!");
        }

        // Check if category name already exists for the same type
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kategori WHERE nama_kategori = ? AND tipe = ?");
        $stmt->execute([$_POST['nama_kategori'], $_POST['tipe']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Nama kategori sudah ada untuk tipe yang dipilih!");
        }

        // Prepare data
        $data = [
            'nama_kategori' => trim($_POST['nama_kategori']),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'tipe' => $_POST['tipe'],
            'status' => $_POST['status'],
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insert category
        $stmt = $pdo->prepare("
            INSERT INTO kategori (nama_kategori, deskripsi, tipe, status, created_by, created_at)
            VALUES (:nama_kategori, :deskripsi, :tipe, :status, :created_by, :created_at)
        ");
        
        $stmt->execute($data);
        
        $_SESSION['success'] = "Kategori berhasil ditambahkan!";
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Kategori Baru</h1>
            <p class="text-sm text-gray-600">Buat kategori baru untuk inventaris, layanan, atau obat</p>
        </div>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                           id="nama_kategori" 
                           name="nama_kategori" 
                           required
                           value="<?php echo $_POST['nama_kategori'] ?? ''; ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="text-sm text-gray-500 mt-1">
                        Masukkan nama kategori yang unik
                    </p>
                </div>

                <div>
                    <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipe <span class="text-red-600">*</span>
                    </label>
                    <select id="tipe" 
                            name="tipe" 
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Pilih Tipe</option>
                        <option value="Inventory" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] === 'Inventory') ? 'selected' : ''; ?>>Inventaris</option>
                        <option value="Service" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] === 'Service') ? 'selected' : ''; ?>>Layanan</option>
                        <option value="Medicine" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] === 'Medicine') ? 'selected' : ''; ?>>Obat</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        Pilih tipe kategori sesuai penggunaan
                    </p>
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi
                    </label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"><?php echo $_POST['deskripsi'] ?? ''; ?></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        Tambahkan deskripsi detail tentang kategori (opsional)
                    </p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Pilih Status</option>
                        <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        Tentukan status awal kategori
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="index.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg inline-flex items-center">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>