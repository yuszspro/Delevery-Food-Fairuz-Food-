<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fairuz Food</title>
  <link rel="icon" href="ff.png" type="image/png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to bottom right, #fde2e2, #fff5f5);
    }

    .fade-in {
      animation: fadeIn 1s ease-out forwards;
      opacity: 0;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .hero-bg {
      background: url('ff.png') no-repeat center center;
      background-size: contain;
      opacity: 0.05;
      position: absolute;
      inset: 0;
      z-index: 0;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">

  <!-- Background transparan logo -->
  <div class="hero-bg"></div>

  <!-- Konten Utama -->
  <div class="relative z-10 text-center max-w-md w-full bg-white/60 backdrop-blur-lg p-10 rounded-2xl shadow-2xl fade-in border border-white/30">
    <img src="ff.png" alt="Fairuz Food Logo" class="w-32 mx-auto mb-6" />
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang di Fairuz Food</h1>
    <p class="text-gray-700 text-sm mb-6">Tempat Chicken berkualitas dan premium.</p>
    <a href="login/index.php" class="inline-block w-full py-3 rounded-full bg-gray-800 hover:bg-gray-900 text-white font-semibold text-sm shadow-lg transition-all duration-300">
      Mulai Sekarang
    </a>
  </div>

</body>
</html>
