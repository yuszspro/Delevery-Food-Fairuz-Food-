<?php
session_start();

// Pastikan user sudah login dan memiliki role 'user'
if (!isset($_SESSION['user_username']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}
if (empty($_POST['selected'])) {
    header("Location: keranjang.php");
    exit;
}

require 'koneksi.php';
$id_user = $_SESSION['user_id'];
$selected_ids = $_POST['selected'];

// Ambil data voucher dari POST
$id_user_voucher = !empty($_POST['id_user_voucher']) ? (int)$_POST['id_user_voucher'] : null;
$potongan_harga = !empty($_POST['potongan_harga_final']) ? (int)$_POST['potongan_harga_final'] : 0;
$kode_voucher = !empty($_POST['kode_voucher_final']) ? htmlspecialchars($_POST['kode_voucher_final']) : '';

$placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
$types = str_repeat('i', count($selected_ids));

// Query mengambil harga asli dan harga diskon
$query = "SELECT k.id AS id_keranjang, p.id AS id_produk, p.nama, p.harga, p.harga_diskon, p.gambar, k.jumlah 
          FROM keranjang k 
          JOIN produk p ON p.id = k.id_produk 
          WHERE k.id_user = ? AND k.id IN ($placeholders)";

$stmt = $conn->prepare($query);
$stmt->bind_param("i" . $types, $id_user, ...$selected_ids);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

$subtotal = 0;
foreach ($items as $item) {
    // Kalkulasi subtotal menggunakan harga diskon jika ada
    $harga_jual = (isset($item['harga_diskon']) && $item['harga_diskon'] > 0) ? $item['harga_diskon'] : $item['harga'];
    $subtotal += $harga_jual * $item['jumlah'];
}

$total_akhir = $subtotal - $potongan_harga;
if ($total_akhir < 0) $total_akhir = 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .bg-primary { background-color: #f59e0b; } .text-primary { color: #f59e0b; } .border-primary { border-color: #f59e0b; } .accent-primary { accent-color: #f59e0b; } .ring-primary:focus { --tw-ring-color: #f59e0b; } #success-overlay { transition: opacity 0.3s ease-in-out; } .checkmark__circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 3; stroke-miterlimit: 10; stroke: #f59e0b; fill: none; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; } .checkmark { width: 100px; height: 100px; border-radius: 50%; display: block; stroke-width: 4; stroke: #fff; stroke-miterlimit: 10; margin: 10% auto; box-shadow: inset 0px 0px 0px #f59e0b; animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; } .checkmark__check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; } @keyframes stroke { 100% { stroke-dashoffset: 0; } } @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } } @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 50px #f59e0b; } }
        /* [PERBAIKAN] Style untuk transisi toast */
        #toast {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-pink-50 font-sans">

    <div id="checkout-content">
        <header class="bg-primary p-5 flex items-center text-white font-semibold text-lg shadow-md sticky top-0 z-10">
            <a href="keranjang.php" class="mr-4">&larr;</a>
            <h1>Konfirmasi Pesanan</h1>
        </header>

        <main class="p-4 pb-32">
            <h2 class="text-xl font-bold text-gray-800 mb-3">Ringkasan Pesanan</h2>
            <div class="bg-white rounded-xl shadow p-4 space-y-3">
                <?php foreach ($items as $item): 
                    $harga_jual_item = (isset($item['harga_diskon']) && $item['harga_diskon'] > 0) ? $item['harga_diskon'] : $item['harga'];
                ?>
                <div class="flex items-center">
                    <img src="../uploads/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="w-14 h-14 rounded-lg object-cover mr-4" />
                    <div class="flex-grow">
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama']) ?></p>
                        <div class="text-sm text-gray-500 flex items-baseline gap-2">
                           <span><?= $item['jumlah'] ?> x </span>
                            <?php if (isset($item['harga_diskon']) && $item['harga_diskon'] > 0): ?>
                                <p class="text-red-600 font-semibold">Rp<?= number_format($item['harga_diskon'], 0, ',', '.') ?></p>
                                <p class="line-through text-xs">Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                            <?php else: ?>
                                <p>Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="font-bold text-gray-800">Rp <?= number_format($harga_jual_item * $item['jumlah'], 0, ',', '.') ?></p>
                </div>
                <?php endforeach; ?>
                
                <div class="border-t pt-3 space-y-1">
                    <div class="flex justify-between font-semibold">
                        <span>Subtotal</span>
                        <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    <?php if ($potongan_harga > 0): ?>
                    <div class="flex justify-between font-semibold text-green-600">
                        <span>Diskon (<?= $kode_voucher ?>)</span>
                        <span>- Rp <?= number_format($potongan_harga, 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                 <div class="border-t mt-2 pt-2 flex justify-between font-bold text-lg">
                    <span>Total Akhir</span>
                    <span class="text-primary">Rp <?= number_format($total_akhir, 0, ',', '.') ?></span>
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-800 mt-6 mb-3">Metode Pembayaran</h2>
            <form id="paymentForm">
                <div class="space-y-3">
                    <?php
                    $payment_methods = ['COD' => 'Cash on Delivery', 'DANA' => 'DANA', 'OVO' => 'OVO', 'GoPay' => 'GoPay'];
                    $banks = ['BCA', 'BRI', 'Mandiri', 'BNI', 'CIMB Niaga'];
                    ?>
                    <?php foreach ($payment_methods as $key => $value): ?>
                    <label class="flex items-center bg-white rounded-xl shadow p-4 cursor-pointer hover:bg-amber-50 transition">
                        <input type="radio" name="metode_pembayaran" value="<?= $key ?>" class="h-5 w-5 accent-primary" required>
                        <span class="ml-4 font-semibold text-gray-700"><?= $value ?></span>
                    </label>
                    <?php endforeach; ?>
                    <div>
                        <label class="flex items-center bg-white rounded-xl shadow p-4 cursor-pointer hover:bg-amber-50 transition">
                            <input type="radio" id="debit_radio" name="metode_pembayaran" value="Debit" class="h-5 w-5 accent-primary" required>
                            <span class="ml-4 font-semibold text-gray-700">Kartu Debit</span>
                        </label>
                        <div id="debit_details" class="hidden mt-2 mx-4">
                            <select name="jenis_debit" class="w-full p-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 ring-primary">
                                <option value="" disabled selected>-- Pilih Bank --</option>
                                <?php foreach($banks as $bank): ?>
                                <option value="<?= $bank ?>"><?= $bank ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </main>

        <footer class="fixed bottom-0 left-0 w-full bg-white p-4 border-t">
            <div class="flex justify-between items-center mb-2 px-1">
                <span class="text-gray-600 font-semibold">Total Pembayaran</span>
                <span class="text-primary font-bold text-xl">Rp <?= number_format($total_akhir, 0, ',', '.') ?></span>
            </div>
            <button id="payButton" class="w-full py-3 bg-primary text-white font-bold rounded-lg hover:bg-amber-600 transition text-lg shadow-lg">
                Bayar Sekarang
            </button>
        </footer>
    </div>

    <div id="success-overlay" class="hidden fixed inset-0 bg-pink-50 flex items-center justify-center z-50 opacity-0">
        <div class="text-center p-8">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>
            <h1 class="text-3xl font-bold text-gray-800 mt-4">Pesanan Berhasil!</h1>
            <p class="text-gray-600 mt-2">Terima kasih, pesananmu sedang kami proses.</p>
            <p class="text-sm text-gray-400 mt-8">Anda akan dialihkan ke halaman utama...</p>
        </div>
    </div>

    <div id="toast" class="hidden fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-gray-800 text-center text-white px-6 py-3 rounded-full font-bold shadow-lg z-50 opacity-0 -translate-y-10"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentRadios = document.querySelectorAll('input[name="metode_pembayaran"]');
    const debitDetails = document.getElementById('debit_details');
    const debitSelect = debitDetails.querySelector('select');
    const payButton = document.getElementById('payButton');

    function showToast(message) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translate(-50%, 0)'; }, 10);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -20px)';
            setTimeout(() => { toast.classList.add('hidden'); }, 300);
        }, 3000);
    }

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'Debit' && radio.checked) {
                debitDetails.classList.remove('hidden');
                debitSelect.setAttribute('required', 'required');
            } else {
                debitDetails.classList.add('hidden');
                debitSelect.removeAttribute('required');
                debitSelect.value = '';
            }
        });
    });

    payButton.addEventListener('click', function(event) {
        event.preventDefault();
        const selectedRadio = document.querySelector('input[name="metode_pembayaran"]:checked');
        
        if (!selectedRadio) {
            showToast('pilih metode pembayaran.');
            return;
        }
        if (selectedRadio.value === 'Debit' && debitSelect.value === '') {
            showToast('Silakan pilih bank untuk kartu debit.');
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Memproses...';
        
        const formData = new FormData();
        const itemsToProcess = [];
        <?php foreach ($items as $item): ?>
        itemsToProcess.push({
            id_produk: '<?= $item['id_produk'] ?>',
            nama: '<?= json_encode($item['nama']) ?>',
            jumlah: '<?= $item['jumlah'] ?>'
        });
        <?php endforeach; ?>
        
        formData.append('items_json', JSON.stringify(itemsToProcess));

        <?php foreach ($selected_ids as $id_keranjang) : ?>
        formData.append('keranjang_ids[]', '<?= $id_keranjang ?>');
        <?php endforeach; ?>
        
        formData.append('metode_pembayaran', selectedRadio.value);
        if (selectedRadio.value === 'Debit') {
            formData.append('jenis_debit', debitSelect.value);
        }
        
        formData.append('id_user_voucher', '<?= $id_user_voucher ?>');

        fetch('proses_transaksi.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Cek jika respons bukan JSON atau ada error server
            if (!response.ok) {
                return response.text().then(text => { 
                    // Tampilkan pesan error PHP jika ada, untuk debugging
                    console.error('Server Response:', text);
                    throw new Error('Server merespons dengan error. Silakan cek konsol.');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                showSuccessAndRedirect();
            } else {
                // Menampilkan pesan error yang dikirim dari server
                showToast(data.message || 'Terjadi kesalahan yang tidak diketahui.');
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Gagal terhubung ke server.');
            payButton.disabled = false;
            payButton.textContent = 'Bayar Sekarang';
        });
    });

    function showSuccessAndRedirect() {
        const checkoutContent = document.getElementById('checkout-content');
        const successOverlay = document.getElementById('success-overlay');
        checkoutContent.style.display = 'none';
        successOverlay.classList.remove('hidden');
        setTimeout(() => successOverlay.classList.remove('opacity-0'), 10);
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 4000);
    }
});
</script>
</body>
</html>