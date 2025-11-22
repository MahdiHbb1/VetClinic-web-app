<?php
/**
 * Deployment Preparation Script for VetClinic
 * This script prepares the application for deployment to InfinityFree
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$config = [
    'source_dir' => __DIR__ . '/..',
    'deploy_dir' => __DIR__ . '/package',
    'zip_file' => __DIR__ . '/vetclinic_deploy.zip',
    'exclude_patterns' => [
        '/\.git/',
        '/\.gitignore/',
        '/node_modules/',
        '/vendor/composer/.*',
        '/deploy/',
        '/logs/',
        '/backup/',
        '/tests/',
        '/\.env/',
        '/.*\.log$/',
        '/.*\.md$/',
        '/composer\..*/',
        '/package.*\.json/',
    ],
    'required_dirs' => [
        'config',
        'includes',
        'auth',
        'assets',
        'uploads',
        'kategori'
    ]
];

// Create clean deployment directory
function createDeploymentPackage($config) {
    echo "🚀 Creating deployment package...\n";

    // Remove existing package directory and zip
    if (file_exists($config['deploy_dir'])) {
        removeDirectory($config['deploy_dir']);
    }
    if (file_exists($config['zip_file'])) {
        unlink($config['zip_file']);
    }

    // Create fresh deploy directory
    mkdir($config['deploy_dir'], 0755, true);
    
    // Copy files
    copyFiles($config['source_dir'], $config['deploy_dir'], $config['exclude_patterns']);
    
    // Create necessary directories
    foreach ($config['required_dirs'] as $dir) {
        $path = $config['deploy_dir'] . '/' . $dir;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    // Create uploads directory with proper permissions
    $uploads_dir = $config['deploy_dir'] . '/uploads';
    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    // Create production configuration
    createProductionConfig($config['deploy_dir']);

    // Create production .htaccess
    createProductionHtaccess($config['deploy_dir']);

    // Create ZIP archive
    createZipArchive($config['deploy_dir'], $config['zip_file']);

    echo "✅ Deployment package created successfully!\n";
    echo "📦 Package location: " . $config['zip_file'] . "\n";
    echo "📊 Package size: " . formatBytes(filesize($config['zip_file'])) . "\n";
}

// Copy files recursively
function copyFiles($source, $dest, $exclude_patterns) {
    $dir = opendir($source);
    
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $sourcePath = $source . '/' . $file;
        $destPath = $dest . '/' . $file;

        // Check exclude patterns
        $excluded = false;
        foreach ($exclude_patterns as $pattern) {
            if (preg_match($pattern, str_replace('\\', '/', $sourcePath))) {
                $excluded = true;
                break;
            }
        }

        if ($excluded) {
            continue;
        }

        if (is_dir($sourcePath)) {
            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }
            copyFiles($sourcePath, $destPath, $exclude_patterns);
        } else {
            copy($sourcePath, $destPath);
        }
    }
    
    closedir($dir);
}

// Remove directory recursively
function removeDirectory($dir) {
    if (!file_exists($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

// Create production configuration
function createProductionConfig($deploy_dir) {
    $config_content = <<<'EOT'
<?php
// Production Database Configuration
define('DB_HOST', '{{DB_HOST}}');  // Will be provided by InfinityFree
define('DB_USER', '{{DB_USER}}');  // Will be provided by InfinityFree
define('DB_PASS', '{{DB_PASS}}');  // Will be provided by InfinityFree
define('DB_NAME', '{{DB_NAME}}');  // Will be provided by InfinityFree

// Error Reporting (production settings)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create PDO connection with security settings
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_PERSISTENT => false
        ]
    );
    
    // Set additional MySQL session variables
    $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
    $pdo->exec("SET SESSION max_execution_time = 30000"); // 30 seconds
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Service temporarily unavailable. Please try again later.");
}

// Security function to prevent SQL injection in LIKE clauses
function escapeLikeString($str) {
    return str_replace(['%', '_'], ['\%', '\_'], $str);
}

// Function to safely handle transactions
function executeTransaction($pdo, callable $callback) {
    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
EOT;

    file_put_contents($deploy_dir . '/config/database.php', $config_content);
}

// Create production .htaccess
function createProductionHtaccess($deploy_dir) {
    $htaccess_content = <<<'EOT'
# Disable directory listing
Options -Indexes

# Set default character set
AddDefaultCharset UTF-8

# Protect hidden files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect sensitive files
<FilesMatch "\.(ini|log|sh|sql|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable rewrite engine
RewriteEngine On

# Redirect to HTTPS if available
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Block suspicious request methods
<LimitExcept GET POST>
    Order allow,deny
    Deny from all
</LimitExcept>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src cdnjs.cloudflare.com"
</IfModule>

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 30
php_value max_input_time 60
php_value memory_limit 256M
php_flag display_errors off
php_flag log_errors on
php_value error_log logs/error.log

# Prevent access to sensitive directories
RewriteRule ^(config|includes|logs)/ - [F,L]

# Handle 404 errors
ErrorDocument 404 /404.php
EOT;

    file_put_contents($deploy_dir . '/.htaccess', $htaccess_content);
}

// Create ZIP archive
function createZipArchive($source, $destination) {
    $zip = new ZipArchive();
    
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $source = str_replace('\\', '/', realpath($source));
        
        if (is_dir($source)) {
            $iterator = new RecursiveDirectoryIterator($source);
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            
            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);
                
                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else {
                    $zip->addFile($file, str_replace($source . '/', '', $file));
                }
            }
        }
        
        $zip->close();
        return true;
    }
    return false;
}

// Format bytes to human readable format
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Execute deployment package creation
createDeploymentPackage($config);

// Generate deployment checklist
echo "\n📋 Deployment Checklist:\n";
echo "1. ⬜ Register at InfinityFree (https://app.infinityfree.net/register)\n";
echo "2. ⬜ Create new hosting account\n";
echo "3. ⬜ Create MySQL database and note credentials\n";
echo "4. ⬜ Upload vetclinic_deploy.zip via File Manager\n";
echo "5. ⬜ Extract ZIP file in public_html\n";
echo "6. ⬜ Update database.php with InfinityFree credentials\n";
echo "7. ⬜ Import database using phpMyAdmin\n";
echo "8. ⬜ Test website at your subdomain\n";
echo "9. ⬜ Verify all features work correctly\n";
echo "10. ⬜ Delete deployment ZIP file from server\n";

?>