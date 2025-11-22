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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

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

// Fetch veterinarian details (supplier folder manages veterinarian table)
// Map veterinarian columns to expected supplier field names
$stmt = $pdo->prepare("
    SELECT 
        dokter_id as supplier_id,
        nama_dokter as nama_supplier,
        no_lisensi,
        spesialisasi,
        no_telepon as kontak,
        email,
        jadwal_praktek,
        status,
        foto_url,
        tanggal_bergabung,
        tanggal_bergabung as created_at,
        '' as alamat,
        NULL as npwp,
        NULL as notes,
        NULL as bank_name,
        NULL as bank_account,
        NULL as bank_account_name
    FROM veterinarian 
    WHERE dokter_id = ?
");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    $_SESSION['error'] = "Dokter hewan tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Map form fields (using supplier naming) to database columns (veterinarian table)
    $data = [
        'nama_dokter' => $_POST['nama_supplier'] ?? '',
        'no_lisensi' => $_POST['no_lisensi'] ?? '',
        'spesialisasi' => $_POST['spesialisasi'] ?? 'Umum',
        'no_telepon' => $_POST['kontak'] ?? '',
        'email' => $_POST['email'] ?? '',
        'jadwal_praktek' => $_POST['jadwal_praktek'] ?? '',
        'status' => $_POST['status'] ?? 'Aktif'
    ];

    // Validate input
    $errors = [];
    
    if (empty($data['nama_dokter'])) {
        $errors[] = "Nama dokter hewan harus diisi";
    }

    if (empty($data['no_telepon']) && empty($data['email'])) {
        $errors[] = "Minimal salah satu dari kontak atau email harus diisi";
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Validate unique license number
    if (!empty($data['no_lisensi']) && $data['no_lisensi'] !== $supplier['no_lisensi']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM veterinarian WHERE no_lisensi = ? AND dokter_id != ?");
        $stmt->execute([$data['no_lisensi'], $supplier_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nomor lisensi sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE veterinarian SET
                    nama_dokter = ?,
                    no_lisensi = ?,
                    spesialisasi = ?,
                    no_telepon = ?,
                    email = ?,
                    jadwal_praktek = ?,
                    status = ?
                WHERE dokter_id = ?
            ");

            $stmt->execute([
                $data['nama_dokter'],
                $data['no_lisensi'],
                $data['spesialisasi'],
                $data['no_telepon'],
                $data['email'],
                $data['jadwal_praktek'],
                $data['status'],
                $supplier_id
            ]);

            $_SESSION['success'] = "Dokter hewan berhasil diperbarui";
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
                    Nama Dokter Hewan <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama_supplier" required
                       value="<?php echo htmlspecialchars($supplier['nama_supplier']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Lisensi
                </label>
                <input type="text" name="no_lisensi"
                       value="<?php echo htmlspecialchars($supplier['no_lisensi'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Nomor lisensi praktek">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Spesialisasi <span class="text-red-600">*</span>
                </label>
                <select name="spesialisasi" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="Umum" <?php echo $supplier['spesialisasi'] === 'Umum' ? 'selected' : ''; ?>>Umum</option>
                    <option value="Bedah" <?php echo $supplier['spesialisasi'] === 'Bedah' ? 'selected' : ''; ?>>Bedah</option>
                    <option value="Gigi" <?php echo $supplier['spesialisasi'] === 'Gigi' ? 'selected' : ''; ?>>Gigi</option>
                    <option value="Kulit" <?php echo $supplier['spesialisasi'] === 'Kulit' ? 'selected' : ''; ?>>Kulit</option>
                    <option value="Kardio" <?php echo $supplier['spesialisasi'] === 'Kardio' ? 'selected' : ''; ?>>Kardio</option>
                    <option value="Eksotik" <?php echo $supplier['spesialisasi'] === 'Eksotik' ? 'selected' : ''; ?>>Eksotik</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="Aktif" <?php echo $supplier['status'] === 'Aktif' ? 'selected' : ''; ?>>
                        Aktif
                    </option>
                    <option value="Cuti" <?php echo $supplier['status'] === 'Cuti' ? 'selected' : ''; ?>>
                        Cuti
                    </option>
                    <option value="Resign" <?php echo $supplier['status'] === 'Resign' ? 'selected' : ''; ?>>
                        Resign
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Kontak <span class="text-red-600">*</span>
                </label>
                <input type="text" name="kontak"
                       value="<?php echo htmlspecialchars($supplier['kontak'] ?? ''); ?>"
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
                       value="<?php echo htmlspecialchars($supplier['email'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="email@dokter.com">
                <p class="mt-1 text-sm text-gray-500">
                    Minimal salah satu dari kontak atau email harus diisi
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Jadwal Praktek
                </label>
                <input type="text" name="jadwal_praktek"
                       value="<?php echo htmlspecialchars($supplier['jadwal_praktek'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Contoh: Senin-Jumat 08:00-16:00">
            </div>
        </div>

        <!-- Optional Fields (kept for compatibility but hidden or informational) -->
        <div class="hidden">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    NPWP
                </label>
                <input type="text" name="npwp"
                       value="<?php echo htmlspecialchars($supplier['npwp'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="00.000.000.0-000.000">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alamat
                </label>
                <textarea name="alamat" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Alamat lengkap supplier..."><?php echo htmlspecialchars($supplier['alamat'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Bank
                </label>
                <input type="text" name="bank_name"
                       value="<?php echo htmlspecialchars($supplier['bank_name'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Rekening
                </label>
                <input type="text" name="bank_account"
                       value="<?php echo htmlspecialchars($supplier['bank_account'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Pemilik Rekening
                </label>
                <input type="text" name="bank_account_name"
                       value="<?php echo htmlspecialchars($supplier['bank_account_name'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan
                </label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Catatan tambahan..."><?php echo htmlspecialchars($supplier['notes'] ?? ''); ?></textarea>
            </div>
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