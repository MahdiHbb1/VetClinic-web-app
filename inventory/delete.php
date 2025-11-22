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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

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
    SELECT obat_id as item_id, nama_obat as nama_item, stok as current_stock
    FROM medicine 
    WHERE obat_id = ?
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

    // Medicine table doesn't track usage history, so we can mark as unavailable
    // Mark item as unavailable instead of deleting
    $stmt = $pdo->prepare("
        UPDATE medicine 
        SET status_tersedia = 0
        WHERE obat_id = ?
    ");
    $stmt->execute([$item_id]);
    
    $_SESSION['success'] = "Item telah dinonaktifkan";

    $pdo->commit();
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: detail.php?id=" . $item_id);
    exit;
}