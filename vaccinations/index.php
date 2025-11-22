<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Vaksinasi';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$pet_id = isset($_GET['pet_id']) ? intval($_GET['pet_id']) : 0;

// Build query
$query = "
    SELECT 
        v.*,
        p.nama_hewan,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        v.jenis_vaksin as nama_vaksin,
        v.tanggal_vaksin as tanggal_vaksinasi,
        v.tanggal_vaksin_berikutnya as tanggal_berikutnya
    FROM vaksinasi v
    JOIN pet p ON v.pet_id = p.pet_id
    JOIN owner o ON p.owner_id = o.owner_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (p.nama_hewan LIKE ? OR o.nama_lengkap LIKE ? OR v.jenis_vaksin LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($pet_id) {
    $query .= " AND v.pet_id = ?";
    $params[] = $pet_id;
}

// Get total records
$count_query = str_replace("SELECT v.*, p.nama_hewan", "SELECT COUNT(*)", $query);
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$query .= " ORDER BY v.tanggal_vaksin DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vaccinations = $stmt->fetchAll();

// Get pets for filter
$pets_stmt = $pdo->query("SELECT pet_id, nama_hewan FROM pet ORDER BY nama_hewan");
$pets = $pets_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 md:mb-0">
            <i class="fas fa-syringe text-blue-500 mr-2"></i>
            Data Vaksinasi
        </h2>
        <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Tambah Vaksinasi
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Nama hewan, pemilik, vaksin...">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Hewan</label>
            <select name="pet_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Hewan</option>
                <?php foreach ($pets as $pet): ?>
                    <option value="<?php echo $pet['pet_id']; ?>" <?php echo $pet_id == $pet['pet_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pet['nama_hewan']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg mr-2">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            <a href="index.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg">
                Reset
            </a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hewan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemilik</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vaksin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Berikutnya</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($vaccinations)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data vaksinasi
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vaccinations as $vacc): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo date('d/m/Y', strtotime($vacc['tanggal_vaksinasi'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($vacc['nama_hewan']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($vacc['owner_name']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($vacc['nama_vaksin']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo $vacc['tanggal_berikutnya'] ? date('d/m/Y', strtotime($vacc['tanggal_berikutnya'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="detail.php?id=<?php echo $vacc['vaksinasi_id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $vacc['vaksinasi_id']; ?>" 
                                   class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $vacc['vaksinasi_id']; ?>" 
                                   class="text-red-600 hover:text-red-900"
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="inline-flex rounded-md shadow">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&pet_id=<?php echo $pet_id; ?>"
                       class="px-3 py-2 border <?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
