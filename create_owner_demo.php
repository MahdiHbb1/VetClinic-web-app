<?php
require_once 'config/database.php';

echo "<h2>Creating Demo Owner Account</h2>";

try {
    // Check if owner account exists
    $stmt = $pdo->prepare("
        SELECT u.*, o.owner_id, o.nama_lengkap as owner_name 
        FROM users u 
        LEFT JOIN owner o ON u.user_id = o.user_id 
        WHERE u.username = 'demo_owner' AND u.role = 'Owner'
    ");
    $stmt->execute();
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "<p style='color: orange;'>Account 'demo_owner' already exists</p>";
        echo "<pre>";
        print_r($existing);
        echo "</pre>";
        
        // Update password to ensure it works
        $newPassword = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'demo_owner'");
        $stmt->execute([$newPassword]);
        echo "<p style='color: green;'>Password updated to: password123</p>";
        
        // Ensure user is active
        $stmt = $pdo->prepare("UPDATE users SET status = 'Aktif' WHERE username = 'demo_owner'");
        $stmt->execute();
        echo "<p style='color: green;'>Status set to Aktif</p>";
        
    } else {
        echo "<p style='color: blue;'>Creating new owner account...</p>";
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Create user
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at) 
            VALUES ('demo_owner', ?, 'Demo Owner', 'demo@owner.com', 'Owner', 'Aktif', NOW())
        ");
        $stmt->execute([$password]);
        $userId = $pdo->lastInsertId();
        echo "<p>✓ User created with ID: $userId</p>";
        
        // Create owner record
        $stmt = $pdo->prepare("
            INSERT INTO owner (user_id, nama_lengkap, alamat, no_telepon, email, tanggal_registrasi) 
            VALUES (?, 'Demo Owner', 'Jl. Demo No. 123', '081234567890', 'demo@owner.com', NOW())
        ");
        $stmt->execute([$userId]);
        $ownerId = $pdo->lastInsertId();
        echo "<p>✓ Owner record created with ID: $ownerId</p>";
        
        $pdo->commit();
        echo "<p style='color: green; font-weight: bold;'>SUCCESS! Owner account created</p>";
    }
    
    // Display login credentials
    echo "<div style='background: #e8f5e9; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3 style='color: #2e7d32; margin-top: 0;'>🔑 Owner Login Credentials</h3>";
    echo "<p style='font-size: 18px;'><strong>Username:</strong> <code style='background: #fff; padding: 5px 10px; border-radius: 4px;'>demo_owner</code></p>";
    echo "<p style='font-size: 18px;'><strong>Password:</strong> <code style='background: #fff; padding: 5px 10px; border-radius: 4px;'>password123</code></p>";
    echo "<p style='font-size: 14px; color: #666;'>Use these credentials on the landing page Owner Portal login</p>";
    echo "</div>";
    
    // Verify the account works
    echo "<h3>Verification:</h3>";
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, u.role, u.status, o.owner_id, o.nama_lengkap, o.email 
        FROM users u 
        INNER JOIN owner o ON u.user_id = o.user_id 
        WHERE u.username = 'demo_owner' AND u.role = 'Owner' AND u.status = 'Aktif'
    ");
    $stmt->execute();
    $account = $stmt->fetch();
    
    if ($account) {
        echo "<p style='color: green;'>✓ Account verified and ready to use!</p>";
        echo "<pre>";
        print_r($account);
        echo "</pre>";
        
        // Test password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'demo_owner'");
        $stmt->execute();
        $hash = $stmt->fetchColumn();
        
        if (password_verify('password123', $hash)) {
            echo "<p style='color: green;'>✓ Password verification: PASSED</p>";
        } else {
            echo "<p style='color: red;'>✗ Password verification: FAILED</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Account verification failed</p>";
    }
    
    // Show all owners
    echo "<h3>All Owner Accounts:</h3>";
    $stmt = $pdo->query("
        SELECT u.username, u.status, o.nama_lengkap, o.email 
        FROM users u 
        LEFT JOIN owner o ON u.user_id = o.user_id 
        WHERE u.role = 'Owner'
    ");
    $owners = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Username</th><th>Status</th><th>Nama Lengkap</th><th>Email</th></tr>";
    foreach ($owners as $owner) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($owner['username'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($owner['status'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($owner['nama_lengkap'] ?? 'NO OWNER RECORD') . "</td>";
        echo "<td>" . htmlspecialchars($owner['email'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
