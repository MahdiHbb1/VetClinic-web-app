<?php
require_once 'config/database.php';

echo "<h2>Setting Up Andi Owner Account</h2>";

try {
    // Generate proper password hash
    $password = password_hash('password123', PASSWORD_BCRYPT);
    echo "<p>Generated password hash: $password</p>";
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = 'andi_owner'");
    $stmt->execute();
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'andi_owner'");
        $stmt->execute([$password]);
        echo "<p style='color: green;'>✓ Password updated for existing user</p>";
        $userId = $existing['user_id'];
    } else {
        // Create new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at) 
            VALUES ('andi_owner', ?, 'Andi Wijaya', 'andi.wijaya@email.com', 'Owner', 'Aktif', NOW())
        ");
        $stmt->execute([$password]);
        $userId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✓ New user created</p>";
    }
    
    // Link to owner record if not already linked
    $stmt = $pdo->prepare("UPDATE owner SET user_id = ? WHERE owner_id = 1 AND user_id IS NULL");
    $stmt->execute([$userId]);
    echo "<p style='color: green;'>✓ User linked to owner record</p>";
    
    // Test password verification
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'andi_owner'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    
    if (password_verify('password123', $hash)) {
        echo "<p style='color: green; font-weight: bold;'>✓ Password verification: SUCCESS</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Password verification: FAILED</p>";
    }
    
    // Display account info
    echo "<div style='background: #e8f5e9; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3 style='color: #2e7d32; margin-top: 0;'>🔑 Owner Login Credentials</h3>";
    echo "<p style='font-size: 18px;'><strong>Username:</strong> <code style='background: #fff; padding: 5px 10px; border-radius: 4px;'>andi_owner</code></p>";
    echo "<p style='font-size: 18px;'><strong>Password:</strong> <code style='background: #fff; padding: 5px 10px; border-radius: 4px;'>password123</code></p>";
    echo "</div>";
    
    // Show pets
    $stmt = $pdo->query("
        SELECT p.nama_hewan, p.jenis, p.ras 
        FROM pet p 
        INNER JOIN owner o ON p.owner_id = o.owner_id 
        WHERE o.owner_id = 1
    ");
    $pets = $stmt->fetchAll();
    
    echo "<h3>Pets for this owner:</h3>";
    echo "<ul>";
    foreach ($pets as $pet) {
        echo "<li><strong>{$pet['nama_hewan']}</strong> - {$pet['jenis']} ({$pet['ras']})</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>
