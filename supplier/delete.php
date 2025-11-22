<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

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

// Get supplier ID from POST data
$supplier_id = $_POST['supplier_id'] ?? null;
if (!$supplier_id) {
    $_SESSION['error'] = "ID Supplier tidak valid";
    header("Location: index.php");
    exit;
}

// Fetch supplier details to verify it exists
$stmt = $pdo->prepare("SELECT supplier_id, nama_supplier FROM supplier WHERE supplier_id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    $_SESSION['error'] = "Supplier tidak ditemukan";
    header("Location: index.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if supplier has any items
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $item_count = $stmt->fetchColumn();

    if ($item_count > 0) {
        // If supplier has items, just mark as inactive
        $stmt = $pdo->prepare("
            UPDATE supplier 
            SET status = 'Inactive',
                updated_by = ?,
                updated_at = NOW()
            WHERE supplier_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $supplier_id]);
        
        $_SESSION['success'] = "Supplier telah dinonaktifkan karena masih memiliki item terkait";
    } else {
        // Check if supplier has any stock movements
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM stock_movement sm
            JOIN inventory i ON sm.item_id = i.item_id
            WHERE i.supplier_id = ?
        ");
        $stmt->execute([$supplier_id]);
        $movement_count = $stmt->fetchColumn();

        if ($movement_count > 0) {
            // If supplier has stock movement history, just mark as inactive
            $stmt = $pdo->prepare("
                UPDATE supplier 
                SET status = 'Inactive',
                    updated_by = ?,
                    updated_at = NOW()
                WHERE supplier_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $supplier_id]);
            
            $_SESSION['success'] = "Supplier telah dinonaktifkan karena memiliki riwayat transaksi";
        } else {
            // If supplier has no items and no history, we can safely delete it
            $stmt = $pdo->prepare("DELETE FROM supplier WHERE supplier_id = ?");
            $stmt->execute([$supplier_id]);

            $_SESSION['success'] = "Supplier berhasil dihapus";
        }
    }

    $pdo->commit();
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: detail.php?id=" . $supplier_id);
    exit;
}