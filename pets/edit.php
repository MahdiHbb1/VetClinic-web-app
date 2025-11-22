<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Edit Hewan';

// Get pet ID from URL
$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pet_id) {
    $_SESSION['error'] = "ID Hewan tidak valid";
    header("Location: index.php");
    exit;
}

// Get pet data
$stmt = $pdo->prepare("SELECT * FROM pet WHERE pet_id = ?");
$stmt->execute([$pet_id]);
$pet = $stmt->fetch();

if (!$pet) {
    $_SESSION['error'] = "Data hewan tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get all owners for the dropdown
$owners_stmt = $pdo->query("SELECT owner_id, nama_lengkap, no_telepon FROM owner ORDER BY nama_lengkap");
$owners = $owners_stmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        $owner_id = clean_input($_POST['owner_id']);
        $nama_hewan = clean_input($_POST['nama_hewan']);
        $jenis = clean_input($_POST['jenis']);
        $ras = clean_input($_POST['ras']);
        $jenis_kelamin = clean_input($_POST['jenis_kelamin']);
        $tanggal_lahir = clean_input($_POST['tanggal_lahir']);
        $warna = clean_input($_POST['warna']);
        $status = clean_input($_POST['status']);
        $catatan = clean_input($_POST['catatan']);

        // Handle file upload if new photo is provided
        if (!empty($_FILES['foto']['name'])) {
            $foto_url = handle_file_upload($_FILES['foto'], 'pets');
            if ($foto_url === false) {
                throw new Exception("Error uploading file");
            }

            // Delete old photo if exists
            if ($pet['foto_url']) {
                $old_photo_path = __DIR__ . '/../assets/images/uploads/' . $pet['foto_url'];
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }
        } else {
            $foto_url = $pet['foto_url']; // Keep existing photo
        }

        // Update pet data
        $stmt = $pdo->prepare("
            UPDATE pet SET 
                owner_id = ?,
                nama_hewan = ?,
                jenis = ?,
                ras = ?,
                jenis_kelamin = ?,
                tanggal_lahir = ?,
                warna = ?,
                status = ?,
                catatan = ?,
                foto_url = ?
            WHERE pet_id = ?
        ");

        $stmt->execute([
            $owner_id,
            $nama_hewan,
            $jenis,
            $ras,
            $jenis_kelamin,
            $tanggal_lahir,
            $warna,
            $status,
            $catatan,
            $foto_url,
            $pet_id
        ]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Data hewan berhasil diperbarui";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container max-w-4xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Data Hewan</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Owner Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="owner_id">
                        Pemilik <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_id" id="owner_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Pemilik</option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?php echo $owner['owner_id']; ?>"
                                    <?php echo $owner['owner_id'] == $pet['owner_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($owner['nama_lengkap']); ?> - 
                                <?php echo htmlspecialchars($owner['no_telepon']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Basic Information -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_hewan">
                        Nama Hewan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_hewan" id="nama_hewan" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo htmlspecialchars($pet['nama_hewan']); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis">
                        Jenis Hewan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="jenis" id="jenis" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Kucing, Anjing, dll"
                           value="<?php echo htmlspecialchars($pet['jenis']); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="ras">
                        Ras
                    </label>
                    <input type="text" name="ras" id="ras"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Persian, Pomeranian, dll"
                           value="<?php echo htmlspecialchars($pet['ras']); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_kelamin">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="jenis_kelamin" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Jantan" <?php echo $pet['jenis_kelamin'] === 'Jantan' ? 'selected' : ''; ?>>
                            Jantan
                        </option>
                        <option value="Betina" <?php echo $pet['jenis_kelamin'] === 'Betina' ? 'selected' : ''; ?>>
                            Betina
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal_lahir">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo $pet['tanggal_lahir']; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="warna">
                        Warna
                    </label>
                    <input type="text" name="warna" id="warna"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo htmlspecialchars($pet['warna']); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Aktif" <?php echo $pet['status'] === 'Aktif' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="Meninggal" <?php echo $pet['status'] === 'Meninggal' ? 'selected' : ''; ?>>
                            Meninggal
                        </option>
                    </select>
                </div>

                <!-- Photo Upload -->
                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">
                        Foto Hewan
                    </label>
                    <?php if ($pet['foto_url']): ?>
                        <div class="mb-4">
                            <img src="/vetclinic/assets/images/uploads/<?php echo $pet['foto_url']; ?>"
                                 alt="<?php echo htmlspecialchars($pet['nama_hewan']); ?>"
                                 class="w-32 h-32 object-cover rounded-lg">
                            <p class="text-sm text-gray-500 mt-1">Foto saat ini</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto" id="foto" accept="image/*"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">
                        Format yang didukung: JPG, PNG. Maksimal 2MB
                    </p>
                </div>

                <!-- Notes -->
                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="catatan">
                        Catatan
                    </label>
                    <textarea name="catatan" id="catatan" rows="4"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Tambahkan catatan khusus tentang hewan ini..."
                    ><?php echo htmlspecialchars($pet['catatan']); ?></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-4">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>