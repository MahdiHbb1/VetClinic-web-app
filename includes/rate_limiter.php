<?php
/**
 * Rate Limiter Implementation
 * Prevents brute force attacks and DOS attempts
 */

// Configure Redis connection for rate limiting
function get_rate_limit_storage() {
    static $redis = null;
    
    if ($redis === null) {
        try {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
        } catch (Exception $e) {
            // Fallback to file-based storage if Redis is not available
            return new FileBasedStorage();
        }
    }
    
    return $redis;
}

class FileBasedStorage {
    private $storage_path;
    
    public function __construct() {
        $this->storage_path = __DIR__ . '/../storage/rate_limits/';
        if (!is_dir($this->storage_path)) {
            mkdir($this->storage_path, 0755, true);
        }
    }
    
    public function get($key) {
        $file = $this->storage_path . hash('sha256', $key);
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                return false;
            }
            return $data['value'];
        }
        return false;
    }
    
    public function set($key, $value, $ttl) {
        $file = $this->storage_path . hash('sha256', $key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        file_put_contents($file, serialize($data));
    }
    
    public function increment($key) {
        $value = $this->get($key);
        if ($value === false) {
            $value = 0;
        }
        $value++;
        $this->set($key, $value, 3600); // 1 hour TTL
        return $value;
    }
}

/**
 * Check rate limit for current user/IP
 * Implements token bucket algorithm
 */
function check_rate_limit() {
    $storage = get_rate_limit_storage();
    
    // Get client identifier (IP + session if available)
    $client_ip = get_client_ip();
    $identifier = isset($_SESSION['user_id']) ? 
                 "{$client_ip}:{$_SESSION['user_id']}" : 
                 $client_ip;
    
    // Different limits for authenticated vs anonymous users
    $is_authenticated = isset($_SESSION['user_id']);
    $rate_limit = $is_authenticated ? 100 : 30; // requests per minute
    
    // Generate key for current minute
    $minute_key = "rate_limit:" . $identifier . ":" . floor(time() / 60);
    
    // Get current request count
    $current_requests = $storage->increment($minute_key);
    
    // Check if limit exceeded
    if ($current_requests > $rate_limit) {
        // Log the rate limit violation
        error_log("Rate limit exceeded for {$identifier}");
        
        // Return 429 Too Many Requests
        http_response_code(429);
        header('Retry-After: ' . (60 - time() % 60));
        
        // Show error page
        include '../includes/rate_limit_exceeded.php';
        exit;
    }
}

/**
 * Get client IP address securely
 */
function get_client_ip() {
    // Check for CloudFlare
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    
    // Check for proxy
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Validate input dates for appointments
 */
function validate_date_range($from, $to) {
    // Validate date format
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $from) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $to)) {
        return false;
    }
    
    // Convert to timestamps
    $from_ts = strtotime($from);
    $to_ts = strtotime($to);
    
    // Check if dates are valid
    if ($from_ts === false || $to_ts === false) {
        return false;
    }
    
    // Check if from date is before to date
    if ($from_ts > $to_ts) {
        return false;
    }
    
    // Check if date range is not too large (e.g., max 1 year)
    if ($to_ts - $from_ts > 31536000) {
        return false;
    }
    
    return true;
}