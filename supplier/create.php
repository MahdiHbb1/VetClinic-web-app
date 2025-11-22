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

$page_title = "Tambah Dokter Hewan";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_dokter' => $_POST['nama_dokter'] ?? '',
        'no_lisensi' => $_POST['no_lisensi'] ?? '',
        'spesialisasi' => $_POST['spesialisasi'] ?? 'Umum',
        'no_telepon' => $_POST['no_telepon'] ?? '',
        'email' => $_POST['email'] ?? '',
        'jadwal_praktek' => $_POST['jadwal_praktek'] ?? '',
        'tanggal_bergabung' => $_POST['tanggal_bergabung'] ?? date('Y-m-d'),
        'status' => 'Aktif'
    ];

    // Validate input
    $errors = [];
    
    if (empty($data['nama_dokter'])) {
        $errors[] = "Nama dokter harus diisi";
    }

    if (empty($data['no_telepon'])) {
        $errors[] = "Nomor telepon harus diisi";
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Validate unique license number
    if (!empty($data['no_lisensi'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM veterinarian WHERE no_lisensi = ?");
        $stmt->execute([$data['no_lisensi']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nomor lisensi sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO veterinarian (
                    nama_dokter, no_lisensi, spesialisasi,
                    no_telepon, email, jadwal_praktek,
                    tanggal_bergabung, status
                ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?
                )
            ");

            $stmt->execute([
                $data['nama_dokter'],
                $data['no_lisensi'],
                $data['spesialisasi'],
                $data['no_telepon'],
                $data['email'],
                $data['jadwal_praktek'],
                $data['tanggal_bergabung'],
                $data['status']
            ]);

            $_SESSION['success'] = "Dokter hewan berhasil ditambahkan";
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