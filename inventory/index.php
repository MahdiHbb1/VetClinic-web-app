<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/inventory_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com data:");

$page_title = "Inventaris";

// Check expired items
check_expired_items($pdo);

// Initialize filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$supplier = isset($_GET['supplier']) ? trim($_GET['supplier']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$stock_filter = isset($_GET['stock_filter']) ? $_GET['stock_filter'] : '';

// Build query
$params = [];
$where = [];

if ($search) {
    $where[] = "(nama_obat LIKE ? OR deskripsi LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category) {
    $where[] = "kategori = ?";
    $params[] = $category;
}

if ($supplier) {
    $where[] = "supplier = ?";
    $params[] = $supplier;
}

if ($status) {
    if ($status === 'In Stock') {
        $where[] = "stok > 10";
    } elseif ($status === 'Low Stock') {
        $where[] = "stok > 0 AND stok <= 10";
    } elseif ($status === 'Out of Stock') {
        $where[] = "stok = 0";
    }
}

if ($stock_filter === 'low') {
    $where[] = "stok > 0 AND stok <= 10";
} elseif ($stock_filter === 'out') {
    $where[] = "stok = 0";
} elseif ($stock_filter === 'expired') {
    $where[] = "expired_date < CURDATE()";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total records for pagination
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM medicine
    $whereClause
");
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$stmt = $pdo->prepare("
    SELECT 
        obat_id as inventory_id,
        nama_obat as nama_item,
        kategori,
        stok as qty,
        satuan as unit,
        harga_beli,
        harga_jual as harga,
        expired_date,
        supplier as supplier_name,
        status_tersedia
    FROM medicine
    $whereClause
    ORDER BY nama_obat ASC
    LIMIT ? OFFSET ?
");

$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$items = $stmt->fetchAll();

// Get categories for filter (using medicine categories)
$categories = [
    ['kategori' => 'Antibiotik'],
    ['kategori' => 'Vitamin'],
    ['kategori' => 'Vaksin'],
    ['kategori' => 'Anti_Parasit'],
    ['kategori' => 'Suplemen'],
    ['kategori' => 'Alat_Medis'],
    ['kategori' => 'Lainnya']
];

// Get suppliers from medicine table
$stmt = $pdo->prepare("
    SELECT DISTINCT supplier as nama_supplier
    FROM medicine
    WHERE supplier IS NOT NULL
    ORDER BY supplier
");
$stmt->execute();
$suppliers = $stmt->fetchAll();

// Get alert counts from medicine table
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN stok > 0 AND stok <= 10 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN stok = 0 THEN 1 ELSE 0 END) as out_stock,
        SUM(CASE WHEN expired_date < CURDATE() THEN 1 ELSE 0 END) as expired
    FROM medicine
    WHERE status_tersedia = 1
");
$stmt->execute();
$alerts = $stmt->fetch();

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Inventaris</h2>
            <p class="text-gray-600">Kelola stok obat dan perlengkapan</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i> Tambah Item
                </a>
                <a href="stock_opname.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-clipboard-check mr-2"></i> Stock Opname
                </a>
            <?php endif; ?>
            <a href="report.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-chart-bar mr-2"></i> Laporan
            </a>
        </div>
    </div>

    <!-- Alert Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-600 text-sm">Stok Menipis</p>
                    <p class="text-2xl font-bold text-yellow-700"><?php echo $alerts['low_stock']; ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                </div>
            </div>
            <a href="?stock_filter=low" class="text-sm text-yellow-600 hover:text-yellow-800">
                Lihat detail <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-600 text-sm">Stok Habis</p>
                    <p class="text-2xl font-bold text-red-700"><?php echo $alerts['out_stock']; ?></p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                </div>
            </div>
            <a href="?stock_filter=out" class="text-sm text-red-600 hover:text-red-800">
                Lihat detail <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Item Kadaluarsa</p>
                    <p class="text-2xl font-bold text-gray-700"><?php echo $alerts['expired']; ?></p>
                </div>
                <div class="bg-gray-100 rounded-full p-3">
                    <i class="fas fa-calendar-times text-gray-500 text-xl"></i>
                </div>
            </div>
            <a href="?stock_filter=expired" class="text-sm text-gray-600 hover:text-gray-800">
                Lihat detail <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Nama/kode item...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="category" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['kategori']; ?>" 
                                <?php echo $category == $cat['kategori'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Supplier</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo $sup['nama_supplier']; ?>" 
                                <?php echo $supplier == $sup['nama_supplier'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sup['nama_supplier']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Status</option>
                    <option value="In Stock" <?php echo $status === 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="Low Stock" <?php echo $status === 'Low Stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="Out of Stock" <?php echo $status === 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    <option value="Expired" <?php echo $status === 'Expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="Discontinued" <?php echo $status === 'Discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg w-full">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
            </div>
        </div>

        <?php if ($search || $category || $supplier || $status || $stock_filter): ?>
            <div class="mt-4 flex justify-end">
                <a href="index.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times mr-2"></i> Reset Filter
                </a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Inventory List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Item
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kategori
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stok
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kadaluarsa
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data inventaris yang ditemukan
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($item['nama_item']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($item['kategori']); ?> - <?php echo htmlspecialchars($item['satuan']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['kategori']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['supplier_name'] ?? '-'); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo number_format($item['qty']); ?> <?php echo htmlspecialchars($item['unit']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php 
                                        $stock_status = $item['qty'] == 0 ? 'Habis' : ($item['qty'] <= 10 ? 'Menipis' : 'Aman');
                                        echo $stock_status;
                                        ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <?php 
                                    $status_class = $item['status_tersedia'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    $status_text = $item['status_tersedia'] ? 'Tersedia' : 'Tidak Tersedia';
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($item['expired_date']): ?>
                                        <div class="text-sm <?php echo strtotime($item['expired_date']) < time() ? 'text-red-600' : 'text-gray-900'; ?>">
                                            <?php echo date('d/m/Y', strtotime($item['expired_date'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                                        <a href="stock_in.php?id=<?php echo $item['inventory_id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800"
                                           title="Stok Masuk">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        <a href="stock_out.php?id=<?php echo $item['inventory_id']; ?>" 
                                           class="text-orange-600 hover:text-orange-800"
                                           title="Stok Keluar">
                                            <i class="fas fa-minus-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="detail.php?id=<?php echo $item['inventory_id']; ?>" 
                                       class="text-green-600 hover:text-green-800"
                                       title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory'])): ?>
                                        <a href="edit.php?id=<?php echo $item['inventory_id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-800"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($item['status'] !== 'Discontinued'): ?>
                                            <button onclick="confirmDelete(<?php echo $item['item_id']; ?>)"
                                                    class="text-red-600 hover:text-red-800"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Menampilkan <?php echo $offset + 1; ?> - 
                        <?php echo min($offset + $per_page, $total_records); ?> 
                        dari <?php echo $total_records; ?> data
                    </div>
                    
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                               class="bg-white hover:bg-gray-50 text-gray-600 px-3 py-1 rounded-lg border">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                               class="<?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-50 text-gray-600'; ?> px-3 py-1 rounded-lg border">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                               class="bg-white hover:bg-gray-50 text-gray-600 px-3 py-1 rounded-lg border">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus item ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete.php?id=${id}`;
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>