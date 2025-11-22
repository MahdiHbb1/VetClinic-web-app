<?php
/**
 * Fix Demo Accounts - Create/Update demo users with correct credentials
 * Run this once to set up demo accounts shown on landing page
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Demo Accounts</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #4f46e5; border-bottom: 3px solid #4f46e5; padding-bottom: 10px; }
        h2 { color: #9333ea; margin-top: 30px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .credential-box { background: #f9fafb; border: 2px solid #e5e7eb; padding: 20px; margin: 15px 0; border-radius: 8px; }
        .credential-box strong { color: #4f46e5; font-size: 18px; }
        code { background: #e5e7eb; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 6px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; background: #f9fafb; border-radius: 6px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔧 Demo Account Fix Tool</h1>";
echo "<p>This tool will create/update demo accounts for the VetClinic landing page.</p>";

$fixes_made = [];
$errors = [];

try {
    $pdo->beginTransaction();
    
    // Step 1: Check and create Admin account
    echo "<div class='step'>";
    echo "<h2>Step 1: Admin Account</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    // Generate password hash for 'password'
    $password_hash = password_hash('password', PASSWORD_BCRYPT);
    
    if ($admin) {
        echo "<div class='warning'>⚠️ Admin account already exists</div>";
        
        // Update password to ensure it's 'password'
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$password_hash]);
        
        echo "<div class='success'>✅ Updated admin password to 'password'</div>";
        $fixes_made[] = "Updated admin password";
    } else {
        // Create admin
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at)
            VALUES ('admin', ?, 'Administrator', 'admin@vetclinic.com', 'Admin', 'Aktif', NOW())
        ");
        $stmt->execute([$password_hash]);
        
        echo "<div class='success'>✅ Created admin account</div>";
        $fixes_made[] = "Created admin account";
    }
    echo "</div>";
    
    // Step 2: Check and create Owner account (budi_owner)
    echo "<div class='step'>";
    echo "<h2>Step 2: Owner Account (budi_owner)</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'budi_owner'");
    $stmt->execute();
    $owner_user = $stmt->fetch();
    
    if ($owner_user) {
        echo "<div class='warning'>⚠️ User 'budi_owner' already exists</div>";
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, status = 'Aktif' WHERE username = 'budi_owner'");
        $stmt->execute([$password_hash]);
        
        echo "<div class='success'>✅ Updated budi_owner password to 'password'</div>";
        $fixes_made[] = "Updated budi_owner password";
        
        $user_id = $owner_user['user_id'];
    } else {
        // Create owner user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at)
            VALUES ('budi_owner', ?, 'Budi Santoso', 'budi@example.com', 'Owner', 'Aktif', NOW())
        ");
        $stmt->execute([$password_hash]);
        $user_id = $pdo->lastInsertId();
        
        echo "<div class='success'>✅ Created budi_owner account (user_id: $user_id)</div>";
        $fixes_made[] = "Created budi_owner account";
    }
    
    // Step 3: Check/create owner record
    echo "<h3>Owner Record</h3>";
    $stmt = $pdo->prepare("SELECT * FROM owner WHERE email = 'budi@example.com'");
    $stmt->execute();
    $owner_record = $stmt->fetch();
    
    if ($owner_record) {
        echo "<div class='info'>ℹ️ Owner record exists (owner_id: {$owner_record['owner_id']})</div>";
        
        // Link user to owner
        $stmt = $pdo->prepare("UPDATE owner SET user_id = ? WHERE owner_id = ?");
        $stmt->execute([$user_id, $owner_record['owner_id']]);
        
        echo "<div class='success'>✅ Linked user to owner record</div>";
        $fixes_made[] = "Linked budi_owner to owner record";
        
        $owner_id = $owner_record['owner_id'];
    } else {
        // Create owner record
        $stmt = $pdo->prepare("
            INSERT INTO owner (user_id, nama_lengkap, alamat, no_telepon, email, tanggal_registrasi)
            VALUES (?, 'Budi Santoso', 'Jl. Merdeka No. 123, Jakarta', '081234567890', 'budi@example.com', NOW())
        ");
        $stmt->execute([$user_id]);
        $owner_id = $pdo->lastInsertId();
        
        echo "<div class='success'>✅ Created owner record (owner_id: $owner_id)</div>";
        $fixes_made[] = "Created owner record for budi_owner";
    }
    
    // Step 4: Verify pets for owner
    echo "<h3>Pet Records</h3>";
    $stmt = $pdo->prepare("SELECT COUNT(*) as pet_count FROM pet WHERE owner_id = ?");
    $stmt->execute([$owner_id]);
    $pet_count = $stmt->fetchColumn();
    
    echo "<div class='info'>ℹ️ Owner has $pet_count pet(s)</div>";
    
    if ($pet_count == 0) {
        // Create sample pets
        $stmt = $pdo->prepare("
            INSERT INTO pet (owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, status, tanggal_registrasi)
            VALUES 
            (?, 'Max', 'Anjing', 'Golden Retriever', 'Jantan', '2020-03-15', 28.5, 'Golden', 'Aktif', NOW()),
            (?, 'Bella', 'Kucing', 'Persian', 'Betina', '2021-06-20', 4.2, 'White', 'Aktif', NOW())
        ");
        $stmt->execute([$owner_id, $owner_id]);
        echo "<div class='success'>✅ Created 2 sample pets (Max & Bella)</div>";
        $fixes_made[] = "Created sample pets";
    }
    
    echo "</div>";
    
    // Commit all changes
    $pdo->commit();
    
    // Final summary
    echo "<div class='step'>";
    echo "<h2>✅ All Fixes Complete!</h2>";
    
    if (!empty($fixes_made)) {
        echo "<div class='success'>";
        echo "<strong>Changes made:</strong><ul>";
        foreach ($fixes_made as $fix) {
            echo "<li>$fix</li>";
        }
        echo "</ul></div>";
    }
    
    // Display credentials
    echo "<div class='credential-box'>";
    echo "<h3>🔑 Demo Login Credentials</h3>";
    
    echo "<h4>Admin/Staff Portal:</h4>";
    echo "<pre>URL: http://localhost/auth/login.php\nUsername: admin\nPassword: password</pre>";
    
    echo "<h4>Owner Portal:</h4>";
    echo "<pre>URL: http://localhost/owners/portal/login.php\nUsername: budi_owner\nPassword: password</pre>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<strong>✅ You can now login with these credentials from the landing page!</strong>";
    echo "</div>";
    
    echo "</div>";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<div class='error'>";
    echo "<strong>❌ Database Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    $errors[] = $e->getMessage();
}

// Verification section
echo "<div class='step'>";
echo "<h2>🔍 Verification</h2>";

try {
    $stmt = $pdo->query("
        SELECT u.username, u.nama_lengkap, u.email, u.role, u.status, o.owner_id
        FROM users u
        LEFT JOIN owner o ON u.user_id = o.user_id
        WHERE u.username IN ('admin', 'budi_owner')
        ORDER BY u.role DESC
    ");
    $accounts = $stmt->fetchAll();
    
    if ($accounts) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>";
        echo "<tr style='background: #f3f4f6;'>";
        echo "<th style='padding: 10px; border: 1px solid #d1d5db; text-align: left;'>Username</th>";
        echo "<th style='padding: 10px; border: 1px solid #d1d5db; text-align: left;'>Name</th>";
        echo "<th style='padding: 10px; border: 1px solid #d1d5db; text-align: left;'>Email</th>";
        echo "<th style='padding: 10px; border: 1px solid #d1d5db; text-align: left;'>Role</th>";
        echo "<th style='padding: 10px; border: 1px solid #d1d5db; text-align: left;'>Status</th>";
        echo "<th style='padding: 10px; border: 1px solid #d1d5db; text-align: left;'>Owner ID</th>";
        echo "</tr>";
        
        foreach ($accounts as $acc) {
            $role_color = $acc['role'] == 'Admin' ? '#4f46e5' : '#9333ea';
            $status_color = $acc['status'] == 'Aktif' ? '#10b981' : '#ef4444';
            
            echo "<tr>";
            echo "<td style='padding: 10px; border: 1px solid #d1d5db;'><strong>{$acc['username']}</strong></td>";
            echo "<td style='padding: 10px; border: 1px solid #d1d5db;'>{$acc['nama_lengkap']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #d1d5db;'>{$acc['email']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #d1d5db;'><span style='background: $role_color; color: white; padding: 4px 8px; border-radius: 4px;'>{$acc['role']}</span></td>";
            echo "<td style='padding: 10px; border: 1px solid #d1d5db;'><span style='background: $status_color; color: white; padding: 4px 8px; border-radius: 4px;'>{$acc['status']}</span></td>";
            echo "<td style='padding: 10px; border: 1px solid #d1d5db;'>" . ($acc['owner_id'] ?: 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>Error verifying accounts: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>";

// Test password verification
echo "<div class='step'>";
echo "<h2>🧪 Password Verification Test</h2>";
$test_result = password_verify('password', $password_hash);
echo "<div class='" . ($test_result ? "success" : "error") . "'>";
echo $test_result ? "✅ Password hash verification: WORKING" : "❌ Password hash verification: FAILED";
echo "</div>";
echo "</div>";

echo "</div></body></html>";
?>
