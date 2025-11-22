<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Profil Saya';

$success = '';
$error = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($nama_lengkap) || empty($email)) {
        $error = 'Nama lengkap dan email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Check if changing password
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error = 'Password saat ini wajib diisi untuk mengganti password';
            } elseif (!password_verify($current_password, $user['password'])) {
                $error = 'Password saat ini salah';
            } elseif (strlen($new_password) < 6) {
                $error = 'Password baru minimal 6 karakter';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Konfirmasi password tidak cocok';
            } else {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, password = ? WHERE user_id = ?");
                $stmt->execute([$nama_lengkap, $email, $hashed_password, $_SESSION['user_id']]);
                $success = 'Profil dan password berhasil diperbarui';
                $_SESSION['nama_lengkap'] = $nama_lengkap;
            }
        } else {
            // Update without password
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$nama_lengkap, $email, $_SESSION['user_id']]);
            $success = 'Profil berhasil diperbarui';
            $_SESSION['nama_lengkap'] = $nama_lengkap;
        }

        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}

include '../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">
            <i class="fas fa-user text-blue-500 mr-2"></i>
            Profil Saya
        </h2>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Profile Info -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Informasi Profil</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div>
                <h3 class="text-lg font-medium text-gray-800 mb-4">Ganti Password</h3>
                <p class="text-sm text-gray-600 mb-4">Kosongkan jika tidak ingin mengganti password</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                        <input type="password" name="current_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="new_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-6 border-t">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
