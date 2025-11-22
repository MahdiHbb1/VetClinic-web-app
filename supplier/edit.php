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

// Get supplier ID from URL
$supplier_id = $_GET['id'] ?? null;
if (!$supplier_id) {
    $_SESSION['error'] = "ID Supplier tidak valid";
    header("Location: index.php");
    exit;
}

// Fetch supplier details
$stmt = $pdo->prepare("SELECT * FROM supplier WHERE supplier_id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    $_SESSION['error'] = "Supplier tidak ditemukan";
    header("Location: index.php");
    exit;
}

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
        'status' => $_POST['status'] ?? 'Active'
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
    if ($data['nama_supplier'] !== $supplier['nama_supplier']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM supplier WHERE nama_supplier = ? AND supplier_id != ?");
        $stmt->execute([$data['nama_supplier'], $supplier_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nama supplier sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE supplier SET
                    nama_supplier = ?,
                    kontak = ?,
                    email = ?,
                    alamat = ?,
                    npwp = ?,
                    bank_name = ?,
                    bank_account = ?,
                    bank_account_name = ?,
                    notes = ?,
                    status = ?,
                    updated_by = ?,
                    updated_at = NOW()
                WHERE supplier_id = ?
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
                $_SESSION['user_id'],
                $supplier_id
            ]);

            $_SESSION['success'] = "Supplier berhasil diperbarui";
            header("Location: detail.php?id=" . $supplier_id);
            exit;

        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

$page_title = "Edit Supplier: " . $supplier['nama_supplier'];

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Supplier</h2>
            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['nama_supplier']); ?></p>
        </div>
        
        <a href="detail.php?id=<?php echo $supplier['supplier_id']; ?>" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
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
                       value="<?php echo htmlspecialchars($supplier['nama_supplier']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="Active" <?php echo $supplier['status'] === 'Active' ? 'selected' : ''; ?>>
                        Aktif
                    </option>
                    <option value="Inactive" <?php echo $supplier['status'] === 'Inactive' ? 'selected' : ''; ?>>
                        Nonaktif
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Kontak <span class="text-red-600">*</span>
                </label>
                <input type="text" name="kontak"
                       value="<?php echo htmlspecialchars($supplier['kontak']); ?>"
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
                       value="<?php echo htmlspecialchars($supplier['email']); ?>"
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
                       value="<?php echo htmlspecialchars($supplier['npwp']); ?>"
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
                      placeholder="Alamat lengkap supplier..."><?php echo htmlspecialchars($supplier['alamat']); ?></textarea>
        </div>

        <!-- Bank Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Bank
                </label>
                <input type="text" name="bank_name"
                       value="<?php echo htmlspecialchars($supplier['bank_name']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Rekening
                </label>
                <input type="text" name="bank_account"
                       value="<?php echo htmlspecialchars($supplier['bank_account']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Pemilik Rekening
                </label>
                <input type="text" name="bank_account_name"
                       value="<?php echo htmlspecialchars($supplier['bank_account_name']); ?>"
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
                      placeholder="Catatan tambahan..."><?php echo htmlspecialchars($supplier['notes']); ?></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="detail.php?id=<?php echo $supplier['supplier_id']; ?>" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>