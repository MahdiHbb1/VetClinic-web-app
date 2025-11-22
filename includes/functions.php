<?php
// Format currency (IDR)
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Format date (Indonesian)
function format_tanggal($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', $date);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Sanitize input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Upload image
function upload_image($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return false;
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    return false;
}

// Pagination
function paginate($total_records, $records_per_page, $current_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'offset' => $offset,
        'current_page' => $current_page
    ];
}

// Get status badge HTML
function get_status_badge($status) {
    $colors = [
        'Pending' => 'yellow',
        'Confirmed' => 'blue',
        'Completed' => 'green',
        'Cancelled' => 'red',
        'No_Show' => 'gray',
        'Aktif' => 'green',
        'Meninggal' => 'gray'
    ];
    
    $color = $colors[$status] ?? 'gray';
    return "<span class='px-2 py-1 text-xs rounded-full bg-$color-100 text-$color-800'>$status</span>";
}
?>