<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests - VetClinic</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-6xl text-red-500 mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-4">
                    Terlalu Banyak Permintaan
                </h1>
                <p class="text-gray-600 mb-6">
                    Anda telah melakukan terlalu banyak permintaan dalam waktu singkat.
                    Mohon tunggu beberapa saat sebelum mencoba kembali.
                </p>
                <div class="text-sm text-gray-500">
                    Silakan coba lagi dalam <span id="countdown">60</span> detik
                </div>
                <div class="mt-6">
                    <a href="/vetclinic/dashboard/" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-home mr-2"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer
        let timeLeft = 60;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.reload();
            }
        }, 1000);
    </script>
</body>
</html>