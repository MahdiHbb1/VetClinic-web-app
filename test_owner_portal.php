<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Portal Test - VetClinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Pet Photos Added Successfully!</h1>
                    <p class="text-gray-600">Owner portal now displays pet pictures just like the admin page</p>
                </div>

                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl p-6 text-white mb-6">
                    <h2 class="text-xl font-bold mb-4">
                        <i class="fas fa-user-circle mr-2"></i>
                        Test Account Ready
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-white bg-opacity-20 rounded-lg p-3">
                            <span class="font-medium">Username:</span>
                            <code class="bg-white bg-opacity-30 px-3 py-1 rounded">andi_owner</code>
                        </div>
                        <div class="flex items-center justify-between bg-white bg-opacity-20 rounded-lg p-3">
                            <span class="font-medium">Password:</span>
                            <code class="bg-white bg-opacity-30 px-3 py-1 rounded">password123</code>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-xl p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-3">
                        <i class="fas fa-paw text-blue-600 mr-2"></i>
                        This Account Has 3 Pets With Photos:
                    </h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-center">
                            <i class="fas fa-dog text-blue-600 mr-3"></i>
                            <strong>Rocky</strong> - Golden Retriever (Male)
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-dog text-pink-600 mr-3"></i>
                            <strong>Bella</strong> - Beagle (Female)
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-cat text-purple-600 mr-3"></i>
                            <strong>Luna</strong> - Persian (Female)
                        </li>
                    </ul>
                </div>

                <div class="bg-green-50 rounded-xl p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-3">
                        <i class="fas fa-check-double text-green-600 mr-2"></i>
                        Changes Implemented:
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-green-600 mr-2 mt-1"></i>
                            <span><strong>Dashboard (index.php):</strong> Pet cards now show circular profile photos in the header</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-green-600 mr-2 mt-1"></i>
                            <span><strong>Health Timeline (pet_profile.php):</strong> Pet header displays photo instead of icon</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-green-600 mr-2 mt-1"></i>
                            <span><strong>Photo Handling:</strong> Supports both external URLs and local uploads with fallback to icons</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-green-600 mr-2 mt-1"></i>
                            <span><strong>Styling:</strong> Photos have white borders, shadows, and rounded edges matching admin design</span>
                        </li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <a href="http://localhost:8080/owners/portal/login.php" 
                       class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl transition shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login to Owner Portal
                    </a>
                    
                    <a href="http://localhost:8080/auth/login.php" 
                       class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-6 rounded-xl transition">
                        <i class="fas fa-user-shield mr-2"></i>
                        Admin Login (for comparison)
                    </a>
                </div>

                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Compare the pet photo display between admin and owner portals</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
