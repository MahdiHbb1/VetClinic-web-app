<?php
/**
 * FORCE FIX - Aggressively fix demo accounts
 * This will DELETE and RECREATE accounts with known-good passwords
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html><html><head><title>Force Fix Demo Accounts</title>";
echo "<style>
    body { font-family: Arial; max-width: 1000px; margin: 50px auto; padding: 20px; background: #1f2937; color: #f3f4f6; }
    .container { background: #374151; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
    h1 { color: #60a5fa; border-bottom: 3px solid #3b82f6; padding-bottom: 10px; }
    .success { background: #064e3b; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; color: #d1fae5; }
    .error { background: #7f1d1d; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; color: #fee2e2; }
    .warning { background: #78350f; border-left: 4px solid #f59e0b; padding: 15px; margin: 10px 0; color: #fef3c7; }
    .info { background: #1e3a8a; border-left: 4px solid #3b82f6; padding: 15px; margin: 10px 0; color: #dbeafe; }
    .btn { display: inline-block; padding: 15px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 10px 5px; }
    .btn:hover { background: #2563eb; }
    .credential-box { background: #1f2937; border: 2px solid #3b82f6; padding: 20px; margin: 20px 0; border-radius: 8px; }
    pre { background: #111827; padding: 15px; border-radius: 6px; color: #10b981; font-family: monospace; }
    code { background: #111827; padding: 2px 6px; border-radius: 3px; color: #fbbf24; }
</style></head><body><div class='container'>";

if (!isset($_GET['confirm'])) {
    echo "<h1>⚠️ FORCE FIX DEMO ACCOUNTS</h1>";
    echo "<div class='warning'>";
    echo "<strong>WARNING: This will DELETE and RECREATE demo accounts!</strong><br>";
    echo "This action will:";
    echo "<ul>";
    echo "<li>Delete existing 'admin' and 'budi_owner' users</li>";
    echo "<li>Recreate them with fresh password hashes</li>";
    echo "<li>Create/update owner records</li>";
    echo "<li>Add sample pets if needed</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<strong>Use this if:</strong>";
    echo "<ul>";
    echo "<li>❌ Regular fix didn't work</li>";
    echo "<li>❌ Login still says 'Invalid username or password'</li>";
    echo "<li>❌ Password hashes are corrupted</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='?confirm=yes' class='btn' style='background: #ef4444;'>🔥 YES, FORCE FIX NOW</a>";
    echo "<a href='/' class='btn' style='background: #6b7280;'>Cancel</a>";
    echo "</div>";
    
    echo "</div></body></html>";
    exit;
}

// User confirmed - proceed with force fix
echo "<h1>🔥 FORCE FIXING DEMO ACCOUNTS...</h1>";

$results = [];
$errors = [];

try {
    $pdo->beginTransaction();
    
    // Generate fresh password hash
    $password_hash = password_hash('password', PASSWORD_BCRYPT);
    echo "<div class='info'>🔑 Generated new password hash for 'password'</div>";
    
    // FORCE FIX ADMIN
    echo "<h2>1️⃣ Admin Account</h2>";
    
    // Delete existing admin
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = 'admin'");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    
    if ($deleted > 0) {
        echo "<div class='warning'>⚠️ Deleted existing admin user</div>";
    }
    
    // Create fresh admin
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at, last_login)
        VALUES ('admin', ?, 'Administrator', 'admin@vetclinic.com', 'Admin', 'Aktif', NOW(), NULL)
    ");
    $stmt->execute([$password_hash]);
    
    echo "<div class='success'>✅ Created NEW admin account</div>";
    $results[] = "Admin account created";
    
    // Verify admin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    $test_admin = password_verify('password', $admin['password']);
    if ($test_admin) {
        echo "<div class='success'>✅ Admin password verified successfully</div>";
    } else {
        echo "<div class='error'>❌ Admin password verification FAILED</div>";
        $errors[] = "Admin password verification failed";
    }
    
    // FORCE FIX OWNER
    echo "<h2>2️⃣ Owner Account (budi_owner)</h2>";
    
    // Delete existing owner user
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = 'budi_owner'");
    $stmt->execute();
    $old_owner_user = $stmt->fetch();
    
    if ($old_owner_user) {
        // Unlink from owner table first
        $stmt = $pdo->prepare("UPDATE owner SET user_id = NULL WHERE user_id = ?");
        $stmt->execute([$old_owner_user['user_id']]);
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = 'budi_owner'");
        $stmt->execute();
        
        echo "<div class='warning'>⚠️ Deleted existing budi_owner user</div>";
    }
    
    // Create fresh owner user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at, last_login)
        VALUES ('budi_owner', ?, 'Budi Santoso', 'budi@example.com', 'Owner', 'Aktif', NOW(), NULL)
    ");
    $stmt->execute([$password_hash]);
    $new_user_id = $pdo->lastInsertId();
    
    echo "<div class='success'>✅ Created NEW budi_owner account (user_id: $new_user_id)</div>";
    $results[] = "Owner user account created";
    
    // Check/create owner record
    $stmt = $pdo->prepare("SELECT * FROM owner WHERE email = 'budi@example.com'");
    $stmt->execute();
    $owner_record = $stmt->fetch();
    
    if ($owner_record) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE owner SET user_id = ?, nama_lengkap = 'Budi Santoso' WHERE owner_id = ?");
        $stmt->execute([$new_user_id, $owner_record['owner_id']]);
        $owner_id = $owner_record['owner_id'];
        echo "<div class='success'>✅ Linked to existing owner record (owner_id: $owner_id)</div>";
    } else {
        // Create new
        $stmt = $pdo->prepare("
            INSERT INTO owner (user_id, nama_lengkap, alamat, no_telepon, email, tanggal_registrasi)
            VALUES (?, 'Budi Santoso', 'Jl. Merdeka No. 123, Jakarta Pusat', '081234567890', 'budi@example.com', NOW())
        ");
        $stmt->execute([$new_user_id]);
        $owner_id = $pdo->lastInsertId();
        echo "<div class='success'>✅ Created NEW owner record (owner_id: $owner_id)</div>";
    }
    
    $results[] = "Owner record created/linked";
    
    // Verify owner
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'budi_owner'");
    $stmt->execute();
    $owner_user = $stmt->fetch();
    
    $test_owner = password_verify('password', $owner_user['password']);
    if ($test_owner) {
        echo "<div class='success'>✅ Owner password verified successfully</div>";
    } else {
        echo "<div class='error'>❌ Owner password verification FAILED</div>";
        $errors[] = "Owner password verification failed";
    }
    
    // Add sample pets if none exist
    echo "<h2>3️⃣ Sample Pets</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pet WHERE owner_id = ?");
    $stmt->execute([$owner_id]);
    $pet_count = $stmt->fetchColumn();
    
    if ($pet_count == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO pet (owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, status, tanggal_registrasi)
            VALUES 
            (?, 'Max', 'Anjing', 'Golden Retriever', 'Jantan', '2020-03-15', 28.5, 'Golden', 'Aktif', NOW()),
            (?, 'Bella', 'Kucing', 'Persian', 'Betina', '2021-06-20', 4.2, 'White', 'Aktif', NOW()),
            (?, 'Charlie', 'Anjing', 'Beagle', 'Jantan', '2019-11-10', 12.3, 'Tricolor', 'Aktif', NOW())
        ");
        $stmt->execute([$owner_id, $owner_id, $owner_id]);
        echo "<div class='success'>✅ Created 3 sample pets (Max, Bella, Charlie)</div>";
        $results[] = "Sample pets created";
    } else {
        echo "<div class='info'>ℹ️ Owner already has $pet_count pet(s)</div>";
    }
    
    // Commit all changes
    $pdo->commit();
    
    echo "<div class='success'>";
    echo "<h2>✅ FORCE FIX COMPLETED!</h2>";
    echo "<ul>";
    foreach ($results as $result) {
        echo "<li>$result</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    if (!empty($errors)) {
        echo "<div class='error'>";
        echo "<h3>⚠️ Errors encountered:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Display credentials
    echo "<div class='credential-box'>";
    echo "<h2>🔑 DEMO LOGIN CREDENTIALS</h2>";
    
    echo "<h3>🔷 Admin/Staff Portal:</h3>";
    echo "<pre>URL:      http://localhost/auth/login.php\nUsername: admin\nPassword: password</pre>";
    
    echo "<h3>🔷 Owner Portal:</h3>";
    echo "<pre>URL:      http://localhost/owners/portal/login.php\nUsername: budi_owner\nPassword: password</pre>";
    
    echo "<p style='color: #10b981; font-size: 18px; font-weight: bold; margin-top: 20px;'>✅ Try logging in now!</p>";
    echo "</div>";
    
    // Verification
    echo "<h2>🔍 Verification</h2>";
    
    echo "<h3>Admin Login Test:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin' AND status = 'Aktif'");
    $stmt->execute();
    $admin_check = $stmt->fetch();
    
    if ($admin_check && password_verify('password', $admin_check['password'])) {
        echo "<div class='success'>✅ Admin login WILL WORK</div>";
    } else {
        echo "<div class='error'>❌ Admin login WILL FAIL</div>";
    }
    
    echo "<h3>Owner Login Test:</h3>";
    $stmt = $pdo->prepare("
        SELECT u.*, o.owner_id, o.nama_lengkap as owner_name
        FROM users u
        JOIN owner o ON u.user_id = o.user_id
        WHERE u.username = 'budi_owner' AND u.role = 'Owner' AND u.status = 'Aktif'
    ");
    $stmt->execute();
    $owner_check = $stmt->fetch();
    
    if ($owner_check && password_verify('password', $owner_check['password'])) {
        echo "<div class='success'>✅ Owner login WILL WORK</div>";
    } else {
        echo "<div class='error'>❌ Owner login WILL FAIL</div>";
    }
    
    echo "<div style='text-align: center; margin: 40px 0;'>";
    echo "<a href='/' class='btn'>🏠 Go to Landing Page</a>";
    echo "<a href='/test_login_directly.php' class='btn' style='background: #9333ea;'>🧪 Run Login Test</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<div class='error'>";
    echo "<h2>❌ DATABASE ERROR</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
