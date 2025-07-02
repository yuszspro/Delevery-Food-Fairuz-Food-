<?php
session_start();
require 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Query untuk mengambil semua voucher yang dimiliki pengguna
$query = "
    SELECT 
        v.kode_voucher,
        v.deskripsi,
        v.potongan_harga,
        v.min_pembelian,
        v.berlaku_hingga,
        uv.status
    FROM user_vouchers uv
    JOIN vouchers v ON uv.id_voucher = v.id
    WHERE uv.id_user = ?
    ORDER BY uv.status ASC, v.berlaku_hingga ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$vouchers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Saya</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-pink-50 min-h-screen">

    <header class="bg-amber-500 p-5 flex items-center text-white font-semibold text-lg shadow-md sticky top-0 z-20">
        <a href="profil.php" class="mr-4 text-xl">&larr;</a>
        <h1>Voucher Saya</h1>
    </header>

    <main class="p-4">
        <?php if (empty($vouchers)): ?>
            <div class="text-center mt-20">
                <i class="ph-ticket text-6xl text-gray-400"></i>
                <p class="mt-4 text-gray-600">Anda belum memiliki voucher.</p>
                <p class="text-sm text-gray-500">Ayo cari voucher menarik!</p>
                <a href="poin_voucher.php" class="mt-6 inline-block px-6 py-2 bg-amber-500 text-white rounded-lg font-bold hover:bg-amber-600 transition-colors">
                    Cari Voucher
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($vouchers as $v): ?>
                    <?php
                        $is_terpakai = $v['status'] === 'terpakai';
                        $is_kadaluarsa = $v['berlaku_hingga'] && (new DateTime($v['berlaku_hingga']) < new DateTime('today'));
                        $is_disabled = $is_terpakai || $is_kadaluarsa;
                    ?>
                    <div class="relative bg-white rounded-lg shadow-sm overflow-hidden flex <?= $is_disabled ? 'opacity-50' : '' ?>">
                        <div class="bg-amber-400 text-white flex flex-col items-center justify-center p-4 w-1/3 text-center">
                            <span class="text-xs">Diskon</span>
                            <span class="text-2xl lg:text-3xl font-bold">Rp</span>
                            <span class="text-4xl lg:text-5xl font-bold -mt-2"><?= number_format($v['potongan_harga'] / 1000) ?>RB</span>
                        </div>
                        <div class="p-4 flex-1">
                            <h3 class="font-bold text-gray-800"><?= htmlspecialchars($v['deskripsi']) ?></h3>
                            <p class="text-sm text-gray-600 mt-1">Min. belanja Rp <?= formatRupiah($v['min_pembelian']) ?></p>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="ph-clock align-middle"></i> 
                                Berlaku hingga: <?= date('d M Y', strtotime($v['berlaku_hingga'])) ?>
                            </p>
                        </div>
                        
                        <?php if ($is_disabled): ?>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                            <div class="border-4 <?= $is_terpakai ? 'border-gray-500 text-gray-500' : 'border-red-500 text-red-500' ?> rounded-lg p-2 transform -rotate-12">
                                <h4 class="text-xl font-bold tracking-wider uppercase"><?= $is_terpakai ? 'Terpakai' : 'Kadaluarsa' ?></h4>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>