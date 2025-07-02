<?php
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

$error_message = '';
$success_message = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_voucher = trim(strtoupper($_POST['kode_voucher']));
    $deskripsi = trim($_POST['deskripsi']);
    $potongan_harga = isset($_POST['potongan_harga']) ? (int)$_POST['potongan_harga'] : 0;
    $min_pembelian = isset($_POST['min_pembelian']) ? (int)$_POST['min_pembelian'] : 0;
    $berlaku_hingga = !empty($_POST['berlaku_hingga']) ? $_POST['berlaku_hingga'] : null;

    // Validasi input
    if (empty($kode_voucher) || empty($deskripsi) || $potongan_harga <= 0 || $min_pembelian <= 0) {
        $error_message = "Semua kolom wajib diisi dengan benar.";
    } else {
        // Cek apakah kode voucher sudah ada (harus unik)
        $stmt_check = $conn->prepare("SELECT id FROM vouchers WHERE kode_voucher = ?");
        $stmt_check->bind_param("s", $kode_voucher);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = "Kode voucher sudah ada. Silakan gunakan kode lain.";
        } else {
            // Jika aman, masukkan data voucher baru
            $stmt_insert = $conn->prepare("INSERT INTO vouchers (kode_voucher, deskripsi, potongan_harga, min_pembelian, berlaku_hingga) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssiis", $kode_voucher, $deskripsi, $potongan_harga, $min_pembelian, $berlaku_hingga);

            if ($stmt_insert->execute()) {
                $success_message = "Voucher berhasil ditambahkan! Anda akan diarahkan ke halaman produk.";
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
            } else {
                $error_message = "Error saat menyimpan data: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Voucher | Fairuz Food</title>
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
                <h2 class="text-3xl font-bold text-gray-800">Tambah Voucher Baru</h2>
                <div class="flex items-center gap-3 font-semibold text-gray-600">
                    <i class="ph-user-circle text-2xl"></i>
                    <span><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                </div>
            </header>

            <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Oops!</strong>
                        <span class="block sm:inline"><?= $error_message ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Sukses!</strong>
                        <span class="block sm:inline"><?= $success_message ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off" class="space-y-6">
                    <div>
                        <label for="kode_voucher" class="block text-sm font-medium text-gray-700 mb-1">Kode Voucher</label>
                        <input type="text" name="kode_voucher" id="kode_voucher" required placeholder="Contoh: DISKON10K"
                               class="w-full px-4 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition uppercase" />
                        <p class="text-xs text-gray-500 mt-1">Gunakan huruf kapital tanpa spasi.</p>
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" required rows="3" placeholder="Contoh: Diskon Spesial Lebaran"
                                  class="w-full px-4 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="potongan_harga" class="block text-sm font-medium text-gray-700 mb-1">Potongan Harga (Rp)</label>
                            <input type="number" name="potongan_harga" id="potongan_harga" required min="1" placeholder="Contoh: 10000"
                                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
                        </div>
                        <div>
                            <label for="min_pembelian" class="block text-sm font-medium text-gray-700 mb-1">Minimal Pembelian (Rp)</label>
                            <input type="number" name="min_pembelian" id="min_pembelian" required min="1" placeholder="Contoh: 50000"
                                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
                        </div>
                    </div>

                    <div>
                        <label for="berlaku_hingga" class="block text-sm font-medium text-gray-700 mb-1">Berlaku Hingga</label>
                        <input type="date" name="berlaku_hingga" id="berlaku_hingga" required
                               class="w-full px-4 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
                    </div>
                    
                    <div class="flex gap-4 pt-4">
                        <a href="index.php" class="w-full text-center bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-lg hover:bg-gray-300 transition">
                            Kembali
                        </a>
                        <button type="submit" class="w-full bg-amber-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-amber-600 transition">
                            Simpan Voucher
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>