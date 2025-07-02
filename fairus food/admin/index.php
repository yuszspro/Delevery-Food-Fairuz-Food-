<?php
/*
================================================================
|   File: index.php (Manajemen Produk)                         |
|   Lokasi: admin/index.php                                    |
================================================================
*/
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

// Logika untuk mendapatkan notifikasi chat
$unread_nav_query = "SELECT COUNT(id) as total_unread FROM chat WHERE is_read = 0 AND pengirim_role = 'user'";
$unread_nav_result = mysqli_query($conn, $unread_nav_query);
$total_unread_nav = mysqli_fetch_assoc($unread_nav_result)['total_unread'] ?? 0;

$result = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Produk | Fairuz Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-100 h-screen font-sans">
    <div class="flex h-full">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 flex flex-col">
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="daftar_pesanan.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Pesanan</a>
                <a href="index.php" class="block py-2.5 px-4 rounded transition duration-200 bg-amber-500 font-semibold">Produk</a>
                <a href="chat.php" class="relative block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
                    Chat
                    <?php if ($total_unread_nav > 0): ?>
                        <span class="absolute top-3 right-3 w-2.5 h-2.5 bg-orange-500 rounded-full animate-pulse"></span>
                    <?php endif; ?>
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <a href="../login/logout.php" class="block text-center py-2.5 px-4 rounded transition duration-200 bg-red-600 hover:bg-red-700">Logout</a>
            </div>
        </aside>
        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            <header class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4">
                <h2 class="text-3xl font-bold text-gray-800">Manajemen Produk</h2>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 font-semibold text-gray-600">
                        <i class="ph-user-circle text-2xl"></i>
                        <span><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                    </div>
                    <a href="tambah_produk.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-amber-600 transition flex items-center gap-2">
                        <i class="ph-plus-circle-fill"></i>
                        Tambah Produk
                    </a>
                    <a href="tambah_voucher.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-amber-600 transition flex items-center gap-2">
                        <i class="ph-plus-circle-fill"></i>
                        Tambah Voucher
                    </a>
                </div>
            </header>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                            <tr>
                                <th class="p-4">No</th>
                                <th class="p-4">Gambar</th>
                                <th class="p-4">Nama Produk</th>
                                <th class="p-4">Harga</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php $i = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-4 font-medium"><?= $i++ ?></td>
                                    <td class="p-4">
                                        <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" width="64" class="rounded-md object-cover aspect-square" alt="gambar" />
                                    </td>
                                    <td class="p-4 font-medium text-gray-900"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class = "p-4" div class="flex items-baseline justify-center gap-2">
    <?php if (isset($row['harga_diskon']) && $row['harga_diskon'] > 0): ?>
        <p class="text-amber-600 font-bold text-lg">Rp <?= number_format($row['harga_diskon'], 0, ',', '.') ?></p>
        <p class="text-gray-500 line-through text-sm">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
    <?php else: ?>
        <p class="text-amber-600 font-bold text-lg">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
    <?php endif; ?>
</div>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-4">
                                            <a href="edit_produk.php?id=<?= $row['id'] ?>" class="font-medium text-amber-600 hover:underline">Edit</a>
                                            <a href="hapus_produk.php?id=<?= $row['id'] ?>" class="font-medium text-red-600 hover:underline delete-link">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center p-6 text-gray-500">Belum ada produk yang ditambahkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <div id="confirmPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl text-center max-w-sm w-full">
            <i class="ph-warning-circle text-5xl text-red-500 mx-auto"></i>
            <h3 class="text-lg font-bold text-gray-800 mt-4 mb-2">Anda Yakin?</h3>
            <p class="text-sm text-gray-600 mb-6">Anda akan menghapus produk ini secara permanen.</p>
            <div class="flex justify-center gap-4">
                <button id="btnConfirmNo" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                <button id="btnConfirmYes" class="bg-red-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-600 transition">Ya, Hapus</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const confirmPopup = document.getElementById('confirmPopup');
            const btnConfirmYes = document.getElementById('btnConfirmYes');
            const btnConfirmNo = document.getElementById('btnConfirmNo');
            let deleteUrl = '';
            document.querySelectorAll('.delete-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    deleteUrl = this.href;
                    confirmPopup.classList.remove('hidden');
                    confirmPopup.classList.add('flex');
                });
            });
            btnConfirmYes.addEventListener('click', () => { window.location.href = deleteUrl; });
            btnConfirmNo.addEventListener('click', () => {
                confirmPopup.classList.add('hidden');
                confirmPopup.classList.remove('flex');
                deleteUrl = '';
            });
        });
    </script>
</body>
</html>

---
