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
        supplier,
        deskripsi,
        status_tersedia,
        kategori as nama_kategori,
        supplier as nama_supplier,
        NULL as supplier_id,
        NULL as supplier_kontak,
        NULL as created_by_name,
        NULL as updated_by_name,
        CURRENT_TIMESTAMP as created_at,
        CURRENT_TIMESTAMP as updated_at,
        CONCAT('MED-', LPAD(obat_id, 5, '0')) as kode_item,
        NULL as batch_number,
        NULL as lokasi,
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

// Stock movement history not available - table doesn't exist
$stock_movements = [];

$page_title = "Detail Item: " . $item['nama_item'];

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($item['nama_item']); ?></h2>
            <p class="text-gray-600">Detail informasi item</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                <a href="edit.php?id=<?php echo $item['item_id']; ?>" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <?php if ($item['current_stock'] == 0): ?>
                    <button onclick="confirmDelete(<?php echo $item['item_id']; ?>)"
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-600">Kode Item</label>
                    <p class="font-medium"><?php echo htmlspecialchars($item['kode_item'] ?? 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm text-gray-600">Kategori</label>
                    <p class="font-medium"><?php echo htmlspecialchars($item['nama_kategori'] ?? 'Tidak ada kategori'); ?></p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Status</label>
                    <p>
                        <?php
                        $status_text = $item['status_tersedia'] ? 'Tersedia' : 'Tidak Tersedia';
                        $statusClass = $item['status_tersedia'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </p>
                </div>

                <?php if ($item['deskripsi']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Deskripsi</label>
                        <p class="whitespace-pre-line"><?php echo nl2br(htmlspecialchars($item['deskripsi'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stock Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Stok</h3>
            
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
                        <p class="font-medium">
                            <?php 
                            $expired_date = new DateTime($item['expired_date']);
                            $today = new DateTime();
                            $interval = $today->diff($expired_date);
                            $expired = $today > $expired_date;
                            
                            echo date('d/m/Y', strtotime($item['expired_date']));
                            
                            if ($expired) {
                                echo ' <span class="text-red-600">(Kadaluarsa)</span>';
                            } elseif ($interval->days <= 30) {
                                echo ' <span class="text-yellow-600">('. $interval->days .' hari lagi)</span>';
                            }
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($item['lokasi']): ?>
                    <div>
                        <label class="text-sm text-gray-600">Lokasi Penyimpanan</label>
                        <p class="font-medium"><?php echo htmlspecialchars($item['lokasi']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Price & Supplier Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Harga & Supplier</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-600">Harga Beli</label>
                    <p class="font-medium">Rp <?php echo number_format($item['harga_beli'], 0, ',', '.'); ?></p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Harga Jual</label>
                    <p class="font-medium">Rp <?php echo number_format($item['harga_jual'], 0, ',', '.'); ?></p>
                </div>

                <?php if (!empty($item['nama_supplier'])): ?>
                    <div>
                        <label class="text-sm text-gray-600">Supplier</label>
                        <p class="font-medium">
                            <?php echo htmlspecialchars($item['nama_supplier']); ?>
                            <?php if (!empty($item['supplier_kontak'])): ?>
                                <br>
                                <span class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($item['supplier_kontak']); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="pt-4 border-t border-gray-200">
                    <?php if (!empty($item['created_at'])): ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="far fa-clock mr-1"></i>
                            Dibuat: <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
                            <?php if (!empty($item['created_by_name'])): ?>
                                oleh <?php echo htmlspecialchars($item['created_by_name']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($item['updated_at']) && $item['updated_at'] != $item['created_at']): ?>
                        <div class="flex items-center text-sm text-gray-600 mt-1">
                            <i class="far fa-edit mr-1"></i>
                            Diperbarui: <?php echo date('d/m/Y H:i', strtotime($item['updated_at'])); ?>
                            <?php if (!empty($item['updated_by_name'])): ?>
                                oleh <?php echo htmlspecialchars($item['updated_by_name']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement History -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Riwayat Pergerakan Stok</h3>
                <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                    <div class="flex space-x-3">
                        <a href="stock_out.php?id=<?php echo $item['item_id']; ?>" 
                           class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <i class="fas fa-minus mr-2"></i> Stok Keluar
                        </a>
                        <a href="stock_in.php?id=<?php echo $item['item_id']; ?>" 
                           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <i class="fas fa-plus mr-2"></i> Stok Masuk
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($stock_movements): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Tanggal</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Tipe</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Jumlah</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Batch</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Kadaluarsa</th>
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
                                        echo number_format($movement['quantity']) . ' ' . $item['satuan'];
                                        ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo $movement['batch_number'] ?: '-'; ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php 
                                        if ($movement['expired_date']) {
                                            echo date('d/m/Y', strtotime($movement['expired_date']));
                                        } else {
                                            echo '-';
                                        }
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
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada riwayat pergerakan stok</p>
            <?php endif; ?>
        </div>
    </div>
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
                                Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" action="delete.php" method="POST" class="inline">
                    <input type="hidden" name="item_id" id="deleteItemId">
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
function confirmDelete(itemId) {
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php include '../includes/footer.php'; ?>