<?php
require_once 'functions.php';

/**
 * Get inventory item status badge HTML
 */
function get_inventory_status_badge($status) {
    $badges = [
        'In Stock' => 'bg-green-100 text-green-800',
        'Low Stock' => 'bg-yellow-100 text-yellow-800',
        'Out of Stock' => 'bg-red-100 text-red-800',
        'Expired' => 'bg-gray-100 text-gray-800',
        'Discontinued' => 'bg-red-100 text-red-800'
    ];

    $badgeClass = $badges[$status] ?? 'bg-gray-100 text-gray-800';
    return "<span class=\"px-2 py-1 text-sm font-medium rounded-full {$badgeClass}\">{$status}</span>";
}

/**
 * Get movement type badge HTML
 */
function get_movement_type_badge($type) {
    $badges = [
        'IN' => 'bg-green-100 text-green-800',
        'OUT' => 'bg-red-100 text-red-800',
        'ADJUST' => 'bg-blue-100 text-blue-800',
        'EXPIRED' => 'bg-gray-100 text-gray-800'
    ];

    $badgeClass = $badges[$type] ?? 'bg-gray-100 text-gray-800';
    return "<span class=\"px-2 py-1 text-sm font-medium rounded-full {$badgeClass}\">{$type}</span>";
}

/**
 * Calculate current stock status based on quantity and min_stock
 */
function calculate_stock_status($quantity, $min_stock, $expired_date = null) {
    if ($expired_date && strtotime($expired_date) <= time()) {
        return 'Expired';
    }
    
    if ($quantity <= 0) {
        return 'Out of Stock';
    }
    
    if ($quantity <= $min_stock) {
        return 'Low Stock';
    }
    
    return 'In Stock';
}

/**
 * Validate inventory item data
 */
function validate_inventory_item($data, $isNew = true) {
    $errors = [];

    if (empty($data['nama_item'])) {
        $errors[] = "Nama item harus diisi";
    }

    if (empty($data['kategori_id'])) {
        $errors[] = "Kategori harus dipilih";
    }

    if (!isset($data['min_stock']) || $data['min_stock'] < 0) {
        $errors[] = "Minimum stok harus diisi dengan angka positif";
    }

    if ($isNew && !isset($data['current_stock'])) {
        $errors[] = "Stok awal harus diisi";
    }

    if (!empty($data['expired_date'])) {
        $expired = strtotime($data['expired_date']);
        if ($expired && $expired < time()) {
            $errors[] = "Tanggal kadaluarsa tidak boleh kurang dari hari ini";
        }
    }

    if (!empty($data['harga_beli']) && $data['harga_beli'] < 0) {
        $errors[] = "Harga beli tidak boleh negatif";
    }

    if (!empty($data['harga_jual']) && $data['harga_jual'] < 0) {
        $errors[] = "Harga jual tidak boleh negatif";
    }

    return $errors;
}

/**
 * Get inventory item by ID with related data
 */
function get_inventory_item($pdo, $item_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                i.*,
                k.nama_kategori,
                s.nama_supplier,
                s.kontak as supplier_kontak,
                u_created.nama_lengkap as created_by_name,
                u_updated.nama_lengkap as updated_by_name
            FROM inventory i
            LEFT JOIN kategori k ON i.kategori_id = k.kategori_id
            LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
            LEFT JOIN users u_created ON i.created_by = u_created.user_id
            LEFT JOIN users u_updated ON i.updated_by = u_updated.user_id
            WHERE i.item_id = ?
        ");
        
        $stmt->execute([$item_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting inventory item: " . $e->getMessage());
        return null;
    }
}

/**
 * Create stock movement history
 */
function create_stock_movement($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO inventory_movement (
                item_id, type, quantity, 
                previous_stock, current_stock, 
                batch_number, expired_date,
                reference_type, reference_id,
                notes, performed_by, performed_at
            ) VALUES (
                ?, ?, ?,
                ?, ?,
                ?, ?,
                ?, ?,
                ?, ?, NOW()
            )
        ");

        return $stmt->execute([
            $data['item_id'],
            $data['type'],
            $data['quantity'],
            $data['previous_stock'],
            $data['current_stock'],
            $data['batch_number'] ?? null,
            $data['expired_date'] ?? null,
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            $data['notes'] ?? null,
            $_SESSION['user_id']
        ]);
    } catch (PDOException $e) {
        error_log("Error creating stock movement: " . $e->getMessage());
        return false;
    }
}

/**
 * Get stock movements for an item
 */
function get_stock_movements($pdo, $item_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                u.nama_lengkap as performed_by_name
            FROM inventory_movement m
            LEFT JOIN users u ON m.performed_by = u.user_id
            WHERE m.item_id = ?
            ORDER BY m.performed_at DESC
        ");
        
        $stmt->execute([$item_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting stock movements: " . $e->getMessage());
        return [];
    }
}

/**
 * Update stock quantity
 */
function update_stock_quantity($pdo, $item_id, $adjustment, $data = []) {
    try {
        $pdo->beginTransaction();

        // Get current stock
        $stmt = $pdo->prepare("
            SELECT current_stock 
            FROM inventory 
            WHERE item_id = ? 
            FOR UPDATE
        ");
        $stmt->execute([$item_id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            throw new Exception("Item tidak ditemukan");
        }

        $previous_stock = $current['current_stock'];
        $new_stock = $previous_stock + $adjustment;

        if ($new_stock < 0) {
            throw new Exception("Stok tidak mencukupi");
        }

        // Update stock
        $stmt = $pdo->prepare("
            UPDATE inventory SET
                current_stock = ?,
                updated_by = ?,
                updated_at = NOW()
            WHERE item_id = ?
        ");
        $stmt->execute([
            $new_stock,
            $_SESSION['user_id'],
            $item_id
        ]);

        // Create movement history
        $movement_data = array_merge([
            'item_id' => $item_id,
            'quantity' => abs($adjustment),
            'type' => $adjustment > 0 ? 'IN' : 'OUT',
            'previous_stock' => $previous_stock,
            'current_stock' => $new_stock
        ], $data);

        if (!create_stock_movement($pdo, $movement_data)) {
            throw new Exception("Gagal mencatat pergerakan stok");
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating stock: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Check expired items and update status
 */
function check_expired_items($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT item_id, nama_item, current_stock, expired_date
            FROM inventory
            WHERE expired_date IS NOT NULL 
            AND expired_date <= CURDATE()
            AND status != 'Expired'
        ");
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            $pdo->beginTransaction();

            // Update status to expired
            $stmt = $pdo->prepare("
                UPDATE inventory SET
                    status = 'Expired',
                    updated_by = ?,
                    updated_at = NOW()
                WHERE item_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $item['item_id']]);

            // Create movement history
            create_stock_movement($pdo, [
                'item_id' => $item['item_id'],
                'type' => 'EXPIRED',
                'quantity' => $item['current_stock'],
                'previous_stock' => $item['current_stock'],
                'current_stock' => 0,
                'notes' => 'Item kadaluarsa pada ' . date('d/m/Y', strtotime($item['expired_date']))
            ]);

            $pdo->commit();
        }

        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error checking expired items: " . $e->getMessage());
        return false;
    }
}

/**
 * Get low stock items
 */
function get_low_stock_items($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                i.*,
                k.nama_kategori,
                s.nama_supplier
            FROM inventory i
            LEFT JOIN kategori k ON i.kategori_id = k.kategori_id
            LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
            WHERE i.current_stock <= i.min_stock
            AND i.status NOT IN ('Discontinued', 'Expired')
            ORDER BY i.current_stock ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting low stock items: " . $e->getMessage());
        return [];
    }
}

/**
 * Format currency
 */
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}