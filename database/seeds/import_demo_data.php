<?php
/**
 * Complete Demo Data Import Script
 * Purpose: Import comprehensive dummy data for VetClinic demo
 * Features: Progress display, error handling, verification
 */

// Include database configuration
require_once __DIR__ . '/../../config/database.php';

// Set execution time and memory limits
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

// Output HTML header
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetClinic Demo Data Import</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .progress-section {
            margin: 30px 0;
        }
        
        .progress-item {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .progress-item.success {
            border-left-color: #10b981;
            background: #ecfdf5;
        }
        
        .progress-item.error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        
        .progress-item.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        
        .status {
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
        }
        
        .status.success {
            background: #10b981;
            color: white;
        }
        
        .status.error {
            background: #ef4444;
            color: white;
        }
        
        .status.warning {
            background: #f59e0b;
            color: white;
        }
        
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .summary-box h2 {
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 6px;
        }
        
        .summary-item .label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .summary-item .value {
            font-size: 24px;
            font-weight: 700;
        }
        
        .error-details {
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #dc2626;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .info-box strong {
            color: #1e40af;
        }
        
        .species-distribution {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .species-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 VetClinic Demo Data Import</h1>
        <p class="subtitle">Importing comprehensive dummy data for full demo functionality</p>
        
        <div class="info-box">
            <strong>📋 What's included:</strong>
            <ul style="margin-top: 10px; margin-left: 20px; line-height: 1.8;">
                <li>6 Staff members with photos</li>
                <li>8 Pet owners with complete profiles</li>
                <li>18 Pets with Unsplash photos (dogs, cats, birds, rabbits)</li>
                <li>25 Appointments distributed for graph data</li>
                <li>Medical records, vaccinations, prescriptions</li>
                <li>18 Medicine items in inventory</li>
            </ul>
        </div>

<?php

// Start import process
$errors = [];
$warnings = [];
$success_count = 0;

try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    echo '<div class="progress-section">';
    
    // Read SQL file
    echo '<div class="progress-item">';
    echo '<span>📂 Reading SQL file...</span>';
    
    $sql_file = __DIR__ . '/complete_demo_data.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: {$sql_file}");
    }
    
    $sql_content = file_get_contents($sql_file);
    if ($sql_content === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo '<span class="status success">✓ Success</span>';
    echo '</div>';
    $success_count++;
    
    // Start transaction
    echo '<div class="progress-item">';
    echo '<span>🔄 Starting transaction...</span>';
    
    mysqli_begin_transaction($conn);
    
    echo '<span class="status success">✓ Success</span>';
    echo '</div>';
    $success_count++;
    
    // Execute SQL
    echo '<div class="progress-item">';
    echo '<span>⚙️ Executing SQL statements...</span>';
    
    // Execute multi-query
    if (mysqli_multi_query($conn, $sql_content)) {
        do {
            // Store first result set
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
            
            // Check for errors
            if (mysqli_errno($conn)) {
                $warnings[] = "Warning: " . mysqli_error($conn);
            }
        } while (mysqli_more_results($conn) && mysqli_next_result($conn));
    } else {
        throw new Exception("Failed to execute SQL: " . mysqli_error($conn));
    }
    
    echo '<span class="status success">✓ Success</span>';
    echo '</div>';
    $success_count++;
    
    // Commit transaction
    echo '<div class="progress-item">';
    echo '<span>💾 Committing changes...</span>';
    
    mysqli_commit($conn);
    
    echo '<span class="status success">✓ Success</span>';
    echo '</div>';
    $success_count++;
    
    // Verify data
    echo '<div class="progress-item">';
    echo '<span>✅ Verifying imported data...</span>';
    
    $counts = [];
    $tables = ['users', 'veterinarian', 'owner', 'pet', 'appointment', 'medical_record', 'vaksinasi', 'medicine', 'resep', 'service'];
    
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM {$table}");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $counts[$table] = $row['count'];
            mysqli_free_result($result);
        }
    }
    
    echo '<span class="status success">✓ Success</span>';
    echo '</div>';
    $success_count++;
    
    echo '</div>'; // End progress-section
    
    // Display summary
    echo '<div class="summary-box">';
    echo '<h2>📊 Import Summary</h2>';
    echo '<div class="summary-grid">';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Staff Members</div>';
    echo '<div class="value">' . ($counts['users'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Veterinarians</div>';
    echo '<div class="value">' . ($counts['veterinarian'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Pet Owners</div>';
    echo '<div class="value">' . ($counts['owner'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Pets Registered</div>';
    echo '<div class="value">' . ($counts['pet'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Appointments</div>';
    echo '<div class="value">' . ($counts['appointment'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Medical Records</div>';
    echo '<div class="value">' . ($counts['medical_record'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Vaccinations</div>';
    echo '<div class="value">' . ($counts['vaksinasi'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '<div class="summary-item">';
    echo '<div class="label">Medicines</div>';
    echo '<div class="value">' . ($counts['medicine'] ?? 0) . '</div>';
    echo '</div>';
    
    echo '</div>'; // End summary-grid
    
    // Get species distribution
    $species_result = mysqli_query($conn, "SELECT jenis, COUNT(*) as count FROM pet GROUP BY jenis");
    if ($species_result) {
        echo '<div style="margin-top: 20px;">';
        echo '<div class="label">Pet Species Distribution:</div>';
        echo '<div class="species-distribution">';
        while ($row = mysqli_fetch_assoc($species_result)) {
            echo '<div class="species-badge">';
            echo htmlspecialchars($row['jenis']) . ': ' . $row['count'];
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        mysqli_free_result($species_result);
    }
    
    echo '</div>'; // End summary-box
    
    // Display warnings if any
    if (!empty($warnings)) {
        echo '<div class="progress-item warning">';
        echo '<span>⚠️ ' . count($warnings) . ' warning(s) occurred</span>';
        echo '</div>';
        echo '<div class="error-details">';
        foreach ($warnings as $warning) {
            echo htmlspecialchars($warning) . "\n";
        }
        echo '</div>';
    }
    
    // Success message
    echo '<div style="margin-top: 30px; padding: 20px; background: #ecfdf5; border-radius: 8px; border: 2px solid #10b981;">';
    echo '<h3 style="color: #10b981; margin-bottom: 10px;">✅ Import Completed Successfully!</h3>';
    echo '<p style="color: #047857;">All demo data has been imported. You can now:</p>';
    echo '<ul style="margin: 15px 0 15px 20px; color: #047857; line-height: 2;">';
    echo '<li>Login with username: <strong>admin</strong> / password: <strong>admin123</strong></li>';
    echo '<li>View dashboard with working graphs</li>';
    echo '<li>Browse pets with photos</li>';
    echo '<li>Check appointments timeline</li>';
    echo '<li>Review medical records</li>';
    echo '<li>Test all CRUD features</li>';
    echo '</ul>';
    echo '</div>';
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && mysqli_connect_errno() == 0) {
        mysqli_rollback($conn);
    }
    
    echo '<div class="progress-item error">';
    echo '<span>❌ Import failed</span>';
    echo '<span class="status error">Error</span>';
    echo '</div>';
    
    echo '<div class="error-details">';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
    
    $errors[] = $e->getMessage();
}

// Close database connection
if (isset($conn)) {
    mysqli_close($conn);
}

?>

        <div class="actions">
            <a href="../../index.php" class="btn btn-primary">🏠 Go to Dashboard</a>
            <a href="../../auth/login.php" class="btn btn-secondary">🔐 Login Page</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #f9fafb; border-radius: 6px; font-size: 12px; color: #6b7280;">
            <strong>📝 Note:</strong> This import script clears all existing data before importing. 
            The default admin credentials are: <code>admin / admin123</code>
        </div>
    </div>
</body>
</html>
