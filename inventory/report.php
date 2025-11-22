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

$page_title = "Laporan Inventori";
$use_chart = true;

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today
$kategori = $_GET['kategori'] ?? '';

// Get inventory summary
$summary_query = "
    SELECT 
        COUNT(*) as total_items,
        SUM(stok) as total_stock,
        SUM(stok * harga_beli) as total_value_buy,
        SUM(stok * harga_jual) as total_value_sell,
        SUM(CASE WHEN status_tersedia = 1 THEN 1 ELSE 0 END) as active_items,
        SUM(CASE WHEN stok < 10 THEN 1 ELSE 0 END) as low_stock_items,
        SUM(CASE WHEN expired_date < CURDATE() THEN 1 ELSE 0 END) as expired_items,
        SUM(CASE WHEN expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon
    FROM medicine
";

$stmt = $pdo->query($summary_query);
$summary = $stmt->fetch();

// Get inventory by category
$category_query = "
    SELECT 
        kategori,
        COUNT(*) as item_count,
        SUM(stok) as total_stock,
        SUM(stok * harga_jual) as total_value
    FROM medicine
    WHERE status_tersedia = 1
";

if ($kategori) {
    $category_query .= " AND kategori = ?";
    $stmt = $pdo->prepare($category_query . " GROUP BY kategori ORDER BY total_value DESC");
    $stmt->execute([$kategori]);
} else {
    $stmt = $pdo->query($category_query . " GROUP BY kategori ORDER BY total_value DESC");
}
$by_category = $stmt->fetchAll();

// Get low stock items
$low_stock_query = "
    SELECT 
        nama_obat,
        kategori,
        stok,
        satuan,
        bentuk_sediaan
    FROM medicine
    WHERE stok < 10 AND status_tersedia = 1
    ORDER BY stok ASC
    LIMIT 10
";
$low_stock = $pdo->query($low_stock_query)->fetchAll();

// Get expiring soon
$expiring_query = "
    SELECT 
        nama_obat,
        kategori,
        stok,
        expired_date,
        DATEDIFF(expired_date, CURDATE()) as days_until_expiry
    FROM medicine
    WHERE expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND status_tersedia = 1
    ORDER BY expired_date ASC
    LIMIT 10
";
$expiring = $pdo->query($expiring_query)->fetchAll();

// Get all categories for filter
$categories = $pdo->query("SELECT DISTINCT kategori FROM medicine ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i>
                Laporan Inventori
            </h2>
            <p class="text-gray-600">Analisis stok dan nilai inventori obat-obatan</p>
        </div>
        <div class="flex gap-2 mt-4 md:mt-0">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select name="kategori" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $kategori === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Item</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($summary['total_items']); ?></p>
                    <p class="text-xs text-green-600 mt-1">
                        <?php echo number_format($summary['active_items']); ?> aktif
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-boxes text-blue-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Stok</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($summary['total_stock']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">unit</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-cubes text-green-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Nilai Inventori</p>
                    <p class="text-2xl font-bold text-gray-800">Rp <?php echo number_format($summary['total_value_sell']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">harga jual</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-wallet text-yellow-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Stok Menipis</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($summary['low_stock_items']); ?></p>
                    <p class="text-xs text-red-600 mt-1">
                        <?php echo number_format($summary['expiring_soon']); ?> akan expired
                    </p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Inventory by Category Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventori per Kategori</h3>
            <canvas id="categoryChart"></canvas>
        </div>

        <!-- Value Distribution Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Nilai</h3>
            <canvas id="valueChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Items -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-exclamation-circle text-orange-500 mr-2"></i>
                Stok Menipis
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-sm font-semibold text-gray-700">Obat</th>
                            <th class="text-left py-2 px-3 text-sm font-semibold text-gray-700">Kategori</th>
                            <th class="text-right py-2 px-3 text-sm font-semibold text-gray-700">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($low_stock)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Semua stok mencukupi
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($low_stock as $item): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 text-sm"><?php echo htmlspecialchars($item['nama_obat']); ?></td>
                                    <td class="py-2 px-3 text-sm">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
                                            <?php echo htmlspecialchars($item['kategori']); ?>
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-sm text-right">
                                        <span class="text-red-600 font-semibold">
                                            <?php echo number_format($item['stok']); ?> <?php echo htmlspecialchars($item['satuan']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-calendar-times text-red-500 mr-2"></i>
                Akan Kadaluarsa (30 Hari)
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-sm font-semibold text-gray-700">Obat</th>
                            <th class="text-left py-2 px-3 text-sm font-semibold text-gray-700">Expired</th>
                            <th class="text-right py-2 px-3 text-sm font-semibold text-gray-700">Sisa Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expiring)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Tidak ada obat yang akan kadaluarsa
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expiring as $item): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 text-sm"><?php echo htmlspecialchars($item['nama_obat']); ?></td>
                                    <td class="py-2 px-3 text-sm"><?php echo date('d/m/Y', strtotime($item['expired_date'])); ?></td>
                                    <td class="py-2 px-3 text-sm text-right">
                                        <span class="<?php echo $item['days_until_expiry'] <= 7 ? 'text-red-600' : 'text-orange-600'; ?> font-semibold">
                                            <?php echo $item['days_until_expiry']; ?> hari
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($by_category, 'kategori')); ?>,
        datasets: [{
            label: 'Jumlah Item',
            data: <?php echo json_encode(array_column($by_category, 'item_count')); ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    color: '#ffffff'
                }
            },
            x: {
                ticks: {
                    color: '#ffffff'
                }
            }
        }
    }
});

// Value Distribution Chart
const valueCtx = document.getElementById('valueChart').getContext('2d');
new Chart(valueCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($by_category, 'kategori')); ?>,
        datasets: [{
            label: 'Nilai (Rp)',
            data: <?php echo json_encode(array_column($by_category, 'total_value')); ?>,
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#ffffff'
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
