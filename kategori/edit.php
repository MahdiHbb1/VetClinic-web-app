<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user has permission
if (!in_array($_SESSION['role'], ['Admin', 'Inventory', 'Service'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut!";
    header("Location: index.php");
    exit;
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = "Edit Kategori";

// Validate and get category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID Kategori tidak valid!";
    header("Location: index.php");
    exit;
}

$kategori_id = (int)$_GET['id'];

// Fetch service details
try {
    $stmt = $pdo->prepare("
        SELECT layanan_id as kategori_id,
               nama_layanan as nama_kategori,
               kategori as tipe,
               deskripsi,
               harga,
               durasi_estimasi,
               status_tersedia as status
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['nama_kategori', 'status'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field $field harus diisi!");
            }
        }

        // Validate status
        $status_tersedia = $_POST['status'] === 'Active' ? 1 : 0;
        
        // Validate kategori enum
        $valid_categories = ['Pemeriksaan', 'Vaksinasi', 'Grooming', 'Bedah', 'Rawat_Inap', 'Tes_Lab', 'Emergency'];
        if (!empty($_POST['kategori']) && !in_array($_POST['kategori'], $valid_categories)) {
            throw new Exception("Kategori tidak valid!");
        }

        // Validate harga and durasi
        $harga = filter_var($_POST['harga'] ?? 0, FILTER_VALIDATE_FLOAT);
        $durasi_estimasi = filter_var($_POST['durasi_estimasi'] ?? 0, FILTER_VALIDATE_INT);
        
        if ($harga === false || $harga < 0) {
            throw new Exception("Harga tidak valid!");
        }

        // Update service
        $stmt = $pdo->prepare("
            UPDATE service 
            SET nama_layanan = ?,
                kategori = ?,
                harga = ?,
                durasi_estimasi = ?,
                deskripsi = ?,
                status_tersedia = ?
            WHERE layanan_id = ?
        ");
        
        $stmt->execute([
            trim($_POST['nama_kategori']),
            $_POST['kategori'] ?? $category['tipe'],
            $harga,
            $durasi_estimasi,
            trim($_POST['deskripsi'] ?? ''),
            $status_tersedia,
            $kategori_id
        ]);
        
        $_SESSION['success'] = "Layanan berhasil diupdate!";
        header("Location: detail.php?id=" . $kategori_id);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Kategori</h1>
            <p class="text-sm text-gray-600">Update informasi kategori</p>
        </div>
        <div class="flex space-x-2">
            <a href="detail.php?id=<?php echo $category['kategori_id']; ?>" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                           id="nama_kategori" 
                           name="nama_kategori" 
                           required
                           value="<?php echo htmlspecialchars($_POST['nama_kategori'] ?? $category['nama_kategori']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="text-sm text-gray-500 mt-1">
                        Masukkan nama kategori yang unik
                    </p>
                </div>

                <div>
                    <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipe
                    </label>
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
                    <div class="mt-1">
                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium <?php echo $typeClass; ?>">
                            <?php echo $typeLabel; ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Tipe kategori tidak dapat diubah
                    </p>
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi
                    </label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"><?php echo htmlspecialchars($_POST['deskripsi'] ?? $category['deskripsi']); ?></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        Tambahkan deskripsi detail tentang kategori (opsional)
                    </p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            <?php 
                            // Disable status change if category is in use
                            $total_items = match($category['tipe']) {
                                'Inventory' => $category['total_inventory'],
                                'Service' => $category['total_service'],
                                'Medicine' => $category['total_medicine'],
                                default => 0
                            };
                            echo $category['status'] === 'Active' && $total_items > 0 ? 'disabled' : '';
                            ?>>
                        <option value="Active" <?php echo (isset($_POST['status']) ? $_POST['status'] === 'Active' : $category['status'] === 'Active') ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="Inactive" <?php echo (isset($_POST['status']) ? $_POST['status'] === 'Inactive' : $category['status'] === 'Inactive') ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>
                    </select>
                    <?php if ($category['status'] === 'Active' && $total_items > 0): ?>
                        <p class="text-sm text-red-500 mt-1">
                            Status tidak dapat diubah karena kategori sedang digunakan
                        </p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mt-1">
                            Pilih status kategori
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="detail.php?id=<?php echo $category['kategori_id']; ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg inline-flex items-center">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>