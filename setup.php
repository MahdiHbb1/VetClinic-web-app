<?php
require_once 'config/database.php';

$message = '';
$error = '';

if (isset($_POST['reset'])) {
    try {
        // Disable foreign key checks to allow dropping tables
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/database/vetclinic.sql');
        if ($schema === false) {
            throw new Exception("Could not read database/vetclinic.sql");
        }
        
        // Split SQL by semicolon, but be careful with stored procedures/triggers if any
        // For simple dumps, splitting by ; works mostly, but let's try to execute raw if possible
        // PDO can execute multiple statements if emulation is on, but let's do it safely
        
        $pdo->exec($schema);
        
        // Read and execute test data
        $data = file_get_contents(__DIR__ . '/database/test_data.sql');
        if ($data === false) {
            throw new Exception("Could not read database/test_data.sql");
        }
        
        $pdo->exec($data);

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        $message = "Database berhasil di-reset dan data dummy telah dimasukkan!";
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - VetClinic</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <i class="fas fa-database text-4xl text-blue-500 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">Database Setup</h1>
            <p class="text-gray-600 mt-2">Reset database dan isi dengan data dummy</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
            <div class="text-center mb-6">
                <a href="/auth/login.php" class="text-blue-600 hover:text-blue-800 font-medium underline">
                    Ke Halaman Login &rarr;
                </a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Perhatian:</strong> Tindakan ini akan menghapus semua data yang ada di database dan menggantinya dengan data default/dummy.
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" onsubmit="return confirm('Yakin ingin mereset database? Semua data akan hilang!');">
            <button type="submit" name="reset" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                <i class="fas fa-sync-alt mr-2 mt-1"></i> Reset & Seed Database
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="/" class="text-gray-500 hover:text-gray-700 text-sm">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
