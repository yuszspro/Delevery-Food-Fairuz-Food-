<?php
// --- Pengaturan Koneksi Database ---
$db_host = "localhost";        // Biasanya "localhost"
$db_user = "znazvumr_fairuz";             // Username database Anda (default XAMPP/Laragon adalah "root")
$db_pass = "ciusstreet2112#";                 // Password database Anda (default XAMPP/Laragon kosong)
$db_name = "znazvumr_fairus_food";      // Nama database yang telah Anda buat

// --- Membuat Koneksi ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Memeriksa Koneksi ---
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error.
    // Sebaiknya jangan tampilkan error detail di lingkungan produksi.
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Mengatur set karakter menjadi utf8mb4 untuk mendukung berbagai macam karakter.
$conn->set_charset("utf8mb4");


date_default_timezone_set('Asia/Jakarta');
?>