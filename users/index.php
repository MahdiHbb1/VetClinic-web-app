<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if ($_SESSION['role'] !== 'Admin') {
    header('Location: /dashboard/');
    exit();
}

$page_title = 'Manajemen User';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (username LIKE ? OR nama_lengkap LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($role) {
    $query .= " AND role = ?";
    $params[] = $role;
}

if ($status) {
    $query .= " AND status = ?";
    $params[] = $status;
}

// Get total records
$count_query = $query;
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->rowCount();
$total_pages = ceil($total_records / $per_page);

// Get records
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 md:mb-0">
            <i class="fas fa-user-cog text-blue-500 mr-2"></i>
            Manajemen User
        </h2>
        <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Tambah User
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Username, nama, email...">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Role</option>
                <option value="Admin" <?php echo $role == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="Dokter" <?php echo $role == 'Dokter' ? 'selected' : ''; ?>>Dokter</option>
                <option value="Staff" <?php echo $role == 'Staff' ? 'selected' : ''; ?>>Staff</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="Aktif" <?php echo $status == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                <option value="Nonaktif" <?php echo $status == 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data user
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo $user['role'] == 'Admin' ? 'bg-red-100 text-red-800' : 
                                        ($user['role'] == 'Dokter' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); 
                                ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo $user['status'] == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; 
                                ?>">
                                    <?php echo htmlspecialchars($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="edit.php?id=<?php echo $user['user_id']; ?>" 
                                   class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <a href="delete.php?id=<?php echo $user['user_id']; ?>" 
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Yakin ingin menghapus user ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
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
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>"
                       class="px-3 py-2 border <?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
