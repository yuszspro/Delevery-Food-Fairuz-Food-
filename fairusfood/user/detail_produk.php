<?php
session_start();
include '../admin/koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_username']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil ID produk dari URL, pastikan itu adalah angka (integer)
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data produk dari database menggunakan prepared statement
$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result_produk = $stmt->get_result();
$produk = $result_produk->fetch_assoc();
$stmt->close();

if (!$produk) {
    die("Produk tidak ditemukan.");
}

// Hitung kembali item di keranjang untuk header
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail - <?= htmlspecialchars($produk['nama']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .animate-pop-in { animation: popIn 0.3s ease-out forwards; }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        body { padding-bottom: 100px; } 
        #toast {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

<header class="absolute top-0 inset-x-0 p-4 z-30 flex items-center justify-between">
    <a href="#" onclick="window.history.back(); return false;" class="w-10 h-10 bg-black bg-opacity-40 rounded-full flex items-center justify-center text-white hover:bg-opacity-60 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <a href="keranjang.php" class="relative">
        <div class="w-10 h-10 bg-black bg-opacity-40 rounded-full flex items-center justify-center p-2 text-white hover:bg-opacity-60 transition">
            <img src="cart.png" alt="Keranjang" class="w-full h-full object-contain">
        </div>
        <span id="cart-notification" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-white <?= $cart_count > 0 ? '' : 'hidden' ?>">
            <?= $cart_count ?>
        </span>
    </a>
</header>

<main>
    <div class="w-full h-80 sm:h-96 bg-white">
        <img src="../uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama']) ?>" class="w-full h-full object-cover">
    </div>

    <div class="relative bg-gray-100 p-5 -mt-5 rounded-t-3xl animate-pop-in">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= htmlspecialchars($produk['nama']) ?></h2>
        
        <div class="flex items-baseline gap-3 mt-2">
            <?php if (isset($produk['harga_diskon']) && $produk['harga_diskon'] > 0): ?>
                <p class="text-2xl sm:text-3xl font-bold text-red-600">Rp <?= number_format($produk['harga_diskon'], 0, ',', '.') ?></p>
                <p class="text-lg text-gray-500 line-through">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
            <?php else: ?>
                <p class="text-2xl sm:text-3xl font-bold text-amber-600">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold text-gray-800 mb-2">Deskripsi</h3>
            <p class="text-gray-600 leading-relaxed text-sm sm:text-base">
                <?= nl2br(htmlspecialchars($produk['deskripsi'] ?? 'Tidak ada deskripsi untuk produk ini.')) ?>
            </p>
        </div>
    </div>
</main>

<footer class="fixed bottom-0 inset-x-0 bg-white p-4 border-t-2 border-amber-200 shadow-lg z-30">
    <form id="cartForm" class="flex items-center gap-4">
        <div class="flex items-center gap-3">
            <button type="button" id="btn-minus" class="w-10 h-10 rounded-full bg-gray-200 text-gray-700 text-2xl font-bold flex items-center justify-center hover:bg-gray-300 transition">-</button>
            <span id="qtyDisplay" class="text-2xl font-bold text-gray-900 w-8 text-center">1</span>
            <button type="button" id="btn-plus" class="w-10 h-10 rounded-full bg-gray-200 text-gray-700 text-2xl font-bold flex items-center justify-center hover:bg-gray-300 transition">+</button>
        </div>
        
        <input type="hidden" name="id_produk" value="<?= $produk['id'] ?>">
        <input type="hidden" name="jumlah" id="jumlah" value="1">
        <button id="addToCartBtn" type="submit" class="flex-grow bg-amber-500 text-white py-3 rounded-full font-bold text-lg hover:bg-amber-600 transition flex items-center justify-center gap-2">
            <span class="btn-text">+ Keranjang</span>
            <svg class="spinner hidden w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle><path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V8h8a8 8 0 01-8 8H4z"></path></svg>
        </button>
    </form>
</footer>

<div id="toast" class="hidden fixed bottom-28 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-6 py-3 rounded-full font-bold shadow-lg z-50 opacity-0 -translate-y-4"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyDisplay = document.getElementById('qtyDisplay');
    const jumlahInput = document.getElementById('jumlah');
    const btnMinus = document.getElementById('btn-minus');
    const btnPlus = document.getElementById('btn-plus');
    const cartNotification = document.getElementById('cart-notification');

    let jumlah = 1;

    function updateJumlah(change) {
        if (jumlah + change >= 1) {
            jumlah += change;
            qtyDisplay.textContent = jumlah;
            jumlahInput.value = jumlah;
        }
    }

    btnMinus.addEventListener('click', () => updateJumlah(-1));
    btnPlus.addEventListener('click', () => updateJumlah(1));

    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.remove('opacity-0', '-translate-y-4');
        }, 10);

        setTimeout(() => {
            toast.classList.add('opacity-0', '-translate-y-4');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 300);
        }, 2000);
    }

    const form = document.getElementById('cartForm');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const spinner = addToCartBtn.querySelector('.spinner');
    const btnText = addToCartBtn.querySelector('.btn-text');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        spinner.classList.remove('hidden');
        btnText.classList.add('hidden');
        addToCartBtn.disabled = true;

        fetch('tambah_keranjang.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.text())
        .then(text => {
            showToast("Berhasil ditambahkan!");
            let currentCount = parseInt(cartNotification.textContent, 10);
            if (isNaN(currentCount)) currentCount = 0;
            const newCount = currentCount + jumlah;
            cartNotification.textContent = newCount;
            cartNotification.classList.remove('hidden');
        })
        .catch(error => {
            showToast("Gagal menambahkan!");
        })
        .finally(() => {
            spinner.classList.add('hidden');
            btnText.classList.remove('hidden');
            addToCartBtn.disabled = false;
        });
    });
});
</script>

</body>
</html>