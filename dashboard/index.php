<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/appointment_functions.php';

// Dashboard is restricted to staff only (not Owner)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner') {
    header('Location: /owners/portal/');
    exit();
}

$page_title = 'Dashboard';
$use_chart = true;

// Get today's appointments
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT 
        a.appointment_id,
        a.tanggal_appointment,
        a.jam_appointment,
        a.status,
        a.jenis_layanan,
        o.nama_lengkap as owner_name, 
        p.nama_hewan as pet_name, 
        v.nama_dokter
    FROM appointment a 
    JOIN owner o ON a.owner_id = o.owner_id
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.tanggal_appointment = ?
    ORDER BY a.jam_appointment ASC
");
$stmt->execute([$today]);
$appointments = $stmt->fetchAll();

// Get total counts for KPI cards
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pet WHERE status = 'Aktif'");
$total_pets = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM owner");
$total_owners = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM veterinarian WHERE status = 'Aktif'");
$total_doctors = $stmt->fetch()['total'];

// Calculate monthly revenue
$stmt = $pdo->prepare("
    SELECT SUM(al.subtotal) as total 
    FROM appointment_layanan al
    JOIN appointment a ON al.appointment_id = a.appointment_id 
    WHERE MONTH(a.tanggal_appointment) = MONTH(CURRENT_DATE())
");
$stmt->execute();
$monthly_revenue = $stmt->fetch()['total'] ?? 0;

// Get alerts
$alerts = [];

// Low stock medicines
$stmt = $pdo->query("
    SELECT nama_obat, stok 
    FROM medicine 
    WHERE stok < 10 AND status_tersedia = 1
    LIMIT 5
");
$low_stock = $stmt->fetchAll();

// Upcoming vaccinations
$stmt = $pdo->prepare("
    SELECT v.*, p.nama_hewan, o.nama_lengkap as owner_name
    FROM vaksinasi v
    JOIN pet p ON v.pet_id = p.pet_id
    JOIN owner o ON p.owner_id = o.owner_id
    WHERE v.tanggal_vaksin BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    AND v.status = 'Scheduled'
    LIMIT 5
");
$stmt->execute();
$upcoming_vaccinations = $stmt->fetchAll();

// Expired medicines
$stmt = $pdo->prepare("
    SELECT nama_obat, expired_date
    FROM medicine
    WHERE expired_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
    AND status_tersedia = 1
    LIMIT 5
");
$stmt->execute();
$expiring_medicines = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Dashboard Content -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Pets -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Pasien</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_pets); ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-paw text-blue-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Owners -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Pemilik</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_owners); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-users text-green-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Doctors -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Dokter Aktif</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_doctors); ?></p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-user-md text-purple-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Pendapatan Bulan Ini</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo format_rupiah($monthly_revenue); ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-money-bill-wave text-yellow-500 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Today's Appointments -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Janji Temu Hari Ini</h2>
            </div>
            <div class="p-4">
                <?php if (empty($appointments)): ?>
                    <p class="text-gray-500 text-center py-4">Tidak ada janji temu hari ini</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Jam</th>
                                    <th class="px-4 py-2 text-left">Pemilik</th>
                                    <th class="px-4 py-2 text-left">Hewan</th>
                                    <th class="px-4 py-2 text-left">Dokter</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($appointments as $appt): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-900"><?php echo date('H:i', strtotime($appt['jam_appointment'])); ?></td>
                                    <td class="px-4 py-2 text-gray-900"><?php echo htmlspecialchars($appt['owner_name']); ?></td>
                                    <td class="px-4 py-2 text-gray-900"><?php echo htmlspecialchars($appt['pet_name']); ?></td>
                                    <td class="px-4 py-2 text-gray-900"><?php echo htmlspecialchars($appt['nama_dokter']); ?></td>
                                    <td class="px-4 py-2"><?php echo get_appointment_status_badge($appt['status']); ?></td>
                                    <td class="px-4 py-2">
                                        <a href="../appointments/detail.php?id=<?php echo $appt['appointment_id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Notifikasi</h2>
            </div>
            <div class="p-4">
                <!-- Low Stock Medicines -->
                <?php if (!empty($low_stock)): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-red-600 mb-2">
                            <i class="fas fa-exclamation-triangle"></i> Stok Obat Menipis
                        </h3>
                        <ul class="text-sm space-y-1">
                            <?php foreach ($low_stock as $med): ?>
                                <li class="flex justify-between">
                                    <span><?php echo htmlspecialchars($med['nama_obat']); ?></span>
                                    <span class="text-red-600"><?php echo $med['stok']; ?> tersisa</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Upcoming Vaccinations -->
                <?php if (!empty($upcoming_vaccinations)): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-blue-600 mb-2">
                            <i class="fas fa-syringe"></i> Jadwal Vaksinasi
                        </h3>
                        <ul class="text-sm space-y-1">
                            <?php foreach ($upcoming_vaccinations as $vac): ?>
                                <li>
                                    <?php echo htmlspecialchars($vac['nama_hewan']); ?> -
                                    <?php echo format_tanggal($vac['tanggal_vaksin']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Expiring Medicines -->
                <?php if (!empty($expiring_medicines)): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-yellow-600 mb-2">
                            <i class="fas fa-clock"></i> Obat Mendekati Kadaluarsa
                        </h3>
                        <ul class="text-sm space-y-1">
                            <?php foreach ($expiring_medicines as $med): ?>
                                <li class="flex justify-between">
                                    <span><?php echo htmlspecialchars($med['nama_obat']); ?></span>
                                    <span class="text-yellow-600"><?php echo format_tanggal($med['expired_date']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($low_stock) && empty($upcoming_vaccinations) && empty($expiring_medicines)): ?>
                    <p class="text-gray-500 text-center py-4">Tidak ada notifikasi penting</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="mt-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Grafik Pendapatan 6 Bulan Terakhir</h2>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Comprehensive checks before initializing chart
    const chartCanvas = document.getElementById('revenueChart');
    
    // Check if canvas element exists
    if (!chartCanvas) {
        console.warn('Revenue chart canvas not found in DOM');
        return;
    }
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        const parent = chartCanvas.parentElement;
        if (parent) {
            parent.innerHTML = '<div class="text-red-600 text-center p-4">Chart.js gagal dimuat. Silakan refresh halaman.</div>';
        }
        return;
    }
    
    // Destroy existing chart instance if any
    const existingChart = Chart.getChart(chartCanvas);
    if (existingChart) {
        existingChart.destroy();
    }
    
    // Fetch revenue data and initialize chart
    fetch('../api/dashboard_stats.php?type=revenue')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('Revenue data received:', data);
            
            // Verify canvas still exists after async fetch
            const canvas = document.getElementById('revenueChart');
            if (!canvas) {
                console.warn('Canvas element disappeared during data fetch');
                return;
            }
            
            if (!data.months || !data.values || data.months.length === 0) {
                const parent = canvas.parentElement;
                if (parent) {
                    parent.innerHTML = '<div class="text-gray-600 text-center p-4">Tidak ada data pendapatan untuk ditampilkan.</div>';
                }
                return;
            }
            
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Cannot get 2D context from canvas');
                return;
            }
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.months,
                    datasets: [{
                        label: 'Pendapatan',
                        data: data.values,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            const canvas = document.getElementById('revenueChart');
            if (canvas && canvas.parentElement) {
                canvas.parentElement.innerHTML = 
                    '<div class="text-red-600 text-center p-4">' +
                    '<i class="fas fa-exclamation-triangle mb-2"></i><br>' +
                    'Gagal memuat data grafik: ' + error.message + 
                    '</div>';
            }
        });
});
</script>

<?php include '../includes/footer.php'; ?>