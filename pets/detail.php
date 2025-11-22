<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/appointment_functions.php';

$page_title = 'Detail Hewan';

// Get pet ID from URL
$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pet_id) {
    $_SESSION['error'] = "ID Hewan tidak valid";
    header("Location: index.php");
    exit;
}

// Get detailed pet data including owner info and statistics
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        o.email as owner_email,
        o.alamat as owner_address,
        COUNT(DISTINCT a.appointment_id) as total_visits,
        COUNT(DISTINCT v.vaksinasi_id) as total_vaccinations
    FROM pet p
    JOIN owner o ON p.owner_id = o.owner_id
    LEFT JOIN appointment a ON p.pet_id = a.pet_id
    LEFT JOIN vaksinasi v ON p.pet_id = v.pet_id
    WHERE p.pet_id = ?
    GROUP BY p.pet_id
");
$stmt->execute([$pet_id]);
$pet = $stmt->fetch();

if (!$pet) {
    $_SESSION['error'] = "Data hewan tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get vaccination history
$vac_stmt = $pdo->prepare("
    SELECT 
        v.*,
        vet.nama_dokter as dokter_name
    FROM vaksinasi v
    LEFT JOIN veterinarian vet ON v.dokter_id = vet.dokter_id
    WHERE v.pet_id = ?
    ORDER BY v.tanggal_vaksin DESC
");
$vac_stmt->execute([$pet_id]);
$vaccinations = $vac_stmt->fetchAll();

// Get appointment history
$app_stmt = $pdo->prepare("
    SELECT 
        a.*,
        vet.nama_dokter as dokter_name
    FROM appointment a
    LEFT JOIN veterinarian vet ON a.dokter_id = vet.dokter_id
    WHERE a.pet_id = ?
    ORDER BY a.tanggal_appointment DESC
");
$app_stmt->execute([$pet_id]);
$appointments = $app_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Detail Hewan</h2>
        <div class="flex gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <a href="edit.php?id=<?php echo $pet_id; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Photo Section -->
                    <div class="w-full md:w-1/3">
                        <?php if ($pet['foto_url']): ?>
                            <?php 
                            // Check if foto_url is external URL or local path
                            $foto_src = (strpos($pet['foto_url'], 'http') === 0) 
                                ? $pet['foto_url'] 
                                : '/vetclinic/assets/images/uploads/' . $pet['foto_url'];
                            ?>
                            <img src="<?php echo $foto_src; ?>"
                                 alt="<?php echo htmlspecialchars($pet['nama_hewan']); ?>"
                                 class="w-full h-64 object-cover rounded-lg shadow-md"
                                 onerror="this.src='https://via.placeholder.com/400x300?text=Pet+Photo'">
                        <?php else: ?>
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-paw text-gray-400 text-5xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Details Section -->
                    <div class="w-full md:w-2/3">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    <?php echo htmlspecialchars($pet['nama_hewan']); ?>
                                </h3>
                                <p class="text-gray-600">
                                    <?php echo htmlspecialchars($pet['jenis']); ?> - 
                                    <?php echo htmlspecialchars($pet['ras'] ?? 'Tidak ada ras'); ?>
                                </p>
                            </div>
                            <?php echo get_status_badge($pet['status'], 'lg'); ?>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-gray-500">Jenis Kelamin</p>
                                <p class="font-medium">
                                    <?php if ($pet['jenis_kelamin'] === 'Jantan'): ?>
                                        <i class="fas fa-mars text-blue-500"></i>
                                    <?php else: ?>
                                        <i class="fas fa-venus text-pink-500"></i>
                                    <?php endif; ?>
                                    <?php echo $pet['jenis_kelamin']; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500">Warna</p>
                                <p class="font-medium"><?php echo htmlspecialchars($pet['warna'] ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Tanggal Lahir</p>
                                <p class="font-medium">
                                    <?php echo $pet['tanggal_lahir'] ? date('d/m/Y', strtotime($pet['tanggal_lahir'])) : '-'; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500">Tanggal Registrasi</p>
                                <p class="font-medium">
                                    <?php echo date('d/m/Y', strtotime($pet['tanggal_registrasi'])); ?>
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($pet['ciri_khusus'])): ?>
                            <div class="mb-4">
                                <h4 class="text-gray-500 mb-2">Ciri Khusus</h4>
                                <p class="text-gray-800 bg-gray-50 p-3 rounded-lg">
                                    <?php echo nl2br(htmlspecialchars($pet['ciri_khusus'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-user mr-2"></i> Informasi Pemilik
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-500">Nama Lengkap</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pet['owner_name']); ?></p>
                </div>
                <div>
                    <p class="text-gray-500">No. Telepon</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pet['owner_phone']); ?></p>
                </div>
                <?php if ($pet['owner_email']): ?>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pet['owner_email']); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($pet['owner_address']): ?>
                    <div>
                        <p class="text-gray-500">Alamat</p>
                        <p class="font-medium"><?php echo nl2br(htmlspecialchars($pet['owner_address'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vaccination History -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-syringe mr-2"></i> Riwayat Vaksinasi
                    </h3>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                        Total: <?php echo count($vaccinations); ?>
                    </span>
                </div>

                <?php if (!empty($vaccinations)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Tanggal</th>
                                    <th class="text-left py-2">Jenis Vaksin</th>
                                    <th class="text-left py-2">Dokter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vaccinations as $vac): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2">
                                            <?php echo date('d/m/Y', strtotime($vac['tanggal_vaksin'])); ?>
                                        </td>
                                        <td class="py-2">
                                            <?php echo htmlspecialchars($vac['jenis_vaksin']); ?>
                                        </td>
                                        <td class="py-2">
                                            <?php echo htmlspecialchars($vac['dokter_name'] ?? '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">
                        Belum ada riwayat vaksinasi
                    </p>
                <?php endif; ?>
            </div>

            <!-- Appointment History -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-calendar-check mr-2"></i> Riwayat Kunjungan
                    </h3>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        Total: <?php echo count($appointments); ?>
                    </span>
                </div>

                <?php if (!empty($appointments)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Tanggal</th>
                                    <th class="text-left py-2">Keluhan</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Dokter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $app): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2">
                                            <?php echo date('d/m/Y', strtotime($app['tanggal_appointment'])); ?>
                                        </td>
                                        <td class="py-2">
                                            <?php echo htmlspecialchars($app['keluhan_awal'] ?? '-'); ?>
                                        </td>
                                        <td class="py-2">
                                            <?php echo get_appointment_status_badge($app['status']); ?>
                                        </td>
                                        <td class="py-2">
                                            <?php echo htmlspecialchars($app['dokter_name'] ?? '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">
                        Belum ada riwayat kunjungan
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>