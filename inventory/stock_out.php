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
        kategori as nama_kategori,
        satuan,
        stok as current_stock,
        expired_date,
        NULL as batch_number,
        10 as min_stock
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
        'quantity' => $_POST['quantity'] ? (int)$_POST['quantity'] : 0,
        'reference_type' => $_POST['reference_type'] ?? 'USAGE',
        'reference_id' => $_POST['reference_id'] ?? null,
        'batch_number' => $_POST['batch_number'] ?? null,
        'notes' => $_POST['notes'] ?? ''
    ];

    // Validate input
    $errors = [];
    
    if ($data['quantity'] <= 0) {
        $errors[] = "Jumlah stok keluar harus lebih dari 0";
    }

    if ($data['quantity'] > $item['current_stock']) {
        $errors[] = "Jumlah stok keluar tidak boleh melebihi stok saat ini";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update medicine stock
            $new_stock = $item['current_stock'] - $data['quantity'];
            
            $stmt = $pdo->prepare("
                UPDATE medicine 
                SET stok = ?,
                    status_tersedia = CASE 
                        WHEN ? = 0 THEN 0
                        ELSE 1
                    END
                WHERE obat_id = ?
            ");

            $stmt->execute([
                $new_stock,
                $new_stock,
                $item_id
            ]);

            // Stock movement tracking removed - table doesn't exist

            $pdo->commit();
            $_SESSION['success'] = "Stok berhasil dikurangi";
            header("Location: detail.php?id=" . $item_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

$page_title = "Kurangi Stok: " . $item['nama_item'];

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Kurangi Stok</h2>
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
                        <label class="text-sm text-gray-600">Nomor Batch</label>
                        <p class="font-medium"><?php echo htmlspecialchars($item['batch_number']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($item['expired_date']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Tanggal Kadaluarsa</label>
                        <p class="font-medium"><?php echo date('d/m/Y', strtotime($item['expired_date'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($item['current_stock'] <= $item['min_stock']): ?>
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-700 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Stok sudah mencapai batas minimum
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Stock Out Form -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Form Kurangi Stok</h3>

            <form action="" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah <span class="text-red-600">*</span>
                        </label>
                        <div class="flex">
                            <input type="number" name="quantity" required min="1"
                                   max="<?php echo $item['current_stock']; ?>"
                                   value="<?php echo $_POST['quantity'] ?? ''; ?>"
                                   class="flex-1 rounded-l-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500">
                                <?php echo htmlspecialchars($item['satuan']); ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipe Referensi <span class="text-red-600">*</span>
                        </label>
                        <select name="reference_type" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="USAGE" <?php echo isset($_POST['reference_type']) && $_POST['reference_type'] === 'USAGE' ? 'selected' : ''; ?>>
                                Pemakaian
                            </option>
                            <option value="TREATMENT" <?php echo isset($_POST['reference_type']) && $_POST['reference_type'] === 'TREATMENT' ? 'selected' : ''; ?>>
                                Pengobatan
                            </option>
                            <option value="EXPIRED" <?php echo isset($_POST['reference_type']) && $_POST['reference_type'] === 'EXPIRED' ? 'selected' : ''; ?>>
                                Kadaluarsa
                            </option>
                            <option value="DAMAGED" <?php echo isset($_POST['reference_type']) && $_POST['reference_type'] === 'DAMAGED' ? 'selected' : ''; ?>>
                                Rusak
                            </option>
                            <option value="RETURN" <?php echo isset($_POST['reference_type']) && $_POST['reference_type'] === 'RETURN' ? 'selected' : ''; ?>>
                                Return ke Supplier
                            </option>
                            <option value="OTHER" <?php echo isset($_POST['reference_type']) && $_POST['reference_type'] === 'OTHER' ? 'selected' : ''; ?>>
                                Lainnya
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ID Referensi
                        </label>
                        <input type="text" name="reference_id"
                               value="<?php echo $_POST['reference_id'] ?? ''; ?>"
                               placeholder="No. Treatment/Return/dll"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <p class="mt-1 text-sm text-gray-500">
                            Nomor treatment/return/dll jika ada
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Batch
                        </label>
                        <input type="text" name="batch_number"
                               value="<?php echo $_POST['batch_number'] ?? $item['batch_number']; ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan <span class="text-red-600">*</span>
                        </label>
                        <textarea name="notes" required rows="3"
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                  placeholder="Masukkan alasan pengurangan stok..."><?php echo $_POST['notes'] ?? ''; ?></textarea>
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