<?php
/**
 * Quick Verification Script
 * Check if demo data is properly imported
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$results = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Check table counts
    $tables = [
        'users' => ['expected' => 6, 'actual' => 0],
        'veterinarian' => ['expected' => 4, 'actual' => 0],
        'owner' => ['expected' => 8, 'actual' => 0],
        'pet' => ['expected' => 18, 'actual' => 0],
        'appointment' => ['expected' => 25, 'actual' => 0],
        'medical_record' => ['expected' => 13, 'actual' => 0],
        'vaksinasi' => ['expected' => 27, 'actual' => 0],
        'medicine' => ['expected' => 18, 'actual' => 0],
        'resep' => ['expected' => 7, 'actual' => 0],
        'service' => ['expected' => 10, 'actual' => 0]
    ];
    
    foreach ($tables as $table => &$data) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM {$table}");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $data['actual'] = (int)$row['count'];
            $data['status'] = ($data['actual'] >= $data['expected']) ? 'OK' : 'MISSING';
            mysqli_free_result($result);
        } else {
            $data['status'] = 'ERROR';
            $data['error'] = mysqli_error($conn);
        }
    }
    
    // Check species distribution
    $species = [];
    $result = mysqli_query($conn, "SELECT jenis, COUNT(*) as count FROM pet GROUP BY jenis");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $species[$row['jenis']] = (int)$row['count'];
        }
        mysqli_free_result($result);
    }
    
    // Check appointment status distribution
    $appointments = [];
    $result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM appointment GROUP BY status");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[$row['status']] = (int)$row['count'];
        }
        mysqli_free_result($result);
    }
    
    // Check photos
    $photos_check = [];
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM veterinarian WHERE foto_url IS NOT NULL AND foto_url != ''");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $photos_check['veterinarians_with_photos'] = (int)$row['count'];
        mysqli_free_result($result);
    }
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM pet WHERE foto_url IS NOT NULL AND foto_url != ''");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $photos_check['pets_with_photos'] = (int)$row['count'];
        mysqli_free_result($result);
    }
    
    // Calculate overall status
    $all_ok = true;
    foreach ($tables as $data) {
        if ($data['status'] !== 'OK') {
            $all_ok = false;
            break;
        }
    }
    
    $results['success'] = $all_ok;
    $results['message'] = $all_ok ? 'All data verified successfully!' : 'Some data is missing or incorrect';
    $results['data'] = [
        'tables' => $tables,
        'species_distribution' => $species,
        'appointment_status' => $appointments,
        'photos' => $photos_check
    ];
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    $results['success'] = false;
    $results['message'] = $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
