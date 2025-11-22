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

$page_title = "Detail Kategori";

// Validate and get category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID Kategori tidak valid!";
    header("Location: index.php");
    exit;
}

$kategori_id = (int)$_GET['id'];

// Fetch service details (kategori uses service table)
try {
    $stmt = $pdo->prepare("
        SELECT 
            layanan_id as kategori_id,
            nama_layanan as nama_kategori,
            kategori as tipe,
            deskripsi,
            harga,
            durasi_estimasi,
            CASE WHEN status_tersedia = 1 THEN 'Active' ELSE 'Inactive' END as status,
            status_tersedia,
            0 as total_inventory,
            0 as total_service,
            0 as total_medicine,
            NULL as created_by_name,
            NULL as updated_by_name
        FROM service
        WHERE layanan_id = ?
    ");
    
    $stmt->execute([$kategori_id]);
    $category = $stmt->fetch();

    if (!$category) {
        throw new Exception("Layanan tidak ditemukan!");
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: index.php");
    exit;
}

// Fetch items using this category based on type
$items = [];
try {
    switch ($category['tipe']) {
        case 'Inventory':
            $stmt = $pdo->prepare("
                SELECT i.*, u.nama as updated_by_name
                FROM inventory i
                LEFT JOIN users u ON i.updated_by = u.user_id
                WHERE i.kategori_id = ?
                ORDER BY i.nama_item
                LIMIT 10
            ");
            break;
        
        case 'Service':
            $stmt = $pdo->prepare("
                SELECT s.*, u.nama as updated_by_name
                FROM service s
                LEFT JOIN users u ON s.updated_by = u.user_id
                WHERE s.kategori_id = ?
                ORDER BY s.nama_service
                LIMIT 10
            ");
            break;
        
        case 'Medicine':
            $stmt = $pdo->prepare("
                SELECT m.*, u.nama as updated_by_name
                FROM medicine m
                LEFT JOIN users u ON m.updated_by = u.user_id
                WHERE m.kategori_id = ?
                ORDER BY m.nama_medicine
                LIMIT 10
            ");
            break;
    }

    if ($stmt) {
        $stmt->execute([$kategori_id]);
        $items = $stmt->fetchAll();
    }

} catch (Exception $e) {
    // Just log the error, don't redirect
    error_log("Error fetching items: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Kategori</h1>
            <p class="text-sm text-gray-600">Informasi lengkap dan penggunaan kategori</p>
        </div>
        <div class="flex space-x-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory', 'Service'])): ?>
                <a href="edit.php?id=<?php echo $category['kategori_id']; ?>" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
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

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Kategori</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                        <p class="mt-1 text-gray-900">
                            <?php echo htmlspecialchars($category['nama_kategori']); ?>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipe</label>
                        <p class="mt-1">
                            <?php
                            $typeLabel = match($category['tipe']) {
                                'Inventory' => 'Inventaris',
                                'Service' => 'Layanan',
                                'Medicine' => 'Obat',
                                default => $category['tipe']
                            };
                            $typeClass = match($category['tipe']) {
                                'Inventory' => 'bg-blue-100 text-blue-800',
                                'Service' => 'bg-purple-100 text-purple-800',
                                'Medicine' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $typeClass; ?>">
                                <?php echo $typeLabel; ?>
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p class="mt-1">
                            <?php
                            $statusClass = $category['status'] === 'Active'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                <?php echo $category['status'] === 'Active' ? 'Aktif' : 'Nonaktif'; ?>
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Item</label>
                        <p class="mt-1 text-gray-900">
                            <?php
                            $total = match($category['tipe']) {
                                'Inventory' => $category['total_inventory'],
                                'Service' => $category['total_service'],
                                'Medicine' => $category['total_medicine'],
                                default => 0
                            };
                            echo number_format($total) . ' item';
                            ?>
                        </p>
                    </div>

                    <?php if ($category['deskripsi']): ?>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <p class="mt-1 text-gray-900">
                                <?php echo nl2br(htmlspecialchars($category['deskripsi'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($items): ?>
                <!-- Items List -->
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Item dalam Kategori
                        </h2>
                        <?php
                        $viewAllLink = match($category['tipe']) {
                            'Inventory' => '../inventory/index.php?kategori_id=' . $category['kategori_id'],
                            'Service' => '../service/index.php?kategori_id=' . $category['kategori_id'],
                            'Medicine' => '../medicine/index.php?kategori_id=' . $category['kategori_id'],
                            default => '#'
                        };
                        ?>
                        <a href="<?php echo $viewAllLink; ?>" class="text-blue-500 hover:text-blue-600">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">
                                        <?php
                                        echo match($category['tipe']) {
                                            'Inventory' => 'Nama Item',
                                            'Service' => 'Nama Layanan',
                                            'Medicine' => 'Nama Obat',
                                            default => 'Nama'
                                        };
                                        ?>
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Update Terakhir</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($items as $item): 
                                    $itemName = match($category['tipe']) {
                                        'Inventory' => $item['nama_item'] ?? '',
                                        'Service' => $item['nama_service'] ?? '',
                                        'Medicine' => $item['nama_medicine'] ?? '',
                                        default => ''
                                    };
                                    
                                    $itemId = match($category['tipe']) {
                                        'Inventory' => $item['item_id'] ?? '',
                                        'Service' => $item['service_id'] ?? '',
                                        'Medicine' => $item['medicine_id'] ?? '',
                                        default => ''
                                    };
                                    
                                    $detailLink = match($category['tipe']) {
                                        'Inventory' => '../inventory/detail.php?id=' . $itemId,
                                        'Service' => '../service/detail.php?id=' . $itemId,
                                        'Medicine' => '../medicine/detail.php?id=' . $itemId,
                                        default => '#'
                                    };
                                ?>
                                    <tr>
                                        <td class="px-4 py-2">
                                            <?php echo htmlspecialchars($itemName); ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <?php
                                            $statusClass = $item['status'] === 'Active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800';
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                                <?php echo $item['status'] === 'Active' ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500">
                                            <?php 
                                            if (isset($item['updated_at']) && $item['updated_at']) {
                                                echo date('d/m/Y H:i', strtotime($item['updated_at']));
                                                if (isset($item['updated_by_name']) && $item['updated_by_name']) {
                                                    echo ' oleh ' . htmlspecialchars($item['updated_by_name']);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="<?php echo $detailLink; ?>" 
                                               class="text-blue-500 hover:text-blue-600"
                                               title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Audit Information -->
        <div class="lg:col-span-1">
            <div class="bg-blue-500 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Informasi Audit</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-100">Dibuat Oleh</label>
                        <p class="mt-1 text-white font-medium">
                            <?php echo htmlspecialchars($category['created_by_name'] ?? 'System'); ?>
                        </p>
                        <?php if (!empty($category['created_at'])): ?>
                        <p class="text-sm text-blue-100">
                            <?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?>
                        </p>
                        <?php else: ?>
                        <p class="text-sm text-blue-100">-</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($category['updated_at'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-blue-100">Terakhir Diupdate</label>
                            <p class="mt-1 text-white font-medium">
                                <?php echo htmlspecialchars($category['updated_by_name'] ?? 'System'); ?>
                            </p>
                            <p class="text-sm text-blue-100">
                                <?php echo date('d/m/Y H:i', strtotime($category['updated_at'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Penggunaan Kategori</h3>
                        <div class="space-y-2">
                            <?php
                            $usageLinks = [];
                            
                            if ($category['total_inventory'] > 0) {
                                $usageLinks[] = sprintf(
                                    '<a href="../inventory/index.php?kategori_id=%d" class="text-blue-500 hover:text-blue-600">%d item inventaris</a>',
                                    $category['kategori_id'],
                                    $category['total_inventory']
                                );
                            }
                            
                            if ($category['total_service'] > 0) {
                                $usageLinks[] = sprintf(
                                    '<a href="../service/index.php?kategori_id=%d" class="text-blue-500 hover:text-blue-600">%d layanan</a>',
                                    $category['kategori_id'],
                                    $category['total_service']
                                );
                            }
                            
                            if ($category['total_medicine'] > 0) {
                                $usageLinks[] = sprintf(
                                    '<a href="../medicine/index.php?kategori_id=%d" class="text-blue-500 hover:text-blue-600">%d obat</a>',
                                    $category['kategori_id'],
                                    $category['total_medicine']
                                );
                            }
                            
                            if ($usageLinks): ?>
                                <p class="text-sm text-gray-600">
                                    Kategori ini digunakan di:
                                </p>
                                <ul class="list-disc list-inside text-sm">
                                    <?php foreach ($usageLinks as $link): ?>
                                        <li><?php echo $link; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">
                                    Kategori ini belum digunakan
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>