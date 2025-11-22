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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

// Check role authorization
if (!in_array($_SESSION['role'], ['Admin', 'Dokter'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut";
    header("Location: index.php");
    exit;
}

$page_title = "Tambah Rekam Medis";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'pet_id' => $_POST['pet_id'] ?? '',
        'dokter_id' => $_POST['dokter_id'] ?? '',
        'appointment_id' => $_POST['appointment_id'] ?? null,
        'tanggal' => $_POST['tanggal'] ?? '',
        'diagnosis' => $_POST['diagnosis'] ?? '',
        'tindakan' => $_POST['tindakan'] ?? '',
        'resep' => $_POST['resep'] ?? '',
        'catatan' => $_POST['catatan'] ?? '',
        'biaya' => $_POST['biaya'] ? str_replace(['.', ','], '', $_POST['biaya']) : 0,
        'status' => 'Active'
    ];

    // Validate input
    $errors = validate_medical_record($data);

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert medical record
            $stmt = $pdo->prepare("
                INSERT INTO medical_record (
                    pet_id, dokter_id, appointment_id, tanggal,
                    diagnosis, tindakan, resep, catatan,
                    biaya, status, created_by, created_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $data['pet_id'],
                $data['dokter_id'],
                $data['appointment_id'],
                $data['tanggal'],
                $data['diagnosis'],
                $data['tindakan'],
                $data['resep'],
                $data['catatan'],
                $data['biaya'],
                $data['status'],
                $_SESSION['user_id']
            ]);

            $record_id = $pdo->lastInsertId();

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

            // Create history record
            create_medical_record_history(
                $pdo, 
                $record_id, 
                'CREATE', 
                null, 
                'Active',
                'Rekam medis baru dibuat'
            );

            // Update appointment status if from appointment
            if ($data['appointment_id']) {
                $stmt = $pdo->prepare("
                    UPDATE appointment 
                    SET status = 'Completed',
                        updated_by = ?,
                        updated_at = NOW()
                    WHERE appointment_id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $data['appointment_id']]);

                // Create appointment history
                $stmt = $pdo->prepare("
                    INSERT INTO appointment_history (
                        appointment_id, action, old_status, new_status,
                        notes, performed_by, performed_at
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, ?, NOW()
                    )
                ");
                $stmt->execute([
                    $data['appointment_id'],
                    'UPDATE',
                    'Confirmed',
                    'Completed',
                    'Status diperbarui setelah pembuatan rekam medis',
                    $_SESSION['user_id']
                ]);
            }

            $pdo->commit();
            $_SESSION['success'] = "Rekam medis berhasil dibuat";
            header("Location: detail.php?id=" . $record_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get appointment data if from appointment
$appointment = null;
if (isset($_GET['appointment_id'])) {
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            p.pet_id,
            p.nama_hewan,
            p.jenis as jenis_hewan,
            p.ras as ras_hewan,
            o.nama_lengkap as owner_name
        FROM appointment a
        JOIN pet p ON a.pet_id = p.pet_id
        JOIN owner o ON p.owner_id = o.owner_id
        WHERE a.appointment_id = ? AND a.status = 'Confirmed'
    ");
    $stmt->execute([$_GET['appointment_id']]);
    $appointment = $stmt->fetch();
}

// Get pets for dropdown
$stmt = $pdo->prepare("
    SELECT 
        p.pet_id,
        p.nama_hewan,
        p.jenis,
        p.ras,
        o.nama_lengkap as owner_name
    FROM pet p
    JOIN owner o ON p.owner_id = o.owner_id
    WHERE p.status = 'Active'
    ORDER BY p.nama_hewan
");
$stmt->execute();
$pets = $stmt->fetchAll();

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
            <h2 class="text-2xl font-bold text-gray-800">Tambah Rekam Medis</h2>
            <p class="text-gray-600">Buat rekam medis baru</p>
        </div>
        
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
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

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
        <!-- Appointment Info -->
        <?php if ($appointment): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-2">
                    Membuat Rekam Medis dari Janji Temu
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-blue-600">Pasien</p>
                        <p class="font-medium">
                            <?php echo htmlspecialchars($appointment['nama_hewan']); ?>
                            (<?php echo htmlspecialchars($appointment['jenis_hewan']); ?>
                            <?php if ($appointment['ras_hewan']): ?>
                                - <?php echo htmlspecialchars($appointment['ras_hewan']); ?>
                            <?php endif; ?>)
                        </p>
                        <p class="text-sm text-blue-600 mt-2">Pemilik</p>
                        <p class="font-medium">
                            <?php echo htmlspecialchars($appointment['owner_name']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-600">Tanggal Janji Temu</p>
                        <p class="font-medium">
                            <?php echo date('l, d F Y', strtotime($appointment['tanggal'])); ?>
                        </p>
                        <p class="text-sm text-blue-600 mt-2">Waktu</p>
                        <p class="font-medium">
                            <?php 
                            echo date('H:i', strtotime($appointment['jam_mulai'])) . ' - ' . 
                                 date('H:i', strtotime($appointment['jam_selesai'])) . ' WIB'; 
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
            <input type="hidden" name="pet_id" value="<?php echo $appointment['pet_id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Patient Selection -->
            <?php if (!$appointment): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pasien <span class="text-red-600">*</span>
                    </label>
                    <select name="pet_id" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Pilih Pasien</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?php echo $pet['pet_id']; ?>">
                                <?php echo htmlspecialchars($pet['nama_hewan']); ?> -
                                <?php echo htmlspecialchars($pet['owner_name']); ?>
                                (<?php echo htmlspecialchars($pet['jenis']); ?>
                                <?php if ($pet['ras']): ?>
                                    - <?php echo htmlspecialchars($pet['ras']); ?>
                                <?php endif; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Doctor Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Dokter <span class="text-red-600">*</span>
                </label>
                <select name="dokter_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Pilih Dokter</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo $doctor['dokter_id']; ?>">
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
                       value="<?php echo $appointment ? $appointment['tanggal'] : date('Y-m-d'); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
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
                          placeholder="Masukkan diagnosis..."><?php echo $_POST['diagnosis'] ?? ''; ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tindakan <span class="text-red-600">*</span>
                </label>
                <textarea name="tindakan" rows="3" required
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan tindakan yang dilakukan..."><?php echo $_POST['tindakan'] ?? ''; ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Resep
                </label>
                <textarea name="resep" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan resep obat jika ada..."><?php echo $_POST['resep'] ?? ''; ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan Tambahan
                </label>
                <textarea name="catatan" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                          placeholder="Masukkan catatan tambahan jika ada..."><?php echo $_POST['catatan'] ?? ''; ?></textarea>
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
                           value="<?php echo $_POST['biaya'] ?? '0'; ?>"
                           class="w-full pl-12 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="0"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Lampiran
                </label>
                <input type="file" name="attachment[]" multiple accept="image/*,.pdf"
                       class="w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-sm text-gray-500">
                    Format yang didukung: JPG, PNG, GIF, PDF. Maksimal 5MB per file.
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

<script>
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
</script>

<?php include '../includes/footer.php'; ?>