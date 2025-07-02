<?php
session_start();
require 'koneksi.php';

// Atur header untuk output JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim respons
function send_json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// Validasi sesi pengguna
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    send_json_response('error', 'Akses ditolak. Silakan login kembali.');
}

// Validasi input
if (!isset($_POST['id_voucher']) || !filter_var($_POST['id_voucher'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    send_json_response('error', 'ID Voucher tidak valid.');
}

$id_user = $_SESSION['user_id'];
$id_voucher = (int)$_POST['id_voucher'];

// Mulai transaksi database untuk keamanan
$conn->begin_transaction();

try {
    // 1. Cek apakah voucher valid dan ada di tabel `vouchers`
    $stmt = $conn->prepare("SELECT id FROM vouchers WHERE id = ?");
    $stmt->bind_param("i", $id_voucher);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Voucher tidak ditemukan.");
    }

    // 2. Cek apakah pengguna sudah pernah mengklaim voucher ini (untuk mencegah duplikat)
    $stmt = $conn->prepare("SELECT id FROM user_vouchers WHERE id_user = ? AND id_voucher = ?");
    $stmt->bind_param("ii", $id_user, $id_voucher);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("Anda sudah memiliki voucher ini.");
    }

    // 3. Jika semua aman, masukkan data ke `user_vouchers`
    $stmt = $conn->prepare("INSERT INTO user_vouchers (id_user, id_voucher, status) VALUES (?, ?, 'tersedia')");
    $stmt->bind_param("ii", $id_user, $id_voucher);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan voucher. Silakan coba lagi.");
    }
    
    // Jika semua query berhasil, commit transaksi
    $conn->commit();
    send_json_response('success', 'Voucher berhasil diklaim!');

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan
    $conn->rollback();
    send_json_response('error', $e->getMessage());
}

$stmt->close();
$conn->close();
?>