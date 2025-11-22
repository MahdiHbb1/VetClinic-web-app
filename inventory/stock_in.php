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

// Get item ID from URL
$item_id = $_GET['id'] ?? null;
if (!$item_id) {
    $_SESSION['error'] = "ID Item tidak valid";
    header("Location: index.php");
    exit;
}

// Fetch item details
$stmt = $pdo->prepare("
    SELECT 
        i.*,
        k.nama_kategori,
        s.nama_supplier,
        s.supplier_id,
        s.kontak as supplier_kontak
    FROM inventory i
    LEFT JOIN kategori k ON i.kategori_id = k.kategori_id
    LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
    WHERE i.item_id = ?
");

$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get all active suppliers
$stmt = $pdo->prepare("
    SELECT supplier_id, nama_supplier, kontak
    FROM supplier
    WHERE status = 'Active'
    ORDER BY nama_supplier
");
$stmt->execute();
$suppliers = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'quantity' => $_POST['quantity'] ? (int)$_POST['quantity'] : 0,
        'supplier_id' => $_POST['supplier_id'] ?: null,
        'batch_number' => $_POST['batch_number'] ?? null,
        'expired_date' => $_POST['expired_date'] ?? null,
        'invoice_number' => $_POST['invoice_number'] ?? null,
        'harga_beli' => $_POST['harga_beli'] ? str_replace(['.', ','], '', $_POST['harga_beli']) : $item['harga_beli'],
        'notes' => $_POST['notes'] ?? ''
    ];

    // Validate input
    $errors = [];
    
    if ($data['quantity'] <= 0) {
        $errors[] = "Jumlah stok masuk harus lebih dari 0";
    }

    if ($data['expired_date'] && $data['expired_date'] <= date('Y-m-d')) {
        $errors[] = "Tanggal kadaluarsa harus lebih dari hari ini";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update inventory current stock and price
            $new_stock = $item['current_stock'] + $data['quantity'];
            
            $stmt = $pdo->prepare("
                UPDATE inventory 
                SET current_stock = ?,
                    harga_beli = ?,
                    batch_number = ?,
                    expired_date = ?,
                    supplier_id = ?,
                    status = CASE 
                        WHEN ? <= min_stock THEN 'Low Stock'
                        ELSE 'In Stock'
                    END,
                    updated_by = ?,
                    updated_at = NOW()
                WHERE item_id = ?
            ");

            $stmt->execute([
                $new_stock,
                $data['harga_beli'],
                $data['batch_number'],
                $data['expired_date'],
                $data['supplier_id'],
                $new_stock,
                $_SESSION['user_id'],
                $item_id
            ]);

            // Create stock movement record
            create_stock_movement($pdo, [
                'item_id' => $item_id,
                'type' => 'IN',
                'quantity' => $data['quantity'],
                'previous_stock' => $item['current_stock'],
                'current_stock' => $new_stock,
                'supplier_id' => $data['supplier_id'],
                'batch_number' => $data['batch_number'],
                'expired_date' => $data['expired_date'],
                'invoice_number' => $data['invoice_number'],
                'notes' => $data['notes'],
                'reference_type' => 'PURCHASE'
            ]);

            $pdo->commit();
            $_SESSION['success'] = "Stok berhasil ditambahkan";
            header("Location: detail.php?id=" . $item_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

$page_title = "Tambah Stok: " . $item['nama_item'];

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Stok</h2>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Current Stock Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Stok Saat Ini</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-600">Stok Saat Ini</label>
                    <p class="text-2xl font-bold <?php echo $item['current_stock'] <= $item['min_stock'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <?php echo number_format($item['current_stock']); ?> <?php echo htmlspecialchars($item['satuan']); ?>
                    </p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Minimum Stok</label>
                    <p class="font-medium"><?php echo number_format($item['min_stock']); ?> <?php echo htmlspecialchars($item['satuan']); ?></p>
                </div>

                <?php if ($item['batch_number']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Nomor Batch Terakhir</label>
                        <p class="font-medium"><?php echo htmlspecialchars($item['batch_number']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($item['expired_date']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Tanggal Kadaluarsa Terakhir</label>
                        <p class="font-medium"><?php echo date('d/m/Y', strtotime($item['expired_date'])); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($item['supplier_id']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Supplier Terakhir</label>
                        <p class="font-medium">
                            <?php echo htmlspecialchars($item['nama_supplier']); ?>
                            <?php if ($item['supplier_kontak']): ?>
                                <br>
                                <span class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($item['supplier_kontak']); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stock In Form -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Form Tambah Stok</h3>

            <form action="" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah <span class="text-red-600">*</span>
                        </label>
                        <div class="flex">
                            <input type="number" name="quantity" required min="1"
                                   value="<?php echo $_POST['quantity'] ?? ''; ?>"
                                   class="flex-1 rounded-l-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500">
                                <?php echo htmlspecialchars($item['satuan']); ?>
                            </span>
                        </div>
                    </div>

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
                            Supplier
                        </label>
                        <select name="supplier_id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">Pilih Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['supplier_id']; ?>"
                                        <?php echo $item['supplier_id'] == $supplier['supplier_id'] ? 'selected' : ''; ?>>
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
                            Nomor Batch
                        </label>
                        <input type="text" name="batch_number"
                               value="<?php echo $_POST['batch_number'] ?? ''; ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Kadaluarsa
                        </label>
                        <input type="date" name="expired_date"
                               value="<?php echo $_POST['expired_date'] ?? ''; ?>"
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Invoice/Faktur
                        </label>
                        <input type="text" name="invoice_number"
                               value="<?php echo $_POST['invoice_number'] ?? ''; ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan
                        </label>
                        <textarea name="notes" rows="3"
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                  placeholder="Masukkan catatan tambahan..."><?php echo $_POST['notes'] ?? ''; ?></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
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
    </div>
</div>

<?php include '../includes/footer.php'; ?>