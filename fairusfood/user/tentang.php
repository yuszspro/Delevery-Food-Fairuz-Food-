<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Aplikasi</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-pink-50 min-h-screen">

    <header class="bg-amber-500 p-5 flex items-center text-white font-semibold text-lg shadow-md sticky top-0 z-20">
        <a href="profil.php" class="mr-4 text-xl">&larr;</a>
        <h1>Tentang Aplikasi Ini</h1>
    </header>

    <main class="p-4 sm:p-6 text-gray-700">
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
            <div class="text-center mb-6">
                <img src="ff.png" alt="Logo Fairus Food" class="mx-auto w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover shadow-md">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mt-4">Fairuz Food</h2>
                <p class="text-sm text-gray-500">Versi 1.0.0</p>
            </div>
            
            <p class="text-left sm:text-justify leading-relaxed">
                Fairuz Food adalah aplikasi pemesanan makanan revolusioner yang dirancang untuk memberikan kemudahan dan kenyamanan dalam menikmati hidangan favorit Anda. Dibangun dengan cinta dan teknologi terkini, kami berkomitmen untuk menghubungkan Anda dengan berbagai pilihan kuliner terbaik di kota Anda.
            </p>
            <p class="text-left sm:text-justify leading-relaxed mt-4">
                Terima kasih telah menjadi bagian dari perjalanan kami. Selamat menikmati!
            </p>
            
            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-xs text-gray-400">
                &copy; <?= date("Y") ?> Fairuz Food. All Rights Reserved.
            </div>
        </div>
    </main>
</body>
</html>