<?php
session_start();

if (!isset($_SESSION['user_username']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];
require '../admin/koneksi.php';

$username = $_SESSION['user_username'];

// Ambil data alamat user untuk validasi
$user_stmt = $conn->prepare("SELECT alamat, foto_profil FROM users WHERE id = ?");
$user_stmt->bind_param("i", $id_user);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$alamat_user = $user_data['alamat'] ?? '';
$foto_profil = $user_data['foto_profil'] ?? null;
$inisial = !empty($username) ? strtoupper(substr($username, 0, 1)) : '?';
$user_stmt->close();

// Query untuk mengambil item di keranjang beserta harga diskon
$query = "SELECT k.id AS id_keranjang, p.id AS id_produk, p.nama, p.harga, p.harga_diskon, p.gambar, k.jumlah 
          FROM keranjang k 
          JOIN produk p ON p.id = k.id_produk 
          WHERE k.id_user = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$item_count_in_cart = count($items);


// Ambil voucher yang tersedia untuk user
$voucher_query = "SELECT uv.id AS id_user_voucher, v.kode_voucher, v.deskripsi, v.potongan_harga, v.min_pembelian
                  FROM user_vouchers uv
                  JOIN vouchers v ON uv.id_voucher = v.id
                  WHERE uv.id_user = ?
                    AND uv.status = 'tersedia'
                    AND (v.berlaku_hingga IS NULL OR v.berlaku_hingga >= CURDATE())";
$voucher_stmt = $conn->prepare($voucher_query);
$voucher_stmt->bind_param("i", $id_user);
$voucher_stmt->execute();
$voucher_result = $voucher_stmt->get_result();
$available_vouchers = $voucher_result->fetch_all(MYSQLI_ASSOC);
$voucher_stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Keranjang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .voucher-disabled {
            background-color: #f3f4f6; /* gray-100 */
            cursor: not-allowed;
            opacity: 0.6;
        }
        /* [PERUBAHAN] Menyesuaikan padding body untuk header fixed yang lebih tinggi */
        body {
            padding-top: 96px; /* Jarak untuk header fixed */
        }
    </style>
</head>

<body class="bg-pink-50 font-sans">

    <header class="fixed top-0 inset-x-0 bg-amber-500 p-5 text-white shadow-lg z-30">
        <div class="w-full max-w-screen-xl mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.php" class="mr-4 text-2xl hover:text-amber-200 transition-colors">&larr;</a>
                <h1 class="text-xl font-bold">Keranjang Anda</h1>
            </div>
            <nav class="hidden md:flex items-center gap-6 text-base font-semibold">
                <a href="index.php" class="hover:text-amber-200 transition-colors">Home</a>
                <a href="riwayat.php" class="hover:text-amber-200 transition-colors">Riwayat</a>
                <a href="chat.php" class="hover:text-amber-200 transition-colors">Chat</a>
                <a href="profil.php" class="hover:text-amber-200 transition-colors">Profil</a>
            </nav>
        </div>
    </header>

    <main class="p-4 max-w-screen-xl mx-auto w-full pb-60">
        <form id="checkoutForm" action="checkout.php" method="POST">
            <input type="hidden" name="id_user_voucher" id="id_user_voucher_input" value="">
            <input type="hidden" name="potongan_harga_final" id="potongan_harga_final_input" value="0">
            <input type="hidden" name="kode_voucher_final" id="kode_voucher_final_input" value="">

            <?php if (!empty($items)): ?>
            <div class="bg-white rounded-xl shadow p-4 mb-4 flex items-center">
                <input type="checkbox" id="pilihSemua" class="w-5 h-5 cursor-pointer accent-amber-500" />
                <label for="pilihSemua" class="ml-3 font-semibold text-gray-700 cursor-pointer">Pilih Semua</label>
            </div>
            <?php endif; ?>

            <div id="cart-items-container">
                <?php if (empty($items)): ?>
                    <div class="text-center text-gray-500 pt-16">
                        <img src="https://placehold.co/150x150/FDE68A/F59E0B?text=🛒" class="mx-auto rounded-full" alt="Keranjang Kosong">
                        <h3 class="font-bold text-xl mt-4">Keranjangmu Kosong</h3>
                        <p class="mt-2">Yuk, isi keranjangnya!</p>
                        <a href="index.php" class="mt-6 inline-block bg-amber-500 text-white font-bold px-6 py-2 rounded-full hover:bg-amber-600 transition">Mulai Belanja</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): 
                        $harga_aktif = (isset($item['harga_diskon']) && $item['harga_diskon'] > 0) ? $item['harga_diskon'] : $item['harga'];
                    ?>
                    <div 
                      class="item bg-white rounded-xl shadow p-4 mb-4 flex items-center justify-between relative"
                      data-id="<?= $item['id_keranjang'] ?>"
                      data-harga="<?= $harga_aktif ?>"
                    >
                        <input type="checkbox" name="selected[]" value="<?= $item['id_keranjang'] ?>" class="check w-5 h-5 cursor-pointer accent-amber-500" />
                        <div class="flex items-center flex-grow ml-4">
                            <img src="../uploads/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="w-16 h-16 rounded-lg object-cover mr-4" />
                            <div>
                                <h4 class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($item['nama']) ?></h4>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <?php if (isset($item['harga_diskon']) && $item['harga_diskon'] > 0): ?>
                                        <p class="text-red-600 font-bold text-base">Rp<?= number_format($item['harga_diskon'], 0, ',', '.') ?></p>
                                        <p class="text-gray-500 line-through text-sm">Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <?php else: ?>
                                        <p class="text-gray-600 font-bold text-base">Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center mt-2 space-x-2">
                                    <button type="button" class="btn-minus bg-amber-500 text-white rounded-md w-7 h-7 font-semibold hover:bg-amber-600 transition">-</button>
                                    <input type="number" class="qty-input w-12 text-center bg-pink-50 rounded" name="jumlah_<?= $item['id_keranjang'] ?>" value="<?= $item['jumlah'] ?>" min="1" readonly />
                                    <button type="button" class="btn-plus bg-amber-500 text-white rounded-md w-7 h-7 font-semibold hover:bg-amber-600 transition">+</button>
                                </div>
                            </div>
                        </div>
                        <button
                          type="button"
                          class="delete-btn absolute top-3 right-3 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-lg font-bold hover:bg-red-600 transition"
                          data-id="<?= $item['id_keranjang'] ?>"
                          title="Hapus item"
                        >&times;</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </form>
    </main>
    
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-lg z-20">
        <?php if (!empty($items)): ?>
        <div class="max-w-screen-xl mx-auto">
            <div class="p-4 border-b">
                <div id="voucherDisplay" class="w-full flex justify-between items-center font-semibold">
                    <button type="button" id="pilihVoucherBtn" class="text-amber-600">
                        <span id="voucherBtnText">Gunakan Voucher</span>
                    </button>
                    <button id="hapusVoucherBtn" class="hidden text-red-500 text-sm font-bold">&times; Hapus</button>
                </div>
            </div>
            
            <div class="p-4 flex flex-col">
                <div id="infoDiskon" class="hidden text-sm flex justify-between items-center mb-2">
                    <p>Diskon (<span id="kodeVoucherTerpakai"></span>)</p>
                    <p class="font-semibold text-green-600">-Rp <span id="potonganHargaDisplay">0</span></p>
                </div>
                <div class="text-sm flex justify-between items-center">
                    <p class="text-gray-600">Subtotal</p>
                    <p>Rp <span id="subTotalHarga">0</span></p>
                </div>
                <div class="mt-2 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Total Harga</p>
                        <p class="text-amber-600 font-bold text-xl">Rp <span id="totalHarga">0</span></p>
                    </div>
                    <button type="button" id="checkoutBtn" class="bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 transition shadow-lg px-6 py-3">
                        Lanjutkan
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <nav class="w-full flex justify-around border-t border-gray-200 py-3 select-none bg-white md:hidden">
             <a href="index.php" class="flex flex-col items-center justify-center text-gray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                 <span class="text-sm">Home</span>
             </a>
             <a href="riwayat.php" class="flex flex-col items-center justify-center text-gray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg><span class="text-sm">Riwayat</span></a>
             <a href="chat.php" class="flex flex-col items-center justify-center text-gray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg><span class="text-sm">Chat</span></a>
             <a href="profil.php" class="flex flex-col items-center justify-center text-gray-500 w-1/4 pt-1 hover:text-amber-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg><span class="text-sm">Profil</span></a>
        </nav>
    </div>
    
    <div id="toast" class="fixed left-1/2 bottom-40 transform -translate-x-1/2 min-w-[260px] bg-gray-800 text-white text-center rounded-full px-6 py-3 opacity-0 pointer-events-none shadow-lg z-50 transition-opacity"></div>
    <div id="modalConfirm" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"></div>

    <div id="voucherModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex justify-end z-50">
        <div id="voucherModalContent" class="bg-pink-50 w-full max-w-md h-full flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out">
            <header class="bg-amber-500 p-5 flex items-center text-white font-semibold text-lg shadow-md flex-shrink-0">
                <button id="closeVoucherModal" class="mr-4 text-2xl">&larr;</button>
                <h1>Pilih Voucher</h1>
            </header>
            <main class="flex-1 overflow-y-auto p-4 space-y-3">
                <p class="font-semibold text-gray-700">Voucher Untukmu</p>
                <?php if (empty($available_vouchers)): ?>
                    <p class="text-center text-gray-500 mt-8">Kamu tidak punya voucher saat ini.</p>
                <?php else: ?>
                    <?php foreach ($available_vouchers as $voucher): ?>
                    <label class="voucher-item-label flex items-start bg-white p-4 rounded-lg border-l-4 border-amber-400 shadow-sm cursor-pointer"
                           data-id="<?= $voucher['id_user_voucher'] ?>" data-kode="<?= htmlspecialchars($voucher['kode_voucher']) ?>"
                           data-potongan="<?= $voucher['potongan_harga'] ?>" data-min="<?= $voucher['min_pembelian'] ?>">
                        <input type="radio" name="selected_voucher" class="mt-1 accent-amber-500">
                        <div class="ml-4">
                            <p class="font-bold text-gray-800"><?= htmlspecialchars($voucher['deskripsi']) ?></p>
                            <p class="text-sm text-gray-600 mt-1">Potongan Rp <?= number_format($voucher['potongan_harga']) ?></p>
                            <p class="text-xs text-gray-500 mt-2">Min. belanja Rp <?= number_format($voucher['min_pembelian']) ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
            <footer class="p-4 flex-shrink-0 bg-white shadow-inner grid grid-cols-2 gap-4">
                <button id="batalkanVoucherBtn" class="w-full bg-gray-300 text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-400 transition">Batal</button>
                <button id="gunakanVoucherBtn" class="w-full bg-amber-500 text-white font-bold py-3 rounded-lg hover:bg-amber-600 transition">Gunakan</button>
            </footer>
        </div>
    </div>

<script>
// Kode JavaScript tidak ada yang diubah.
document.addEventListener('DOMContentLoaded', function() {
    const totalHargaElem = document.getElementById('totalHarga');
    const subTotalHargaElem = document.getElementById('subTotalHarga');
    const checkoutForm = document.getElementById('checkoutForm');
    const checkoutButton = document.getElementById('checkoutBtn');
    const isAlamatSet = <?= !empty($alamat_user) ? 'true' : 'false' ?>;
    const itemCheckboxes = document.querySelectorAll('.check');
    const pilihSemuaCheckbox = document.getElementById('pilihSemua');

    let appliedVoucher = { id: null, kode: '', potongan: 0, min: 0 };
    const pilihVoucherBtn = document.getElementById('pilihVoucherBtn');
    const voucherModal = document.getElementById('voucherModal');
    const voucherModalContent = document.getElementById('voucherModalContent');
    const closeVoucherModal = document.getElementById('closeVoucherModal');
    const batalkanVoucherBtn = document.getElementById('batalkanVoucherBtn');
    const gunakanVoucherBtn = document.getElementById('gunakanVoucherBtn');
    const infoDiskon = document.getElementById('infoDiskon');
    const kodeVoucherTerpakai = document.getElementById('kodeVoucherTerpakai');
    const potonganHargaDisplay = document.getElementById('potonganHargaDisplay');
    const idUserVoucherInput = document.getElementById('id_user_voucher_input');
    const potonganHargaFinalInput = document.getElementById('potongan_harga_final_input');
    const kodeVoucherFinalInput = document.getElementById('kode_voucher_final_input');
    const voucherBtnText = document.getElementById('voucherBtnText');
    const hapusVoucherBtn = document.getElementById('hapusVoucherBtn');
    const voucherItemLabels = document.querySelectorAll('.voucher-item-label');

    function showToast(message) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.style.opacity = '1';
        toast.classList.remove('pointer-events-none');
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.classList.add('pointer-events-none'), 500);
        }, 3000);
    }

    function formatRupiah(angka) {
        return String(angka).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateTotal() {
        let subTotal = 0;
        let checkedCount = 0;
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const item = checkbox.closest('.item');
                const harga = parseInt(item.dataset.harga, 10);
                const qty = parseInt(item.querySelector('.qty-input').value, 10);
                subTotal += harga * qty;
                checkedCount++;
            }
        });
        if (pilihSemuaCheckbox) {
            pilihSemuaCheckbox.checked = (checkedCount > 0 && checkedCount === itemCheckboxes.length);
        }

        let total = subTotal;
        let potonganFinal = 0;
        if (appliedVoucher.id && subTotal > 0) {
            if (subTotal >= appliedVoucher.min) {
                total = subTotal - appliedVoucher.potongan;
                potonganFinal = appliedVoucher.potongan;
                infoDiskon.classList.remove('hidden');
                potonganHargaDisplay.innerText = formatRupiah(appliedVoucher.potongan);
                kodeVoucherTerpakai.innerText = appliedVoucher.kode;
            } else {
                infoDiskon.classList.add('hidden');
                if (document.body.contains(pilihVoucherBtn)) {
                    showToast(`Belanja minimal Rp ${formatRupiah(appliedVoucher.min)} untuk voucher ${appliedVoucher.kode}. Voucher dihapus.`);
                }
                resetVoucher();
            }
        } else {
            infoDiskon.classList.add('hidden');
        }

        if (total < 0) total = 0;
        if (totalHargaElem) totalHargaElem.innerText = formatRupiah(total);
        if (subTotalHargaElem) subTotalHargaElem.innerText = formatRupiah(subTotal);
        potonganHargaFinalInput.value = potonganFinal;
        idUserVoucherInput.value = potonganFinal > 0 ? appliedVoucher.id : '';
        kodeVoucherFinalInput.value = potonganFinal > 0 ? appliedVoucher.kode : '';

        updateVoucherView(subTotal);
    }
    
    function updateVoucherView(currentSubtotal) {
        voucherItemLabels.forEach(label => {
            const minPembelian = parseInt(label.dataset.min, 10);
            const radio = label.querySelector('input[type="radio"]');
            if (currentSubtotal < minPembelian) {
                label.classList.add('voucher-disabled');
                radio.disabled = true;
            } else {
                label.classList.remove('voucher-disabled');
                radio.disabled = false;
            }
        });
    }

    function applyVoucher(voucherData) {
        appliedVoucher = voucherData;
        voucherBtnText.textContent = `Voucher Terpakai: ${appliedVoucher.kode}`;
        pilihVoucherBtn.classList.add('font-bold', 'text-gray-800');
        pilihVoucherBtn.classList.remove('text-amber-600');
        hapusVoucherBtn.classList.remove('hidden');
        updateTotal();
        closeVoucherModalFunction();
    }

    function resetVoucher() {
        const selectedRadio = document.querySelector('input[name="selected_voucher"]:checked');
        if (selectedRadio) {
            selectedRadio.checked = false;
        }
        appliedVoucher = { id: null, kode: '', potongan: 0, min: 0 };
        voucherBtnText.textContent = 'Gunakan Voucher';
        pilihVoucherBtn.classList.remove('font-bold', 'text-gray-800');
        pilihVoucherBtn.classList.add('text-amber-600');
        hapusVoucherBtn.classList.add('hidden');
        infoDiskon.classList.add('hidden');
        updateTotal();
    }

    function setupItemEventListeners(item) {
        item.querySelector('.btn-plus')?.addEventListener('click', () => {
            const qtyInput = item.querySelector('.qty-input');
            qtyInput.value = parseInt(qtyInput.value, 10) + 1;
            updateTotal();
            updateJumlahServer(item.dataset.id, qtyInput.value);
        });
        item.querySelector('.btn-minus')?.addEventListener('click', () => {
            const qtyInput = item.querySelector('.qty-input');
            let qty = parseInt(qtyInput.value, 10);
            if (qty > 1) {
                qtyInput.value = --qty;
                updateTotal();
                updateJumlahServer(item.dataset.id, qty);
            }
        });
        item.querySelector('.check')?.addEventListener('change', updateTotal);
        item.querySelector('.delete-btn')?.addEventListener('click', () => {
            const idKeranjang = item.dataset.id;
            if(confirm('Yakin ingin menghapus menu ini dari keranjang?')){
                fetch('hapus_item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id_keranjang=' + encodeURIComponent(idKeranjang)
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        item.remove();
                        updateTotal();
                        showToast('Menu berhasil dihapus');
                        if (document.querySelectorAll('.item').length === 0) location.reload();
                    } else { showToast('Gagal menghapus menu, coba lagi'); }
                }).catch(() => showToast('Terjadi kesalahan jaringan'));
            }
        });
    }

    document.querySelectorAll('.item').forEach(setupItemEventListeners);
    
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function () {
            if (!isAlamatSet) {
                showToast('Atur lokasi pengiriman terlebih dahulu di halaman profil!');
                return;
            }
            const checkedItems = document.querySelectorAll('input[name="selected[]"]:checked');
            if (checkedItems.length === 0) {
                showToast('Pilih menu yang ingin dibeli terlebih dahulu.');
                return;
            }
            checkoutForm.submit();
        });
    }
    
    pilihSemuaCheckbox?.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotal();
    });

    const openVoucherModalFunction = () => {
        const activeRadio = document.querySelector(`.voucher-item-label[data-id="${appliedVoucher.id}"] input[type="radio"]`);
        if (activeRadio) activeRadio.checked = true;
        voucherModal.classList.remove('hidden');
        setTimeout(() => { voucherModalContent.classList.remove('translate-x-full'); }, 10);
    };
    const closeVoucherModalFunction = () => {
        voucherModalContent.classList.add('translate-x-full');
        setTimeout(() => { voucherModal.classList.add('hidden'); }, 300);
    };
    pilihVoucherBtn?.addEventListener('click', openVoucherModalFunction);
    closeVoucherModal?.addEventListener('click', closeVoucherModalFunction);
    voucherModal?.addEventListener('click', (e) => { if (e.target === voucherModal) closeVoucherModalFunction(); });

    gunakanVoucherBtn?.addEventListener('click', () => {
        const selectedRadio = document.querySelector('input[name="selected_voucher"]:checked');
        if (!selectedRadio) { showToast('Pilih salah satu voucher atau klik Batal.'); return; }
        const label = selectedRadio.closest('.voucher-item-label');
        applyVoucher({
            id: label.dataset.id, kode: label.dataset.kode,
            potongan: parseInt(label.dataset.potongan, 10), min: parseInt(label.dataset.min, 10)
        });
        showToast('Voucher berhasil digunakan.');
    });
    batalkanVoucherBtn?.addEventListener('click', () => {
        if (appliedVoucher.id) {
            resetVoucher();
            showToast('Pilihan voucher dibatalkan.');
        }
        closeVoucherModalFunction();
    });
    hapusVoucherBtn?.addEventListener('click', () => {
        resetVoucher();
        showToast('Voucher dihapus.');
    });

    function updateJumlahServer(idKeranjang, jumlah) {
        fetch('update_jumlah.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_keranjang=${encodeURIComponent(idKeranjang)}&jumlah=${encodeURIComponent(jumlah)}`
        }).then(res => { if(!res.ok) console.error("Gagal update jumlah di server."); })
        .catch(err => console.error("Error update jumlah:", err));
    }
    updateTotal();
});
</script>

</body>
</html>