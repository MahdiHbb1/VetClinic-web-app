<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

// Check role authorization
if (!in_array($_SESSION['role'], ['Admin', 'Inventory'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut";
    header("Location: index.php");
    exit;
}

$page_title = "Tambah Supplier";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_supplier' => $_POST['nama_supplier'] ?? '',
        'kontak' => $_POST['kontak'] ?? '',
        'email' => $_POST['email'] ?? '',
        'alamat' => $_POST['alamat'] ?? '',
        'npwp' => $_POST['npwp'] ?? '',
        'bank_name' => $_POST['bank_name'] ?? '',
        'bank_account' => $_POST['bank_account'] ?? '',
        'bank_account_name' => $_POST['bank_account_name'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'status' => 'Active'
    ];

    // Validate input
    $errors = [];
    
    if (empty($data['nama_supplier'])) {
        $errors[] = "Nama supplier harus diisi";
    }

    if (empty($data['kontak']) && empty($data['email'])) {
        $errors[] = "Minimal salah satu dari kontak atau email harus diisi";
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Validate unique supplier name
    if (!empty($data['nama_supplier'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM supplier WHERE nama_supplier = ?");
        $stmt->execute([$data['nama_supplier']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nama supplier sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO supplier (
                    nama_supplier, kontak, email, 
                    alamat, npwp, bank_name,
                    bank_account, bank_account_name, notes,
                    status, created_by, created_at
                ) VALUES (
                    ?, ?, ?, 
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $data['nama_supplier'],
                $data['kontak'],
                $data['email'],
                $data['alamat'],
                $data['npwp'],
                $data['bank_name'],
                $data['bank_account'],
                $data['bank_account_name'],
                $data['notes'],
                $data['status'],
                $_SESSION['user_id']
            ]);

            $_SESSION['success'] = "Supplier berhasil ditambahkan";
            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Supplier</h2>
            <p class="text-gray-600">Tambah supplier baru</p>
        </div>
        
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="bg-white rounded-lg shadow-md p-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Supplier <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama_supplier" required
                       value="<?php echo $_POST['nama_supplier'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Kontak <span class="text-red-600">*</span>
                </label>
                <input type="text" name="kontak"
                       value="<?php echo $_POST['kontak'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="No. Telepon/HP/WhatsApp">
                <p class="mt-1 text-sm text-gray-500">
                    Minimal salah satu dari kontak atau email harus diisi
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-600">*</span>
                </label>
                <input type="email" name="email"
                       value="<?php echo $_POST['email'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="email@supplier.com">
                <p class="mt-1 text-sm text-gray-500">
                    Minimal salah satu dari kontak atau email harus diisi
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    NPWP
                </label>
                <input type="text" name="npwp"
                       value="<?php echo $_POST['npwp'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="00.000.000.0-000.000">
            </div>
        </div>

        <!-- Address -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Alamat
            </label>
            <textarea name="alamat" rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                      placeholder="Alamat lengkap supplier..."><?php echo $_POST['alamat'] ?? ''; ?></textarea>
        </div>

        <!-- Bank Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Bank
                </label>
                <input type="text" name="bank_name"
                       value="<?php echo $_POST['bank_name'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Rekening
                </label>
                <input type="text" name="bank_account"
                       value="<?php echo $_POST['bank_account'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Pemilik Rekening
                </label>
                <input type="text" name="bank_account_name"
                       value="<?php echo $_POST['bank_account_name'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Catatan
            </label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                      placeholder="Catatan tambahan..."><?php echo $_POST['notes'] ?? ''; ?></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>