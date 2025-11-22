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

// Get item ID from URL
$item_id = $_GET['id'] ?? null;
if (!$item_id) {
    $_SESSION['error'] = "ID Item tidak valid";
    header("Location: index.php");
    exit;
}

// Fetch item details from medicine table
$stmt = $pdo->prepare("
    SELECT 
        obat_id as item_id,
        nama_obat as nama_item,
        kategori,
        bentuk_sediaan,
        satuan,
        stok as current_stock,
        harga_beli,
        harga_jual,
        expired_date,
        supplier as supplier_id,
        deskripsi,
        status_tersedia
    FROM medicine
    WHERE obat_id = ?
");

$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_item' => $_POST['nama_item'] ?? '',
        'kategori_id' => $_POST['kategori_id'] ?? '',
        'bentuk_sediaan' => $_POST['bentuk_sediaan'] ?? 'Tablet',
        'supplier_id' => $_POST['supplier_id'] ?? null,
        'deskripsi' => $_POST['deskripsi'] ?? '',
        'satuan' => $_POST['satuan'] ?? '',
        'harga_beli' => $_POST['harga_beli'] ? str_replace(['.', ','], '', $_POST['harga_beli']) : 0,
        'harga_jual' => $_POST['harga_jual'] ? str_replace(['.', ','], '', $_POST['harga_jual']) : 0,
        'expired_date' => $_POST['expired_date'] ?? null
    ];

    // Validate input
    $errors = validate_inventory_item($data);

    // Validate unique nama_obat
    if ($data['nama_item'] !== $item['nama_item']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM medicine WHERE nama_obat = ? AND obat_id != ?");
        $stmt->execute([$data['nama_item'], $item_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nama obat sudah digunakan";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update medicine item
            $stmt = $pdo->prepare("
                UPDATE medicine SET
                    nama_obat = ?,
                    kategori = ?,
                    bentuk_sediaan = ?,
                    satuan = ?,
                    harga_beli = ?,
                    harga_jual = ?,
                    expired_date = ?,
                    supplier = ?,
                    deskripsi = ?,
                    status_tersedia = CASE 
                        WHEN stok = 0 THEN 0
                        ELSE 1
                    END
                WHERE obat_id = ?
            ");

            $stmt->execute([
                $data['nama_item'],
                $data['kategori_id'],
                $data['bentuk_sediaan'],
                $data['satuan'],
                $data['harga_beli'],
                $data['harga_jual'],
                $data['expired_date'],
                $data['supplier_id'],
                $data['deskripsi'],
                $item_id
            ]);

            $pdo->commit();
            $_SESSION['success'] = "Item berhasil diperbarui";
            header("Location: detail.php?id=" . $item_id);
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

$page_title = "Edit Item: " . $item['nama_item'];

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Item</h2>
            <p class="text-gray-600"><?php echo htmlspecialchars($item['nama_item']); ?></p>
        </div>
        
        <a href="detail.php?id=<?php echo $item['item_id']; ?>" 
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Obat <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama_item" required
                       value="<?php echo htmlspecialchars($item['nama_item']); ?>"
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
                                <?php echo $item['kategori'] == $category['value'] ? 'selected' : ''; ?>>
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
                                <?php echo $item['bentuk_sediaan'] == $bentuk ? 'selected' : ''; ?>>
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
                       value="<?php echo htmlspecialchars($item['supplier_id'] ?? ''); ?>"
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
                    Stok Saat Ini
                </label>
                <div class="flex">
                    <input type="text" readonly
                           value="<?php echo number_format($item['current_stock']); ?>"
                           class="flex-1 rounded-l-lg bg-gray-50 border-gray-300 text-gray-500">
                    <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500">
                        <?php echo htmlspecialchars($item['satuan']); ?>
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Gunakan fitur Stok Masuk/Keluar untuk mengubah stok
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Satuan <span class="text-red-600">*</span>
                </label>
                <select name="satuan" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Satuan</option>
                    <option value="Pcs" <?php echo $item['satuan'] === 'Pcs' ? 'selected' : ''; ?>>Pcs</option>
                    <option value="Box" <?php echo $item['satuan'] === 'Box' ? 'selected' : ''; ?>>Box</option>
                    <option value="Botol" <?php echo $item['satuan'] === 'Botol' ? 'selected' : ''; ?>>Botol</option>
                    <option value="Strip" <?php echo $item['satuan'] === 'Strip' ? 'selected' : ''; ?>>Strip</option>
                    <option value="Tablet" <?php echo $item['satuan'] === 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                    <option value="Kapsul" <?php echo $item['satuan'] === 'Kapsul' ? 'selected' : ''; ?>>Kapsul</option>
                    <option value="mL" <?php echo $item['satuan'] === 'mL' ? 'selected' : ''; ?>>mL</option>
                    <option value="mg" <?php echo $item['satuan'] === 'mg' ? 'selected' : ''; ?>>mg</option>
                    <option value="g" <?php echo $item['satuan'] === 'g' ? 'selected' : ''; ?>>g</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Kadaluarsa
                </label>
                <input type="date" name="expired_date"
                       value="<?php echo $item['expired_date']; ?>"
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
                           value="<?php echo number_format($item['harga_beli'], 0, ',', '.'); ?>"
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
                           value="<?php echo number_format($item['harga_jual'], 0, ',', '.'); ?>"
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
                      placeholder="Masukkan deskripsi item..."><?php echo htmlspecialchars($item['deskripsi']); ?></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="detail.php?id=<?php echo $item['item_id']; ?>" 
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