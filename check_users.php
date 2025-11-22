<?php
require_once __DIR__ . '/config/database.php';

echo "<h1>Database Users Check</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } table { border-collapse: collapse; width: 100%; margin: 20px 0; } th, td { border: 1px solid #ddd; padding: 12px; text-align: left; } th { background: #4CAF50; color: white; } tr:nth-child(even) { background: #f2f2f2; }</style>";

try {
    // Check all users
    echo "<h2>All Users in Database:</h2>";
    $stmt = $pdo->query("SELECT user_id, username, nama_lengkap, email, role, status, created_at FROM users ORDER BY user_id");
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['user_id']}</td>";
            echo "<td><strong>{$user['username']}</strong></td>";
            echo "<td>{$user['nama_lengkap']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><span style='padding: 4px 8px; background: " . ($user['role'] == 'Owner' ? '#9333ea' : '#4f46e5') . "; color: white; border-radius: 4px;'>{$user['role']}</span></td>";
            echo "<td><span style='padding: 4px 8px; background: " . ($user['status'] == 'Aktif' ? '#10b981' : '#ef4444') . "; color: white; border-radius: 4px;'>{$user['status']}</span></td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total users:</strong> " . count($users) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ No users found in database!</p>";
    }
    
    // Check owners linked to users
    echo "<h2>Owners with User Accounts:</h2>";
    $stmt = $pdo->query("
        SELECT o.owner_id, o.nama_lengkap, o.email, o.no_telepon, u.username, u.role, u.status
        FROM owner o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.owner_id
    ");
    $owners = $stmt->fetchAll();
    
    if ($owners) {
        echo "<table>";
        echo "<tr><th>Owner ID</th><th>Nama</th><th>Email</th><th>Phone</th><th>Username</th><th>Role</th><th>Status</th></tr>";
        foreach ($owners as $owner) {
            $hasAccount = $owner['username'] ? 'Yes' : 'No';
            $bgColor = $owner['username'] ? '#d1fae5' : '#fee2e2';
            echo "<tr style='background: $bgColor;'>";
            echo "<td>{$owner['owner_id']}</td>";
            echo "<td>{$owner['nama_lengkap']}</td>";
            echo "<td>{$owner['email']}</td>";
            echo "<td>{$owner['no_telepon']}</td>";
            echo "<td>" . ($owner['username'] ?: '<em>No account</em>') . "</td>";
            echo "<td>" . ($owner['role'] ?: '-') . "</td>";
            echo "<td>" . ($owner['status'] ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test password verification
    echo "<h2>Password Test:</h2>";
    $test_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $test_password = 'password';
    $is_valid = password_verify($test_password, $test_hash);
    
    echo "<div style='padding: 15px; background: " . ($is_valid ? '#d1fae5' : '#fee2e2') . "; border-left: 4px solid " . ($is_valid ? '#10b981' : '#ef4444') . "; margin: 20px 0;'>";
    echo "<p><strong>Testing password:</strong> '$test_password'</p>";
    echo "<p><strong>Against hash:</strong> $test_hash</p>";
    echo "<p style='font-size: 20px; font-weight: bold;'>" . ($is_valid ? "✅ VALID" : "❌ INVALID") . "</p>";
    echo "</div>";
    
    // Check specific demo accounts
    echo "<h2>Demo Account Check:</h2>";
    $demo_accounts = ['admin', 'budi_owner', 'owner1', 'test_owner'];
    
    echo "<table>";
    echo "<tr><th>Username</th><th>Exists</th><th>Password Hash</th><th>Role</th><th>Status</th></tr>";
    
    foreach ($demo_accounts as $username) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<tr style='background: #d1fae5;'>";
            echo "<td><strong>$username</strong></td>";
            echo "<td>✅ YES</td>";
            echo "<td style='font-size: 10px; max-width: 200px; word-break: break-all;'>{$user['password']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "</tr>";
        } else {
            echo "<tr style='background: #fee2e2;'>";
            echo "<td><strong>$username</strong></td>";
            echo "<td>❌ NO</td>";
            echo "<td colspan='3'><em>User not found</em></td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>
