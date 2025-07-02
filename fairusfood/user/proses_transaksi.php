<?php
session_start();
require '../admin/koneksi.php';

// Atur header untuk merespons sebagai JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim respons error dan menghentikan skrip
function send_error($message, $conn = null) {
    if ($conn && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    http_response_code(400); 
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// 1. Validasi Awal yang Ketat
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Metode request tidak valid.');
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    send_error('Akses ditolak. Silakan login kembali.');
}
if (empty($_POST['items_json']) || empty($_POST['keranjang_ids'])) {
    send_error('Tidak ada item yang diproses.');
}

$id_user = $_SESSION['user_id'];
$items_from_post = json_decode($_POST['items_json'], true);
$keranjang_ids = $_POST['keranjang_ids'];
$metode_pembayaran = htmlspecialchars($_POST['metode_pembayaran'] ?? 'Tidak diketahui');
$id_user_voucher = !empty($_POST['id_user_voucher']) ? (int)$_POST['id_user_voucher'] : null;
$jenis_debit = htmlspecialchars($_POST['jenis_debit'] ?? '');

if (json_last_error() !== JSON_ERROR_NONE) send_error('Data item tidak valid.');
if ($metode_pembayaran === 'Debit' && !empty($jenis_debit)) {
    $metode_pembayaran = 'Debit - ' . $jenis_debit;
}

$conn->begin_transaction();

try {
    // 2. Rekalkulasi Subtotal di Server
    $subtotal_server = 0;
    $ids_produk = array_column($items_from_post, 'id_produk');
    if (empty($ids_produk)) throw new Exception("Tidak ada produk yang valid.");
    
    $placeholders = implode(',', array_fill(0, count($ids_produk), '?'));
    $types = str_repeat('i', count($ids_produk));
    $stmt_produk = $conn->prepare("SELECT id, harga, harga_diskon FROM produk WHERE id IN ($placeholders)");
    $stmt_produk->bind_param($types, ...$ids_produk);
    $stmt_produk->execute();
    $produk_db = $stmt_produk->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_produk->close();
    
    $produk_map = array_column($produk_db, null, 'id');

    foreach ($items_from_post as $item_post) {
        $id_produk = $item_post['id_produk'];
        if (!isset($produk_map[$id_produk])) throw new Exception("Produk '{$item_post['nama']}' tidak ditemukan.");
        $produk_info = $produk_map[$id_produk];
        $harga_jual = (isset($produk_info['harga_diskon']) && $produk_info['harga_diskon'] > 0) ? $produk_info['harga_diskon'] : $produk_info['harga'];
        $subtotal_server += $harga_jual * (int)$item_post['jumlah'];
    }
    
    // 3. Rekalkulasi Diskon & Ambil Detail Voucher
    $potongan_harga_server = 0.0;
    $kode_voucher_terpakai = null;
    $id_voucher_final_to_db = null;

    if ($id_user_voucher) {
        $stmt_v = $conn->prepare("SELECT v.id AS id_voucher, v.potongan_harga, v.min_pembelian, v.kode_voucher FROM user_vouchers uv JOIN vouchers v ON uv.id_voucher = v.id WHERE uv.id = ? AND uv.id_user = ? AND uv.status = 'tersedia'");
        $stmt_v->bind_param("ii", $id_user_voucher, $id_user);
        $stmt_v->execute();
        $voucher = $stmt_v->get_result()->fetch_assoc();
        $stmt_v->close();

        if ($voucher && $subtotal_server >= $voucher['min_pembelian']) {
            $potongan_harga_server = (float) $voucher['potongan_harga'];
            $kode_voucher_terpakai = $voucher['kode_voucher'];
            $id_voucher_final_to_db = $voucher['id_voucher']; 
        }
    }
    
    $total_akhir_server = $subtotal_server - $potongan_harga_server;
    if ($total_akhir_server < 0) $total_akhir_server = 0;

    // 4. Simpan Transaksi Utama
    $stmt_t = $conn->prepare("INSERT INTO transaksi (id_user, total_harga, metode_pembayaran, status, potongan_harga, id_voucher_terpakai, kode_voucher_terpakai) VALUES (?, ?, ?, 'Diproses', ?, ?, ?)");
    
    // [PERBAIKAN SANGAT PENTING] Tipe data dan urutan bind_param diperbaiki
    // Urutan: id_user(i), total_harga(d), metode(s), potongan(d), id_voucher(i), kode_voucher(s)
    $stmt_t->bind_param("idsdis", 
        $id_user, 
        $total_akhir_server, 
        $metode_pembayaran, 
        $potongan_harga_server, 
        $id_voucher_final_to_db, 
        $kode_voucher_terpakai
    );
    $stmt_t->execute();
    $id_transaksi_baru = $conn->insert_id;
    if (!$id_transaksi_baru) throw new Exception("Gagal membuat entri transaksi baru.");
    $stmt_t->close();

    // 5. Simpan Detail Transaksi
    $stmt_dt = $conn->prepare("INSERT INTO detail_transaksi (id_transaksi, id_produk, nama_produk_saat_transaksi, jumlah, harga_saat_transaksi) VALUES (?, ?, ?, ?, ?)");
    foreach ($items_from_post as $item) {
        $produk_info = $produk_map[$item['id_produk']];
        $harga_untuk_disimpan = (isset($produk_info['harga_diskon']) && $produk_info['harga_diskon'] > 0) ? $produk_info['harga_diskon'] : $produk_info['harga'];
        $stmt_dt->bind_param("iisid", $id_transaksi_baru, $item['id_produk'], $item['nama'], $item['jumlah'], $harga_untuk_disimpan);
        $stmt_dt->execute();
    }
    $stmt_dt->close();

    // 6. Hapus dari keranjang
    $placeholders_keranjang = implode(',', array_fill(0, count($keranjang_ids), '?'));
    $types_keranjang = str_repeat('i', count($keranjang_ids));
    $stmt_k = $conn->prepare("DELETE FROM keranjang WHERE id_user = ? AND id IN ($placeholders_keranjang)");
    $stmt_k->bind_param("i" . $types_keranjang, $id_user, ...$keranjang_ids);
    $stmt_k->execute();
    $stmt_k->close();

    // 7. Update status voucher
    if ($id_user_voucher && $kode_voucher_terpakai) {
        $stmt_uv = $conn->prepare("UPDATE user_vouchers SET status = 'terpakai' WHERE id = ? AND id_user = ?");
        $stmt_uv->bind_param("ii", $id_user_voucher, $id_user);
        $stmt_uv->execute();
        $stmt_uv->close();
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    send_error("Terjadi kesalahan: " . $e->getMessage(), $conn);
}

$conn->close();
?>