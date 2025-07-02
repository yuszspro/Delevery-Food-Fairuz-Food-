<?php
session_start();
require '../admin/koneksi.php'; // Pastikan path ke koneksi.php sesuai

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

    <style>
        .bg-primary { background-color: #f59e0b; }
        .hover\:text-primary:hover { color: #f59e0b; }
        .text-primary { color: #f59e0b; }
        body { padding-top: 90px; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
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

    <nav class="fixed bottom-0 w-full flex justify-around border-t border-gray-200 py-3 bg-white shadow-lg select-none z-50 md:hidden">
        <a href="index.php" class="flex flex-col items-center justify-center text-gray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg><span class="text-sm">Home</span></a>
        <a href="riwayat.php" class="flex flex-col items-center justify-center text-dray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg><span class="text-sm">Riwayat</span></a>
        <a href="chat.php" class="flex flex-col items-center justify-center text-gray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg><span class="text-sm">Chat</span></a>
        <a href="profil.php" class="flex flex-col items-center justify-center text-amber-500 w-1/4 pt-1 "><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg><span class="text-sm">Profil</span></a>
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
    </script>
</body>
</html>