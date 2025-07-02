<?php
session_start();
require '../admin/koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Query canggih untuk mengambil semua voucher aktif DAN menandai mana yang sudah dimiliki user
$query = "
    SELECT 
        v.id,
        v.kode_voucher,
        v.deskripsi,
        v.potongan_harga,
        v.min_pembelian,
        v.berlaku_hingga,
        (CASE WHEN uv.id_user IS NOT NULL THEN 1 ELSE 0 END) AS sudah_diklaim
    FROM 
        vouchers v
    LEFT JOIN 
        user_vouchers uv ON v.id = uv.id_voucher AND uv.id_user = ?
    WHERE
        v.berlaku_hingga >= CURDATE() OR v.berlaku_hingga IS NULL
    ORDER BY
        sudah_diklaim ASC, v.id ASC
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
    <title>Klaim Voucher</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-pink-50 min-h-screen">

    <header class="bg-amber-500 p-5 flex items-center text-white font-semibold text-lg shadow-md sticky top-0 z-20">
        <a href="profil.php" class="mr-4 text-xl">&larr;</a>
        <h1>Voucher Fairuz Food</h1>
    </header>

    <main class="p-4 pb-20">
        <?php if (empty($vouchers)): ?>
            <div class="text-center mt-20">
                <i class="ph-ticket text-6xl text-gray-400"></i>
                <p class="mt-4 text-gray-600">Mohon maaf, saat ini tidak ada voucher yang tersedia.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($vouchers as $v): ?>
                    <div class="bg-white rounded-lg shadow-sm flex">
                        <div class="bg-amber-100 p-4 flex flex-col items-center justify-center w-28 border-r-2 border-dashed border-amber-200">
                            <img src="ff.png" alt="logo" class="w-16 h-16 rounded-full">
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($v['deskripsi']) ?></h3>
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="ph-clock align-middle"></i> 
                                    Berlaku hingga: <?= date('d M Y', strtotime($v['berlaku_hingga'])) ?>
                                </p>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <?php if ($v['sudah_diklaim']): ?>
                                    <button disabled class="bg-gray-300 text-gray-500 font-bold text-sm py-2 px-4 rounded-lg cursor-not-allowed">
                                        Telah Diklaim
                                    </button>
                                <?php else: ?>
                                    <button class="btn-klaim bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm py-2 px-4 rounded-lg transition" data-voucher-id="<?= $v['id'] ?>">
                                        Klaim
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div id="toast" class="hidden fixed bottom-10 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 text-center text-white px-6 py-3 rounded-full font-semibold shadow-lg transition-all duration-300 opacity-0 -translate-y-4">
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('toast');

    function showToast(message, isSuccess = true) {
        toast.textContent = message;
        toast.className = 'hidden fixed bottom-10 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 text-center text-white px-6 py-3 rounded-full font-semibold shadow-lg transition-all duration-300 opacity-0 -translate-y-4'; // reset
        toast.classList.add(isSuccess ? 'bg-green-500' : 'bg-red-500');
        
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.remove('opacity-0', '-translate-y-4');
        }, 10);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', '-translate-y-4');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    }

    document.querySelectorAll('.btn-klaim').forEach(button => {
        button.addEventListener('click', function () {
            const voucherId = this.dataset.voucherId;
            const originalButton = this;
            
            // Tampilkan status loading
            originalButton.textContent = 'Memproses...';
            originalButton.disabled = true;

            const formData = new FormData();
            formData.append('id_voucher', voucherId);

            fetch('proses_klaim_voucher.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message, true);
                    // Ubah tombol menjadi "Telah Diklaim"
                    originalButton.textContent = 'Telah Diklaim';
                    originalButton.classList.remove('bg-amber-500', 'hover:bg-amber-600');
                    originalButton.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                } else {
                    showToast(data.message, false);
                    // Kembalikan tombol ke keadaan semula jika gagal
                    originalButton.textContent = 'Klaim';
                    originalButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan jaringan.', false);
                originalButton.textContent = 'Klaim';
                originalButton.disabled = false;
            });
        });
    });
});
</script>
</body>
</html>