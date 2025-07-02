<?php
session_start();
require 'koneksi.php';

// Validasi: Pastikan pengguna sudah login
if (!isset($_SESSION['user_username']) || $_SESSION['role'] !== 'user') {
    http_response_code(403); // Forbidden
    echo "Akses ditolak";
    exit;
}

// Validasi: Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo "Metode tidak diizinkan";
    exit;
}

// Ambil data dari request
$id_keranjang = isset($_POST['id_keranjang']) ? (int)$_POST['id_keranjang'] : 0;
$id_user = $_SESSION['user_id'];

// Validasi: Pastikan ID Keranjang valid
if ($id_keranjang <= 0) {
    http_response_code(400); // Bad Request
    echo "ID item tidak valid.";
    exit;
}

// Siapkan query DELETE dengan prepared statement untuk keamanan
// Query ini memastikan pengguna hanya bisa menghapus item miliknya sendiri (WHERE id_user = ?)
$stmt = $conn->prepare("DELETE FROM keranjang WHERE id = ? AND id_user = ?");
$stmt->bind_param("ii", $id_keranjang, $id_user);

// Eksekusi query dan kirim respons
if ($stmt->execute()) {
    // Periksa apakah ada baris yang benar-benar terhapus
    if ($stmt->affected_rows > 0) {
        echo "success";
    } else {
        // Ini terjadi jika item tidak ditemukan atau bukan milik user ini
        echo "error: item tidak ditemukan atau bukan milik Anda.";
    }
} else {
    http_response_code(500); // Internal Server Error
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>