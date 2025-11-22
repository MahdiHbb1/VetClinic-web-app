<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/appointment_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

$page_title = 'Detail Janji Temu';

// Get appointment ID
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointment_id) {
    $_SESSION['error'] = "ID Janji Temu tidak valid";
    header("Location: index.php");
    exit;
}

// Get appointment details
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        p.nama_hewan,
        p.jenis as jenis_hewan,
        p.ras as ras_hewan,
        p.foto_url as pet_foto,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        o.email as owner_email,
        d.nama_lengkap as dokter_name,
        d.spesialisasi as dokter_spesialisasi,
        d.foto_url as dokter_foto,
        s.nama_layanan,
        s.durasi_estimasi,
        s.harga as harga_layanan,
        u_created.nama_lengkap as created_by_name,
        u_updated.nama_lengkap as updated_by_name
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN owner o ON a.owner_id = o.owner_id
    JOIN dokter d ON a.dokter_id = d.dokter_id
    JOIN service s ON a.layanan_id = s.layanan_id
    LEFT JOIN users u_created ON a.created_by = u_created.user_id
    LEFT JOIN users u_updated ON a.updated_by = u_updated.user_id
    WHERE a.appointment_id = ?
");
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    $_SESSION['error'] = "Data janji temu tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get appointment history
$stmt = $pdo->prepare("
    SELECT 
        ah.*,
        u.nama_lengkap as performed_by_name
    FROM appointment_history ah
    LEFT JOIN users u ON ah.performed_by = u.user_id
    WHERE ah.appointment_id = ?
    ORDER BY ah.performed_at DESC
");
$stmt->execute([$appointment_id]);
$history = $stmt->fetchAll();

// Get medical records if appointment is completed
$medical_records = [];
if ($appointment['status'] === 'Completed') {
    $stmt = $pdo->prepare("
        SELECT 
            mr.*,
            d.nama_lengkap as dokter_name
        FROM medical_record mr
        JOIN dokter d ON mr.dokter_id = d.dokter_id
        WHERE mr.appointment_id = ?
        ORDER BY mr.created_at DESC
    ");
    $stmt->execute([$appointment_id]);
    $medical_records = $stmt->fetchAll();
}

// Get related services and charges
$stmt = $pdo->prepare("
    SELECT 
        al.*,
        s.nama_layanan
    FROM appointment_layanan al
    JOIN service s ON al.layanan_id = s.layanan_id
    WHERE al.appointment_id = ?
    ORDER BY al.created_at
");
$stmt->execute([$appointment_id]);
$services = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Detail Janji Temu</h2>
            <p class="text-gray-600">
                ID: #<?php echo str_pad($appointment_id, 6, '0', STR_PAD_LEFT); ?>
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <?php if ($appointment['status'] !== 'Completed' && $appointment['status'] !== 'Cancelled'): ?>
                <a href="edit.php?id=<?php echo $appointment_id; ?>" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Dokter'): ?>
                    <button onclick="confirmDelete(<?php echo $appointment_id; ?>)"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-trash mr-2"></i> Hapus
                    </button>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($appointment['status'] === 'Confirmed'): ?>
                <a href="../medical-records/create.php?appointment_id=<?php echo $appointment_id; ?>"
                   class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-notes-medical mr-2"></i> Buat Rekam Medis
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Appointment Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Janji Temu</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Status and Schedule -->
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Status</p>
                        <div class="mb-4">
                            <?php echo get_appointment_status_badge($appointment['status']); ?>
                        </div>

                        <p class="text-sm text-gray-600 mb-1">Tanggal & Waktu</p>
                        <p class="font-medium mb-4">
                            <?php echo date('l, d F Y', strtotime($appointment['tanggal'])); ?><br>
                            <?php echo date('H:i', strtotime($appointment['jam_mulai'])); ?> - 
                            <?php echo date('H:i', strtotime($appointment['jam_selesai'])); ?> WIB
                        </p>

                        <p class="text-sm text-gray-600 mb-1">Layanan</p>
                        <p class="font-medium mb-4">
                            <?php echo htmlspecialchars($appointment['nama_layanan']); ?><br>
                            <span class="text-sm text-gray-600">
                                Durasi: <?php echo $appointment['durasi_estimasi']; ?> menit
                            </span>
                        </p>
                    </div>

                    <!-- Created/Updated Info -->
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dibuat Oleh</p>
                        <p class="font-medium mb-4">
                            <?php echo htmlspecialchars($appointment['created_by_name']); ?><br>
                            <span class="text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($appointment['created_at'])); ?>
                            </span>
                        </p>

                        <?php if ($appointment['updated_by']): ?>
                            <p class="text-sm text-gray-600 mb-1">Terakhir Diubah</p>
                            <p class="font-medium mb-4">
                                <?php echo htmlspecialchars($appointment['updated_by_name']); ?><br>
                                <span class="text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($appointment['updated_at'])); ?>
                                </span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Complaint & Notes -->
                <div class="mt-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Keluhan</p>
                        <p class="bg-gray-50 p-3 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($appointment['keluhan'])); ?>
                        </p>
                    </div>

                    <?php if ($appointment['catatan']): ?>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Catatan</p>
                            <p class="bg-gray-50 p-3 rounded-lg">
                                <?php echo nl2br(htmlspecialchars($appointment['catatan'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medical Records -->
            <?php if (!empty($medical_records)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Rekam Medis</h3>
                
                <?php foreach ($medical_records as $record): ?>
                    <div class="border-b border-gray-200 last:border-0 pb-4 mb-4 last:pb-0 last:mb-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">
                                    Dr. <?php echo htmlspecialchars($record['dokter_name']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?>
                                </p>
                            </div>
                            <a href="../medical-records/detail.php?id=<?php echo $record['record_id']; ?>"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="text-sm">
                                <span class="text-gray-600">Diagnosis:</span>
                                <?php echo htmlspecialchars($record['diagnosis']); ?>
                            </p>
                            <p class="text-sm">
                                <span class="text-gray-600">Tindakan:</span>
                                <?php echo htmlspecialchars($record['tindakan']); ?>
                            </p>
                            <?php if ($record['resep']): ?>
                                <p class="text-sm">
                                    <span class="text-gray-600">Resep:</span>
                                    <?php echo nl2br(htmlspecialchars($record['resep'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Services and Charges -->
            <?php if (!empty($services)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Layanan & Biaya</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Layanan</th>
                                <th class="text-right py-2">Jumlah</th>
                                <th class="text-right py-2">Harga</th>
                                <th class="text-right py-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            foreach ($services as $service): 
                                $subtotal = $service['jumlah'] * $service['harga'];
                                $total += $subtotal;
                            ?>
                                <tr class="border-b">
                                    <td class="py-2"><?php echo htmlspecialchars($service['nama_layanan']); ?></td>
                                    <td class="text-right py-2"><?php echo $service['jumlah']; ?></td>
                                    <td class="text-right py-2">Rp <?php echo number_format($service['harga'], 0, ',', '.'); ?></td>
                                    <td class="text-right py-2">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="font-bold">
                                <td colspan="3" class="py-2 text-right">Total</td>
                                <td class="py-2 text-right">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Appointment History -->
            <?php if (!empty($history)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Perubahan</h3>
                
                <div class="space-y-4">
                    <?php foreach ($history as $log): ?>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <?php if ($log['action'] === 'CREATE'): ?>
                                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                <?php elseif ($log['action'] === 'UPDATE'): ?>
                                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1">
                                <p class="font-medium">
                                    <?php echo htmlspecialchars($log['performed_by_name']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($log['performed_at'])); ?>
                                </p>
                                <?php if ($log['action'] === 'UPDATE'): ?>
                                    <p class="text-sm mt-1">
                                        Status: 
                                        <span class="text-gray-600">
                                            <?php echo $log['old_status']; ?> → <?php echo $log['new_status']; ?>
                                        </span>
                                    </p>
                                <?php endif; ?>
                                <?php if ($log['notes']): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($log['notes']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Patient Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Pasien</h3>
                
                <div class="flex items-start gap-4 mb-4">
                    <?php if ($appointment['pet_foto']): ?>
                        <img src="/vetclinic/assets/images/uploads/<?php echo $appointment['pet_foto']; ?>"
                             alt="<?php echo htmlspecialchars($appointment['nama_hewan']); ?>"
                             class="w-20 h-20 rounded-lg object-cover">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paw text-gray-400 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h4 class="font-bold text-lg">
                            <?php echo htmlspecialchars($appointment['nama_hewan']); ?>
                        </h4>
                        <p class="text-gray-600">
                            <?php echo htmlspecialchars($appointment['jenis_hewan']); ?>
                            <?php if ($appointment['ras_hewan']): ?>
                                - <?php echo htmlspecialchars($appointment['ras_hewan']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <p class="text-sm text-gray-600 mb-1">Pemilik</p>
                    <p class="font-medium">
                        <?php echo htmlspecialchars($appointment['owner_name']); ?>
                    </p>
                    <p class="text-gray-600 text-sm">
                        <?php echo htmlspecialchars($appointment['owner_phone']); ?>
                        <?php if ($appointment['owner_email']): ?>
                            <br><?php echo htmlspecialchars($appointment['owner_email']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Doctor Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Dokter</h3>
                
                <div class="flex items-start gap-4">
                    <?php if ($appointment['dokter_foto']): ?>
                        <img src="/vetclinic/assets/images/uploads/<?php echo $appointment['dokter_foto']; ?>"
                             alt="Dr. <?php echo htmlspecialchars($appointment['dokter_name']); ?>"
                             class="w-20 h-20 rounded-lg object-cover">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-md text-gray-400 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h4 class="font-bold text-lg">
                            Dr. <?php echo htmlspecialchars($appointment['dokter_name']); ?>
                        </h4>
                        <?php if ($appointment['dokter_spesialisasi']): ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($appointment['dokter_spesialisasi']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus janji temu ini?",
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