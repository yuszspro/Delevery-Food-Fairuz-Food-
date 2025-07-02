<?php
session_start();
require 'koneksi.php'; // Pastikan path ke koneksi.php sesuai

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$username = $_SESSION['user_username'];

// Ambil data lengkap user termasuk foto profil (Tetap diperlukan untuk konten halaman)
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result_user = $stmt->get_result();
$user_data = $result_user->fetch_assoc();
$stmt->close();

$foto_profil = $user_data['foto_profil'] ?? null;
$inisial = !empty($username) ? strtoupper(substr($username, 0, 1)) : '?';

// Mengambil jumlah item di keranjang (Tetap diperlukan untuk ikon di header)
$cart_query = $conn->prepare("SELECT COUNT(id) as total_items FROM keranjang WHERE id_user = ?");
$cart_query->bind_param("i", $id_user);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
$cart_query->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <style>
        .bg-primary { background-color: #f59e0b; }
        .hover\:text-primary:hover { color: #f59e0b; }
        .text-primary { color: #f59e0b; }
        body { padding-top: 90px; }
        
        
    </style>
</head>
<body class="bg-pink-50 min-h-screen pb-24 font-sans">
    <header class="fixed top-0 inset-x-0 bg-amber-500 p-4 text-white shadow-lg z-30">
        <div class="w-full max-w-screen-xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Profil Saya</h1>
            
            <div class="flex items-center gap-8">
                <nav class="hidden md:flex items-center gap-6 text-base font-semibold">
                    <a href="index.php" class="hover:text-amber-200 transition-colors">Home</a>
                    <a href="riwayat.php" class="hover:text-amber-200 transition-colors">Riwayat</a>
                    <a href="chat.php" class="hover:text-amber-200 transition-colors">Chat</a>
                    <a href="profil.php" class="text-amber-200 font-bold">Profil</a> </nav>

                <a href="keranjang.php" class="p-2 relative">
                    <img src="cart.png" alt="Keranjang Belanja" class="w-10 h-10">
                    <span class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-amber-500 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                        <?= $cart_count ?>
                    </span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex flex-col min-h-screen">
        <main class="flex-1 bg-pink-50 p-4 pb-24 max-w-screen-xl mx-auto w-full">
            <div class="bg-white rounded-xl shadow p-6 text-center mb-6">
                <div class="w-24 h-24 rounded-full mx-auto border-4 border-amber-200 shadow-lg overflow-hidden">
                    <?php if ($foto_profil && file_exists("../uploads/profiles/" . $foto_profil)): ?>
                        <img src="../uploads/profiles/<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-amber-100 text-amber-600 text-5xl font-bold flex items-center justify-center">
                            <?= htmlspecialchars($inisial) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mt-4"><?= htmlspecialchars($username) ?></h2>
                <p class="text-gray-500 font-medium">(Pembeli)</p>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <nav class="divide-y divide-gray-200">
                    <a href="edit_profil.php" class="flex justify-between items-center p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <i class="ph-user-circle text-2xl text-gray-500"></i>
                            <span class="font-semibold text-gray-700">Edit Profil</span>
                        </div>
                        <i class="ph-caret-right text-lg text-gray-400">></i>
                    </a>
                    <a href="peta.php" class="flex justify-between items-center p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <i class="ph-target text-2xl text-gray-500"></i>
                            <span class="font-semibold text-gray-700">Alamat Penerima</span>
                        </div>
                        <i class="ph-caret-right text-lg text-gray-400">></i>
                    </a>
                    <a href="voucher_saya.php" class="flex justify-between items-center p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <i class="ph-ticket text-2xl text-gray-500"></i>
                            <span class="font-semibold text-gray-700">Voucher Saya</span>
                        </div>
                        <i class="ph-caret-right text-lg text-gray-400">></i>
                    </a>
                    <a href="poin_voucher.php" class="flex justify-between items-center p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <i class="ph-star text-2xl text-gray-500"></i>
                            <span class="font-semibold text-gray-700">Poin dan Voucher</span>
                        </div>
                        <i class="ph-caret-right text-lg text-gray-400">></i>
                    </a>
                    <a href="tentang.php" class="flex justify-between items-center p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <i class="ph-info text-2xl text-gray-500"></i>
                            <span class="font-semibold text-gray-700">Tentang Aplikasi</span>
                        </div>
                        <i class="ph-caret-right text-lg text-gray-400">></i>
                    </a>
                </nav>
            </div>

            <div class="mt-6">
                <a href="#" id="logoutBtn" class="block w-full text-center bg-red-500 text-white font-bold p-3 rounded-xl hover:bg-red-600 transition-colors shadow">
                    Log out
                </a>
            </div>
        </main>
    </div>

   <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 md:hidden">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-around py-2">
            <a href="index.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-semibold">Beranda</span>
            </a>
            <a href="riwayat.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-history text-xl mb-1"></i>
                <span class="text-xs font-semibold">Riwayat</span>
            </a>
            <a href="chat.php" class="flex flex-col items-center py-2 relative text-gray-500 hover:text-amber-500 transition-colors">
    <i class="fas fa-comments text-xl mb-1"></i>
    <span class="text-xs font-semibold">Chat</span>
    <span id="chat-badge-mobile" class="absolute top-0 right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">0</span>
</a>

            <a href="profil.php" class="flex flex-col items-center py-2 text-amber-500 ">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-semibold">Profil</span>
            </a>
        </div>
    </div>
</nav>

    <script>
    document.getElementById('logoutBtn').addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Yakin ingin logout?',
            text: "Kamu akan keluar dari akun!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../login/logout.php';
            }
        });
    });
    
    function updateChatBadge() {
    fetch('get_chat_notifikasi.php')
        .then(res => res.json())
        .then(data => {
            const count = data.unread_count || 0;
            const badgeMobile = document.getElementById('chat-badge-mobile');
            const badgeDesktop = document.getElementById('chat-badge-desktop');

            [badgeMobile, badgeDesktop].forEach(badge => {
                if (!badge) return;
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        })
        .catch(err => console.error('Gagal mengambil chat badge:', err));
}

// Jalankan saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    updateChatBadge();
    // Perbarui badge setiap 10 detik
    setInterval(updateChatBadge, 10000);
});
    </script>
</body>
</html>