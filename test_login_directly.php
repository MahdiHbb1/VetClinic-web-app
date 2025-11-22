<?php
/**
 * Direct Login Test - Test authentication without forms
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Direct Login Test</title>
    <style>
        body { font-family: Arial; max-width: 1200px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #4f46e5; border-bottom: 3px solid #4f46e5; padding-bottom: 10px; }
        h2 { color: #9333ea; margin-top: 30px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 10px 0; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; border: 1px solid #d1d5db; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        tr:nth-child(even) { background: #f9fafb; }
        code { background: #e5e7eb; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 12px; }
        .test-box { background: #f9fafb; border: 2px dashed #d1d5db; padding: 20px; margin: 20px 0; border-radius: 8px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Direct Login Test & Diagnosis</h1>";

// Test accounts
$test_accounts = [
    ['username' => 'admin', 'password' => 'password', 'expected_role' => 'Admin'],
    ['username' => 'budi_owner', 'password' => 'password', 'expected_role' => 'Owner']
];

foreach ($test_accounts as $test) {
    echo "<div class='test-box'>";
    echo "<h2>Testing: {$test['username']}</h2>";
    
    try {
        // Step 1: Check if user exists
        echo "<h3>Step 1: Check User Existence</h3>";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$test['username']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "<div class='error'>❌ User '{$test['username']}' NOT FOUND in database!</div>";
            echo "<div class='info'>💡 <strong>Solution:</strong> Run <code>http://localhost/fix_demo_accounts.php</code> to create this user.</div>";
            continue;
        }
        
        echo "<div class='success'>✅ User found in database</div>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>User ID</td><td>{$user['user_id']}</td></tr>";
        echo "<tr><td>Username</td><td><strong>{$user['username']}</strong></td></tr>";
        echo "<tr><td>Name</td><td>{$user['nama_lengkap']}</td></tr>";
        echo "<tr><td>Email</td><td>{$user['email']}</td></tr>";
        echo "<tr><td>Role</td><td><span style='background: #4f46e5; color: white; padding: 4px 8px; border-radius: 4px;'>{$user['role']}</span></td></tr>";
        echo "<tr><td>Status</td><td><span style='background: " . ($user['status'] == 'Aktif' ? '#10b981' : '#ef4444') . "; color: white; padding: 4px 8px; border-radius: 4px;'>{$user['status']}</span></td></tr>";
        echo "<tr><td>Password Hash</td><td><code style='font-size: 10px; word-break: break-all;'>{$user['password']}</code></td></tr>";
        echo "</table>";
        
        // Step 2: Check status
        echo "<h3>Step 2: Check Account Status</h3>";
        if ($user['status'] !== 'Aktif') {
            echo "<div class='error'>❌ Account status is '{$user['status']}' (must be 'Aktif')</div>";
            echo "<div class='info'>💡 <strong>Solution:</strong> Run this SQL: <code>UPDATE users SET status = 'Aktif' WHERE username = '{$test['username']}';</code></div>";
            continue;
        }
        echo "<div class='success'>✅ Account status is 'Aktif'</div>";
        
        // Step 3: Test password verification
        echo "<h3>Step 3: Test Password Verification</h3>";
        echo "<p>Testing password: <code>{$test['password']}</code></p>";
        
        $password_valid = password_verify($test['password'], $user['password']);
        
        if (!$password_valid) {
            echo "<div class='error'>❌ Password verification FAILED!</div>";
            echo "<div class='info'>💡 <strong>Problem:</strong> The password hash in database doesn't match the password '{$test['password']}'</div>";
            
            // Generate correct hash
            $correct_hash = password_hash($test['password'], PASSWORD_BCRYPT);
            echo "<div class='info'>";
            echo "<strong>💡 Solution:</strong> Update the password hash:<br>";
            echo "<code>UPDATE users SET password = '{$correct_hash}' WHERE username = '{$test['username']}';</code>";
            echo "</div>";
            
            continue;
        }
        
        echo "<div class='success'>✅ Password verification SUCCESSFUL!</div>";
        
        // Step 4: Check role-specific requirements
        echo "<h3>Step 4: Check Role-Specific Requirements</h3>";
        
        if ($user['role'] === 'Owner') {
            // Check if owner record exists
            $stmt = $pdo->prepare("SELECT o.* FROM owner o WHERE o.user_id = ?");
            $stmt->execute([$user['user_id']]);
            $owner = $stmt->fetch();
            
            if (!$owner) {
                echo "<div class='error'>❌ Owner record NOT FOUND for user_id {$user['user_id']}</div>";
                echo "<div class='info'>💡 <strong>Problem:</strong> Owner portal requires a linked owner record</div>";
                echo "<div class='info'>💡 <strong>Solution:</strong> Run <code>http://localhost/fix_demo_accounts.php</code> to create/link owner record.</div>";
                continue;
            }
            
            echo "<div class='success'>✅ Owner record found (owner_id: {$owner['owner_id']})</div>";
            echo "<table>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>Owner ID</td><td>{$owner['owner_id']}</td></tr>";
            echo "<tr><td>Name</td><td>{$owner['nama_lengkap']}</td></tr>";
            echo "<tr><td>Email</td><td>{$owner['email']}</td></tr>";
            echo "<tr><td>Phone</td><td>{$owner['no_telepon']}</td></tr>";
            echo "<tr><td>Address</td><td>" . ($owner['alamat'] ?: 'Not set') . "</td></tr>";
            echo "</table>";
            
            // Check pets
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pet WHERE owner_id = ?");
            $stmt->execute([$owner['owner_id']]);
            $pet_count = $stmt->fetchColumn();
            
            echo "<div class='info'>ℹ️ This owner has <strong>$pet_count</strong> pet(s) registered</div>";
        }
        
        // Step 5: Simulate actual login
        echo "<h3>Step 5: Simulate Login Process</h3>";
        
        if ($user['role'] === 'Admin') {
            // Test admin login query
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'Aktif'");
            $stmt->execute([$test['username']]);
            $login_test = $stmt->fetch();
            
            if ($login_test && password_verify($test['password'], $login_test['password'])) {
                echo "<div class='success'>✅ Admin login would SUCCEED</div>";
                echo "<div class='info'>🎯 <strong>Login URL:</strong> <a href='/auth/login.php' target='_blank'>http://localhost/auth/login.php</a></div>";
                echo "<div class='info'>📋 <strong>Credentials:</strong> Username: <code>admin</code> | Password: <code>password</code></div>";
            } else {
                echo "<div class='error'>❌ Admin login would FAIL</div>";
            }
        } else if ($user['role'] === 'Owner') {
            // Test owner login query (with JOIN)
            $stmt = $pdo->prepare("
                SELECT u.*, o.owner_id, o.nama_lengkap as owner_name
                FROM users u
                JOIN owner o ON u.user_id = o.user_id
                WHERE u.username = ? AND u.role = 'Owner' AND u.status = 'Aktif'
            ");
            $stmt->execute([$test['username']]);
            $login_test = $stmt->fetch();
            
            if ($login_test && password_verify($test['password'], $login_test['password'])) {
                echo "<div class='success'>✅ Owner login would SUCCEED</div>";
                echo "<div class='info'>🎯 <strong>Login URL:</strong> <a href='/owners/portal/login.php' target='_blank'>http://localhost/owners/portal/login.php</a></div>";
                echo "<div class='info'>📋 <strong>Credentials:</strong> Username: <code>budi_owner</code> | Password: <code>password</code></div>";
            } else {
                echo "<div class='error'>❌ Owner login would FAIL</div>";
                
                if (!$login_test) {
                    echo "<div class='error'>❌ JOIN query returned no results (owner record not linked)</div>";
                }
            }
        }
        
        echo "<div class='success'>";
        echo "<h3>✅ ALL TESTS PASSED for {$test['username']}!</h3>";
        echo "<p>This account should work for login.</p>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo "</div>"; // End test-box
}

// Overall summary
echo "<div class='test-box' style='background: #f0f9ff; border-color: #3b82f6;'>";
echo "<h2>📋 Summary & Next Steps</h2>";

echo "<h3>Current Database State:</h3>";
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Aktif'");
$active_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM owner WHERE user_id IS NOT NULL");
$linked_owners = $stmt->fetchColumn();

echo "<ul>";
echo "<li>Total users: <strong>$total_users</strong></li>";
echo "<li>Active users: <strong>$active_users</strong></li>";
echo "<li>Owners with login: <strong>$linked_owners</strong></li>";
echo "</ul>";

echo "<h3>🔧 If Login Still Fails:</h3>";
echo "<ol>";
echo "<li>Run the fix script: <a href='/fix_demo_accounts.php' target='_blank'><strong>http://localhost/fix_demo_accounts.php</strong></a></li>";
echo "<li>Clear your browser cache and cookies</li>";
echo "<li>Try incognito/private browsing mode</li>";
echo "<li>Check browser console for JavaScript errors (F12)</li>";
echo "<li>Verify the login form is submitting to the correct URL</li>";
echo "</ol>";

echo "</div>";

echo "</div></body></html>";
?>
