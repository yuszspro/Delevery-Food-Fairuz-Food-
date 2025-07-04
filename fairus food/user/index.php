<?php 
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_username']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$username = $_SESSION['user_username'];

// Ambil data user
$stmt = $conn->prepare("SELECT alamat FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result_user = $stmt->get_result();
$user_data = $result_user->fetch_assoc();
$alamat_user = $user_data['alamat'] ?? '';
$stmt->close();

// Hitung item di keranjang
$cart_query = $conn->prepare("SELECT COUNT(id) as total_items FROM keranjang WHERE id_user = ?");
$cart_query->bind_param("i", $id_user);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
$cart_query->close();

// Ambil semua kategori untuk ditampilkan sebagai tombol filter
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id ASC");

// Tangkap input dari URL
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Bangun query produk secara dinamis dan aman
$sql = "SELECT * FROM produk";
$where_clauses = [];
$params = [];
$types = '';

if ($cari !== '') {
    $where_clauses[] = "nama LIKE ?";
    $params[] = "%" . $cari . "%";
    $types .= 's';
}
if ($kategori_id > 0) {
    $where_clauses[] = "id_kategori = ?";
    $params[] = $kategori_id;
    $types .= 'i';
}
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY id DESC";

$stmt_produk = $conn->prepare($sql);
if ($stmt_produk === false) {
    die("Query Error: " . $conn->error);
}
if (!empty($params)) {
    $stmt_produk->bind_param($types, ...$params);
}
$stmt_produk->execute();
$produk = $stmt_produk->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Fairus Food - Home</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <style>
        .animate-slide-up { animation: slideUp 0.3s ease-out forwards; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .swiper-button-next, .swiper-button-prev { color: #ffffff; }
        .swiper-pagination-bullet-active { background-color: #ffffff; }
        body { padding-top: 150px; }
        @media (min-width: 768px) {
            body { padding-top: 100px; }
        }
    </style>
</head>
<body class="bg-pink-50 pb-20 font-sans">

<header class="fixed top-0 left-0 right-0 z-40 bg-amber-500 rounded-b-3xl pb-6 pt-4 px-5 text-white shadow-lg">
    <!-- Mobile cart -->
    <a href="keranjang.php" class="absolute top-4 right-5 md:hidden p-1">
        <img src="cart.png" alt="Keranjang" class="w-9 h-9">
        <span title="cart-count" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-amber-500 <?= $cart_count > 0 ? '' : 'hidden' ?>">
            <?= $cart_count ?>
        </span>
    </a>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-amber-500 text-2xl shadow">
                👤
            </div>
            <div>
                <span class="font-semibold text-lg truncate max-w-xs block"><?= htmlspecialchars($username) ?></span>
                <a href="peta.php" class="text-xs flex items-center gap-1 text-white/90 hover:text-white transition-opacity">
                    <i class="ph-map-pin-line"></i>
                    <span id="location-text">
                        <?= !empty($alamat_user) ? htmlspecialchars(substr($alamat_user, 0, 35)) . '...' : 'Atur Lokasi Pengiriman' ?>
                    </span>
                </a>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-6">
            <nav class="flex items-center gap-6 text-base font-semibold">
                <a href="index.php" class="text-amber-200 font-bold">Home</a>
                <a href="riwayat.php" class="hover:text-amber-200">Riwayat</a>
<a href="chat.php" class="relative hover:text-amber-200">
    Chat
    <span id="chat-badge-desktop" class="absolute -top-2 -right-4 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">0</span>
</a>

                <a href="profil.php" class="hover:text-amber-200">Profil</a>
            </nav>
            <a href="keranjang.php" class="relative">
                <img src="cart.png" class="w-10 h-10">
                <span title="cart-count" class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-amber-500 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                    <?= $cart_count ?>
                </span>
            </a>
        </div>
    </div>

    <div>
        <h1 class="text-3xl font-bold leading-snug">Lapar?<br>gass cari makan</h1>
    </div>

    <!-- Search bar mobile -->
    <div class="mt-3 md:hidden">
        <form method="GET" class="flex items-center bg-white shadow rounded-xl px-4 py-2">
            <i class="ph-fork-knife text-xl text-amber-500"></i>
            <input type="text" name="cari" placeholder="Kamu mau makan apa?" 
                value="<?= htmlspecialchars($cari) ?>" 
                class="ml-2 w-full outline-none font-medium text-gray-800 text-base bg-transparent" />
            <button type="submit" class="text-gray-500 text-xl ml-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
            </button>
        </form>
    </div>
</header>

<main class="px-4 pt-[85px]">
    <div class="mb-9">
        <div class="mt-7 mb-9">
            <div class="swiper h-52 sm:h-60 w-full rounded-2xl shadow-lg">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <a href="detail_produk.php?id=15" class="block w-full h-full bg-cover bg-center relative">
                            <img src="comboff.png" alt="Promo Paket Combo" class="absolute w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                                <h2 class="text-2xl sm:text-3xl font-bold leading-tight">Paket Combo FF</h2>
                                <p class="text-sm sm:text-base mt-1 mb-3">Nasi + 2 chicken (sayap & dada) + Es Teh</p>
                                <span class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full self-start mb-2">PROMO SPESIAL</span>
                            </div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="detail_produk.php?id=16" class="block w-full h-full bg-cover bg-center relative">
                            <img src="baketff.png" alt="Promo Crispy Chicken" class="absolute w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                                <h2 class="text-2xl sm:text-3xl font-bold leading-tight">Crispy Chicken Bucket</h2>
                                <p class="text-sm sm:text-base mt-1 mb-3">Lebih hemat, lebih puas untuk bersama!</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev hidden md:flex"></div>
                <div class="swiper-button-next hidden md:flex"></div>
            </div>
        </div>

        <div class="mb-10">
            <div class="mb-6 hidden md:block text-center">
                <h3 class="text-gray-800 font-bold text-2xl mb-4">Pilih Menu</h3>
                <form method="GET" class="flex items-center bg-white shadow-lg rounded-xl px-4 py-3 w-full max-w-lg mx-auto">
                    <i class="ph-magnifying-glass text-xl text-gray-500"></i>
                    <input type="text" name="cari" placeholder="Cari menu favoritmu di sini..." value="<?= htmlspecialchars($cari) ?>" class="ml-3 w-full outline-none font-medium text-gray-800 text-base bg-transparent" />
                </form>
            </div>

            <h3 class="text-gray-800 font-bold text-xl mb-4 md:hidden">Pilih Menu</h3>
            
            <div class="w-full">
                <div class="flex items-center space-x-3 overflow-x-auto no-scrollbar pb-4 mb-4">
                    <a href="index.php" class="py-2 px-4 text-sm font-semibold rounded-full flex-shrink-0 transition <?= ($kategori_id == 0) ? 'bg-amber-500 text-white shadow' : 'bg-white text-gray-700 hover:bg-amber-100' ?>">Semua</a>
                    <?php mysqli_data_seek($kategori_list, 0);
                          while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                        <a href="index.php?kategori=<?= $kat['id'] ?><?= !empty($cari) ? '&cari='.urlencode($cari) : '' ?>" class="py-2 px-4 text-sm font-semibold rounded-full flex-shrink-0 transition <?= ($kategori_id == $kat['id']) ? 'bg-amber-500 text-white shadow' : 'bg-white text-gray-700 hover:bg-amber-100' ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></a>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php if ($produk->num_rows > 0): ?>
                    <?php while ($row = $produk->fetch_assoc()) : ?>
                        <div class="group bg-white rounded-2xl p-3 text-center shadow hover:shadow-lg transition cursor-pointer flex flex-col justify-between" onclick="openModal('<?= htmlspecialchars(addslashes($row['nama'])) ?>', '<?= $row['harga'] ?>', '<?= $row['harga_diskon'] ?? 0 ?>', '<?= htmlspecialchars($row['gambar']) ?>', '<?= $row['id'] ?>')">
                            <div class="aspect-square w-full overflow-hidden rounded-xl mb-3">
                                <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1 line-clamp-1" title="<?= htmlspecialchars($row['nama']) ?>"><?= htmlspecialchars($row['nama']) ?></h4>
                                
                                <div class="flex items-baseline justify-center gap-2">
                                    <?php if (isset($row['harga_diskon']) && $row['harga_diskon'] > 0): ?>
                                        <p class="text-red-600 font-bold text-lg">Rp<?= number_format($row['harga_diskon'], 0, ',', '.') ?></p>
                                        <p class="text-gray-500 line-through text-sm">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                                    <?php else: ?>
                                        <p class="text-amber-600 font-bold text-lg">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center text-gray-500 font-semibold py-10">
                        <i class="ph-smiley-sad text-5xl"></i>
                        <p class="mt-2">Menu yang Anda cari tidak ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div id="modalContent" class="bg-white p-4 rounded-2xl w-full max-w-xs text-center shadow-xl relative animate-slide-up">
        <button onclick="closeModal()" class="absolute top-3 right-3 w-8 h-8 bg-gray-100 rounded-full text-gray-600 hover:bg-gray-200">&times;</button>
        <div class="w-full aspect-square rounded-xl overflow-hidden mb-3"><img id="modalImage" src="" alt="Gambar produk" class="w-full h-full object-cover" /></div>
        <h3 id="modalName" class="text-lg font-bold text-gray-800 line-clamp-2"></h3>
        
        <div id="modalPriceContainer" class="my-3 flex flex-col items-center justify-center">
        </div>
        
        <form id="cartForm">
            <div class="flex justify-center items-center gap-4 my-4">
                <button type="button" class="text-white bg-amber-500 w-10 h-10 rounded-full text-lg font-bold hover:bg-amber-600" onclick="updateQty(-1)">-</button>
                <span id="qtyDisplay" class="text-2xl font-bold text-gray-800 w-10 text-center">1</span>
                <button type="button" class="text-white bg-amber-500 w-10 h-10 rounded-full text-lg font-bold hover:bg-amber-600" onclick="updateQty(1)">+</button>
            </div>
            <input type="hidden" id="modalId" name="id_produk" value="">
            <input type="hidden" id="modalQty" name="jumlah" value="1">
            <button id="addToCartBtn" type="submit" class="w-full bg-amber-500 text-white py-3 rounded-full font-bold text-lg hover:bg-amber-600 flex items-center justify-center gap-2">
                <span class="btn-text">+ Keranjang</span>
                <svg class="spinner hidden w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle><path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V8h8a8 8 0 01-8 8H4z"></path></svg>
            </button>
        </form>
    </div>
</div>

<div id="toast" class="hidden fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-6 py-3 rounded-full font-bold shadow-md z-50 transition-opacity duration-500 max-w-xs sm:max-w-md text-center text-sm sm:text-base"></div>

<!-- Updated Mobile Navigation -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 md:hidden">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-around py-2">
            <a href="index.php" class="flex flex-col items-center py-2 text-amber-500">
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
            <a href="profil.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-semibold">Profil</span>
            </a>
        </div>
    </div>
</nav>

<!-- SCRIPT -->
<script>
const swiper = new Swiper('.swiper', {
    loop: true,
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
});

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    const modalImage = document.getElementById('modalImage');
    const modalName = document.getElementById('modalName');
    const modalPriceContainer = document.getElementById('modalPriceContainer'); 
    const modalId = document.getElementById('modalId');
    const qtyDisplay = document.getElementById('qtyDisplay');
    const modalQty = document.getElementById('modalQty');

    window.openModal = (name, price, price_discount, image, id) => {
        modal.classList.remove('hidden');
        modalName.textContent = name;
        modalImage.src = '../uploads/' + image;
        modalId.value = id;
        
        const hargaAsli = parseInt(price);
        const hargaDiskon = parseInt(price_discount);
        const formatRupiah = (angka) => 'Rp ' + Number(angka).toLocaleString('id-ID');
        
        modalPriceContainer.innerHTML = hargaDiskon > 0 && hargaDiskon < hargaAsli
            ? `<p class="text-2xl font-bold text-red-600">${formatRupiah(hargaDiskon)}</p><p class="text-base text-gray-500 line-through">${formatRupiah(hargaAsli)}</p>`
            : `<p class="text-2xl font-bold text-amber-600">${formatRupiah(hargaAsli)}</p>`;

        qtyDisplay.textContent = '1';
        modalQty.value = '1';
    };

    window.closeModal = () => modal.classList.add('hidden');
    window.updateQty = (delta) => {
        let qty = Math.max(1, parseInt(qtyDisplay.textContent) + delta);
        qtyDisplay.textContent = qty;
        modalQty.value = qty;
    };

    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.remove('hidden');
        toast.style.opacity = '1';
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.classList.add('hidden'), 500);
        }, 2000);
    }

    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(res => res.json())
            .then(data => {
                const count = data.count || 0;
                document.querySelectorAll('span[title="cart-count"]').forEach(el => {
                    el.textContent = count;
                    el.classList.toggle('hidden', count <= 0);
                });
            });
    }

    const form = document.getElementById('cartForm');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const spinner = addToCartBtn.querySelector('.spinner');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        spinner.classList.remove('hidden');
        addToCartBtn.disabled = true;

        fetch('tambah_keranjang.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast("Berhasil ditambahkan!");
                updateCartCount();
                closeModal();
            } else {
                showToast("Gagal: " + data.message);
            }
        })
        .finally(() => {
            spinner.classList.add('hidden');
            addToCartBtn.disabled = false;
        });
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
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