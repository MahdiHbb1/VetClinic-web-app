<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if ($_SESSION['role'] !== 'Admin') {
    header('Location: /dashboard/');
    exit();
}

$page_title = 'Laporan';

// Get date range
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Get appointment statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
    FROM appointment
    WHERE tanggal_appointment BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$appointment_stats = $stmt->fetch();

// Get medical records count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM medical_record
    WHERE tanggal_kunjungan BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$medical_record_count = $stmt->fetchColumn();

// Get vaccination count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM vaksinasi
    WHERE tanggal_vaksin BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$vaccination_count = $stmt->fetchColumn();

// Get top services
$stmt = $pdo->prepare("
    SELECT 
        jenis_layanan,
        COUNT(*) as total
    FROM appointment
    WHERE tanggal_appointment BETWEEN ? AND ?
    GROUP BY jenis_layanan
    ORDER BY total DESC
    LIMIT 5
");
$stmt->execute([$date_from, $date_to]);
$top_services = $stmt->fetchAll();

// Get active doctors
$stmt = $pdo->prepare("
    SELECT 
        v.nama_dokter,
        COUNT(a.appointment_id) as total_appointments
    FROM veterinarian v
    LEFT JOIN appointment a ON v.dokter_id = a.dokter_id AND a.tanggal_appointment BETWEEN ? AND ?
    WHERE v.status = 'Aktif'
    GROUP BY v.dokter_id
    ORDER BY total_appointments DESC
");
$stmt->execute([$date_from, $date_to]);
$doctor_stats = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
        Laporan Klinik
    </h2>

    <!-- Date Filter -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
            <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
            <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-filter mr-2"></i>Tampilkan
            </button>
        </div>
    </form>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-50 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Total Janji Temu</p>
                    <p class="text-3xl font-bold text-blue-700"><?php echo $appointment_stats['total']; ?></p>
                </div>
                <i class="fas fa-calendar-alt text-4xl text-blue-500"></i>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 text-sm font-medium">Selesai</p>
                    <p class="text-3xl font-bold text-green-700"><?php echo $appointment_stats['completed']; ?></p>
                </div>
                <i class="fas fa-check-circle text-4xl text-green-500"></i>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-600 text-sm font-medium">Rekam Medis</p>
                    <p class="text-3xl font-bold text-purple-700"><?php echo $medical_record_count; ?></p>
                </div>
                <i class="fas fa-notes-medical text-4xl text-purple-500"></i>
            </div>
        </div>

        <div class="bg-yellow-50 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-600 text-sm font-medium">Vaksinasi</p>
                    <p class="text-3xl font-bold text-yellow-700"><?php echo $vaccination_count; ?></p>
                </div>
                <i class="fas fa-syringe text-4xl text-yellow-500"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top Services -->
        <div class="bg-white border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Layanan Terpopuler</h3>
            <div class="space-y-3">
                <?php foreach ($top_services as $service): ?>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($service['jenis_layanan']); ?></span>
                            <span class="text-sm text-gray-600"><?php echo $service['total']; ?> kali</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo ($service['total'] / $appointment_stats['total']) * 100; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Doctor Statistics -->
        <div class="bg-white border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Dokter</h3>
            <div class="space-y-3">
                <?php foreach ($doctor_stats as $doctor): ?>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($doctor['nama_dokter']); ?></span>
                        <span class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full"><?php echo $doctor['total_appointments']; ?> pasien</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
