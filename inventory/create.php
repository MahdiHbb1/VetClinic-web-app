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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

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
        'kode_item' => $_POST['kode_item'] ?? '',
        'nama_item' => $_POST['nama_item'] ?? '',
        'kategori_id' => $_POST['kategori_id'] ?? '',
        'supplier_id' => $_POST['supplier_id'] ?? null,
        'deskripsi' => $_POST['deskripsi'] ?? '',
        'min_stock' => $_POST['min_stock'] ? (int)$_POST['min_stock'] : 0,
        'current_stock' => $_POST['current_stock'] ? (int)$_POST['current_stock'] : 0,
        'satuan' => $_POST['satuan'] ?? '',
        'harga_beli' => $_POST['harga_beli'] ? str_replace(['.', ','], '', $_POST['harga_beli']) : 0,
        'harga_jual' => $_POST['harga_jual'] ? str_replace(['.', ','], '', $_POST['harga_jual']) : 0,
        'batch_number' => $_POST['batch_number'] ?? null,
        'expired_date' => $_POST['expired_date'] ?? null,
        'lokasi' => $_POST['lokasi'] ?? '',
        'status' => 'In Stock'
    ];

    // Validate input
    $errors = validate_inventory_item($data);

    // Validate unique kode_item
    if (!empty($data['kode_item'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE kode_item = ?");
        $stmt->execute([$data['kode_item']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Kode item sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert inventory item
            $stmt = $pdo->prepare("
                INSERT INTO inventory (
                    kode_item, nama_item, kategori_id,
                    supplier_id, deskripsi, min_stock,
                    current_stock, satuan, harga_beli,
                    harga_jual, batch_number, expired_date,
                    lokasi, status, created_by,
                    created_at
                ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    NOW()
                )
            ");

            $stmt->execute([
                $data['kode_item'],
                $data['nama_item'],
                $data['kategori_id'],
                $data['supplier_id'],
                $data['deskripsi'],
                $data['min_stock'],
                $data['current_stock'],
                $data['satuan'],
                $data['harga_beli'],
                $data['harga_jual'],
                $data['batch_number'],
                $data['expired_date'],
                $data['lokasi'],
                $data['status'],
                $_SESSION['user_id']
            ]);

            $item_id = $pdo->lastInsertId();

            // Create initial stock movement
            if ($data['current_stock'] > 0) {
                create_stock_movement($pdo, [
                    'item_id' => $item_id,
                    'type' => 'IN',
                    'quantity' => $data['current_stock'],
                    'previous_stock' => 0,
                    'current_stock' => $data['current_stock'],
                    'batch_number' => $data['batch_number'],
                    'expired_date' => $data['expired_date'],
                    'reference_type' => 'INITIAL',
                    'notes' => 'Stok awal'
                ]);
            }

            $pdo->commit();
            $_SESSION['success'] = "Item berhasil ditambahkan";
            header("Location: detail.php?id=" . $item_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get categories
$stmt = $pdo->prepare("
    SELECT kategori_id, nama_kategori
    FROM kategori 
    WHERE tipe = 'Inventory'
    ORDER BY nama_kategori
");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get suppliers
$stmt = $pdo->prepare("
    SELECT supplier_id, nama_supplier, kontak
    FROM supplier
    WHERE status = 'Active'
    ORDER BY nama_supplier
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kode Item <span class="text-red-600">*</span>
                </label>
                <input type="text" name="kode_item" required
                       value="<?php echo $_POST['kode_item'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Item <span class="text-red-600">*</span>
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
                        <option value="<?php echo $category['kategori_id']; ?>"
                                <?php echo isset($_POST['kategori_id']) && $_POST['kategori_id'] == $category['kategori_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Supplier
                </label>
                <select name="supplier_id"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['supplier_id']; ?>"
                                <?php echo isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['supplier_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['nama_supplier']); ?>
                            <?php if ($supplier['kontak']): ?>
                                (<?php echo htmlspecialchars($supplier['kontak']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Lokasi Penyimpanan
                </label>
                <input type="text" name="lokasi"
                       value="<?php echo $_POST['lokasi'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Rak/Gudang/Lemari...">
            </div>
        </div>

        <!-- Stock Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
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
                    Minimum Stok <span class="text-red-600">*</span>
                </label>
                <input type="number" name="min_stock" required min="0"
                       value="<?php echo $_POST['min_stock'] ?? '0'; ?>"
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
                    Batch Number
                </label>
                <input type="text" name="batch_number"
                       value="<?php echo $_POST['batch_number'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
        </div>

        <!-- Price Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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