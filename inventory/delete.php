<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/inventory_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

// Check role authorization
if (!in_array($_SESSION['role'], ['Admin', 'Inventory'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut";
    header("Location: index.php");
    exit;
}

// Verify if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak valid";
    header("Location: index.php");
    exit;
}

// Get item ID from POST data
$item_id = $_POST['item_id'] ?? null;
if (!$item_id) {
    $_SESSION['error'] = "ID Item tidak valid";
    header("Location: index.php");
    exit;
}

// Fetch item details to verify it exists and has no stock
$stmt = $pdo->prepare("
    SELECT item_id, nama_item, current_stock
    FROM inventory 
    WHERE item_id = ?
");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Check if item has stock
if ($item['current_stock'] > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus item yang masih memiliki stok";
    header("Location: detail.php?id=" . $item_id);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if item is used in any transactions or treatments
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM stock_movement 
        WHERE item_id = ? 
        AND reference_type IN ('TREATMENT', 'TRANSACTION')
    ");
    $stmt->execute([$item_id]);
    $usageCount = $stmt->fetchColumn();

    if ($usageCount > 0) {
        // If item is used, just mark it as inactive
        $stmt = $pdo->prepare("
            UPDATE inventory 
            SET status = 'Inactive',
                updated_by = ?,
                updated_at = NOW()
            WHERE item_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $item_id]);
        
        $_SESSION['success'] = "Item telah dinonaktifkan karena memiliki riwayat penggunaan";
    } else {
        // If item is never used, we can safely delete it and its stock movements
        // Delete stock movements first
        $stmt = $pdo->prepare("DELETE FROM stock_movement WHERE item_id = ?");
        $stmt->execute([$item_id]);

        // Then delete the item
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE item_id = ?");
        $stmt->execute([$item_id]);

        $_SESSION['success'] = "Item berhasil dihapus";
    }

    $pdo->commit();
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: detail.php?id=" . $item_id);
    exit;
}