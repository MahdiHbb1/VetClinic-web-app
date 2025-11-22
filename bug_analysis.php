<?php
/**
 * COMPREHENSIVE BUG ANALYSIS & FIX
 * Issues: 1) Detail page routing bug 2) Owner login failing 3) Kategori undefined key
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html><html><head><title>Bug Analysis & Fix</title>";
echo "<style>
    body { font-family: Arial; max-width: 1200px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
    .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    h1 { color: #ef4444; border-bottom: 3px solid #ef4444; padding-bottom: 10px; }
    h2 { color: #3b82f6; margin-top: 30px; }
    .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; }
    .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; }
    .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 10px 0; }
    .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 10px 0; }
    code { background: #e5e7eb; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 6px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th, td { padding: 10px; border: 1px solid #d1d5db; text-align: left; }
    th { background: #3b82f6; color: white; }
</style></head><body><div class='container'>";

echo "<h1>🐛 Comprehensive Bug Analysis</h1>";

// BUG 1: Check Owner Login Issue
echo "<h2>1️⃣ Owner Login Issue</h2>";

try {
    // Check if budi_owner exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'budi_owner'");
    $stmt->execute();
    $owner_user = $stmt->fetch();
    
    if (!$owner_user) {
        echo "<div class='error'>❌ User 'budi_owner' NOT FOUND in database!</div>";
        echo "<div class='warning'>⚠️ <strong>Fix:</strong> Run <code>http://localhost/force_fix_accounts.php</code></div>";
    } else {
        echo "<div class='success'>✅ User 'budi_owner' exists</div>";
        
        // Check password
        $password_test = password_verify('password', $owner_user['password']);
        if (!$password_test) {
            echo "<div class='error'>❌ Password 'password' does NOT match hash!</div>";
            echo "<div class='info'>Current hash: <code style='font-size: 10px; word-break: break-all;'>{$owner_user['password']}</code></div>";
        } else {
            echo "<div class='success'>✅ Password 'password' is CORRECT</div>";
        }
        
        // Check if owner record exists and is linked
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.status 
            FROM owner o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE u.username = 'budi_owner'
        ");
        $stmt->execute();
        $owner_record = $stmt->fetch();
        
        if (!$owner_record) {
            echo "<div class='error'>❌ Owner record NOT LINKED to user!</div>";
            echo "<div class='warning'>⚠️ Owner portal requires JOIN with owner table</div>";
        } else {
            echo "<div class='success'>✅ Owner record linked (owner_id: {$owner_record['owner_id']})</div>";
            
            // Show full diagnostic
            echo "<table>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>Username</td><td>{$owner_user['username']}</td></tr>";
            echo "<tr><td>User ID</td><td>{$owner_user['user_id']}</td></tr>";
            echo "<tr><td>Role</td><td>{$owner_user['role']}</td></tr>";
            echo "<tr><td>Status</td><td>{$owner_user['status']}</td></tr>";
            echo "<tr><td>Owner ID</td><td>{$owner_record['owner_id']}</td></tr>";
            echo "<tr><td>Owner Name</td><td>{$owner_record['nama_lengkap']}</td></tr>";
            echo "</table>";
            
            // Test the EXACT query used in login
            $stmt = $pdo->prepare("
                SELECT u.*, o.owner_id, o.nama_lengkap as owner_name
                FROM users u
                JOIN owner o ON u.user_id = o.user_id
                WHERE u.username = ? AND u.role = 'Owner' AND u.status = 'Aktif'
            ");
            $stmt->execute(['budi_owner']);
            $login_test = $stmt->fetch();
            
            if ($login_test && password_verify('password', $login_test['password'])) {
                echo "<div class='success'>✅ <strong>Login SHOULD WORK!</strong></div>";
                echo "<div class='info'>Try logging in at: <a href='/owners/portal/login.php' target='_blank'>http://localhost/owners/portal/login.php</a></div>";
            } else {
                echo "<div class='error'>❌ Login query FAILED</div>";
                if (!$login_test) {
                    echo "<div class='error'>Query returned no results</div>";
                }
            }
        }
    }
} catch (PDOException $e) {
    echo "<div class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// BUG 2: Detail Page Routing Issue
echo "<h2>2️⃣ Detail Page Routing Bug (Supplier/Veterinarian)</h2>";

echo "<div class='info'>";
echo "<strong>🔍 Problem Identified:</strong><br>";
echo "The sidebar links to <code>/supplier/</code> for 'Dokter Hewan'<br>";
echo "The supplier folder uses <code>veterinarian</code> table from database<br>";
echo "Links might be going to wrong detail pages causing redirects<br>";
echo "</div>";

// Check what's in supplier table vs veterinarian table
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM veterinarian");
    $vet_count = $stmt->fetchColumn();
    
    echo "<div class='success'>✅ Found $vet_count veterinarians in database</div>";
    
    // Show sample veterinarian
    $stmt = $pdo->query("SELECT dokter_id, nama_dokter, spesialisasi FROM veterinarian LIMIT 1");
    $sample = $stmt->fetch();
    
    if ($sample) {
        echo "<div class='info'>";
        echo "<strong>Sample Veterinarian:</strong><br>";
        echo "ID: {$sample['dokter_id']}<br>";
        echo "Name: {$sample['nama_dokter']}<br>";
        echo "Specialty: {$sample['spesialisasi']}<br>";
        echo "<br><strong>Correct Detail URL:</strong> <code>/supplier/detail.php?id={$sample['dokter_id']}</code>";
        echo "</div>";
    }
    
    // Check if service table exists (kategori uses service table)
    $stmt = $pdo->query("SELECT COUNT(*) FROM service");
    $service_count = $stmt->fetchColumn();
    echo "<div class='success'>✅ Found $service_count services/kategori in database</div>";
    
    $stmt = $pdo->query("SELECT layanan_id, nama_layanan, kategori FROM service LIMIT 1");
    $sample_service = $stmt->fetch();
    
    if ($sample_service) {
        echo "<div class='info'>";
        echo "<strong>Sample Service/Kategori:</strong><br>";
        echo "ID: {$sample_service['layanan_id']}<br>";
        echo "Name: {$sample_service['nama_layanan']}<br>";
        echo "Type: {$sample_service['kategori']}<br>";
        echo "<br><strong>Correct Detail URL:</strong> <code>/kategori/detail.php?id={$sample_service['layanan_id']}</code>";
        echo "</div>";
    }
    
    echo "<div class='warning'>";
    echo "<strong>⚠️ Potential Issue:</strong><br>";
    echo "If clicking a veterinarian redirects to 'Konsultasi Umum', it means:<br>";
    echo "<ul>";
    echo "<li>Wrong ID being passed in URL</li>";
    echo "<li>OR session/cache causing redirect</li>";
    echo "<li>OR detail.php not properly checking table</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// BUG 3: Kategori undefined key warning
echo "<h2>3️⃣ Kategori Detail - Undefined Key Warning</h2>";

echo "<div class='info'>";
echo "<strong>Issue:</strong> Line 302 - <code>Warning: Undefined array key 'updated_at'</code><br>";
echo "<strong>Cause:</strong> Service table doesn't have <code>updated_at</code> field<br>";
echo "<strong>Fix:</strong> Add null coalescing operator and check in query<br>";
echo "</div>";

// Check service table structure
try {
    $stmt = $pdo->query("DESCRIBE service");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='info'><strong>Service Table Columns:</strong><br>";
    echo "<code>" . implode('</code>, <code>', $columns) . "</code>";
    echo "</div>";
    
    if (!in_array('updated_at', $columns)) {
        echo "<div class='warning'>⚠️ <strong>CONFIRMED:</strong> Service table DOES NOT have 'updated_at' column</div>";
        echo "<div class='success'>✅ Fix applied: Added null coalescing in kategori/detail.php</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// SUMMARY
echo "<h2>📋 Summary & Action Items</h2>";

echo "<div class='info'>";
echo "<h3>🎯 Issues & Status:</h3>";
echo "<ol>";
echo "<li><strong>Owner Login:</strong> Check if database has correct account</li>";
echo "<li><strong>Detail Page Routing:</strong> Ensure ID parameter passed correctly</li>";
echo "<li><strong>Kategori Undefined Key:</strong> Fixed with null coalescing</li>";
echo "</ol>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ Recommended Actions:</h3>";
echo "<ol>";
echo "<li>If owner login fails: Run <code>force_fix_accounts.php</code></li>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Try incognito/private mode</li>";
echo "<li>Check browser console (F12) for JavaScript errors</li>";
echo "<li>Test detail pages with direct URLs</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?>
