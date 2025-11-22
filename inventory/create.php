<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/inventory_functions.php';

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

$page_title = "Tambah Item";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_item' => $_POST['nama_item'] ?? '',
        'kategori_id' => $_POST['kategori_id'] ?? '',
        'bentuk_sediaan' => $_POST['bentuk_sediaan'] ?? 'Tablet',
        'supplier_id' => $_POST['supplier_id'] ?? null,
        'deskripsi' => $_POST['deskripsi'] ?? '',
        'current_stock' => $_POST['current_stock'] ? (int)$_POST['current_stock'] : 0,
        'satuan' => $_POST['satuan'] ?? '',
        'harga_beli' => $_POST['harga_beli'] ? str_replace(['.', ','], '', $_POST['harga_beli']) : 0,
        'harga_jual' => $_POST['harga_jual'] ? str_replace(['.', ','], '', $_POST['harga_jual']) : 0,
        'expired_date' => $_POST['expired_date'] ?? null
    ];

    // Validate input
    $errors = validate_inventory_item($data);

    // Validate unique nama_obat - medicine table doesn't have kode_item
    if (!empty($data['nama_item'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM medicine WHERE nama_obat = ?");
        $stmt->execute([$data['nama_item']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nama obat sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert medicine item - medicine table uses different columns
            $stmt = $pdo->prepare("
                INSERT INTO medicine (
                    nama_obat, kategori, bentuk_sediaan,
                    satuan, stok, harga_beli,
                    harga_jual, expired_date, supplier,
                    deskripsi, status_tersedia
                ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?
                )
            ");

            $stmt->execute([
                $data['nama_item'],
                $data['kategori_id'],
                $data['bentuk_sediaan'],
                $data['satuan'],
                $data['current_stock'],
                $data['harga_beli'],
                $data['harga_jual'],
                $data['expired_date'],
                $data['supplier_id'],
                $data['deskripsi'],
                1 // status_tersedia = true
            ]);

            $obat_id = $pdo->lastInsertId();

            // Stock movement tracking removed - table doesn't exist

            $pdo->commit();
            $_SESSION['success'] = "Item berhasil ditambahkan";
            header("Location: detail.php?id=" . $obat_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Categories are ENUM values in medicine table
$categories = [
    ['value' => 'Antibiotik', 'label' => 'Antibiotik'],
    ['value' => 'Vitamin', 'label' => 'Vitamin'],
    ['value' => 'Vaksin', 'label' => 'Vaksin'],
    ['value' => 'Anti_Parasit', 'label' => 'Anti Parasit'],
    ['value' => 'Suplemen', 'label' => 'Suplemen'],
    ['value' => 'Alat_Medis', 'label' => 'Alat Medis'],
    ['value' => 'Lainnya', 'label' => 'Lainnya']
];

// Bentuk sediaan are ENUM values
$bentuk_sediaan_options = [
    'Tablet', 'Kapsul', 'Sirup', 'Injeksi', 'Salep', 'Tetes', 'Lainnya'
];

// Get suppliers from medicine table
$stmt = $pdo->prepare("
    SELECT DISTINCT supplier
    FROM medicine
    WHERE supplier IS NOT NULL AND supplier != ''
    ORDER BY supplier
");
$stmt->execute();
$suppliers = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Item</h2>
            <p class="text-gray-600">Tambah item baru ke inventaris</p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Obat <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama_item" required
                       value="<?php echo $_POST['nama_item'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kategori <span class="text-red-600">*</span>
                </label>
                <select name="kategori_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['value']; ?>"
                                <?php echo isset($_POST['kategori_id']) && $_POST['kategori_id'] == $category['value'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Bentuk Sediaan <span class="text-red-600">*</span>
                </label>
                <select name="bentuk_sediaan" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Bentuk Sediaan</option>
                    <?php foreach ($bentuk_sediaan_options as $bentuk): ?>
                        <option value="<?php echo $bentuk; ?>"
                                <?php echo isset($_POST['bentuk_sediaan']) && $_POST['bentuk_sediaan'] == $bentuk ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($bentuk); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Supplier
                </label>
                <input type="text" name="supplier_id" list="suppliers_list"
                       value="<?php echo $_POST['supplier_id'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Nama supplier...">
                <datalist id="suppliers_list">
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo htmlspecialchars($supplier['supplier']); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <!-- Stock Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Stok Awal <span class="text-red-600">*</span>
                </label>
                <input type="number" name="current_stock" required min="0"
                       value="<?php echo $_POST['current_stock'] ?? '0'; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Satuan <span class="text-red-600">*</span>
                </label>
                <select name="satuan" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Satuan</option>
                    <option value="Pcs" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'Pcs' ? 'selected' : ''; ?>>Pcs</option>
                    <option value="Box" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'Box' ? 'selected' : ''; ?>>Box</option>
                    <option value="Botol" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'Botol' ? 'selected' : ''; ?>>Botol</option>
                    <option value="Strip" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'Strip' ? 'selected' : ''; ?>>Strip</option>
                    <option value="Tablet" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                    <option value="Kapsul" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'Kapsul' ? 'selected' : ''; ?>>Kapsul</option>
                    <option value="mL" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'mL' ? 'selected' : ''; ?>>mL</option>
                    <option value="mg" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'mg' ? 'selected' : ''; ?>>mg</option>
                    <option value="g" <?php echo isset($_POST['satuan']) && $_POST['satuan'] === 'g' ? 'selected' : ''; ?>>g</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Kadaluarsa
                </label>
                <input type="date" name="expired_date"
                       value="<?php echo $_POST['expired_date'] ?? ''; ?>"
                       min="<?php echo date('Y-m-d'); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
        </div>

        <!-- Price Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Beli
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">Rp</span>
                    </div>
                    <input type="text" name="harga_beli"
                           value="<?php echo $_POST['harga_beli'] ?? '0'; ?>"
                           class="w-full pl-12 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Jual
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">Rp</span>
                    </div>
                    <input type="text" name="harga_jual"
                           value="<?php echo $_POST['harga_jual'] ?? '0'; ?>"
                           class="w-full pl-12 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Deskripsi
            </label>
            <textarea name="deskripsi" rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                      placeholder="Masukkan deskripsi item..."><?php echo $_POST['deskripsi'] ?? ''; ?></textarea>
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