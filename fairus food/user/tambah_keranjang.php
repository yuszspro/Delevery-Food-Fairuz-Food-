<?php
session_start();
require 'koneksi.php';

// Set header agar response terbaca sebagai JSON
header('Content-Type: application/json');

// Validasi sesi dan input
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user' || !isset($_POST['id_produk'])) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak atau data tidak lengkap.'
    ]);
    exit;
}

$id_user = $_SESSION['user_id'];
$id_produk = (int)$_POST['id_produk'];
$jumlah = isset($_POST['jumlah']) && (int)$_POST['jumlah'] > 0 ? (int)$_POST['jumlah'] : 1;

if ($id_produk <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'ID Produk tidak valid.'
    ]);
    exit;
}

// Cek apakah produk sudah ada di keranjang
$stmt = $conn->prepare("SELECT id, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?");
$stmt->bind_param("ii", $id_user, $id_produk);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    // Produk sudah ada di keranjang, update jumlahnya
    $row = $result->fetch_assoc();
    $jumlah_baru = $row['jumlah'] + $jumlah;
    $id_keranjang = $row['id'];

    $update_stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $jumlah_baru, $id_keranjang);
    $success = $update_stmt->execute();
    $update_stmt->close();
} else {
    // Produk belum ada, tambahkan baru
    $insert_stmt = $conn->prepare("INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iii", $id_user, $id_produk, $jumlah);
    $success = $insert_stmt->execute();
    $insert_stmt->close();
}

// Respons ke client
if ($success) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Produk berhasil ditambahkan ke keranjang.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan produk ke keranjang.'
    ]);
}

$conn->close();
?>
