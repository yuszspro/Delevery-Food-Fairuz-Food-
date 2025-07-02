<?php
session_start();
require 'koneksi.php';

// Menampilkan error untuk debugging (bisa dihapus saat produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan user sudah login dan memiliki role 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// [PERUBAHAN] Query untuk nama dan alamat dihapus karena tidak lagi ditampilkan di header
// Hanya query untuk cart count yang dipertahankan
$cart_query = $conn->prepare("SELECT COUNT(id) as total_items FROM keranjang WHERE id_user = ?");
$cart_query->bind_param("i", $id_user);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
$cart_query->close();

// Query utama untuk riwayat transaksi (tidak berubah)
$query = "
    SELECT
        t.id AS id_transaksi,
        t.total_harga,
        t.status,
        t.tanggal_transaksi,
        t.metode_pembayaran,
        t.potongan_harga,
        t.kode_voucher_terpakai,
        dt.jumlah,
        dt.harga_saat_transaksi,
        p.harga AS harga_asli_produk,
        COALESCE(dt.nama_produk_saat_transaksi, p.nama) AS nama_produk
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.id_transaksi
    LEFT JOIN produk p ON dt.id_produk = p.id
    WHERE t.id_user = ?
    ORDER BY t.tanggal_transaksi DESC, t.id DESC
";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Gagal mempersiapkan statement: " . $conn->error);
}

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$details = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$transaksi_grouped = [];
foreach ($details as $detail) {
    $id_transaksi = $detail['id_transaksi'];
    if (!isset($transaksi_grouped[$id_transaksi])) {
        $transaksi_grouped[$id_transaksi] = [
            'id' => $id_transaksi,
            'total_harga' => $detail['total_harga'],
            'status' => $detail['status'],
            'tanggal_transaksi' => $detail['tanggal_transaksi'],
            'metode_pembayaran' => $detail['metode_pembayaran'],
            'potongan_harga' => $detail['potongan_harga'],
            'kode_voucher' => $detail['kode_voucher_terpakai'],
            'items' => []
        ];
    }
    $transaksi_grouped[$id_transaksi]['items'][] = $detail;
}

function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <style>
        .bg-primary { background-color: #f59e0b; } .text-primary { color: #b45309; } .hover\:text-primary:hover { color: #f59e0b; } .details-content { max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out, padding 0.5s ease-in-out; } .details-content.open { max-height: 500px; padding-top: 1rem; padding-bottom: 1rem; } .details-toggle-icon { transition: transform 0.3s ease-in-out; } .details-toggle-icon.open { transform: rotate(180deg); }
        body { padding-top: 80px; }
    </style>
</head>
<body class="bg-pink-50 min-h-screen pb-24 font-sans">

    <header class="fixed top-0 inset-x-0 bg-amber-500 p-4 text-white shadow-lg z-30">
        <div class="w-full max-w-screen-xl mx-auto flex justify-between items-center">
            
            <h1 class="text-xl font-bold">Riwayat Pesanan</h1>
            
            <div class="flex items-center gap-8">
                <nav class="hidden md:flex items-center gap-6 text-base font-semibold">
                    <a href="index.php" class="hover:text-amber-200 transition-colors">Home</a>
                    <a href="riwayat.php" class="text-amber-200 font-bold">Riwayat</a>
                    <a href="chat.php" class="hover:text-amber-200 transition-colors">Chat</a>
                    <a href="profil.php" class="hover:text-amber-200 transition-colors">Profil</a>
                </nav>

                <a href="keranjang.php" class="p-2 relative">
                    <img src="cart.png" alt="Keranjang Belanja" class="w-10 h-10">
                    <span id="cart-notification" class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-amber-500 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                        <?= $cart_count ?>
                    </span>
                </a>
            </div>
        </div>
    </header>

    <main class="p-4 max-w-screen-xl mx-auto mt-6">
        <?php if (empty($transaksi_grouped)): ?>
            <div class="text-center mt-20">
                <i class="ph-receipt text-6xl text-gray-400"></i>
                <p class="mt-4 text-gray-600">Anda belum memiliki riwayat transaksi.</p>
                <a href="index.php" class="mt-6 inline-block px-6 py-2 bg-primary text-white rounded-lg font-bold hover:bg-amber-600 transition-colors">
                    Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($transaksi_grouped as $t): ?>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden" data-id-transaksi="<?= $t['id'] ?>">
                        <div class="p-4 border-b">
                            <div class="flex justify-between items-center">
                                <?php $professional_order_id = "FF-" . substr(strtoupper(hash('sha1', $t['id'] . $t['tanggal_transaksi'])), 0, 8); ?>
                                <h4 class="font-bold text-gray-800">Pesanan <?= htmlspecialchars($professional_order_id) ?></h4>
                                <span id="status-<?= $t['id'] ?>" class="status-badge text-xs font-semibold px-2 py-1 rounded-full
                                    <?= $t['status'] == 'Selesai' ? 'bg-green-100 text-green-800' : ($t['status'] == 'Dibatalkan' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                    <?= htmlspecialchars($t['status']) ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?= date('d F Y, H:i', strtotime($t['tanggal_transaksi'])) ?></p>
                        </div>
                        <div class="details-content px-4 space-y-2">
                           <?php
                                $total_harga_asli = 0;
                           ?>
                           <?php foreach ($t['items'] as $item): ?>
                               <div class="flex justify-between text-sm">
                                   <p class="text-gray-700">
                                       <?= htmlspecialchars($item['nama_produk'] ?? '(Produk Telah Dihapus)') ?>
                                       <span class="text-gray-500">(x<?= $item['jumlah'] ?>)</span>
                                   </p>
                                   <div class="flex items-baseline gap-2">
                                        <?php
                                            $harga_saat_transaksi = $item['harga_saat_transaksi'];
                                            $harga_asli_produk = $item['harga_asli_produk'];
                                            $total_harga_asli += $harga_asli_produk * $item['jumlah'];
                                        ?>
                                        <?php if (isset($harga_asli_produk) && $harga_saat_transaksi < $harga_asli_produk): ?>
                                            <p class="text-gray-600 font-semibold">Rp <?= formatRupiah($harga_saat_transaksi * $item['jumlah']) ?></p>
                                            <p class="text-gray-400 line-through text-xs">Rp <?= formatRupiah($harga_asli_produk * $item['jumlah']) ?></p>
                                        <?php else: ?>
                                            <p class="text-gray-600">Rp <?= formatRupiah($harga_saat_transaksi * $item['jumlah']) ?></p>
                                        <?php endif; ?>
                                   </div>
                               </div>
                           <?php endforeach; ?>

                           <div class="border-t border-gray-200 mt-3 pt-3 space-y-1 text-sm">
                                <?php
                                    $subtotal_setelah_diskon_produk = $t['total_harga'] + $t['potongan_harga'];
                                ?>
                                <?php if($subtotal_setelah_diskon_produk < $total_harga_asli): ?>
                                    <div class="flex justify-between"><span class="text-gray-600">Total Harga Asli</span><span class="text-gray-800 font-medium line-through">Rp <?= formatRupiah($total_harga_asli) ?></span></div>
                                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="text-gray-800 font-medium">Rp <?= formatRupiah($subtotal_setelah_diskon_produk) ?></span></div>
                                <?php else: ?>
                                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="text-gray-800 font-medium">Rp <?= formatRupiah($subtotal_setelah_diskon_produk) ?></span></div>
                                <?php endif; ?>

                                 <?php if ($t['potongan_harga'] > 0): ?>
                                 <div class="flex justify-between"><span class="text-green-600">Diskon Voucher (<?= htmlspecialchars($t['kode_voucher'] ?? 'Voucher') ?>)</span><span class="text-green-600 font-medium">- Rp <?= formatRupiah($t['potongan_harga']) ?></span></div>
                                 <?php endif; ?>
                           </div>
                        </div>
                        <div class="p-4 bg-gray-50 flex justify-between items-center">
                            <div class="text-xs text-gray-600"><button class="details-toggle text-primary font-semibold flex items-center gap-1"><span>Lihat Detail</span><i class="ph-caret-down-bold details-toggle-icon"></i></button></div>
                            <div class="text-right"><p class="text-xs text-gray-600">Total Bayar</p><p class="font-bold text-lg text-primary">Rp <?= formatRupiah($t['total_harga']) ?></p></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 md:hidden">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-around py-2">
            <a href="index.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-semibold">Beranda</span>
            </a>
            <a href="riwayat.php" class="flex flex-col items-center py-2 text-amber-500 ">
                <i class="fas fa-history text-xl mb-1"></i>
                <span class="text-xs font-semibold">Riwayat</span>
            </a>
            <a href="chat.php" class="flex flex-col items-center py-2 relative text-gray-500 hover:text-amber-500 transition-colors">
    <i class="fas fa-comments text-xl mb-1"></i>
    <span class="text-xs font-semibold">Chat</span>
    <span id="chat-badge-mobile" class="absolute top-0 right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">0</span>
</a>

            <a href="profil.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-semibold">Profil</span>
            </a>
        </div>
    </div>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsionalitas "Lihat Detail"
    document.querySelectorAll('.details-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.bg-white');
            if (!card) return;
            const content = card.querySelector('.details-content');
            const icon = this.querySelector('.details-toggle-icon');
            const textSpan = this.querySelector('span');
            const isOpen = content.classList.toggle('open');
            icon.classList.toggle('open');
            if (textSpan) {
                textSpan.textContent = isOpen ? 'Sembunyikan Detail' : 'Lihat Detail';
            }
        });
    });

    // Fungsionalitas Status Real-time (Tidak berubah)
    const transactionIds = Array.from(document.querySelectorAll('[data-id-transaksi]')).map(card => card.dataset.idTransaksi);
    function updateStatusBadgeClass(badgeElement, newStatus) {
        badgeElement.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
        if (newStatus === 'Selesai') {
            badgeElement.classList.add('bg-green-100', 'text-green-800');
        } else if (newStatus === 'Dibatalkan') {
            badgeElement.classList.add('bg-red-100', 'text-red-800');
        } else {
            badgeElement.classList.add('bg-yellow-100', 'text-yellow-800');
        }
    }
    const checkStatus = () => {
        if (transactionIds.length === 0) return;
        fetch('cek_status_terbaru.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(transactionIds)
        })
        .then(response => {
            if (!response.ok) {
                console.error('Network response was not ok: ' + response.statusText);
                clearInterval(statusInterval);
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.error) {
                console.error('Server error:', data ? data.error : 'Invalid response');
                return;
            }
            for (const id in data) {
                const newStatus = data[id];
                const badge = document.getElementById(`status-${id}`);
                if (badge && badge.textContent.trim() !== newStatus) {
                    badge.textContent = newStatus;
                    updateStatusBadgeClass(badge, newStatus);
                }
            }
        })
        .catch(error => {
            console.error('Gagal mengecek status:', error);
            clearInterval(statusInterval);
        });
    };
    let statusInterval;
    if (transactionIds.length > 0) {
        statusInterval = setInterval(checkStatus, 5000);
    }
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