<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/medical_record_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

// Check role authorization
if (!in_array($_SESSION['role'], ['Admin', 'Dokter'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut";
    header("Location: index.php");
    exit;
}

$page_title = "Edit Rekam Medis";

// Get record ID
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$record_id) {
    $_SESSION['error'] = "ID rekam medis tidak valid";
    header("Location: index.php");
    exit;
}

// Get record data
$record = get_medical_record($pdo, $record_id);

if (!$record) {
    $_SESSION['error'] = "Data rekam medis tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Check if record can be edited
if ($record['status'] !== 'Active') {
    $_SESSION['error'] = "Rekam medis yang sudah diarsipkan tidak dapat diedit";
    header("Location: detail.php?id=" . $record_id);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'dokter_id' => $_POST['dokter_id'] ?? '',
        'tanggal' => $_POST['tanggal'] ?? '',
        'diagnosis' => $_POST['diagnosis'] ?? '',
        'tindakan' => $_POST['tindakan'] ?? '',
        'resep' => $_POST['resep'] ?? '',
        'catatan' => $_POST['catatan'] ?? '',
        'biaya' => $_POST['biaya'] ? str_replace(['.', ','], '', $_POST['biaya']) : 0,
        'status' => $_POST['status'] ?? 'Active'
    ];

    // Validate input
    $errors = validate_medical_record($data, false);

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update medical record
            $stmt = $pdo->prepare("
                UPDATE medical_record SET
                    dokter_id = ?,
                    tanggal_kunjungan = ?,
                    diagnosa = ?,
                    tindakan = ?,
                    catatan_dokter = ?,
                    status_kunjungan = ?
                WHERE rekam_id = ?
            ");

            $stmt->execute([
                $data['dokter_id'],
                $data['tanggal'],
                $data['diagnosis'],
                $data['tindakan'],
                $data['catatan'],
                $data['status_kunjungan'] ?? 'Pemeriksaan',
                $record_id
            ]);

            // Handle file uploads if any
            if (!empty($_FILES['attachment']['name'][0])) {
                $upload_result = handle_medical_record_attachments($_FILES, $record_id);
                
                if (!empty($upload_result['errors'])) {
                    throw new Exception(implode("<br>", $upload_result['errors']));
                }

                if (!empty($upload_result['uploaded_files'])) {
                    save_medical_record_attachments($pdo, $record_id, $upload_result['uploaded_files']);
                }
            }

            // Create history record if status changed
            if ($data['status'] !== $record['status']) {
                create_medical_record_history(
                    $pdo, 
                    $record_id, 
                    'UPDATE', 
                    $record['status'],
                    $data['status'],
                    'Status rekam medis diubah'
                );
            }

            $pdo->commit();
            $_SESSION['success'] = "Rekam medis berhasil diperbarui";
            header("Location: detail.php?id=" . $record_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get current attachments
$attachments = get_medical_record_attachments($pdo, $record_id);

// Get doctors for dropdown
$stmt = $pdo->prepare("
    SELECT dokter_id, nama_lengkap, spesialisasi
    FROM dokter
    WHERE status = 'Active'
    ORDER BY nama_lengkap
");
$stmt->execute();
$doctors = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Rekam Medis</h2>
            <p class="text-gray-600">Edit data rekam medis pasien</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="detail.php?id=<?php echo $record_id; ?>" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
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

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
        <!-- Patient Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-blue-600">Pasien</p>
                    <p class="font-medium">
                        <?php echo htmlspecialchars($record['nama_hewan']); ?>
                        (<?php echo htmlspecialchars($record['jenis_hewan']); ?>
                        <?php if ($record['ras_hewan']): ?>
                            - <?php echo htmlspecialchars($record['ras_hewan']); ?>
                        <?php endif; ?>)
                    </p>
                    <p class="text-sm text-blue-600 mt-2">Pemilik</p>
                    <p class="font-medium">
                        <?php echo htmlspecialchars($record['owner_name']); ?>
                    </p>
                </div>
                <?php if ($record['appointment_id']): ?>
                    <div>
                        <p class="text-sm text-blue-600">Dari Janji Temu</p>
                        <p class="font-medium">
                            <?php echo date('l, d F Y', strtotime($record['appointment_date'])); ?>
                            <br>
                            <?php echo date('H:i', strtotime($record['appointment_time'])); ?> WIB
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Doctor Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Dokter <span class="text-red-600">*</span>
                </label>
                <select name="dokter_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Dokter</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo $doctor['dokter_id']; ?>"
                                <?php echo $doctor['dokter_id'] == $record['dokter_id'] ? 'selected' : ''; ?>>
                            Dr. <?php echo htmlspecialchars($doctor['nama_lengkap']); ?>
                            <?php if ($doctor['spesialisasi']): ?>
                                (<?php echo htmlspecialchars($doctor['spesialisasi']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal <span class="text-red-600">*</span>
                </label>
                <input type="date" name="tanggal" required
                       value="<?php echo $record['tanggal']; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="Active" <?php echo $record['status'] === 'Active' ? 'selected' : ''; ?>>
                        Active
                    </option>
                    <option value="Archived" <?php echo $record['status'] === 'Archived' ? 'selected' : ''; ?>>
                        Archived
                    </option>
                </select>
            </div>
        </div>

        <!-- Medical Details -->
        <div class="grid grid-cols-1 gap-6 mt-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Diagnosis <span class="text-red-600">*</span>
                </label>
                <textarea name="diagnosis" rows="3" required
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan diagnosis..."><?php echo htmlspecialchars($record['diagnosis']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tindakan <span class="text-red-600">*</span>
                </label>
                <textarea name="tindakan" rows="3" required
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan tindakan yang dilakukan..."><?php echo htmlspecialchars($record['tindakan']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Resep
                </label>
                <textarea name="resep" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan resep obat jika ada..."><?php echo htmlspecialchars($record['resep']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan Tambahan
                </label>
                <textarea name="catatan" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan catatan tambahan jika ada..."><?php echo htmlspecialchars($record['catatan']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Biaya
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">Rp</span>
                    </div>
                    <input type="text" name="biaya"
                           value="<?php echo number_format($record['biaya'], 0, ',', '.'); ?>"
                           class="w-full pl-12 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="0"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                </div>
            </div>

            <!-- Current Attachments -->
            <?php if (!empty($attachments)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lampiran Saat Ini
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($attachments as $attachment): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <?php if (strpos($attachment['file_type'], 'image/') === 0): ?>
                                        <i class="fas fa-image text-blue-500 mr-2"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php 
                                            echo strlen($attachment['original_name']) > 20 
                                                ? substr($attachment['original_name'], 0, 17) . '...' 
                                                : $attachment['original_name']; 
                                            ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo formatBytes($attachment['file_size']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="/vetclinic/assets/uploads/medical_records/<?php echo $record_id; ?>/<?php echo $attachment['stored_name']; ?>"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" 
                                            onclick="deleteAttachment(<?php echo $attachment['attachment_id']; ?>)"
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- New Attachments -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tambah Lampiran Baru
                </label>
                <input type="file" name="attachment[]" multiple accept="image/*,.pdf"
                       class="w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-sm text-gray-500">
                    Format yang didukung: JPG, PNG, GIF, PDF. Maksimal 5MB per file.
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="detail.php?id=<?php echo $record_id; ?>" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
function deleteAttachment(attachmentId) {
    if (confirm('Apakah Anda yakin ingin menghapus lampiran ini?')) {
        window.location.href = `delete_attachment.php?id=${attachmentId}&record_id=<?php echo $record_id; ?>`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle file input validation
    const attachmentInput = document.querySelector('input[name="attachment[]"]');
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            const files = this.files;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.size > maxSize) {
                    alert(`File "${file.name}" terlalu besar. Maksimal ukuran file adalah 5MB.`);
                    this.value = '';
                    return;
                }
                if (!allowedTypes.includes(file.type)) {
                    alert(`File "${file.name}" tidak didukung. Format yang didukung: JPG, PNG, GIF, PDF.`);
                    this.value = '';
                    return;
                }
            }
        });
    }
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}