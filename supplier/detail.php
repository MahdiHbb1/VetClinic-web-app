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

// Get supplier ID from URL
$supplier_id = $_GET['id'] ?? null;
if (!$supplier_id) {
    $_SESSION['error'] = "ID Supplier tidak valid";
    header("Location: index.php");
    exit;
}

// Fetch supplier details with joins
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        u.nama as created_by_name,
        u2.nama as updated_by_name
    FROM supplier s
    LEFT JOIN users u ON s.created_by = u.user_id
    LEFT JOIN users u2 ON s.updated_by = u2.user_id
    WHERE s.supplier_id = ?
");

$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    $_SESSION['error'] = "Supplier tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Fetch recent items from this supplier
$stmt = $pdo->prepare("
    SELECT 
        i.*,
        k.nama_kategori
    FROM inventory i
    LEFT JOIN kategori k ON i.kategori_id = k.kategori_id
    WHERE i.supplier_id = ?
    ORDER BY i.nama_item
    LIMIT 5
");
$stmt->execute([$supplier_id]);
$items = $stmt->fetchAll();

// Count total items
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM inventory 
    WHERE supplier_id = ?
");
$stmt->execute([$supplier_id]);
$total_items = $stmt->fetchColumn();

// Get recent stock movements
$stmt = $pdo->prepare("
    SELECT 
        sm.*,
        i.nama_item,
        i.kode_item,
        i.satuan,
        u.nama as created_by_name
    FROM stock_movement sm
    JOIN inventory i ON sm.item_id = i.item_id
    LEFT JOIN users u ON sm.created_by = u.user_id
    WHERE i.supplier_id = ?
    ORDER BY sm.created_at DESC
    LIMIT 10
");
$stmt->execute([$supplier_id]);
$stock_movements = $stmt->fetchAll();

$page_title = "Detail Supplier: " . $supplier['nama_supplier'];

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($supplier['nama_supplier']); ?></h2>
            <p class="text-gray-600">Detail informasi supplier</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                <a href="edit.php?id=<?php echo $supplier['supplier_id']; ?>" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <?php if ($total_items == 0): ?>
                    <button onclick="confirmDelete(<?php echo $supplier['supplier_id']; ?>)"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <i class="fas fa-trash mr-2"></i> Hapus
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-600">Nama Supplier</label>
                    <p class="font-medium"><?php echo htmlspecialchars($supplier['nama_supplier']); ?></p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Status</label>
                    <p>
                        <?php
                        $statusClass = $supplier['status'] === 'Active' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                            <?php echo $supplier['status'] === 'Active' ? 'Aktif' : 'Nonaktif'; ?>
                        </span>
                    </p>
                </div>

                <?php if ($supplier['kontak']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Kontak</label>
                        <p class="font-medium"><?php echo htmlspecialchars($supplier['kontak']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($supplier['email']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Email</label>
                        <p class="font-medium"><?php echo htmlspecialchars($supplier['email']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($supplier['alamat']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Alamat</label>
                        <p class="whitespace-pre-line"><?php echo nl2br(htmlspecialchars($supplier['alamat'])); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($supplier['npwp']): ?>
                    <div>
                        <label class="text-sm text-gray-600">NPWP</label>
                        <p class="font-medium"><?php echo htmlspecialchars($supplier['npwp']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($supplier['notes']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Catatan</label>
                        <p class="whitespace-pre-line"><?php echo nl2br(htmlspecialchars($supplier['notes'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="far fa-clock mr-1"></i>
                        Dibuat: <?php echo date('d/m/Y H:i', strtotime($supplier['created_at'])); ?>
                        oleh <?php echo htmlspecialchars($supplier['created_by_name']); ?>
                    </div>
                    <?php if ($supplier['updated_at']): ?>
                        <div class="flex items-center text-sm text-gray-600 mt-1">
                            <i class="far fa-edit mr-1"></i>
                            Diperbarui: <?php echo date('d/m/Y H:i', strtotime($supplier['updated_at'])); ?>
                            oleh <?php echo htmlspecialchars($supplier['updated_by_name']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Bank</h3>
            
            <div class="space-y-4">
                <?php if ($supplier['bank_name'] || $supplier['bank_account'] || $supplier['bank_account_name']): ?>
                    <?php if ($supplier['bank_name']): ?>
                        <div>
                            <label class="text-sm text-gray-600">Nama Bank</label>
                            <p class="font-medium"><?php echo htmlspecialchars($supplier['bank_name']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($supplier['bank_account']): ?>
                        <div>
                            <label class="text-sm text-gray-600">Nomor Rekening</label>
                            <p class="font-medium"><?php echo htmlspecialchars($supplier['bank_account']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($supplier['bank_account_name']): ?>
                        <div>
                            <label class="text-sm text-gray-600">Nama Pemilik Rekening</label>
                            <p class="font-medium"><?php echo htmlspecialchars($supplier['bank_account_name']); ?></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-gray-500">Belum ada informasi bank</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    Daftar Item
                    <?php if ($total_items > 0): ?>
                        <span class="text-sm font-normal text-gray-500">
                            (<?php echo number_format($total_items); ?> item)
                        </span>
                    <?php endif; ?>
                </h3>
                
                <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                    <a href="../inventory/create.php?supplier_id=<?php echo $supplier['supplier_id']; ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i> Tambah Item
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($items): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Kode</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Nama Item</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Kategori</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Stok</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm">
                                        <?php echo htmlspecialchars($item['kode_item']); ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['nama_item']); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <?php echo htmlspecialchars($item['nama_kategori']); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <?php echo number_format($item['current_stock']); ?> 
                                        <?php echo htmlspecialchars($item['satuan']); ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php
                                        $statusClass = match($item['status']) {
                                            'In Stock' => 'bg-green-100 text-green-800',
                                            'Low Stock' => 'bg-yellow-100 text-yellow-800',
                                            'Out of Stock' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                            <?php echo $item['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="../inventory/detail.php?id=<?php echo $item['item_id']; ?>" 
                                           class="text-blue-500 hover:text-blue-600">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_items > 5): ?>
                    <div class="mt-4 text-center">
                        <a href="../inventory/index.php?supplier_id=<?php echo $supplier['supplier_id']; ?>" 
                           class="text-blue-500 hover:text-blue-600">
                            Lihat semua <?php echo number_format($total_items); ?> item
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada item dari supplier ini</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Stock Movements -->
    <?php if ($stock_movements): ?>
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Pergerakan Stok Terakhir</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Tanggal</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Item</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Tipe</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Jumlah</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Catatan</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($stock_movements as $movement): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($movement['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($movement['nama_item']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($movement['kode_item']); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php if ($movement['type'] === 'IN'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Masuk
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Keluar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php
                                        echo $movement['type'] === 'IN' ? '+' : '-';
                                        echo number_format($movement['quantity']) . ' ' . $movement['satuan'];
                                        ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($movement['notes']) ?: '-'; ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($movement['created_by_name']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed z-10 inset-0 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Konfirmasi Hapus
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Apakah Anda yakin ingin menghapus supplier ini? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" action="delete.php" method="POST" class="inline">
                    <input type="hidden" name="supplier_id" id="deleteSupplierId">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(supplierId) {
    document.getElementById('deleteSupplierId').value = supplierId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php include '../includes/footer.php'; ?>