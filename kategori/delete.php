<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user has permission
if (!in_array($_SESSION['role'], ['Admin', 'Inventory', 'Service'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut!";
    header("Location: index.php");
    exit;
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'");

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak valid!";
    header("Location: index.php");
    exit;
}

// Validate and sanitize input
if (!isset($_POST['kategori_id']) || !is_numeric($_POST['kategori_id'])) {
    $_SESSION['error'] = "ID Kategori tidak valid!";
    header("Location: index.php");
    exit;
}

$kategori_id = (int)$_POST['kategori_id'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if category exists
    $stmt = $pdo->prepare("
        SELECT k.*, 
               COUNT(DISTINCT i.item_id) as total_inventory,
               COUNT(DISTINCT s.service_id) as total_service,
               COUNT(DISTINCT m.medicine_id) as total_medicine
        FROM kategori k
        LEFT JOIN inventory i ON k.kategori_id = i.kategori_id
        LEFT JOIN service s ON k.kategori_id = s.kategori_id
        LEFT JOIN medicine m ON k.kategori_id = m.kategori_id
        WHERE k.kategori_id = ?
        GROUP BY k.kategori_id
    ");
    
    $stmt->execute([$kategori_id]);
    $category = $stmt->fetch();

    if (!$category) {
        throw new Exception("Kategori tidak ditemukan!");
    }

    // Check if category is being used
    $total_items = $category['total_inventory'] + $category['total_service'] + $category['total_medicine'];
    if ($total_items > 0) {
        throw new Exception("Tidak dapat menghapus kategori yang sedang digunakan!");
    }

    // Delete the category
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE kategori_id = ?");
    $stmt->execute([$kategori_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Kategori berhasil dihapus!";
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollBack();

    $_SESSION['error'] = $e->getMessage();
    header("Location: index.php");
    exit;
}
?>