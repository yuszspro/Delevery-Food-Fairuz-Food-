<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

$unread_nav_query = "SELECT COUNT(id) as total_unread FROM chat WHERE is_read = 0 AND pengirim_role = 'user'";
$unread_nav_result = mysqli_query($conn, $unread_nav_query);
$total_unread_nav = mysqli_fetch_assoc($unread_nav_result)['total_unread'] ?? 0;

$query = "
    SELECT 
        t.id AS id_transaksi, t.total_harga, t.status, t.tanggal_transaksi,
        t.metode_pembayaran, t.potongan_harga, t.kode_voucher_terpakai,
        u.username, u.alamat,
        dt.jumlah, dt.harga_saat_transaksi, p.nama AS nama_produk
    FROM transaksi t
    JOIN users u ON t.id_user = u.id
    JOIN detail_transaksi dt ON t.id = dt.id_transaksi
    JOIN produk p ON dt.id_produk = p.id
    ORDER BY t.tanggal_transaksi DESC, t.id DESC
";
$result = mysqli_query($conn, $query);
if (!$result) die("Query Error: " . mysqli_error($conn));
$details = mysqli_fetch_all($result, MYSQLI_ASSOC);

$transaksi_grouped = [];
foreach ($details as $detail) {
    $id_transaksi = $detail['id_transaksi'];
    if (!isset($transaksi_grouped[$id_transaksi])) {
        $transaksi_grouped[$id_transaksi] = [
            'id' => $id_transaksi, 'username' => $detail['username'], 'alamat' => $detail['alamat'],
            'total_harga' => $detail['total_harga'], 'status' => $detail['status'],
            'tanggal_transaksi' => $detail['tanggal_transaksi'], 'metode_pembayaran' => $detail['metode_pembayaran'],
            'potongan_harga' => $detail['potongan_harga'], 'kode_voucher' => $detail['kode_voucher_terpakai'],
            'items' => []
        ];
    }
    $transaksi_grouped[$id_transaksi]['items'][] = $detail;
}
$possible_statuses = ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Manajemen Pesanan | Fairuz Food Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .details-content { max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out, padding 0.5s ease-in-out; }
        .details-content.open { max-height: 600px; padding-top: 1rem; padding-bottom: 1rem; }
        .details-toggle i { transition: transform 0.3s ease-in-out; }
        .details-toggle.open i { transform: rotate(180deg); }
        #new-order-notif { transition: all 0.5s ease-in-out; }
    </style>
</head>
<body class="bg-gray-100 h-screen">
    <div class="flex h-full">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 flex flex-col">
            <div class="p-4 border-b border-gray-700"><h1 class="text-2xl font-bold">Admin Panel</h1></div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="daftar_pesanan.php" class="block py-2.5 px-4 rounded transition bg-amber-500 font-semibold">Pesanan</a>
                <a href="index.php" class="block py-2.5 px-4 rounded transition hover:bg-gray-700">Produk</a>
                <a href="chat.php" class="relative block py-2.5 px-4 rounded transition hover:bg-gray-700">Chat<?php if ($total_unread_nav > 0): ?><span class="absolute top-3 right-3 w-2.5 h-2.5 bg-orange-500 rounded-full animate-pulse"></span><?php endif; ?></a>
            </nav>
            <div class="p-4 border-t border-gray-700"><a href="../login/logout.php" class="block text-center py-2.5 px-4 rounded transition bg-red-600 hover:bg-red-700">Logout</a></div>
        </aside>

        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            <header class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Daftar Pesanan Masuk</h2>
                <div class="flex items-center gap-3 font-semibold text-gray-600"><i class="ph-user-circle text-2xl"></i><span><?= htmlspecialchars($_SESSION['admin_username']) ?></span></div>
            </header>

            <div id="new-order-notif" class="hidden transform -translate-y-16 opacity-0 mb-6 bg-amber-500 text-white font-bold p-4 rounded-lg shadow-lg text-center cursor-pointer">
                Ada Pesanan Baru! Klik untuk Memuat Ulang.
            </div>

            <div class="space-y-4" data-latest-id-on-page="<?= empty($transaksi_grouped) ? 0 : reset($transaksi_grouped)['id'] ?>">
                <?php if (empty($transaksi_grouped)): ?>
                    <div class="p-6 text-center text-gray-500 bg-white rounded-lg shadow-md">Belum ada pesanan yang masuk.</div>
                <?php else: ?>
                    <?php foreach ($transaksi_grouped as $t): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden" data-id-transaksi-card="<?= $t['id'] ?>">
                        <div class="p-4 border-b">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-gray-900">Pesanan <?= "FF-" . substr(strtoupper(hash('sha1', $t['id'] . $t['tanggal_transaksi'])), 0, 8); ?></h4>
                                    <p class="text-xs text-gray-500">Oleh: <span class="font-medium text-gray-700"><?= htmlspecialchars($t['username']) ?></span> - <?= date('d M Y, H:i', strtotime($t['tanggal_transaksi'])) ?></p>
                                </div>
                                <div class="flex items-center gap-2 mt-2 sm:mt-0">
                                    <select class="status-select border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition" data-id="<?= $t['id'] ?>">
                                        <?php foreach ($possible_statuses as $status): ?>
                                            <option value="<?= $status ?>" <?= $t['status'] == $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="status-indicator text-green-500 opacity-0 transition-opacity"><i class="ph-check-circle-fill text-xl"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="details-content px-4">
                            <div class="py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h5 class="font-semibold mb-2 text-gray-700">Detail Item:</h5>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <?php foreach ($t['items'] as $item): ?>
                                        <li class="flex justify-between">
                                            <span><?= htmlspecialchars($item['nama_produk']) ?> <span class="text-gray-500">x<?= $item['jumlah'] ?></span></span>
                                            <span class="font-medium">Rp <?= formatRupiah($item['harga_saat_transaksi'] * $item['jumlah']) ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="border-t mt-3 pt-3 space-y-1 text-sm">
                                        <h5 class="font-semibold mb-2 text-gray-700">Rincian Pembayaran:</h5>
                                        <?php $subtotal = $t['total_harga'] + $t['potongan_harga']; ?>
                                        <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-medium text-gray-800">Rp <?= formatRupiah($subtotal) ?></span></div>
                                        <?php if ($t['potongan_harga'] > 0): ?>
                                        <div class="flex justify-between text-green-600">
                                            <span class="font-medium">Diskon (<?= htmlspecialchars($t['kode_voucher'] ?? 'Voucher Dihapus') ?>)</span>
                                            <span class="font-medium">- Rp <?= formatRupiah($t['potongan_harga']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="border-t md:border-t-0 md:border-l pt-6 md:pt-0 md:pl-6">
                                    <h5 class="font-semibold mb-2 text-gray-700">Info Pengiriman:</h5>
                                    <p class="text-sm text-gray-600">Metode: <span class="font-medium text-gray-800"><?= htmlspecialchars($t['metode_pembayaran']) ?></span></p>
                                    <p class="text-sm text-gray-600 mt-2">Alamat:</p>
                                    <p class="text-sm font-normal text-gray-800"><?= nl2br(htmlspecialchars($t['alamat'] ?: 'Alamat belum diatur.')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 flex justify-between items-center">
                            <button class="details-toggle text-amber-600 font-semibold text-sm flex items-center gap-1 hover:text-amber-700">
                                <span>Lihat Detail</span><i class="ph-caret-down-bold text-base"></i>
                            </button>
                            <div class="text-right">
                                <span class="text-sm text-gray-600">Total:</span>
                                <span class="font-bold text-lg text-gray-800 ml-1">Rp <?= formatRupiah($t['total_harga']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listener untuk admin mengubah status secara manual
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const transactionId = this.dataset.id;
            const newStatus = this.value;
            const indicator = this.nextElementSibling;
            const formData = new FormData();
            formData.append('id_transaksi', transactionId);
            formData.append('status', newStatus);
            fetch('update_status.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    indicator.classList.remove('opacity-0');
                    setTimeout(() => indicator.classList.add('opacity-0'), 2000);
                } else { alert('Gagal: ' + data.message); }
            }).catch(() => alert('Error terhubung ke server.'));
        });
    });

    // Event listener untuk tombol dropdown detail
    document.querySelectorAll('.details-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.bg-white');
            const content = card.querySelector('.details-content');
            const icon = this.querySelector('i');
            content.classList.toggle('open');
            this.classList.toggle('open');
            if (icon) icon.classList.toggle('rotate-180');
        });
    });

    // --- BLOK KODE LENGKAP UNTUK AUTO-UPDATE REAL-TIME ---
    const orderContainer = document.querySelector('[data-latest-id-on-page]');
    const newOrderNotif = document.getElementById('new-order-notif');
    
    if (orderContainer) {
        let latestIdOnPage = parseInt(orderContainer.dataset.latestIdOnPage, 10);
        
        newOrderNotif.addEventListener('click', () => window.location.reload());

        const checkOrderUpdates = () => {
            const transactionIdsOnPage = Array.from(document.querySelectorAll('[data-id-transaksi-card]')).map(el => el.dataset.idTransaksiCard);
            
            fetch('cek_update_pesanan.php', { // Menggunakan file baru yang lebih cerdas
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: transactionIdsOnPage })
            })
            .then(response => response.json())
            .then(data => {
                if (!data) return;

                // 1. Cek Pesanan Baru
                if (data.latest_order_id > latestIdOnPage) {
                    newOrderNotif.classList.remove('hidden');
                    setTimeout(() => {
                        newOrderNotif.classList.remove('-translate-y-16', 'opacity-0');
                    }, 10);
                }

                // 2. Cek Perubahan Status
                const statuses = data.updated_statuses;
                for (const id in statuses) {
                    const newStatus = statuses[id];
                    const selectElement = document.querySelector(`.status-select[data-id="${id}"]`);
                    if (selectElement && selectElement.value !== newStatus) {
                        selectElement.value = newStatus;
                    }
                }
            })
            .catch(error => console.error('Gagal mengecek update pesanan:', error));
        };
        
        // Jalankan pengecekan setiap 8 detik
        setInterval(checkOrderUpdates, 8000);
    }
});
</script>
</body>
</html>