<?php
session_start();
require 'koneksi.php';

// 1. Validasi Awal & Keamanan
// Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit('Metode tidak diizinkan.');
}

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403); // Forbidden
    exit('Akses ditolak.');
}

// Pastikan data yang diperlukan ada
if (!isset($_POST['id_keranjang']) || !isset($_POST['jumlah'])) {
    http_response_code(400); // Bad Request
    exit('Data tidak lengkap.');
}

// 2. Ambil dan Amankan Data
$id_keranjang = (int)$_POST['id_keranjang'];
$jumlah = (int)$_POST['jumlah'];
$id_user = $_SESSION['user_id'];

// Pastikan jumlah tidak kurang dari 1
if ($jumlah < 1) {
    $jumlah = 1;
}

// 3. Update Database
// Siapkan query UPDATE dengan prepared statement untuk keamanan
// [PENTING] Query ini menyertakan `id_user = ?` untuk memastikan 
// pengguna hanya bisa mengubah item di keranjangnya sendiri.
$stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ? AND id_user = ?");
if ($stmt === false) {
    http_response_code(500);
    exit('Gagal mempersiapkan statement.');
}

$stmt->bind_param("iii", $jumlah, $id_keranjang, $id_user);

// 4. Eksekusi dan Beri Respons
if ($stmt->execute()) {
    // Periksa apakah ada baris yang benar-benar diperbarui
    if ($stmt->affected_rows > 0) {
        http_response_code(200); // OK
        echo "success";
    } else {
        // Ini bisa terjadi jika item tidak ditemukan atau bukan milik user ini
        http_response_code(404); // Not Found
        echo "Item tidak ditemukan atau Anda tidak memiliki izin.";
    }
} else {
    http_response_code(500); // Internal Server Error
    echo "Gagal memperbarui database.";
}

$stmt->close();
$conn->close();

?>