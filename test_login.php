<?php
// Test login functionality
require_once 'config/database.php';

$username = 'admin';
$password = 'password';

echo "Testing login for username: $username\n\n";

// Check user credentials
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'Aktif'");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "ERROR: User not found or not active\n";
    exit(1);
}

echo "User found:\n";
echo "- ID: " . $user['user_id'] . "\n";
echo "- Username: " . $user['username'] . "\n";
echo "- Name: " . $user['nama_lengkap'] . "\n";
echo "- Role: " . $user['role'] . "\n";
echo "- Status: " . $user['status'] . "\n";
echo "- Password hash: " . substr($user['password'], 0, 30) . "...\n\n";

// Test password
$passwordMatch = password_verify($password, $user['password']);
echo "Password verification: " . ($passwordMatch ? "SUCCESS ✓" : "FAILED ✗") . "\n";

if ($passwordMatch) {
    echo "\nLogin should work!\n";
} else {
    echo "\nLogin will fail - password hash is incorrect\n";
}
?>
